<?php
declare (strict_types = 1);
namespace T3G\Elasticorn\Service;

use Elastica\Client;
use Elastica\Index;
use Elastica\Mapping;
use Psr\Log\LoggerInterface;
use T3G\Elasticorn\Utility\ConfigurationParser;
use T3G\Elasticorn\Utility\DiffUtility;

/**
 * Class ConfigurationService
 *
 */
class ConfigurationService
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
     * ConfigurationService constructor.
     *
     * @param \Elastica\Client $client
     * @param \T3G\Elasticorn\Utility\ConfigurationParser $configurationParser
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Client $client,
        ConfigurationParser $configurationParser,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->configurationParser = $configurationParser;
        $this->logger = $logger;
    }

    /**
     * @param string $indexName
     * @param Index $index
     */
    public function applyMapping(string $indexName, Index $index, string $language = '')
    {
        $documentTypeConfigurations = $this->configurationParser->getDocumentTypeConfigurations($indexName, $language);
        $this->logger->debug('Loading mapping for ' . $indexName);
        foreach ($documentTypeConfigurations as $documentType => $configuration) {
            $mapping = new Mapping($configuration);
            $mapping->send($index);
            $this->logger->debug('Applying mapping for ' . $documentType);
        }
    }

    /**
     * Compare mapping configurations (applied in elasticsearch and configured in file)
     *
     * @param string $indexName
     * @param Index $index
     * @return string
     */
    public function compareMappingConfiguration(string $indexName, Index $index = null) : string
    {
        if ($index === null) {
            $index = $this->client->getIndex($indexName);
        }
        $mapping = $index->getMapping();
        $this->logger->debug('Get mapping configuration for ' . $indexName);
        $documentTypeConfigurations =
            $this->configurationParser->convertDocumentTypeConfigurationToMappingFromElastica(
                $this->configurationParser->getDocumentTypeConfigurations($indexName)
            );

        return $this->compareConfigurations($mapping, $documentTypeConfigurations);
    }

    /**
     * Creates configuration directories and files from settings and mappings of an existing index
     *
     * @param string $indexName
     * @param Index $index
     */
    public function createConfigurationFromExistingIndex(string $indexName, Index $index)
    {
        $settings = $index->getSettings();
        $mapping = $index->getMapping();
        $cleanSettings = $this->configurationParser->cleanSettingsArray($settings->get());
        $this->configurationParser->createConfigurationForIndex($indexName, $mapping, $cleanSettings);
    }

    /**
     * @param string $indexName
     *
     * @return array
     */
    public function getIndexConfiguration(string $indexName) : array
    {
        return $this->configurationParser->getIndexConfiguration($indexName);
    }

    /**
     * @return array
     */
    public function getIndexConfigurations() : array
    {
        return $this->configurationParser->getIndexConfigurations();
    }

    /**
     * @param $configuration1
     * @param $configuration2
     * @return string
     */
    private function compareConfigurations($configuration1, $configuration2) : string
    {
        $result = '';
        if ($configuration1 === $configuration2) {
            $this->logger->info('No difference between configurations.');
        } else {
            $result = $this->compareDocTypeConfiguration($configuration1, $configuration2);
        }
        return $result;
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
}
