<?php
declare (strict_types = 1);
namespace T3G\Elasticorn\Service;

use Elastica\Client;
use Elastica\Index;
use Elastica\Tool\CrossIndex;
use Psr\Log\LoggerInterface;

/**
 * Class IndexService
 *
 */
class IndexService
{
    /**
     * @var \Elastica\Client
     */
    protected $client;

    /**
     * @var Index
     */
    private $index;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \T3G\Elasticorn\Service\ConfigurationService
     */
    private $configurationService;

    /**
     * IndexService constructor.
     *
     * @param \Elastica\Client $client
     * @param \T3G\Elasticorn\Service\ConfigurationService $configurationService
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $indexName
     */
    public function __construct(
        Client $client,
        ConfigurationService $configurationService,
        LoggerInterface $logger,
        string $indexName = null
    ) {
        $this->client = $client;
        $this->logger = $logger;
        $this->configurationService = $configurationService;
        if (null !== $indexName) {
            $this->index = $this->client->getIndex($indexName);
        }
    }

    /**
     * @return Index|null
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Rename an index (creates new index with data from old)
     * CAUTION: All mappings are lost, only data is preserved
     *
     * @param string $newName
     */
    public function renameIndex(string $newName)
    {
        $newIndex = $this->client->getIndex($newName);
        $newIndex->create();
        CrossIndex::reindex($this->index, $newIndex);
        $this->index->delete();
    }

    /**
     * Add all indices found in configuration directory
     * Creates indices with suffixes _a and _b and adds an alias as indexName
     */
    public function initIndices()
    {
        $indexConfigurations = $this->configurationService->getIndexConfigurations();
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
        $config = $this->configurationService->getIndexConfiguration($indexName);
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
     * Re-apply mappings to all indices found in configuration directory
     *
     * @see remap($indexName)
     * @param bool $force
     */
    public function remapAll(bool $force = false)
    {
        $indexConfigurations = $this->configurationService->getIndexConfigurations();
        $indices = array_keys($indexConfigurations);
        foreach ($indices as $indexName) {
            $this->remap($indexName, $force);
        }
    }


    /**
     * Creates configuration directories and files from settings and mappings of an existing index
     *
     * @param string $indexName
     */
    public function createConfigurationFromExistingIndex(string $indexName)
    {
        $settings = $this->index->getSettings();
        $mapping = $this->getMappingForIndex();
        $this->configurationParser->createConfigurationForIndex($indexName, $mapping, $settings->get());
    }

    /**
     * Compare mapping configurations (applied in elasticsearch and configured in file)
     *
     * @param string $indexName
     * @return string
     */
    public function compareMappingConfiguration(string $indexName) : string
    {
        if (!($this->index instanceof Index)) {
           $this->index = $this->client->getIndex($indexName);
        }
        $mapping = $this->getMappingForIndex();

        $this->logger->debug('Get mapping configuration for ' . $indexName);
        $documentTypeConfigurations =
            $this->configurationParser->convertDocumentTypeConfigurationToMappingFromElastica(
                $this->configurationParser->getDocumentTypeConfigurations($indexName)
            );


        return $this->compareConfigurations($mapping, $documentTypeConfigurations);
    }

    /**
     * @return array
     */
    public function getMappingForIndex()
    {
        $this->logger->debug('Get current mapping for ' . $this->index->getName());
        return $this->index->getMapping();
    }


    /**
     * @param $configuration1
     * @param $configuration2
     * @return string
     */
    private function compareConfigurations($configuration1, $configuration2) : string
    {
        if ($configuration1 === $configuration2) {
            $this->logger->info('No difference between configurations.');
		return '';
        } else {
          return $this->compareDocTypeConfiguration($configuration1, $configuration2);        
}
    }


    /**
     * @param $configuration1
     * @param $configuration2
      * @return string
     */
    private function compareDocTypeConfiguration(array $configuration1, array $configuration2) : string
    {
        $result = '';
        $differ = new DiffUtility();
        foreach ($configuration2 as $documentType => $configuration) {
            if (array_key_exists($documentType, $configuration1)) {
                $documentTypeMapping = $configuration1[$documentType]['properties'];
                $configuration = $configuration['properties'];
                ksort($documentTypeMapping);
                ksort($configuration);
                if ($documentTypeMapping === $configuration) {
                    $this->logger->info(
                        'No difference between configurations of document type "' . $documentType . '"'
                    );
                } else {
                    $diff = "Document Type \"$documentType\": \n" .
                            $differ->diff($documentTypeMapping, $configuration);
                    $this->logger->info($diff);
                    $result .= $diff;
                }
            }
        }
        return $result;
    }

    /**
     * Remap an index
     *
     * Drops and recreates the current inactive index, applies mappings and imports data from active index
     * After successfully importing data the alias gets set to the new index
     *
     * @param string $indexName
     * @param bool $force
     * @throws \InvalidArgumentException
     */
    public function remap(string $indexName, bool $force = false)
    {
        if ($this->compareMappingConfiguration($indexName) === '') {
            if (false === $force) {
                $this->logger->info('No difference between configurations, no remapping done');
                return;
            } else {
                $this->logger->info('No difference between configurations but force given, remapping anyway.');
            }
        }
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
        $this->configurationService->applyMapping($indexName, $index);
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
        $indexConfiguration = $this->configurationService->getIndexConfiguration($indexName);
        $this->logger->debug('Deleting index ' . $indexName);
        $index->delete();
        $this->createWithMapping($indexName, $index, $indexConfiguration);
    }
}
