<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class Crypt
 *
 * @method static VM\Crypt\Driver\CryptDriver driver(string $str = null)
 * @method static null|string key(string $str = null)
 * @method static string en(string $str, $key = null)
 * @method static string de(string $str, $key = null)
 */
class Crypt extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'crypt';
    }
}
