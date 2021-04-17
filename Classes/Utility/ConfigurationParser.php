<?php

declare(strict_types=1);

/*
 * This file is part of the package t3g/elasticorn.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\Elasticorn\Utility;

use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ConfigurationParser.
 */
class ConfigurationParser
{
    /**
     * Name of index configuration file.
     */
    const INDEX_CONF_FILENAME = 'IndexConfiguration.yaml';
    const MAPPING_FILENAME = '/Mapping.yaml';

    /**
     * @var string
     */
    private $configFolder;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * ConfigurationParser constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param string                   $configPath
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Get configurations for all indices.
     *
     * @return array
     */
    public function getIndexConfigurations(): array
    {
        $indices = [];
        $configPath = getenv('configurationPath');
        $this->logger->info('Loading configuration from ' . $configPath);
        $filesInFolder = $this->getFilesInFolder($configPath);
        $this->logger->debug('Found: ' . var_export($filesInFolder, true));
        foreach ($filesInFolder as $indexName) {
            $subFolder = $configPath . $indexName;
            if (true === is_dir($subFolder) && file_exists($subFolder . '/' . self::INDEX_CONF_FILENAME)) {
                $indices[strtolower($indexName)] = $this->getIndexConfiguration($indexName);
            }
        }

        return $indices;
    }

    /**
     * Get Configuration for specified index1.
     *
     * @param string $indexName
     *
     * @return array
     */
    public function getIndexConfiguration(string $indexName): array
    {
        $filePath = $this->getIndexDirectory($indexName) . '/' . self::INDEX_CONF_FILENAME;

        $config = $this->getConfig($filePath);

        return is_array($config) ? $config : [];
    }

    /**
     * Get all document type configurations for index.
     *
     * @param string $indexName
     * @param string $language
     *
     * @return array
     */
    public function getMapping(string $indexName, string $language = ''): array
    {
        $directory = $this->getIndexDirectory($indexName);
        $filePath = $directory . self::MAPPING_FILENAME;
        $pathInfo = pathinfo($filePath);
        if ('yaml' === $pathInfo['extension']) {
            $mapping = $this->getConfig($filePath);
        }
        if ('' !== $language) {
            $mapping = $this->addAnalyzerToConfig($language, $mapping);
        }

        return $mapping;
    }

    /**
     * @param string $language
     * @param        $mapping
     *
     * @return mixed
     */
    private function addAnalyzerToConfig(string $language, $mapping)
    {
        foreach ($mapping as $name => $config) {
            if (array_key_exists('analyzer', $config)) {
                $mapping[$name]['analyzer'] = $language;
            }
        }

        return $mapping;
    }

    /**
     * @param string $indexName
     *
     * @return string
     */
    private function getDocumentTypesDirectory(string $indexName): string
    {
        return $this->getIndexDirectory($indexName) . '/DocumentTypes/';
    }

    /**
     * @param string $directory
     *
     * @return array
     */
    private function getFilesInFolder(string $directory): array
    {
        return array_diff(scandir($directory, 0), ['..', '.']);
    }

    /**
     * @param string $indexName
     *
     * @return string
     */
    private function getIndexDirectory(string $indexName): string
    {
        $indexDir = getenv('configurationPath') . $indexName;
        if (!file_exists($indexDir)) {
            throw new \InvalidArgumentException('Configuration directory ' . $indexDir . ' for index ' . $indexName . ' does not exist.', 666);
        }

        return $indexDir;
    }

    /**
     * @param string $filePath
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    private function getConfig(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException('No configuration found at ' . $filePath, 666);
        }
        $configFileContent = file_get_contents($filePath);
        $config = Yaml::parse($configFileContent);

        return is_array($config) ? $config : [];
    }

    public function createConfigurationForIndex(string $indexName, array $mapping, array $settings)
    {
        $this->createConfigurationDirectories($indexName);
        $indexDirectory = $this->getIndexDirectory($indexName);
        file_put_contents($indexDirectory . '/IndexConfiguration.yaml', Yaml::dump($settings));
        file_put_contents(
            $indexDirectory . '/Mapping.yaml',
            Yaml::dump($mapping['properties'] ?? '')
        );
    }

    /**
     * Remove array keys from settings the remote server returns
     * but we don't want in the config file.
     *
     * @param array $settings
     *
     * @return array
     */
    public function cleanSettingsArray(array $settings): array
    {
        $unwantedSettings = [
            'creation_date',
            'uuid',
            'version',
            'provided_name',
        ];

        return array_diff_key($settings, array_flip($unwantedSettings));
    }

    private function createConfigurationDirectories(string $indexName)
    {
        $configPath = getenv('configurationPath');
        mkdir($configPath . $indexName);
    }
}
