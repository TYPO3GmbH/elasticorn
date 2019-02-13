<?php
declare(strict_types = 1);
namespace T3G\Elasticorn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use T3G\Elasticorn\Utility\ConfigurationParser;

class ConfigurationParserTest extends TestCase
{
    /**
     * @var ConfigurationParser
     */
    protected $configurationParser;

    public function setUp()
    {
        parent::setUp();
        putenv('configurationPath=' . realpath(__DIR__ . '/../../Fixtures/Configuration') . '/');
        $loggerProphecy = $this->prophesize(LoggerInterface::class);
        $this->configurationParser = new ConfigurationParser($loggerProphecy->reveal());
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
    public function cleanSettingsRemovesSuperfluousSettings()
    {
        $settings = [
            'creation_date' => '1231231',
            'number_of_shards' => 5,
            'number_of_replicas' => 3,
            'uuid' => '_ao4eu6565',
        ];
        $result = $this->configurationParser->cleanSettingsArray($settings);

        self::assertArrayNotHasKey('creation_date', $result);
        self::assertArrayNotHasKey('uuid', $result);
        self::assertSame(5, $result['number_of_shards']);
        self::assertSame(3, $result['number_of_replicas']);
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
                        'type' => 'text',
                        'analyzer' => 'english'
                    ]
                ]
            ],
            'users' => [
                'properties' => [
                    'id' => [
                        'type' => 'integer'
                    ],
                    'username' => [
                        'type' => 'keyword',
                        'store' => true
                    ],
                    'fullname' => [
                        'type' => 'keyword',
                        'store' => true
                    ],
                    'email' => [
                        'type' => 'keyword',
                        'store' => true
                    ],
                    'avatar' => [
                        'type' => 'text',
                        'analyzer' => 'english'
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
                'type' => 'text',
                'analyzer' => 'english'
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
                    'type' => 'text',
                    'analyzer' => 'english'
                ]
            ],
            'users' => [
                'id' => [
                    'type' => 'integer'
                ],
                'username' => [
                    'type' => 'keyword',
                    'store' => true
                ],
                'fullname' => [
                    'type' => 'keyword',
                    'store' => true
                ],
                'email' => [
                    'type' => 'keyword',
                    'store' => true
                ],
                'avatar' => [
                    'type' => 'text',
                    'analyzer' => 'english'
                ]
            ]
        ];

        $config = $this->configurationParser->getDocumentTypeConfigurations('footest');

        self::assertSame($expectedConfig, $config);
    }
}
