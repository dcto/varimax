<?php

/**
 * Varimax The Slim PHP Frameworks.
 *
 * Github: https://github.com/dcto/varimax
 */

namespace VM\Exception;

class ServiceException extends \Error
{
    protected $code = 501;

    protected $message = 'The service was error';
}