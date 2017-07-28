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
    public function convertDocumentTypeConfigToElasticaMapping()
    {
        $documentTypeConfigurations = $this->configurationParser->getDocumentTypeConfigurations('footest');

        $expected = [
            'tweets' => [
                'properties' => [
                    'name' => [
                        'type' => 'string',
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
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'store' => true
                    ],
                    'fullname' => [
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'store' => true
                    ],
                    'email' => [
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'store' => true
                    ],
                    'avatar' => [
                        'type' => 'string',
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
                'type' => 'string',
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
                    'type' => 'string',
                    'analyzer' => 'english'
                ]
            ],
            'users' => [
                'id' => [
                    'type' => 'integer'
                ],
                'username' => [
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'store' => true
                ],
                'fullname' => [
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'store' => true
                ],
                'email' => [
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'store' => true
                ],
                'avatar' => [
                    'type' => 'string',
                    'analyzer' => 'english'
                ]
            ]
        ];

        $config = $this->configurationParser->getDocumentTypeConfigurations('footest');

        self::assertSame($expectedConfig, $config);
    }
}
