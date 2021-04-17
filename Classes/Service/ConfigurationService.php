<?php

declare(strict_types=1);

/*
 * This file is part of the package t3g/elasticorn.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\Elasticorn\Service;

use Elastica\Client;
use Elastica\Index;
use Elastica\Mapping;
use Psr\Log\LoggerInterface;
use T3G\Elasticorn\Utility\ConfigurationParser;
use T3G\Elasticorn\Utility\DiffUtility;

/**
 * Class ConfigurationService.
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
     * @param \Elastica\Client                            $client
     * @param \T3G\Elasticorn\Utility\ConfigurationParser $configurationParser
     * @param \Psr\Log\LoggerInterface                    $logger
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
     * @param Index  $index
     */
    public function applyMapping(string $indexName, Index $index, string $language = '')
    {
        $mappingConfiguration = $this->configurationParser->getMapping($indexName, $language);
        $this->logger->debug('Loading mapping for ' . $indexName);
        if (count($mappingConfiguration) > 0) {
            $mapping = new Mapping($mappingConfiguration);
            $mapping->send($index);
            $this->logger->debug('Applying mapping for ' . $indexName);
        } else {
            $this->logger->debug('No mapping available for ' . $indexName);
        }
    }

    /**
     * Compare mapping configurations (applied in elasticsearch and configured in file).
     *
     * @param string $indexName
     * @param Index  $index
     *
     * @return string
     */
    public function compareMappingConfiguration(string $indexName, Index $index = null): string
    {
        if (null === $index) {
            $index = $this->client->getIndex($indexName);
        }
        $mapping = $index->getMapping();
        $this->logger->debug('Get mapping configuration for ' . $indexName);
        $mappingConfiguration['properties'] = $this->configurationParser->getMapping($indexName);

        return $this->compareConfigurations($mapping, $mappingConfiguration);
    }

    /**
     * Creates configuration directories and files from settings and mappings of an existing index.
     *
     * @param string $indexName
     * @param Index  $index
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
    public function getIndexConfiguration(string $indexName): array
    {
        return $this->configurationParser->getIndexConfiguration($indexName);
    }

    /**
     * @return array
     */
    public function getIndexConfigurations(): array
    {
        return $this->configurationParser->getIndexConfigurations();
    }

    /**
     * @param $configuration1
     * @param $configuration2
     *
     * @return string
     */
    private function compareConfigurations($configuration1, $configuration2): string
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
     *
     * @return string
     */
    private function compareDocTypeConfiguration(array $configuration1, array $configuration2): string
    {
        $result = '';
        $differ = new DiffUtility();
        $configuration1Props = $configuration1['properties'] ?? [];
        $configuration2Props = $configuration2['properties'] ?? [];
        ksort($configuration1Props);
        ksort($configuration2Props);
        if ($configuration1Props === $configuration2Props) {
            $this->logger->info(
                'No difference between configurations'
            );
        } else {
            $diff = "Diff: \n" .
                    $differ->diff($configuration1Props, $configuration2Props);
            $this->logger->info($diff);
            $result .= $diff;
        }

        return $result;
    }
}
