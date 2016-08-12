<?php
declare(strict_types = 1);
namespace T3G\Elasticorn\Tests\Unit\Utility;

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
        $expected = '--- On Server
+++ In Configuration
@@ @@
 Array
 (
     [avatar.type] => string
     [email.index] => not_analyzed
     [email.store] => 1
-    [email.type] => integer
+    [email.type] => string
     [fullname.index] => not_analyzed
     [fullname.store] => 1
     [fullname.type] => string
     [id.type] => integer
     [username.index] => not_analyzed
     [username.store] => 1
     [username.type] => string
 )';

        $diffUtility = new DiffUtility();
        $result = $diffUtility->diff($arr, $arr2);

        self::assertContains($expected, $result);
    }
}
