<?php
declare (strict_types=1);

namespace T3G\Elasticorn\Service;

use Elastica\Client;
use Elastica\Index;
use Elastica\Reindex;
use Psr\Log\LoggerInterface;
use T3G\Elasticorn\Configuration\ApplicationConfiguration;

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
     * @param \Elastica\Client                             $client
     * @param \T3G\Elasticorn\Service\ConfigurationService $configurationService
     * @param \Psr\Log\LoggerInterface                     $logger
     * @param string                                       $indexName
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
        $reindex = new Reindex($this->index, $newIndex);
        $reindex->run();
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
        $configuration = $this->configurationService->getIndexConfiguration($indexName);

        $this->createIndex($indexName, $configuration);
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
        $reindex = new Reindex($oldIndex, $newIndex);
        $reindex->run();
    }

    /**
     * Re-apply mappings to all indices found in configuration directory
     *
     * @see remap($indexName)
     *
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
     * @return array
     */
    public function getMappingForIndex()
    {
        $this->logger->debug('Get current mapping for ' . $this->index->getName());
        return $this->index->getMapping();
    }


    /**
     * Remap an index
     *
     * Drops and recreates the current inactive index, applies mappings and imports data from active index
     * After successfully importing data the alias gets set to the new index
     *
     * @param string $indexName
     * @param bool   $force
     *
     * @throws \InvalidArgumentException
     */
    public function remap(string $indexName, bool $force = false)
    {
        if ($this->configurationService->compareMappingConfiguration($indexName) === '') {
            if (false === $force) {
                $this->logger->info('No difference between configurations, no remapping done');
                return;
            } else {
                $this->logger->info('No difference between configurations but force given, remapping anyway.');
            }
        }
        $languages = ApplicationConfiguration::getLanguageConfiguration();
        if (count($languages) > 0) {
            foreach ($languages as $language) {
                $this->remapIndex($indexName, $language);
            }
            $this->logger->debug(
                'Adding alias to ' . $indexName . '_' . $languages[0] . ' from ' . $indexName
            );
            $this->client->getIndex($indexName . '_' . $languages[0])->addAlias($indexName);
        } else {
            $this->remapIndex($indexName);
        }
    }

    /**
     * @param string $indexName
     */
    private function remapIndex(string $indexName, string $language = '')
    {
        $aliasName = $indexName;
        $languageMessage = '';
        if ($language) {
            $aliasName .= '_' . $language;
            $languageMessage = ' in language ' . $language;
        }
        $this->logger->info('Remapping ' . $indexName . $languageMessage);
        $primaryIndexIdentifier = $this->getFullIndexIdentifier($indexName, 'a', $language);
        $indexA = $this->client->getIndex($primaryIndexIdentifier);
        if ($indexA->exists()) {
            $indexB = $this->client->getIndex($this->getFullIndexIdentifier($indexName, 'b', $language));

            if ($indexA->hasAlias($aliasName)) {
                $activeIndex = $indexA;
                $inactiveIndex = $indexB;
            } elseif ($indexB->hasAlias($aliasName)) {
                $activeIndex = $indexB;
                $inactiveIndex = $indexA;
            } else {
                throw new \InvalidArgumentException('No active index with name ' . $indexName . ' found.');
            }

            $this->recreateIndex($indexName, $inactiveIndex, $language);
            $this->logger->debug('Reindexing data with new mapping.');
            $reindex = new Reindex($activeIndex, $inactiveIndex);
            $reindex->run();
            $this->switchAlias($aliasName, $activeIndex, $inactiveIndex);
        } else {
            $configuration = $this->configurationService->getIndexConfigurations();
            $this->createPrimaryIndex($indexName, $configuration, $language);
            $this->createSecondaryIndex($indexName, $configuration, $language);
        }
    }

    /**
     * @param string          $indexName
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
     * @param array  $configuration
     *
     * @throws \InvalidArgumentException
     */
    private function createIndex(string $indexName, array $configuration)
    {
        $this->logger->info('Creating index ' . $indexName);
        $index = $this->client->getIndex($indexName);
        $languages = ApplicationConfiguration::getLanguageConfiguration();
        if (!$index->exists()) {
            if (count($languages) > 0) {
                foreach ($languages as $language) {
                    $this->createPrimaryIndex($indexName, $configuration, $language);
                    $this->createSecondaryIndex($indexName, $configuration, $language);
                }
                $primaryIndex = $this->client->getIndex($indexName . '_' . $languages[0] . '_a');
                $primaryIndex->addAlias($indexName);
            } else {
                $this->createPrimaryIndex($indexName, $configuration);
                $this->createSecondaryIndex($indexName, $configuration);
            }
        } else {
            throw new \InvalidArgumentException('Index ' . $indexName . ' already exists.');
        }
    }

    private function createPrimaryIndex(string $indexName, array $configuration, string $language = '')
    {
        $index = $this->client->getIndex($this->getFullIndexIdentifier($indexName, 'a', $language));
        $this->createWithMapping($indexName, $index, $configuration, $language);
        $index->addAlias($indexName . ($language ? '_' . $language : ''));
    }

    /**
     * @param string $indexName
     * @param array  $configuration
     * @param string $language
     */
    private function createSecondaryIndex(string $indexName, array $configuration, string $language = '')
    {
        $index = $this->client->getIndex($this->getFullIndexIdentifier($indexName, 'b', $language));
        $this->createWithMapping($indexName, $index, $configuration, $language);
    }

    private function getFullIndexIdentifier(string $indexName, string $suffix = '', string $language = '')
    {
        return $indexName . ($language ? '_' . $language : '') . ($suffix ? '_' . $suffix : '');
    }

    /**
     * @param string          $indexName
     * @param \Elastica\Index $index
     * @param                 $indexConfiguration
     */
    private function createWithMapping(
        string $indexName,
        Index $index,
        array $indexConfiguration,
        string $language = ''
    ) {
        $index->create(['settings' => $indexConfiguration]);
        $this->logger->debug('Creating index ' . $indexName);
        $this->configurationService->applyMapping($indexName, $index, $language);
    }

    /**
     * @param string $indexName
     * @param Index  $index
     * @param string $language
     */
    private function recreateIndex(string $indexName, Index $index, string $language)
    {
        $indexConfiguration = $this->configurationService->getIndexConfiguration($indexName);
        $this->logger->debug('Deleting index ' . $indexName);
        $index->delete();
        $this->createWithMapping($indexName, $index, $indexConfiguration, $language);
    }
}
