<?php

/**
 * Varimax The Slim PHP Frameworks.
 *
 * Github: https://github.com/dcto/varimax
 */

namespace VM\Exception;

class LogicException extends \LogicException
{
    protected $code = 500;

    protected $message = 'Logic Exception!';
}