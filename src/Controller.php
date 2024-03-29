<?php

/**
 * Varimax The Slim PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 22:03
 * SITE: https://www.varimax.cn/
 */

namespace VM;

/**
 *
 * Class Controller
 *
 * @package VM
 */

abstract class Controller
{
    /**
     * the controller start hook
     */
    protected function on(){}

    /**
     * the controller after hook
     */
    protected function off(){}
}
