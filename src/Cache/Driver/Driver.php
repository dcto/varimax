<?php
/**
 * Varimax The Slim PHP Frameworks.
 * varimax.cn
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-14 15:22
 * SITE: https://www.varimax.cn/
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