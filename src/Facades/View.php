<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class View
 *
 * @method static \Twig_Environment twig()
 *
 * @method static \VM\View path(string $path, $keep = true)
 * @method static \VM\View paths($arg1, $args2, $arg3)
 * @method static \VM\View cache(string $dir)
 * @method static \VM\View show(string $template, array $variables)
 * @method static \VM\View assign(string $var, mixed $val = null)
 * @method static \VM\View render(string $template, array $variables)
 * @method static \VM\View flush()
 */
class View extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'view';
    }
}
