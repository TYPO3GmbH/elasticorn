<?php
declare(strict_types = 1);
namespace T3G\Elasticorn\Utility;

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
     * Creates configuration directories and files from settings and mappings of an existing index
     *
     * @param string $indexName
     */
    public function createConfigurationFromExistingIndex(string $indexName)
    {
        $index = $this->client->getIndex($indexName);
        $settings = $index->getSettings();
        $mapping = $this->getMappingForIndex($index);
        $this->configurationParser->createConfigurationForIndex($indexName, $mapping, $settings->get());
    }

    /**
     * Rename an index (creates new index with data from old)
     * CAUTION: All mappings are lost, only data is preserved
     *
     * @param string $indexName
     * @param string $newName
     */
    public function renameIndex(string $indexName, string $newName)
    {
        $index = $this->client->getIndex($indexName);
        $newIndex = $this->client->getIndex($newName);
        $newIndex->create();
        CrossIndex::reindex($index, $newIndex);
        $index->delete();
    }

    /**
     * Add all indices found in configuration directory
     * Creates indices with suffixes _a and _b and adds an alias as indexName
     */
    public function initIndices()
    {
        $indexConfigurations = $this->configurationParser->getIndexConfigurations();
        foreach ($indexConfigurations as $indexName => $configuration) {
            $this->createIndex($indexName, $configuration);
        }
    }

    /**
     * Initializes a single index from config files
     *
     * @param string $indexName
     */
    public function initIndex(string $indexName)
    {
        $config = $this->configurationParser->getIndexConfiguration($indexName);
        $this->createIndex($indexName, $config);
    }

    /**
     * Copy data from oldIndexName to newIndexName
     *
     * @param string $oldIndexName
     * @param string $newIndexName
     */
    public function copyData(string $oldIndexName, string $newIndexName)
    {
        $oldIndex = $this->client->getIndex($oldIndexName);
        $newIndex = $this->client->getIndex($newIndexName);
        CrossIndex::reindex($oldIndex, $newIndex);
    }

    /**
     * Compare mapping configurations (applied in elasticsearch and configured in file)
     *
     * @param string $indexName
     */
    public function compareMappingConfiguration(string $indexName)
    {
        $index = $this->client->getIndex($indexName);
        $mapping = $this->getMappingForIndex($index);

        $this->logger->debug('Get mapping configuration for ' . $indexName);
        $documentTypeConfigurations =
            $this->configurationParser->convertDocumentTypeConfigurationToMappingFromElastica(
                $this->configurationParser->getDocumentTypeConfigurations($indexName)
            );


        $this->compareConfigurations($mapping, $documentTypeConfigurations);
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
     * @param string $indexName
     */
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
     * @param $configuration1
     * @param $configuration2
     */
    private function compareConfigurations($configuration1, $configuration2)
    {
        if ($configuration1 === $configuration2) {
            $this->logger->info('no difference between configurations.');
        } else {
            $this->compareDocTypeConfiguration($configuration1, $configuration2);
        }
    }

    /**
     * @param $configuration1
     * @param $configuration2
     */
    private function compareDocTypeConfiguration(array $configuration1, array $configuration2)
    {
        $differ = new DiffUtility();
        foreach ($configuration2 as $documentType => $configuration) {
            if (array_key_exists($documentType, $configuration1)) {
                $documentTypeMapping = $configuration1[$documentType]['properties'];
                $configuration = $configuration['properties'];
                ksort($documentTypeMapping);
                ksort($configuration);
                if ($documentTypeMapping === $configuration) {
                    $this->logger->info(
                        'no difference between configurations of document type "' . $documentType . '"'
                    );
                } else {
                    $diff = "Document Type \"$documentType\": \n" .
                            $differ->diff($documentTypeMapping, $configuration);
                    $this->logger->info($diff);
                }
            }
        }
    }

    /**
     * @param string $indexName
     * @param Index $index
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
     * @param \Elastica\Index $index
     * @return array
     */
    private function getMappingForIndex(Index $index)
    {
        $this->logger->debug('Get current mapping for ' . $index->getName());
        return $index->getMapping();
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
}
