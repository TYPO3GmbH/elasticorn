<?php

declare(strict_types=1);

/*
 * This file is part of the package t3g/elasticorn.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\Elasticorn\Commands\Index;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use T3G\Elasticorn\Commands\BaseCommand;

/**
 * Class InitCommand.
 *
 * Command to initialize Elastic index(es)
 */
class InitCommand extends BaseCommand
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
            ->setName('index:init')
            ->setDescription('initializes all configured indices.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            parent::execute($input, $output);
            $this->indexService->initIndices();
        } catch (\InvalidArgumentException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}
