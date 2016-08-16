<?php
declare(strict_types = 1);
namespace T3G\Elasticorn\Commands\Self;

use Humbug\SelfUpdate\Updater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
{

    /**
     * Configure the update command
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('self:update')
            ->setDescription('Update elasticorn to the newest version.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $urlToGithubPagesPharFile = 'https://typo3incteam.github.io/elasticorn/elasticorn.phar';
        $urlToGithubPagesVersionFile = $urlToGithubPagesPharFile . '.version';
        $updater = new Updater();
        $updater->getStrategy()->setPharUrl($urlToGithubPagesPharFile);
        $updater->getStrategy()->setVersionUrl($urlToGithubPagesVersionFile);
        $result = $updater->update();
        if (!$result) {
            $output->writeln('No update necessary');
        } else {
            $new = $updater->getNewVersion();
            $old = $updater->getOldVersion();
            $output->writeln(sprintf('Updated from %s to %s. To perform a rollback use ./elasticorn.phar self:rollback', $old, $new));
        }
    }
}