<?php

/**
 * Varimax The Slim PHP Frameworks.
 *
 * Github: https://github.com/dcto/varimax
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