<?php
declare(strict_types = 1);
namespace T3G\Elasticorn\Commands\Index;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use T3G\Elasticorn\Commands\BaseCommand;

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
        $this->addArgument(
            'indexName',
            InputArgument::OPTIONAL,
            'The name of the index to remap (if none given, all will be re-indexed.)'
        );

        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'If enabled remapping will be forced, even if there are no changes.'
        );

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $force = false;
        if($input->hasOption('force')) {
            $force = true;
        }
        if ($input->hasArgument('indexName') && null !== $indexName = $input->getArgument('indexName')) {
            $output->writeln('Remapping and recreating index ' . $indexName);
            $this->indexService->remap($indexName, $force);
        } else {
            $output->writeln('Remapping and recreating all configured indices.');
            $this->indexService->remapAll($force);
        }
    }

}
