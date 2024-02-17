<?php

/**
 * Varimax The Slim PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 21:49
 * SITE: https://www.varimax.cn/
 */

namespace VM\Exception;

/**
 * Class FatalError
 *
 * @package VM\Exception
 */
class FatalError extends \RuntimeException
{
    protected $code = 500;

    protected $message = 'Fatal Error!';
}