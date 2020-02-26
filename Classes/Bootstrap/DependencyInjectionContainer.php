<?php
declare(strict_types = 1);
namespace T3G\Elasticorn\Bootstrap;

use Elastica\Client;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use T3G\Elasticorn\Service\ConfigurationService;
use T3G\Elasticorn\Service\DocumentTypeService;
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

        $container
            ->register('documentTypeService', DocumentTypeService::class)
            ->addArgument(new Reference('elasticaClient'))
            ->addArgument(new Reference('logger'))
            ->addArgument('%index.name%')
            ->addArgument('%type.name%');

        return $container;
    }

    private function getElasticaConfiguration()
    {
        return array_filter([
            'host' => getenv('ELASTICA_HOST'),
            'port' => getenv('ELASTICA_PORT'),
            'path' => getenv('ELASTICA_PATH'),
            'url' => getenv('ELASTICA_URL'),
            'proxy' => getenv('ELASTICA_PROXY'),
            'transport' => getenv('ELASTICA_TRANSPORT'),
            'persistent' => getenv('ELASTICA_PERSISTENT') ?? true,
            'timeout' => getenv('ELASTICA_TIMEOUT'),
            'log' => true,
            'username' => getenv('ELASTICA_USERNAME'),
            'password' => getenv('ELASTICA_PASSWORD'),
        ]);
    }
}