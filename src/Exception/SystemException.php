<?php

/**
 * Varimax The Slim PHP Frameworks.
 *
 * Github: https://github.com/dcto/varimax
 */

namespace VM\Exception;

class SystemException extends \Error
{
    protected $code = 500;

    protected $message = 'The Varimax System Error';
}