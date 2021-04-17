<?php

declare(strict_types=1);

/*
 * This file is part of the package t3g/elasticorn.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\Elasticorn\Commands\Index;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use T3G\Elasticorn\Commands\BaseCommand;

/**
 * Class CornifyCommand.
 *
 * Converts an existing index with mapping configuration and data to an elasticorn index
 */
class CornifyCommand extends BaseCommand
{
    /**
     * Configure the cornify command.
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('index:cornify')
            ->setDescription('Convert existing index to elasticorn managed index.');

        $this->addArgument('indexName', InputArgument::REQUIRED, 'The index name.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);
        $indexName = $input->getArgument('indexName');
        $helper = $this->getHelper('question');
        $continue = false;
        try {
            $continue = $this->compareConfiguration($input, $output, $helper, $indexName);
        } catch (\InvalidArgumentException $e) {
            if (666 === $e->getCode()) {
                $continue = $this->createConfiguration($input, $output, $helper, $indexName);
            }
        }

        if (true === $continue) {
            $suffix = uniqid('_', true);
            $this->indexService->renameIndex($indexName . $suffix);
            $this->indexService->initIndex($indexName);
            $this->indexService->copyData($indexName . $suffix, $indexName);
        }
        return 0;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param $helper
     * @param $indexName
     *
     * @return bool
     */
    private function createConfiguration(InputInterface $input, OutputInterface $output, $helper, $indexName)
    {
        $question = new ConfirmationQuestion('Configuration does not exist. Shall I create it? [Y/n]', true);
        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('Cannot continue without configuration.');

            return false;
        }
        $this->configurationService->createConfigurationFromExistingIndex($indexName, $this->indexService->getIndex());

        return true;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param $helper
     *
     * @return bool
     */
    private function compareConfiguration(InputInterface $input, OutputInterface $output, $helper, $indexName)
    {
        $this->configurationService->compareMappingConfiguration($indexName, $this->indexService->getIndex());
        $question = new ConfirmationQuestion('Continue? [Y/n]', true);
        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('User aborted.');

            return false;
        } else {
            return true;
        }
    }
}
