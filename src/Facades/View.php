<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class View
 * @method static \VM\View path(...$paths) add base path
 * @method static \VM\View getPath() get all paths
 * @method static \VM\View addPath(string $path, $priority = 100) set path
 * @method static \VM\View cache(string $dir) set view cache dir
 * @method static \VM\View config(array $config) set view config
 * @method static \VM\View assign(mixed ...$variables) set view assigns
 * @method static \VM\View render(string $layout, ...$assign) render layout 
 * @method static \VM\View escape($output) htmlspecialchars output
 * @method static \VM\View flush() flush the cache
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
