<?php

declare(strict_types=1);

/*
 * This file is part of the package t3g/elasticorn.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\Elasticorn\Commands\Type;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use T3G\Elasticorn\Commands\BaseCommand;

/**
 * Class TruncateCommand.
 *
 * Command to truncate specified document type from index
 */
class TruncateCommand extends BaseCommand
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
            ->setName('type:truncate')
            ->setDescription('Truncates given document type (removes all documents of type).');

        $this->addArgument('indexName', InputArgument::REQUIRED, 'The index name.');
        $this->addArgument('documentType', InputArgument::REQUIRED, 'The document type to truncate.');
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
            $this->documentTypeService->deleteDocumentsOfType();
            return 0;
        } catch (\InvalidArgumentException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1;
        }
    }
}
