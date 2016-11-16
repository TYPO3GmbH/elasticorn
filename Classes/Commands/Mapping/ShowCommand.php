<?php
declare(strict_types = 1);
namespace T3G\Elasticorn\Commands\Mapping;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use T3G\Elasticorn\Commands\BaseCommand;

/**
 * @package T3G\Elasticorn\Commands
 */
class ShowCommand extends BaseCommand
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
            ->setName('mapping:show')
            ->setDescription('show currently applied mapping configuration.');

        $this->
            addArgument('indexName', InputArgument::REQUIRED, 'The index name.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $mapping = $this->indexService->getMappingForIndex();
        $dump = Yaml::dump($mapping, 20, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
        $output->write(
            $dump
        );
    }

}