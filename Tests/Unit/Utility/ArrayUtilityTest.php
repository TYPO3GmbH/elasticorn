<?php
declare(strict_types = 1);
namespace T3G\Elasticorn\Tests\Unit\Utility;


use T3G\Elasticorn\Utility\ArrayUtility;

class ArrayUtilityTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @return void
     */
    public function flattenArrayTest()
    {
        $array = [
            'three' => [
                'three' => [
                    'one' => 1,
                    'two' => 2,
                ],
                'one' => 1,
                'two' => 2,
                'four' => null,
                'five' => new \stdClass,
            ],
            'one' => 1,
            'two' => 2,
            'four' => true,
            'five' => false,
        ];
        $expected = [
            'three.three.one' => 1,
            'three.three.two' => 2,
            'three.one' => 1,
            'three.two' => 2,
            'three.four' => 'NULL',
            'three.five' => 'object',
            'one' => 1,
            'two' => 2,
            'four' => true,
            'five' => false,
        ];
        self::assertSame($expected, ArrayUtility::flatten($array));
    }
}
