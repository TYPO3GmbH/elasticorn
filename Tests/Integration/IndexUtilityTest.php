<?php
declare(strict_types = 1);
namespace T3G\Elasticorn\Tests\Integration;

use T3G\Elasticorn\IndexUtility;

class IndexUtilityTest extends \PHPUnit_Framework_TestCase
{

    public function testInit()
    {
        $elasticorn = new IndexUtility();
        $elasticorn->initIndices();
    }

    /**
     * @test
     *
     * test result has to be checked manually in elastic
     *
     * @return void
     */
    public function testIndexCreationCreatesIndexAndAliases()
    {
        $elasticorn = new IndexUtility();
        $elasticorn->remap('footest');
    }

    /**
     * @test
     * @return void
     */
    public function testCompareConfig()
    {
        $elasticorn = new IndexUtility();
        $elasticorn->compareMappingConfiguration('footest');
    }

}
