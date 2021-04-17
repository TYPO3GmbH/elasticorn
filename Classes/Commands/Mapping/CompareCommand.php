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
use T3G\Elasticorn\Commands\BaseCommand;

class CompareCommand extends BaseCommand
{
    /**
     * Configure the compare command.
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('mapping:compare')
            ->setDescription('compare mapping configuration to applied configuration.');

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
        $this->configurationService->compareMappingConfiguration($input->getArgument('indexName'), $this->indexService->getIndex());

        return 0;
    }
}
