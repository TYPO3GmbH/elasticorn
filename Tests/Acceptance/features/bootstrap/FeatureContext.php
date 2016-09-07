<?php
declare(strict_types = 1);
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

require_once __DIR__ . '/../../../../vendor/phpunit/phpunit/src/Framework/Assert/Functions.php';

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    private $defaultTestSourceIndex = 'footest';

    private $output;

    private $filesToDelete = [];

    /**
     * @var \Elastica\Index
     */
    private $index;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->deleteAllIndices();
        $_ENV['configurationPath'] = 'Tests/Fixtures/Configuration';
        putenv('configurationPath=' . $_ENV['configurationPath']);
    }

    /**
     * @When /^I call elasticorn "([^"]*)"$/
     * @param $command
     */
    public function iCallElasticorn($command)
    {
        chdir(__DIR__ . '/../../../../');

        exec('./elasticorn.php -n ' . $command . ' -c ' . $_ENV['configurationPath'] . ' 2>&1', $output);

        $this->output = trim(implode("\n", $output));
    }

    /**
     * @Given /^I don't have any indices$/
     */
    public function iDonTHaveAnyIndices()
    {
        $this->deleteAllIndices();
    }



    /**
     * @Given /^I have a non\-elasticorn index "([^"]*)"$/
     */
    public function iHaveANonElasticornIndex($indexName)
    {
        $client = $this->getElasticaClient();
        $index = $client->getIndex($indexName);
        $index->create();
        $this->index = $index;
    }

    /**
     * @Given /^I have setup mappings and data for index "([^"]*)"$/
     */
    public function iHaveSetupMappingsAndDataForIndex($indexName)
    {
        // setup footest index with mappings and data
        $this->iCallElasticorn('index:init');
        $client = $this->getElasticaClient();

        $baseIndex = $client->getIndex('footest');
        $mapping = $baseIndex->getMapping();
        foreach ($mapping as $documentType => $properties) {
            $type = $this->index->getType($documentType);
            $mappingConfig = new \Elastica\Type\Mapping($type, $properties['properties']);
            $mappingConfig->send();
        }
        $this->index->clearCache();
    }

    /**
     * @Given /^I initialized my indices$/
     */
    public function iInitializedMyIndices()
    {
        $this->iCallElasticorn('index:init');
    }

    /**
     * @Given /^I should have a folder with the new "([^"]*)" configuration$/
     */
    public function iShouldHaveAFolderWithTheNewConfiguration($indexName)
    {
        $folder = $_ENV['configurationPath'] . '/' . $indexName;
        assertTrue(file_exists($folder) && is_dir($folder));
        $this->filesToDelete[] = $folder;
    }

    /**
     * @Then /^I should have document types configuration files for "([^"]*)"$/
     */
    public function iShouldHaveDocumentTypesConfigurationFilesFor($indexName)
    {
        $expected = scandir($_ENV['configurationPath'] . '/' . $this->defaultTestSourceIndex . '/DocumentTypes/');
        $actual = scandir($_ENV['configurationPath'] . '/' . $indexName . '/DocumentTypes/');

        assertSame($expected, $actual);

        $this->filesToDelete[] = $_ENV['configurationPath'] . '/' . $indexName;
    }


    /**
     * @Given /^I should have indices starting with "([^"]*)" and a corresponding alias$/
     */
    public function iShouldHaveIndicesStartingWithAndACorrespondingAlias($indexName)
    {
        $client = $this->getElasticaClient();
        $request = $client->request('_cat/indices?v')->getData();
        assertContains($indexName . '_a', $request['message']);
        assertContains($indexName . '_b', $request['message']);
        $index = $client->getIndex($indexName);
        assertTrue($index->exists());
    }

    /**
     * @Then /^I should see "([^"]*)"$/
     * @Then /^I should see:$/
     */
    public function iShouldSee($expected)
    {
        assertEquals((string)$expected, $this->output);
    }

    /**
     * @Then /^I should see message containing '([^']*)'$/
     */
    public function iShouldSeeMessageContaining($expected)
    {
        assertContains($expected, $this->output);
    }

    /**
     * @Given /^I use alternative configuration folder with changes$/
     */
    public function iUseAlternativeConfigurationFolderWithChanges()
    {
        $_ENV['configurationPath'] = 'Tests/Fixtures/AlternativeConfiguration';
    }

    public function __destruct()
    {
        $this->deleteAllIndices();
        foreach ($this->filesToDelete as $file) {
            @exec('rm -r ' . $file);
        }
    }

    private function getElasticaClient() {
        return new \Elastica\Client();
    }

    private function deleteAllIndices()
    {
        $client = $this->getElasticaClient();
        $client->getIndex('_all')->delete();
        $client->getIndex('_all')->clearCache();
    }

}
