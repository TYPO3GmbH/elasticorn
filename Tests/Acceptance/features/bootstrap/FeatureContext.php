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

    private $output;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
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
     * @Then /^I should see "([^"]*)"$/
     */
    public function iShouldSee($expected)
    {
        assertEquals($expected, $this->output);
    }


    /**
     * @Then /^I should see:$/
     */
    public function iShouldSee1(PyStringNode $expected)
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

}
