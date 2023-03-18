<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class Session
 *
 * @method static \VM\Http\Session start($handler = null)
 * @method static \VM\Http\Session id($session_id = null)
 * @method static \VM\Http\Session setId($session_id)
 * @method static \VM\Http\Session getId($session_id)
 * @method static \VM\Http\Session has(string $name)
 * @method static \VM\Http\Session get(string $name, mixed $default = null)
 * @method static \VM\Http\Session set(string $name, mixed $value)
 * @method static \VM\Http\Session del(string $name)
 * @method static \VM\Http\Session all()
 * @method static \VM\Http\Session decode(string $session_data)
 * @method static \VM\Http\Session delete(string $name)
 * @method static \VM\Http\Session remove(string $name)
 * @method static \VM\Http\Session replace(array $attributes)
 * @method static \VM\Http\Session clear()
 * @method static \VM\Http\Session count()
 * @method static \VM\Http\Session flush()
 * @method static \VM\Http\Session destroy()
 * @method static \VM\Http\Session regenerate($delete = false)
 * @method static \VM\Http\Session isStarted()
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
