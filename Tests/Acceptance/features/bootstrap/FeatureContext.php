<?php
declare(strict_types = 1);
use Behat\Behat\Context\Context;
use Elastica\Type\Mapping;

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
        putenv('configurationPath=Tests/Fixtures/Configuration');
    }

    /**
     * @When /^I call elasticorn "([^"]*)"$/
     * @param $command
     */
    public function iCallElasticorn($command)
    {
        chdir(__DIR__ . '/../../../../');

        exec('php elasticorn.php -n ' . $command . ' -c ' . getenv('configurationPath') . ' 2>&1', $output);

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
    public function iHaveSetupMappingsAndDataForIndex(string $indexName)
    {
        // setup footest index with mappings and data
        $this->iCallElasticorn('index:init');
        $client = $this->getElasticaClient();

        $baseIndex = $client->getIndex('footest');
        $mapping = $baseIndex->getMapping();
        foreach ($mapping as $documentType => $properties) {
            $type = $client->getIndex($indexName)->getType($documentType);
            $mappingConfig = new Mapping($type, $properties['properties']);
            $mappingConfig->send();
        }
        $client->getIndex($indexName)->clearCache();
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
     * @param string $indexName
     */
    public function iShouldHaveAFolderWithTheNewConfiguration($indexName)
    {
        $folder = getenv('configurationPath') . '/' . $indexName;
        assertTrue(file_exists($folder) && is_dir($folder));
        $this->filesToDelete[] = $folder;
    }

    /**
     * @Then /^I should have document types configuration files for "([^"]*)"$/
     */
    public function iShouldHaveDocumentTypesConfigurationFilesFor($indexName)
    {
        $expected = scandir(getenv('configurationPath') . '/' . $this->defaultTestSourceIndex . '/DocumentTypes/', 0);
        $actual = scandir(getenv('configurationPath') . '/' . $indexName . '/DocumentTypes/', 0);

        assertSame($expected, $actual);

        $this->filesToDelete[] = getenv('configurationPath') . '/' . $indexName;
    }


    /**
     * @Given /^I should have indices starting with "([^"]*)" and a corresponding alias$/
     */
    public function iShouldHaveIndicesStartingWithAndACorrespondingAlias($indexName)
    {
        $client = $this->getElasticaClient();
        $request = $client->request('_cat/indices?v')->getData();
        assertContains($indexName, $request['message']);
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
        putenv('configurationPath=Tests/Fixtures/AlternativeConfiguration');
    }

    public function __destruct()
    {
        $this->deleteAllIndices();
        foreach ($this->filesToDelete as $file) {
            $this->rrmdir($file);
        }
    }

    /**
     * @Given /^I use alternative configuration folder with changes and languages$/
     */
    public function iUseAlternativeConfigurationFolderWithChangesAndLanguages()
    {
        putenv('configurationPath=Tests/Fixtures/AlternativeConfigurationWithLanguages');
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

    private function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir, 0);
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    if (is_dir($dir . '/' . $object)) {
                        $this->rrmdir($dir . '/' . $object);
                    } else {
                        unlink($dir . '/' . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }

}
