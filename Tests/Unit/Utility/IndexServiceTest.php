<?php
declare(strict_types = 1);
namespace T3G\Elasticorn\Tests\Unit\Utility;

use Elastica\Client;
use Elastica\Index;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use T3G\Elasticorn\Service\ConfigurationService;
use T3G\Elasticorn\Service\IndexService;
use T3G\Elasticorn\Utility\ConfigurationParser;

class IndexServiceTest extends TestCase
{
    /**
     * @var IndexService
     */
    protected $indexService;

    /**
     * @var Client|ObjectProphecy
     */
    protected $clientProphecy;

    /**
     * @var ConfigurationParser|ObjectProphecy
     */
    protected $configParserProphecy;

    /**
     * @var ConfigurationService|ObjectProphecy
     */
    protected $configServiceProphecy;

    /**
     * @var LoggerInterface|ObjectProphecy
     */
    protected $loggerProphecy;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->clientProphecy = $this->prophesize(Client::class);
        $this->configParserProphecy = $this->prophesize(ConfigurationParser::class);
        $this->configServiceProphecy = $this->prophesize(ConfigurationService::class);
        $this->loggerProphecy = $this->prophesize(LoggerInterface::class);

        $this->indexService = new IndexService(
            $this->clientProphecy->reveal(),
            $this->configServiceProphecy->reveal(),
            $this->loggerProphecy->reveal()
        );
    }

    /**
     * @test
     * @return void
     */
    public function initIndicesCreatesIndices()
    {
        /** @var Index|ObjectProphecy $indexProphecy */
        $indexProphecy = $this->prophesize(Index::class);

        $this->configServiceProphecy->getIndexConfigurations()->willReturn([
            'testindex' => [
                'shards' => 4
            ]
        ]);
        $this->configServiceProphecy->applyMapping('testindex', Argument::any())->willReturn();
        $this->clientProphecy->getIndex(Argument::any())->willReturn($indexProphecy->reveal());
        $this->configParserProphecy->getDocumentTypeConfigurations(Argument::any())->willReturn([]);
        $indexProphecy->exists()->willReturn(false);
        $indexProphecy->create(Argument::any())->willReturn();
        $indexProphecy->addAlias(Argument::any())->willReturn();

        $this->indexService->initIndices();

        $this->clientProphecy->getIndex('testindex_a')->shouldHaveBeenCalled();
        $this->clientProphecy->getIndex('testindex_b')->shouldHaveBeenCalled();
        $indexProphecy->create(['shards' => 4])->shouldHaveBeenCalled();
        $indexProphecy->addAlias('testindex')->shouldHaveBeenCalled();
    }
}
