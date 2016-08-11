<?php
declare(strict_types = 1);
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
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->configFolder = &$_ENV['configurationPath'];
        $this->logger = $logger;
    }

    /**
     * Get configurations for all indices
     *
     * @return array
     */
    public function getIndexConfigurations() : array
    {
        $indices = [];
        $this->logger->info('Loading configuration from ' . $this->configFolder);
        $filesInFolder = $this->getFilesInFolder($this->configFolder);
        $this->logger->debug('Found: ' . var_export($filesInFolder, true));
        foreach ($filesInFolder as $indexName) {
            $subFolder = $this->configFolder . $indexName;
            if(is_dir($subFolder) === true && file_exists($subFolder . '/' . self::INDEX_CONF_FILENAME)) {
                $indices[strtolower($indexName)] = $this->getIndexConfiguration($indexName);
            }
        }
        return $indices;
    }

    /**
     * Get Configuration for specified index1
     *
     * @param string $indexName
     * @return array
     */
    public function getIndexConfiguration(string $indexName) : array
    {
        $filePath = $this->getIndexDirectory($indexName) . '/' . self::INDEX_CONF_FILENAME;
        return $this->getConfig($filePath);
    }

    /**
     * Get all document type configurations for index
     *
     * @param string $indexName
     * @return array
     */
    public function getDocumentTypeConfigurations(string $indexName) : array
    {
        $configs = [];
        $directory = $this->getDocumentTypesDirectory($indexName);
        $configFiles = $this->getFilesInFolder($directory);
        foreach ($configFiles as $configFile) {
            $filePath = $directory . $configFile;
            $pathInfo = pathinfo($filePath);
            if($pathInfo['extension'] === 'yaml') {
                $configs[$pathInfo['filename']] = $this->getConfig($filePath);
            }
        }
        return $configs;
    }

    /**
     * Convert document type configuration to mapping as returned from elastica (for comparison for example)
     *
     * @param array $configurations
     * @return array
     */
    public function convertDocumentTypeConfigurationToMappingFromElastica(array $configurations) : array
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
     * @return array
     */
    public function getDocumentTypeConfiguration(string $indexName, string $documentType) : array
    {
        $filePath = $this->getDocumentTypesDirectory($indexName) . strtolower($documentType) . '.yaml';
        return $this->getConfig($filePath);
    }

    /**
     * @param string $indexName
     * @return string
     */
    private function getDocumentTypesDirectory(string $indexName) : string
    {
        return $this->getIndexDirectory($indexName) . '/DocumentTypes/';
    }

    /**
     * @param string $directory
     * @return array
     */
    private function getFilesInFolder(string $directory) : array
    {
        return array_diff(scandir($directory), ['..', '.']);
    }

    /**
     * @param string $indexName
     * @return string
     */
    private function getIndexDirectory(string $indexName) : string
    {
        return $this->configFolder . ucfirst($indexName);
    }

    /**
     * @param string $filePath
     * @return array
     * @throws \InvalidArgumentException
     */
    private function getConfig(string $filePath) : array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException('No configuration found at ' . $filePath);
        }
        $configFileContent = file_get_contents($filePath);
        return Yaml::parse($configFileContent);
    }
}