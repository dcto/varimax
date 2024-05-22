<?php

/**
 * Varimax The Slim PHP Frameworks.
 *
 * Github: https://github.com/dcto/varimax
 */

namespace VM\Exception;

/**
 * Class FileException
 *
 * @package VM\Exception
 */
class FileException extends \Error
{
    protected $code = 403;

    protected $message = 'File Error!';
}