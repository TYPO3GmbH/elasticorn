<?php
declare(strict_types=1);

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

    /**
     * @var \T3G\Elasticorn\Service\DocumentTypeService
     */
    protected $documentTypeService;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        global $container;
        $verbosityLevelMap = [
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO   => OutputInterface::VERBOSITY_NORMAL,
        ];
        $container->setParameter('logger.output', $output);
        $container->setParameter('logger.verbosityMap', $verbosityLevelMap);
        if ($input->hasOption('config-path') && !empty($input->getOption('config-path'))) {
            putenv('configurationPath=' . $input->getOption('config-path'));
        }
        if ($input->hasArgument('indexName')) {
            $container->setParameter('index.name', $input->getArgument('indexName'));
        } else {
            $container->setParameter('index.name', null);
        }
        if ($input->hasArgument('documentType')) {
            $container->setParameter('type.name', $input->getArgument('documentType'));
            $this->documentTypeService = $container->get('documentTypeService');
        }
        $this->indexService = $container->get('indexService');
        $this->configurationService = $container->get('configurationService');
    }

    protected function configure()
    {
        $this->addOption(
            'config-path',
            'c',
            InputArgument::OPTIONAL,
            'The full path to the configuration directory (may be relative)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        while (!(getenv('configurationPath') && file_exists(getenv('configurationPath')))) {
            $this->askForConfigDir($input, $output);
        }
        $path = rtrim(getenv('configurationPath'), DIRECTORY_SEPARATOR) . '/';
        putenv('configurationPath=' . $path);
    }

    private function askForConfigDir(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new Question('Please enter a valid path to your configuration directory:' . "\n");

        $answer = $helper->ask($input, $output, $question);
        putenv('configurationPath=' . $answer);
    }
}