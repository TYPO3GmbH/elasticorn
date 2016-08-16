<?php
declare(strict_types = 1);
namespace T3G\Elasticorn\Commands\Index;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use T3G\Elasticorn\Commands\BaseCommand;

class CornifyCommand extends BaseCommand
{
    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param $helper
     * @return bool
     */
    private function compareConfig(InputInterface $input, OutputInterface $output, $helper, $indexName)
    {
        $this->indexUtility->compareMappingConfiguration($indexName);
        $question = new ConfirmationQuestion('Continue? [y/N]', false);
        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('User aborted.');
            return false;
        } else {
            return true;
        }
    }

    /**
     * Configure the cornify command
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

    public function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $indexName = $input->getArgument('indexName');
        $helper = $this->getHelper('question');
        $continue = false;
        try {
            $continue = $this->compareConfig($input, $output, $helper, $indexName);
        } catch (\InvalidArgumentException $e) {
            if ($e->getCode() === 666) {
                $continue = $this->createConfiguration($input, $output, $helper, $indexName);
            }
        }

        if (true === $continue) {
            $this->indexUtility->renameIndex($indexName, $indexName . '_old');
            $this->indexUtility->initIndex($indexName);
            $this->indexUtility->copyData($indexName . '_old', $indexName);
        }
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param $helper
     * @param $indexName
     * @return bool
     */
    private function createConfiguration(InputInterface $input, OutputInterface $output, $helper, $indexName)
    {
        $question = new ConfirmationQuestion('Configuration does not exist. Shall I create it? [Y/n]', true);
        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('Cannot continue without configuration.');
            return false;
        } else {
            $this->indexUtility->createConfigurationFromExistingIndex($indexName);
            return true;
        }
    }
}