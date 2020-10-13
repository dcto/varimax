<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class Log
 * @method static \VM\Logger\Logger log($level, string $message, array $context = [])
 * @method static \VM\Logger\Logger dir(string $path, mixed $file = null)
 * @method static \VM\Logger\Logger setLogFilePath($logDirectory)
 * @method static \VM\Logger\Logger getLogFilePath()
 * @method static \VM\Logger\Logger getLastLogLine()
 * @method static \VM\Logger\Logger file(string $filename = null)
 * @method static \VM\Logger\Logger write(string $message = null)
 * @method static \VM\Logger\Logger alert(string $message, array $context = [])
 * @method static \VM\Logger\Logger critical(string $message, array $context = [])
 * @method static \VM\Logger\Logger error(string $message, array $context = [])
 * @method static \VM\Logger\Logger warning(string $message, array $context = [])
 * @method static \VM\Logger\Logger notice(string $message, array $context = [])
 * @method static \VM\Logger\Logger info(string $message, array $context = [])
 * @method static \VM\Logger\Logger debug(string $message, array $context = [])
 * @method static Logger()
 */
class Log extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'log';
    }
}
