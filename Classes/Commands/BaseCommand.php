<?php
declare(strict_types = 1);

namespace T3G\Elasticorn\Commands;

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
        $container
            ->setParameter('logger.output', $output);
        $_ENV['configurationPath'] = $input->getArgument('config-path');
        $this->indexUtility = $container->get('indexUtility');
    }

    protected function configure()
    {
        $this->addArgument('config-path', InputArgument::REQUIRED, 'The full path to the configuration directory.');
    }
}