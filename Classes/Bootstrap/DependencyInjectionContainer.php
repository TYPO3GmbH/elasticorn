<?php
declare(strict_types = 1);
namespace T3G\Elasticorn\Bootstrap;

use Elastica\Client;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use T3G\Elasticorn\Service\ConfigurationService;
use T3G\Elasticorn\Service\IndexService;
use T3G\Elasticorn\Utility\ConfigurationParser;

class DependencyInjectionContainer
{

    public function init()
    {
        $container = new ContainerBuilder();

        $container
            ->register('configurationParser', ConfigurationParser::class)
            ->addArgument(new Reference('logger'));

        $container
            ->register('elasticaClient', Client::class)
            ->addArgument($this->getElasticaConfiguration())
            ->addArgument(null)
            ->addArgument(new Reference('logger'));

        $container
            ->register('logger', ConsoleLogger::class)
            ->addArgument('%logger.output%')
            ->addArgument('%logger.verbosityMap%');

        $container
            ->register('configurationService', ConfigurationService::class)
            ->addArgument(new Reference('elasticaClient'))
            ->addArgument(new Reference('configurationParser'))
            ->addArgument(new Reference('logger'));

        $container
            ->register('indexService', IndexService::class)
            ->addArgument(new Reference('elasticaClient'))
            ->addArgument(new Reference('configurationService'))
            ->addArgument(new Reference('logger'))
            ->addArgument('%index.name%');

        return $container;
    }

    private function getElasticaConfiguration()
    {
        return array_filter([
            'host' => getenv('elastica.host'),
            'port' => getenv('elastica.port'),
            'path' => getenv('elastica.path'),
            'url' => getenv('elastica.url'),
            'proxy' => getenv('elastica.proxy'),
            'transport' => getenv('elastica.transport'),
            'persistent' => getenv('elastica.persistent') ?? true,
            'timeout' => getenv('elastica.timeout'),
            'log' => true,
            'username' => getenv('elastica.username'),
            'password' => getenv('elastica.password'),
        ]);
    }
}