<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class Cookie
 *
 * @method static Symfony\Component\HttpFoundation\Cookie make(string $name, string $value, int $expire = 0, string $path = null, string $domain = null, bool $secure = false, bool $httpOnly = true)
 * @method static string get(string $name)
 * @method static bool has(string $name)
 * @method static array all(...$name)
 * @method static bool del(string $name)
 * @method static bool delete(string $name)
 * @method static bool remove(...$name)
 * @method static bool clear(...$name)
 * @method static \VM\Http\Cookie set(string $name, mixed $value = null, int $expire = 0, string $path = '/', string $domain = null, boolean $secure = false, boolean $httpOnly = true)
 */
class Cookie extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cookie';
    }
}
