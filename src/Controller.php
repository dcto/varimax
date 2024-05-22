<?php

/**
 * Varimax The Slim PHP Frameworks.
 *
 * Github: https://github.com/dcto/varimax
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
