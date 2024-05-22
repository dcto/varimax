<?php

/**
 * Varimax The Slim PHP Frameworks.
 *
 * Github: https://github.com/dcto/varimax
 */

namespace VM\Exception;

class NotFoundException extends \Error
{
    protected $code = 404;

    protected $message = '404 Not Found!';

}