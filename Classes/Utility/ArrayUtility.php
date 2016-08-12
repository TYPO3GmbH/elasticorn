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
     * @param array $result
     * @return array
     */
    public static function flatten(array $array, string $prefix = '', array $result = []) : array
    {
        foreach ($array as $key => $value) {
            if ($prefix) {
                $key = $prefix . '.' . $key;
            }
            if (is_array($value)) {
                $result = self::flatten($value, (string)$key, $result);
            } else {
                $result[$key] = is_scalar($value) ? $value : gettype($value);
            }
        }
        return $result;
    }
}