<?php

/**
 * Varimax The Slim PHP Frameworks.
 *
 * Github: https://github.com/dcto/varimax
 */

namespace VM\Exception;


/**
 * Class HttpException
 *
 * @package VM\Exception
 */
class HttpException extends \RuntimeException
{
    protected $code = 500;

    protected $message = 'HTTP Error!';
}