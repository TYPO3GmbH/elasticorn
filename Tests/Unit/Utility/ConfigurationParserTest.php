<?php
declare(strict_types = 1);
namespace T3G\Elasticorn\Tests\Unit;

use T3G\Elasticorn\Utility\ConfigurationParser;

class ConfigurationParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigurationParser
     */
    protected $configurationParser;

    public function setUp()
    {
        parent::setUp();
        $_ENV['configurationPath'] = realpath(__DIR__ . '/../../Fixtures/Configuration') . '/';
        $this->configurationParser = new ConfigurationParser();
    }

    public function testGetIndexConfigurationFetchesConfigBasedOnIndexName()
    {
        $expectedConfig = [
            'number_of_shards' => 4,
            'number_of_replicas' => 1
        ];

        $config = $this->configurationParser->getIndexConfiguration('footest');

        self::assertSame($expectedConfig, $config);
    }

    public function testGetIndexConfigurationsFetchesAllIndexConfigs()
    {
        $expectedConfig = [
            'footest' => [
                'number_of_shards' => 4,
                'number_of_replicas' => 1
            ]
        ];

        $config = $this->configurationParser->getIndexConfigurations();

        self::assertSame($expectedConfig, $config);
    }

    /**
     * @test
     * @return void
     */
    public function convertDocumentTypeConfigToElasticaMapping()
    {
        $documentTypeConfigurations = $this->configurationParser->getDocumentTypeConfigurations('footest');

        $expected = [
            'tweets' => [
                'properties' => [
                    'name' => [
                        'type' => 'string'
                    ]
                ]
            ],
            'users' => [
                'properties' => [
                    'first_name' => [
                        'type' => 'string'
                    ]
                ]
            ],
        ];

        $converted = $this->configurationParser->convertDocumentTypeConfigurationToMappingFromElastica(
            $documentTypeConfigurations
        );

        self::assertSame($expected, $converted);
    }

    /**
     * @test
     * @return void
     */
    public function testGetDocumentTypeConfigFetchesConfigurationBasedOnIndexAndDocumentType()
    {
        $expectedConfig = [
            'name' => [
                'type' => 'string'
            ]
        ];

        $config = $this->configurationParser->getDocumentTypeConfiguration('footest', 'tweets');

        self::assertSame($expectedConfig, $config);
    }

    /**
     * @test
     * @return void
     */
    public function testGetDocumentTypeConfigurationsFetchesAllConfigsBasedOnIndex()
    {
        $expectedConfig = [
            'tweets' => [
                'name' => [
                    'type' => 'string'
                ]
            ],
            'users' => [
                'first_name' => [
                    'type' => 'string'
                ]
            ]
        ];

        $config = $this->configurationParser->getDocumentTypeConfigurations('footest');

        self::assertSame($expectedConfig, $config);
    }
}
