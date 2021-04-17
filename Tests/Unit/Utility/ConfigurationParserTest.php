<?php

declare(strict_types=1);

/*
 * This file is part of the package t3g/elasticorn.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\Elasticorn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use T3G\Elasticorn\Utility\ConfigurationParser;

class ConfigurationParserTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ConfigurationParser
     */
    protected $configurationParser;

    public function setUp(): void
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
            'number_of_replicas' => 1,
        ];

        $config = $this->configurationParser->getIndexConfiguration('footest');

        self::assertSame($expectedConfig, $config);
    }

    public function testGetIndexConfigurationsFetchesAllIndexConfigs()
    {
        $expectedConfig = [
            'footest' => [
                'number_of_shards' => 4,
                'number_of_replicas' => 1,
            ],
        ];

        $config = $this->configurationParser->getIndexConfigurations();

        self::assertSame($expectedConfig, $config);
    }

    /**
     * @test
     *
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
}
