<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class Router
 * @method static \VM\Routing\Route id(string $id = null)
 * @method static \VM\Routing\Route group(array $attributes, Closure $callback)
 * @method static \VM\Routing\Route any(string $url, array|\Closure $args = null)
 * @method static \VM\Routing\Route get(string $url, array|\Closure $args = null)
 * @method static \VM\Routing\Route post(string $url, array|\Closure $args = null)
 * @method static \VM\Routing\Route put(string $url, array|\Closure $args = null)
 * @method static \VM\Routing\Route head(string $url, array|\Closure $args = null)
 * @method static \VM\Routing\Route patch(string $url, array|\Closure $args = null)
 * @method static \VM\Routing\Route delete(string $url, array|\Closure $args = null)
 * @method static \VM\Routing\Route options(string $url, array|\Closure $args = null)
 * @method static \VM\Routing\Route route(string $id = null) Get current route
 * @method static \VM\Routing\Router routes(string $id = null) Get all routes with id
 *
 * @method static \VM\Routing\Router resource(string $url, string $controller)
 * @method static \VM\Routing\Router restful(string $url, string $controller)
 *    Method     |  Path                |  Action   |
 *    ------------------------------------------------
 *    GET        |  /test               |  index    |
 *    GET        |  /test/(:id)         |  select   |
 *    POST       |  /test               |  create   |
 *    PUT/PATCH  |  /test/(:id)         |  update   |
 *    DELETE     |  /test/(:id)         |  delete   |
 * @method static \VM\Routing\Router regex(string $key, string $regex)
 * @method static \VM\Routing\Router alias(string $key, string $route = null)
 * @method static \VM\Routing\Route  router(string $id = null) Current Route
 * @method static array tree($nodes = '*', $child = 'children')
 * @method static array groups($routes = 'routes', ...$nodes) Get Group by $id Routes Struct
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
