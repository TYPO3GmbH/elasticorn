<?php
declare(strict_types = 1);
namespace T3G\Elasticorn;

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
     * ConfigurationParser constructor.
     *
     * @param string $configFolder
     */
    public function __construct(string $configFolder)
    {
        $this->configFolder = $configFolder;
    }

    /**
     * @return array
     */
    public function getIndexConfigurations() : array
    {
        $indices = [];
        $filesInFolder = $this->getFilesInFolder($this->configFolder);
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
    private function getDocumentTypesDirectory(string $indexName)
    {
        return $this->getIndexDirectory($indexName) . '/DocumentTypes/';
    }

    /**
     * @param $directory
     * @return array
     */
    private function getFilesInFolder($directory)
    {
        return array_diff(scandir($directory), ['..', '.']);
    }

    /**
     * @param string $indexName
     * @return string
     */
    private function getIndexDirectory(string $indexName)
    {
        return $this->configFolder . ucfirst($indexName);
    }

    /**
     * @param string $filePath
     * @return array
     */
    private function getConfig(string $filePath) : array
    {
        $configFileContent = file_get_contents($filePath);
        return Yaml::parse($configFileContent);
    }
}