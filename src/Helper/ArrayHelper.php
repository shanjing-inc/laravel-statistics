<?php

namespace Shanjing\LaravelStatistics\Helper;

class ArrayHelper
{
    /**
     * Flatten a multi-dimensional associative array with arrow (->).
     *
     * @param  iterable  $array
     * @param  string  $prepend
     * @return array
     */
    public static function arrow($array, $prepend = '')
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && ! empty($value)) {
                $results = array_merge($results, static::arrow($value, $prepend . $key . '->'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }
}
