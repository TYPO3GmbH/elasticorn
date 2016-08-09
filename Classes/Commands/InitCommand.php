<?php
declare(strict_types = 1);
namespace T3G\Elasticorn\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use T3G\Elasticorn\ConfigurationParser;
use T3G\Elasticorn\IndexUtility;

class InitCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('index:init')
            ->setDescription('initializes all configured indices.');
        $this->addArgument('config-path', InputArgument::REQUIRED, 'The full path to the configuration directory.');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Initializing...');
        $configurationParser = new ConfigurationParser($input->getArgument('config-path'));
        $indexUtility = new IndexUtility($configurationParser);
        $indexUtility->initIndices();
        $output->writeln('... done.');
    }

}