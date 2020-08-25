<?php

class Arr extends \Illuminate\Support\Arr
{
    /**
     * check the array keys exist
     * @param $array
     * @param $key
     * @return bool
     */
    public static function have($array, $key)
    {
        if(is_array($key)){
            foreach ($key as $k) {
                if(!isset($array[$k])) return false;
            }
            return true;
        }
        return isset($array[$key]);
    }

    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param  array   $array
     * @param  string  $prepend
     * @return array
     */
    public static function dot($array, $prepend = '', $trim = null)
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && ! empty($value)) {
                $results = array_merge($results, static::dot($value, $prepend. ($trim ? trim($key, $trim) : $key).'.'));
            } else {
                $results[$prepend.$key] = $value;
            }
        }

        return $results;
    }


    /**
     * array Undot
     * @param $dotNotationArray
     * @return array
     */
    public static function undot($dotNotationArray)
    {
        $array = [];
        foreach ($dotNotationArray as $key => $value) {
            static::set($array, $key, $value);
        }
        return $array;
    }

}