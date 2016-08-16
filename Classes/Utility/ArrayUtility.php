<?php
declare(strict_types = 1);
namespace T3G\Elasticorn\Utility;


/**
 * Class ArrayUtility
 *
 * @package T3G\Elasticorn\Utility
 */
class ArrayUtility
{

    /**
     * @param array $array
     * @param string $prefix
     * @return array
     */
    public static function flatten(array $array, string $prefix = '') : array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if ($prefix) {
                $key = $prefix . '.' . $key;
            }
            if (is_array($value)) {
                $result = array_merge($result, self::flatten($value, (string)$key));
            } else {
                $result[$key] = is_scalar($value) ? $value : gettype($value);
            }
        }
        return $result;
    }
}