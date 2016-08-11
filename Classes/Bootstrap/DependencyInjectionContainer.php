<?php
declare(strict_types = 1);
namespace T3G\Elasticorn\Bootstrap;

use Elastica\Client;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use T3G\Elasticorn\Utility\ConfigurationParser;
use T3G\Elasticorn\Utility\IndexUtility;

class DependencyInjectionContainer
{

    public function init()
    {
        $container = new ContainerBuilder();

        $container
            ->register('configurationParser', ConfigurationParser::class)
            ->addArgument(new Reference('logger'));

        $container
            ->register('elasticaClient', Client::class);

        $container
            ->register('logger', ConsoleLogger::class)
            ->addArgument('%logger.output%')
            ->addArgument('%logger.verbosityMap%');

        $container
            ->register('indexUtility', IndexUtility::class)
            ->addArgument(new Reference('elasticaClient'))
            ->addArgument(new Reference('configurationParser'))
            ->addArgument(new Reference('logger'));

        return $container;
    }
}