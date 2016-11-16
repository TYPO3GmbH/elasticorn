<?php
declare(strict_types = 1);

namespace T3G\Elasticorn\Commands;

use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use T3G\Elasticorn\Service\ConfigurationService;
use T3G\Elasticorn\Service\IndexService;

class BaseCommand extends Command
{

    /**
     * @var IndexService
     */
    protected $indexService;

    /**
     * @var ConfigurationService
     */
    protected $configurationService;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        global $container;
        $verbosityLevelMap = [
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
        ];
        $container->setParameter('logger.output', $output);
        $container->setParameter('logger.verbosityMap', $verbosityLevelMap);
        if ($input->hasOption('config-path') && !empty($input->getOption('config-path'))) {
            $_ENV['configurationPath'] = $input->getOption('config-path');
        }
        if($input->hasArgument('indexName')) {
            $container->setParameter('index.name', $input->getArgument('indexName'));
        } else {
            $container->setParameter('index.name', null);
        }
        $this->indexService = $container->get('indexService');
        $this->configurationService = $container->get('configurationService');
    }

    protected function configure()
    {
        $this->addOption('config-path', 'c', InputArgument::OPTIONAL, 'The full path to the configuration directory (may be relative)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        while(!(isset($_ENV['configurationPath']) && file_exists($_ENV['configurationPath']))) {
            $this->askForConfigDir($input, $output);
        }
        $_ENV['configurationPath'] = rtrim($_ENV['configurationPath'], DIRECTORY_SEPARATOR) . '/';
    }

    private function askForConfigDir(InputInterface $input, OutputInterface $output)
    {
            $helper = $this->getHelper('question');
            $question = new Question('Please enter a valid path to your configuration directory:' . "\n");

            $_ENV['configurationPath'] = $helper->ask($input, $output, $question);
    }
}