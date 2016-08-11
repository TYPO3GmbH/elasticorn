<?php
declare(strict_types = 1);

namespace T3G\Elasticorn\Commands;

use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use T3G\Elasticorn\Utility\IndexUtility;

class BaseCommand extends Command
{

    /**
     * @var IndexUtility
     */
    protected $indexUtility;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        global $container;
        $verbosityLevelMap = [
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
        ];
        $container->setParameter('logger.output', $output);
        $container->setParameter('logger.verbosityMap', $verbosityLevelMap);
        $_ENV['configurationPath'] = $input->getArgument('config-path');
        $this->indexUtility = $container->get('indexUtility');
    }

    protected function configure()
    {
        $this->addArgument('config-path', InputArgument::REQUIRED, 'The full path to the configuration directory.');
    }
}