<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class Lang
 *
 * @method static string get(string $key)
 * @method static array all()
 * @method static \VM\I18N\Lang set(string $key, string $value)
 * @method static string i18n(string $lang = null)
 * @method static bool flush()
 * @method static string getLang()
 * @method static \VM\I18N\Lang setLang(string $lang)
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
