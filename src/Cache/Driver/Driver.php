<?php
/**
 * Varimax The Slim PHP Frameworks.
 * varimax.cn
 * *
 * Github: https://github.com/dcto/varimax
 */

namespace VM\Cache\Driver;


class Driver
{

    public function cache($key, $value = null)
    {


    }

    /**
     * @param $name
     * @param $arguments
     */
    public function __call($name, $arguments)
    {
        throw new \ErrorException('Invalid cache method Cache::'.$name);
    }

}