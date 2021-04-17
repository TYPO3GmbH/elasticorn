<?php

declare(strict_types=1);

/*
 * This file is part of the package t3g/elasticorn.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\Elasticorn\Commands\Mapping;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use T3G\Elasticorn\Commands\BaseCommand;

class ShowCommand extends BaseCommand
{
    /**
     * Configure the init command.
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
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);
        $mapping = $this->indexService->getMappingForIndex();
        $dump = Yaml::dump($mapping, 20, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
        $output->write(
            $dump
        );

        return 0;
    }
}
