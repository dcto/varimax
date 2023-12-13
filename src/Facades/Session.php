<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class Session
 *
 * @method static \VM\Http\Session start(array $options = [])
 * @method static \VM\Http\Session id($session_id = null) Get and Set Session id
 * @method static int has(string|array ...$keys)
 * @method static string|array|null get(string $key, mixed $default = null)
 * @method static \VM\Http\Session set(string $key, mixed $value)
 * @method static \VM\Http\Session del(string $key)
 * @method static array all()
 * @method static \VM\Http\Session decode(string $session_data)
 * @method static \VM\Http\Session delete(string $key)
 * @method static \VM\Http\Session remove(string $key)
 * @method static \VM\Http\Session replace(array $attributes)
 * @method static array|string config($key = null) get session options
 * @method static int count() count session
 * @method static \VM\Http\Session flush() flush the all session
 * @method static bool clear() clear the all session
 * @method static bool destroy() destory the all session
 * @method static bool regenerate($delete = false)
 * @method static bool status() 
 */
class Session extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'session';
    }
}
