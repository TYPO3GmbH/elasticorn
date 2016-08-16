<?php
declare(strict_types = 1);
namespace T3G\Elasticorn\Utility;

use SebastianBergmann\Diff\Differ;

/**
 * Class DiffUtility
 *
 * @package T3G\Elasticorn\Utility
 */
class DiffUtility
{

    /**
     * @param array $arr
     * @param array $arr2
     * @return string
     */
    public function diff(array $arr, array $arr2) : string
    {
        $result = '';
        $this->prepareArray($arr);
        $this->prepareArray($arr2);
        if ($arr !== $arr2) {
            $differ = new Differ("--- On Server\n+++ In Configuration\n", false);
            $diff = $differ->diff(print_r($arr, true), print_r($arr2, true));
            $result = print_r($diff, true);
        }
        return $result;
    }

    /**
     * @param array &$array
     */
    private function prepareArray(array &$array)
    {
        $array = ArrayUtility::flatten($array);
        ksort($array);
    }
}