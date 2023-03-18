<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class Lang
 *
 * @method static string has(string $key)
 * @method static string get(string $key)
 * @method static string arr(string $key = null)
 * @method static \VM\I18N\Lang set(string $key, string $value)
 * @method static array all()
 * @method static array json($key = null)
 * @method static string i18n(string $lang = null)
 * @method static string detect()
 * @method static bool flush()
 */
class Lang extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'lang';
    }
}
