<?php

/**
 * Varimax The Full Stack PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 21:49
 * SITE: https://www.varimax.cn/
 */

namespace VM\Exception;

class SystemException extends Exception
{
    protected $status = 500;

    protected $message = 'The Varimax System Error';
}