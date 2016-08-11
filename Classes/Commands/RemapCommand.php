<?php
declare(strict_types = 1);
namespace T3G\Elasticorn\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use T3G\Elasticorn\Utility\ConfigurationParser;
use T3G\Elasticorn\Utility\IndexUtility;

/**
 * Class RemapCommand
 *
 * Command for applying new mappings to index(es)
 *
 * @package T3G\Elasticorn\Commands
 */
class RemapCommand extends BaseCommand
{
    /**
     * Configure this command
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('index:remap')
            ->setDescription('remap index');
        $this->addArgument('index', InputArgument::OPTIONAL, 'The name of the index to remap (if none given, all will be reindexed.)');

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Initializing...');
        if($input->hasArgument('index') && null !== $indexName = $input->getArgument('index')) {
            $output->writeln('Remapping and recreating index ' . $indexName);
            $this->indexUtility->remap($indexName);
        } else {
            $output->writeln('Remapping and recreating all configured indices.');
            $this->indexUtility->remapAll();
        }
        $output->writeln('... done.');
    }

}