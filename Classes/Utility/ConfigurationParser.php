<?php
declare(strict_types=1);

namespace T3G\Elasticorn\Utility;

use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ConfigurationParser
 *
 * @package T3G\Elasticorn
 */
class ConfigurationParser
{
    /**
     * Name of index configuration file
     */
    const INDEX_CONF_FILENAME = 'IndexConfiguration.yaml';

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
     * Get configurations for all indices
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
            if (is_dir($subFolder) === true && file_exists($subFolder . '/' . self::INDEX_CONF_FILENAME)) {
                $indices[strtolower($indexName)] = $this->getIndexConfiguration($indexName);
            }
        }
        return $indices;
    }

    /**
     * Get Configuration for specified index1
     *
     * @param string $indexName
     *
     * @return array
     */
    public function getIndexConfiguration(string $indexName): array
    {
        $filePath = $this->getIndexDirectory($indexName) . '/' . self::INDEX_CONF_FILENAME;
        return $this->getConfig($filePath);
    }

    /**
     * Get all document type configurations for index
     *
     * @param string $indexName
     * @param string $language
     *
     * @return array
     */
    public function getDocumentTypeConfigurations(string $indexName, string $language = ''): array
    {
        $configs = [];
        $directory = $this->getDocumentTypesDirectory($indexName);
        $configFiles = $this->getFilesInFolder($directory);
        foreach ($configFiles as $configFile) {
            $filePath = $directory . $configFile;
            $pathInfo = pathinfo($filePath);
            if ($pathInfo['extension'] === 'yaml') {
                $configs[$pathInfo['filename']] = $this->getConfig($filePath);
            }
        }
        if ($language !== '') {
            $configs = $this->addAnalyzerToConfig($language, $configs);
        }
        return $configs;
    }

    /**
     * Convert document type configuration to mapping as returned from elastica (for comparison for example)
     *
     * @param array $configurations
     *
     * @return array
     */
    public function convertDocumentTypeConfigurationToMappingFromElastica(array $configurations): array
    {
        $mappings = [];
        foreach ($configurations as $index => $configuration) {
            $mappings[$index]['properties'] = $configuration;
        }
        return $mappings;
    }

    /**
     * Get configuration for document type in index
     *
     * @param string $indexName
     * @param string $documentType
     *
     * @return array
     */
    public function getDocumentTypeConfiguration(string $indexName, string $documentType): array
    {
        $filePath = $this->getDocumentTypesDirectory($indexName) . strtolower($documentType) . '.yaml';
        return $this->getConfig($filePath);
    }

    /**
     * @param string $language
     * @param        $configs
     *
     * @return mixed
     */
    private function addAnalyzerToConfig(string $language, $configs)
    {
        foreach ($configs as $key => $field) {
            foreach ($field as $name => $config) {
                if (array_key_exists('analyzer', $config)) {
                    $configs[$key][$name]['analyzer'] = $language;
                }
            }
        }
        return $configs;
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
            throw new \InvalidArgumentException(
                'Configuration directory ' . $indexDir . ' for index ' . $indexName . ' does not exist.', 666
            );
        }
        return $indexDir;
    }

    /**
     * @param string $filePath
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    private function getConfig(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException('No configuration found at ' . $filePath, 666);
        }
        $configFileContent = file_get_contents($filePath);
        return Yaml::parse($configFileContent);
    }

    public function createConfigurationForIndex(string $indexName, array $mapping, array $settings)
    {
        $this->createConfigurationDirectories($indexName);
        $indexDirectory = $this->getIndexDirectory($indexName);
        $documentTypesDirectory = $this->getDocumentTypesDirectory($indexName);
        file_put_contents($indexDirectory . '/IndexConfiguration.yaml', Yaml::dump($settings));
        foreach ($mapping as $documentType => $mappingConfig) {
            file_put_contents(
                $documentTypesDirectory . '/' . $documentType . '.yaml',
                Yaml::dump($mappingConfig['properties'])
            );
        }
    }

    /**
     * Remove array keys from settings the remote server returns
     * but we don't want in the config file
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
            'provided_name'
        ];
        return array_diff_key($settings, array_flip($unwantedSettings));

    }

    private function createConfigurationDirectories(string $indexName)
    {
        $configPath = getenv('configurationPath');
        mkdir($configPath . $indexName);
        mkdir($configPath . $indexName . '/DocumentTypes');
    }
}