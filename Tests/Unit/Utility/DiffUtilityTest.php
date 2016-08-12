<?php
declare(strict_types = 1);
namespace T3G\Elasticorn\Tests\Unit\Utility;

use SebastianBergmann\Diff\Diff;
use T3G\Elasticorn\Utility\DiffUtility;

class DiffUtilityTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @return void
     */
    public function compareArraysDiffsArraysOrderIndependent()
    {
        $arr = [
            'id' =>
                [
                    'type' => 'integer',
                ],
            'username' =>
                [
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'store' => true,
                ],
            'fullname' =>
                [
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'store' => true,
                ],
            'email' =>
                [
                    'type' => 'integer',
                    'index' => 'not_analyzed',
                    'store' => true,
                ],
            'avatar' =>
                [
                    'type' => 'string',
                ],
        ];

        $arr2 = [
            'avatar' =>
                [
                    'type' => 'string',
                ],
            'email' =>
                [
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'store' => true,
                ],
            'fullname' =>
                [
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'store' => true,
                ],
            'id' =>
                [
                    'type' => 'integer',
                ],
            'username' =>
                [
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'store' => true,
                ],
        ];
        $expected = '
-    [email.type] => integer
+    [email.type] => string';

        $diffUtility = new DiffUtility();
        $result = $diffUtility->diff($arr, $arr2);

        self::assertContains($expected, $result);
    }

    /**
     * @test
     * @return void
     */
    public function diffWithoutChangesTest()
    {
        $arr = [
            'id' =>
                [
                    'type' => 'integer',
                ],
            'username' =>
                [
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'store' => true,
                ],
            'fullname' =>
                [
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'store' => true,
                ],
            'email' =>
                [
                    'type' => 'integer',
                    'index' => 'not_analyzed',
                    'store' => true,
                ],
            'avatar' =>
                [
                    'type' => 'string',
                ],
        ];

        $diffUtility = new DiffUtility();
        $diff = $diffUtility->diff($arr, $arr);

        self::assertSame('', $diff);
    }
}
