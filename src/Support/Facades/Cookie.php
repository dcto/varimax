<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class Cookie
 *
 * @method static \VM\Http\Cookie set(string $name, mixed $value = null, int $expire = null, string $path = null, string $domain = null, bool $secure = null, bool $httpOnly = null, bool $raw = null, string|null $sameSite = null)
 * @method static string get(string $name)
 * @method static bool has(string $name)
 * @method static array all(...$name)
 * @method static bool del(string $name)
 * @method static \VM\Http\Cookie with($attribute, $value = null)
 * @method static \VM\Http\Cookie path(string $value = null)
 * @method static \VM\Http\Cookie prefix(string $value = null)
 * @method static \VM\Http\Cookie domain(string $value = null)
 * @method static \VM\Http\Cookie secure(bool $value = null)
 * @method static \VM\Http\Cookie encrypt(bool $value = null)
 * @method static \VM\Http\Cookie httpOnly(bool $value = null)
 * @method static \VM\Http\Cookie raw(bool $value = null)
 * @method static \VM\Http\Cookie sameSite('none'|'Lax'|'Strict' $value = null)
 * @method static bool delete(string $name)
 * @method static bool remove(...$name)
 * @method static bool clear(...$name)
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
