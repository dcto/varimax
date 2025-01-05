<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class Captcha
 * @method static mixed get(string $key)
 * @method static void set(string $key, mixed $value)
 * @method static void del(string $key)
 */
class Context extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \VM\Context::class;
    }
}
