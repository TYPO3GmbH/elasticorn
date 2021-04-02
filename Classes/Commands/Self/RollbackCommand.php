<?php

declare(strict_types=1);

/*
 * This file is part of the package t3g/elasticorn.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\Elasticorn\Commands\Self;

use Humbug\SelfUpdate\Updater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RollbackCommand extends Command
{
    /**
     * Configure the rollback command.
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('self:rollback')
            ->setDescription('Rollback elasticorn to the previous version.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $updater = new Updater();
        $result = $updater->rollback();
        if (false === $result) {
            $output->writeln('Something went wrong, rollback failed.');
        } else {
            $output->writeln(sprintf('Successfully performed rollback.'));
        }
    }
}
