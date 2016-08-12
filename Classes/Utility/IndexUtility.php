<?php
declare(strict_types = 1);
namespace T3G\Elasticorn\Utility;

use cogpowered\FineDiff\Diff;
use cogpowered\FineDiff\Granularity\Word;
use Elastica\Client;
use Elastica\Index;
use Elastica\Tool\CrossIndex;
use Elastica\Type\Mapping;
use Psr\Log\LoggerInterface;
use SebastianBergmann\Diff\Differ;

/**
 * Class IndexUtility
 *
 * @package T3G\Elasticorn
 */
class IndexUtility
{
    /**
     * @var \Elastica\Client
     */
    protected $client;

    /**
     * @var \T3G\Elasticorn\Utility\ConfigurationParser
     */
    private $configurationParser;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * IndexUtility constructor.
     *
     * @param \Elastica\Client $client
     * @param \T3G\Elasticorn\Utility\ConfigurationParser $configurationParser
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(Client $client, ConfigurationParser $configurationParser, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->configurationParser = $configurationParser;
        $this->logger = $logger;
    }

    /**
     * Add all indices found in configuration directory
     * Creates indices with suffixes _a and _b and adds an alias as indexName
     *
     * @throws \Exception
     */
    public function initIndices()
    {
        $indexConfigurations = $this->configurationParser->getIndexConfigurations();
        foreach ($indexConfigurations as $indexName => $configuration) {
            $this->createIndex($indexName, $configuration);
        }
    }

    /**
     * Re-apply mappings to all indices found in configuration directory
     *
     * @see remap($indexName)
     */
    public function remapAll()
    {
        $indexConfigurations = $this->configurationParser->getIndexConfigurations();
        $indices = array_keys($indexConfigurations);
        foreach ($indices as $indexName) {
            $this->remap($indexName);
        }
    }

    /**
     * Compare mapping configurations (applied in elasticsearch and configured in file)
     *
     * @param string $indexName
     */
    public function compareMappingConfiguration(string $indexName)
    {
        $index = $this->client->getIndex($indexName);
        $this->logger->debug('Get current mapping for ' . $indexName);
        $mapping = $index->getMapping();

        $this->logger->debug('Get mapping configuration for ' . $indexName);
        $documentTypeConfigurations =
            $this->configurationParser->convertDocumentTypeConfigurationToMappingFromElastica(
                $this->configurationParser->getDocumentTypeConfigurations($indexName)
            );


        $diffUtility = new DiffUtility();
        if ($mapping === $documentTypeConfigurations) {
            $this->logger->info('no difference between configurations.');
        } else {
            foreach ($documentTypeConfigurations as $documentType => $configuration) {
                 if (isset($mapping[$documentType])) {
                    if ($mapping[$documentType] === $configuration) {
                        $this->logger->info('no difference between configurations of document type "' . $documentType . '"');
                    } else {
                        $diff = "Document Type \"$documentType\": \n" .
                                $diffUtility->diff($mapping[$documentType]['properties'], $configuration['properties']);
                        $this->logger->info($diff);
                    }
                }
            }
        }
    }

    public function showMapping(string $indexName)
    {
        $index = $this->client->getIndex($indexName);
        $mapping = $index->getMapping();
        $this->logger->info('Current mapping:' . "\n" . print_r($mapping, true));
    }


    /**
     * Remap an index
     *
     * Drops and recreates the current inactive index, applies mappings and imports data from active index
     * After successfully importing data the alias gets set to the new index
     *
     * @param string $indexName
     * @throws \InvalidArgumentException
     */
    public function remap(string $indexName)
    {
        $this->logger->info('Remapping ' . $indexName);
        $indexA = $this->client->getIndex($indexName . '_a');
        $indexB = $this->client->getIndex($indexName . '_b');

        if ($indexA->hasAlias($indexName)) {
            $activeIndex = $indexA;
            $inactiveIndex = $indexB;
        } elseif ($indexB->hasAlias($indexName)) {
            $activeIndex = $indexB;
            $inactiveIndex = $indexA;
        } else {
            throw new \InvalidArgumentException('no active index with name ' . $indexName . ' found.');
        }

        $this->recreateIndex($indexName, $inactiveIndex);
        $this->logger->debug('Reindexing data with new mapping.');
        CrossIndex::reindex($activeIndex, $inactiveIndex);
        $this->switchAlias($indexName, $activeIndex, $inactiveIndex);

    }

    /**
     * @param string $indexName
     * @param \Elastica\Index $index
     * @param $indexConfiguration
     */
    private function createWithMapping(string $indexName, Index $index, $indexConfiguration)
    {
        $index->create($indexConfiguration);
        $this->logger->debug('Creating index ' . $indexName);
        $this->applyMapping($indexName, $index);
    }

    /**
     * @param string $indexName
     * @param \Elastica\Index $activeIndex
     * @param \Elastica\Index $inactiveIndex
     */
    private function switchAlias(string $indexName, Index $activeIndex, Index $inactiveIndex)
    {
        $this->logger->debug('Switching alias from ' . $activeIndex->getName() . ' to ' . $inactiveIndex->getName());
        $activeIndex->removeAlias($indexName);
        $inactiveIndex->addAlias($indexName);
    }

    /**
     * @param string $indexName
     * @param array $configuration
     * @throws \InvalidArgumentException
     */
    private function createIndex(string $indexName, array $configuration)
    {
        $this->logger->info('Creating index ' . $indexName);
        $index = $this->client->getIndex($indexName);
        if (!$index->exists()) {
            $this->createIndexWithSuffix($indexName, '_a', true, $configuration);
            $this->createIndexWithSuffix($indexName, '_b', false, $configuration);
        } else {
            throw new \InvalidArgumentException('Index ' . $indexName . ' already exists.');
        }
    }

    /**
     * @param string $indexName
     * @param string $suffix
     * @param bool $alias
     * @param array $configuration
     */
    private function createIndexWithSuffix(string $indexName, string $suffix, bool $alias, array $configuration)
    {
        $index = $this->client->getIndex($indexName . $suffix);

        $this->createWithMapping($indexName, $index, $configuration);

        if (true === $alias) {
            $index->addAlias($indexName);
        }
    }

    /**
     * @param string $indexName
     * @param Index $index
     */
    private function recreateIndex(string $indexName, Index $index)
    {
        $indexConfiguration = $this->configurationParser->getIndexConfiguration($indexName);
        $this->logger->debug('Deleting index ' . $indexName);
        $index->delete();
        $this->createWithMapping($indexName, $index, $indexConfiguration);
    }

    /**
     * @param string $indexName
     * @param \Elastica\Index $index
     */
    private function applyMapping(string $indexName, Index $index)
    {
        $documentTypeConfigurations = $this->configurationParser->getDocumentTypeConfigurations($indexName);
        $this->logger->debug('Loading mapping for ' . $indexName);
        foreach ($documentTypeConfigurations as $documentType => $configuration) {
            $type = $index->getType($documentType);
            $mapping = new Mapping();
            $mapping->setType($type);
            $mapping->setProperties($configuration);
            $mapping->send();
            $this->logger->debug('Applying mapping for ' . $documentType);
        }

    }
}