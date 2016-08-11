<?php
declare(strict_types = 1);

namespace T3G\Elasticorn\Commands;

use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
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
        if ($input->hasOption('config-path') && !empty($input->getOption('config-path'))) {
            $_ENV['configurationPath'] = $input->getOption('config-path');
        }
        $this->indexUtility = $container->get('indexUtility');
    }

    protected function configure()
    {
        $this->addOption('config-path', 'c', InputArgument::OPTIONAL, 'The full path to the configuration directory (may be relative)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->askForConfigDir($input, $output);
        $_ENV['configurationPath'] = rtrim($_ENV['configurationPath'], DIRECTORY_SEPARATOR) . '/';
    }

    private function askForConfigDir(InputInterface $input, OutputInterface $output)
    {
        if (!(isset($_ENV['configurationPath']) && file_exists($_ENV['configurationPath']))) {
            $helper = $this->getHelper('question');
            $question = new Question('Please enter a valid path to your configuration directory:' . "\n");

            $_ENV['configurationPath'] = $helper->ask($input, $output, $question);
            if($this->askForConfigDir($input, $output) === true) {
                return true;
            } else {
               $this->askForConfigDir($input, $output);
            }
        } else {
            return true;
        }
    }
}