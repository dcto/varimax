<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class Router
 * @method static \VM\Routing\Route id(string $id = null)
 * @method static \VM\Routing\Route group(array $attributes, Closure $callback)
 * @method static \VM\Routing\Route child(string $pid)
 * @method static \VM\Routing\Route any(string $url, array|\Closure $args = null)
 * @method static \VM\Routing\Route get(string $url, array|\Closure $args = null)
 * @method static \VM\Routing\Route post(string $url, array|\Closure $args = null)
 * @method static \VM\Routing\Route put(string $url, array|\Closure $args = null)
 * @method static \VM\Routing\Route head(string $url, array|\Closure $args = null)
 * @method static \VM\Routing\Route patch(string $url, array|\Closure $args = null)
 * @method static \VM\Routing\Route delete(string $url, array|\Closure $args = null)
 * @method static \VM\Routing\Route options(string $url, array|\Closure $args = null)
 * @method static \VM\Routing\Route share(array $routes)
 * ------------------------------------------------
 * GET        |  /test               |  index    |
 * GET        |  /test/(:id)         |  select   |
 * GET        |  /test/create        |  create   |
 * POST       |  /test               |  insert   |
 * GET        |  /test/(:id)/modify  |  modify   |
 * PUT/PATCH  |  /test/(:id)         |  update   |
 * DELETE     |  /test/(:id)         |  delete   |
 * @method static \VM\Routing\Route route(string $id = null)
 * @method static \VM\Routing\Router routes(string $id = null)
 *
 * @method static \VM\Routing\Router resource(string $url, string $controller)
 * @method static \VM\Routing\Router restful(string $url, string $controller)
 * @method static \VM\Routing\Router regex(string $key, string $regex)
 * @method static  router(string $id = null)
 * @method static \VM\Routing\Router groups($id = null)
 * @method static \VM\Routing\Router dispatch()
 */
class Router extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'router';
    }
}
