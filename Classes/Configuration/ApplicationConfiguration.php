<?php
declare(strict_types = 1);
namespace T3G\Elasticorn\Configuration;

use Symfony\Component\Yaml\Yaml;

final class ApplicationConfiguration
{

    const ELASTICORN_CONFIG_FILENAME = 'Elasticorn.yaml';
    static private $configuration = [];
    static private $isInitialized = false;

    /**
     * @return array
     */
    public static function getElasticornConfiguration() : array
    {
        if (self::$isInitialized === false) {
            $elasticornConfigPath = $_ENV['configurationPath'] . self::ELASTICORN_CONFIG_FILENAME;
            if (file_exists($elasticornConfigPath)) {
                self::$configuration = Yaml::parse(file_get_contents($elasticornConfigPath));
            }
            self::$isInitialized = true;
        }
        return self::$configuration;
    }

    /**
     * @return array
     */
    public static function getLanguageConfiguration() : array
    {
        $languages = [];
        $configuration = self::getElasticornConfiguration();
        if (array_key_exists('languages', $configuration)) {
            $languages = $configuration['languages'];
        }
        return $languages;
    }
}