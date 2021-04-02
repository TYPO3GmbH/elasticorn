<?php

declare(strict_types=1);

/*
 * This file is part of the package t3g/elasticorn.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\Elasticorn\Tests\Unit\Utility;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use T3G\Elasticorn\Utility\ArrayUtility;

class ArrayUtilityTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     *
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
                'five' => new \stdClass(),
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
