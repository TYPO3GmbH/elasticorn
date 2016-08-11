<?php
namespace T3G\Elasticorn\Utility;

use Elastica\Client;
use Elastica\Index;
use Elastica\Tool\CrossIndex;
use Elastica\Type\Mapping;
use Psr\Log\LoggerInterface;

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
     * @todo finish this
     * @param string $indexName
     */
    public function compareMappingConfiguration(string $indexName)
    {
        $index = $this->client->getIndex($indexName);
        $mapping = $index->getMapping();
        $documentTypeConfigurations = $this->configurationParser->getDocumentTypeConfigurations($indexName);
    }


    /**
     * Remap an index
     *
     * Drops and recreates the current inactive index, applies mappings and imports data from active index
     * After successfully importing data the alias gets set to the new index
     *
     * @param string $indexName
     * @throws \Exception
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
            throw new \Exception('no active index with name ' . $indexName . ' found.');
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
     * @throws \Exception
     */
    private function createIndex(string $indexName, array $configuration)
    {
        $this->logger->info('Creating index ' . $indexName);
        $index = $this->client->getIndex($indexName);
        if (!$index->exists()) {
            $this->createIndexWithSuffix($indexName, '_a', true, $configuration);
            $this->createIndexWithSuffix($indexName, '_b', false, $configuration);
        } else {
            throw new \Exception('Index ' . $indexName . ' already exists.');
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