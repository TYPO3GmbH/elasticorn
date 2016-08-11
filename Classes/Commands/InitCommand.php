<?php
declare(strict_types = 1);
namespace T3G\Elasticorn\Commands;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InitCommand
 *
 * Command to initialize Elastic index(es)
 *
 * @package T3G\Elasticorn\Commands
 */
class InitCommand extends BaseCommand
{
    /**
     * Configure the init command
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('index:init')
            ->setDescription('initializes all configured indices.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Initializing...');
        $this->indexUtility->initIndices();
        $output->writeln('... done.');
    }

}