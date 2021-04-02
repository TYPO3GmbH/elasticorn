<?php

declare(strict_types=1);

/*
 * This file is part of the package t3g/elasticorn.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\Elasticorn\Configuration;

use Symfony\Component\Yaml\Yaml;

final class ApplicationConfiguration
{
    const ELASTICORN_CONFIG_FILENAME = 'Elasticorn.yaml';
    private static $configuration = [];
    private static $isInitialized = false;

    /**
     * @return array
     */
    public static function getElasticornConfiguration(): array
    {
        if (false === self::$isInitialized) {
            $elasticornConfigPath = getenv('configurationPath') . self::ELASTICORN_CONFIG_FILENAME;
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
    public static function getLanguageConfiguration(): array
    {
        $languages = [];
        $configuration = self::getElasticornConfiguration();
        if (array_key_exists('languages', $configuration)) {
            $languages = $configuration['languages'];
        }

        return $languages;
    }
}
