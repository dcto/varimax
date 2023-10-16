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
 * Class HttpException
 *
 * @package VM\Exception
 */
class HttpException extends \RuntimeException
{
    protected $status = 500;

    protected $message = 'HTTP Error!';
}