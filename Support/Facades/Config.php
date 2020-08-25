<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class Config
 *
 * @method static bool has(string $key)
 * @method static mixed get(string $key, mixed $default = null)
 * @method static array all()
 * @method static \VM\Config\Config set(string $key, mixed $value = null)
 * @method static \VM\Config\Config push(string $key, mixed $value)
 * @method static \VM\Config\Config load(array $array = array())
 * @method static \VM\Config\Config reload()
 * @method static \VM\Config\Config cache(array $array = array(), string $file = null)
 * @method static \VM\Config\Config flush(string $file = '*')
 */
class Config extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'config';
    }
}
