<?php
namespace T3G\Elasticorn;

use Elastica\Client;
use Elastica\Index;
use Elastica\Tool\CrossIndex;
use Elastica\Type\Mapping;

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
     * @var \T3G\Elasticorn\ConfigurationParser
     */
    private $configurationParser;

    /**
     * IndexUtility constructor.
     *
     * @param \T3G\Elasticorn\ConfigurationParser|null $configurationParser
     */
    public function __construct(ConfigurationParser $configurationParser = null)
    {
        $this->client = new Client();
        $this->configurationParser = $configurationParser ?: new ConfigurationParser();
    }

    /**
     *
     */
    public function initIndices()
    {
        $indexConfigurations = $this->configurationParser->getIndexConfigurations();
        foreach ($indexConfigurations as $indexName => $configuration) {
            $this->createIndex($indexName, $configuration);
        }
    }

    /**
     *
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
     * @param string $indexName
     * @throws \Exception
     */
    public function remap(string $indexName)
    {
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
        CrossIndex::reindex($activeIndex, $inactiveIndex);
        $this->switchAlias($indexName, $activeIndex, $inactiveIndex);

    }

    /**
     * @param string $indexName
     * @param \Elastica\Index $activeIndex
     * @param \Elastica\Index $inactiveIndex
     */
    private function switchAlias(string $indexName, Index $activeIndex, Index $inactiveIndex)
    {
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
        $index = $this->client->getIndex($indexName);
        if (!$index->exists()) {
            $this->createIndexWithSuffix($indexName, '_a', true, $configuration);
            $this->createIndexWithSuffix($indexName, '_b', false, $configuration);
        } else {
            throw new \Exception('Index already exists.');
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

        $index->create($configuration);
        $this->applyMapping($indexName, $index);

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
        $index->delete();
        $indexConfiguration = $this->configurationParser->getIndexConfiguration($indexName);
        $index->create($indexConfiguration);
        $this->applyMapping($indexName, $index);
    }

    /**
     * @param string $indexName
     * @param \Elastica\Index $index
     */
    private function applyMapping(string $indexName, Index $index)
    {
        $documentTypeConfigurations = $this->configurationParser->getDocumentTypeConfigurations($indexName);
        foreach ($documentTypeConfigurations as $documentType => $configuration) {
            $type = $index->getType($documentType);
            $mapping = new Mapping();
            $mapping->setType($type);
            $mapping->setProperties($configuration);
            $mapping->send();
        }

    }
}