<?php

/**
 * Varimax The Full Stack PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 21:49
 * SITE: https://www.varimax.cn/
 */

namespace VM\Routing;

use Arr;
use VM\Http\Request;
use VM\Http\Redirect;
use VM\Http\Response;
use VM\Exception\NotFoundException;

/**
 * Router class will load requested Controller / Closure based on URL.
 */
class Router
{
    /*
     * @var $group
     */
    private $group = array();

    /**
     * Matched Route, the current found Route, if any.
     *
     * @var $router Route
     */
    private $router;

    /**
     * Array of routes
     *
     * @var $routes array
     */
    private $routes = array();

    /**
     * An array of HTTP request Methods.
     *
     * @var array $methods
     */
    private static $methods = ['GET', 'POST', 'PUT', 'HEAD', 'PATCH', 'DELETE', 'OPTIONS'];


    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var array
     * Retrieve the additional Routing Patterns from configuration.
     */
    private $regex = array(
        ':*'    =>  '.*',
        ':id'   =>  '\d+',
        ':any'  =>  '[^/]+',
        ':num'  =>  '[0-9]+',
        ':str'  =>  '[a-zA-Z]+',
        ':hex'  =>  '[a-f0-9]+',
        ':hash' =>  '[a-z0-9]+',
        ':uuid' =>  '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'
    );

    /**
     * Array of Route Groups
     *
     * @var array $groupStack
     */
    private $groupStack = array();

    /**
     * Defines a route with or without Callback and Method.
     *
     * @param string $method
     * @param array @params
     */

    public function __call($method, $params)
    {
        if (($method == 'any') || in_array(strtoupper($method), static::$methods)) {
            // Get the Route.
            $route = array_shift($params);
            if (!$route || !$params) {
               // throw new SystemException('Invalid Parameter Of The Router');
            }
            $property = array_shift($params);

            //Register Route.
           return $this->register($method, $route, $property);
        }else{
            throw new \InvalidArgumentException('Invalid router of the method Router::'.$method);
        }
    }

    /**
     * Register many request URIs to a single Callback.
     *
     * <code>
     *      // Register a group of URIs for a Callback
     *      Router::share(array(array('GET', '/'), array('POST', '/home')), 'App\Controllers\Home@index');
     * </code>
     *
     * @param  array  $routes
     * @param  mixed  $callback
     * @return void
     */
    public function share($routes, $callback)
    {
        foreach($routes as $route) {
            $method = array_shift($route);
            $path  = array_shift($route);
            $this->register($method, $path, $callback);
        }
    }

    /* The Resourceful Routes in the Laravel Style.

    Method     |  Path                 |  Action   |
    ------------------------------------------------
    GET        |  /test               |  index    |
    GET        |  /test/(:id)         |  select   |
    GET        |  /test/create        |  create   |
    POST       |  /test               |  insert   |
    GET        |  /test/(:id)/modify  |  modify   |
    PUT/PATCH  |  /test/(:id)         |  update   |
    DELETE     |  /test/(:id)         |  delete   |

    */

    /**
     * Defines a Resourceful Routes Group to a target Controller.c
     *
     * @param string $basePath The base path of the resourceful routes group
     * @param string $controller The target Resourceful Controller's name.
     */
    public function resource($basePath, $controller)
    {
        $this->register('get',                 $basePath,                 $controller .'@index');
        $this->register('get',                 $basePath .'/(:any)',      $controller .'@select');
        $this->register('get',                 $basePath .'/create',      $controller .'@create');
        $this->register('post',                $basePath,                 $controller .'@insert');
        $this->register('get',                 $basePath .'/(:any)/modify', $controller .'@modify');
        $this->register(array('put', 'patch'), $basePath .'/(:any)',      $controller .'@update');
        $this->register('delete',              $basePath .'/(:any)',      $controller .'@delete');

    }

    /**
     * resource alias name
     * @param $bassPath
     * @param $controller
     */
    public function restful($bassPath, $controller)
    {
        $this->resource($bassPath, $controller);
    }

    /**
     * global pattern
     * @param $key
     * @param $regex
     */
    public function regex($key = null, $regex = null)
    {
        if($regex){
            $this->regex[$key] = $regex;
        }else if($key){
            return $this->regex[$key];
        }else{
            return $this->regex;
        }
    }

    /**
     * The Router alias name
     * @param null $id
     * @return array|object|Route
     */
    public function route($id = null)
    {
        return $this->router($id);
    }

    /**
     * return route object default return current route
     * @param null $id
     * @return array|object|Route
     */
    public function router($id = null)
    {
        if($id){
             if(isset($this->routes[$id])){
                 return $this->routes[$id];
             }else{
                 foreach($this->routes as $router) {
                     if($router->id == $id) {
                         return $router;
                     }
                 }
             }
            throw new NotFoundException('Unknown the ['.$id. '] route.');
        }
        /*
        if(!$this->router){
            throw new NotFoundException('Current route can not available.');
        }
        */
        return $this->router;
    }

    /**
     * return routes array access current params route name
     * @return array
     */
    public function routes()
    {
        $ids = func_get_args();
        $routes = array();
        foreach ($this->routes as $id => $route) {
            if($ids && !in_array($id, $ids, true)) continue;
                $routes[$id] = $route;
        }
        return $routes;
    }

    /**
     * Create a route group with shared attributes.
     *
     * @param  array  $attributes
     * @param  \Closure  $callback
     * @return void
     */
    public function group(array $attributes, \Closure $callback)
    {
        //array_push($this->groupStack, $attributes);
        $this->updateGroupStack($attributes);
        //$this->groupStack[] = $attributes;//array_merge_recursive($this->groupStack, $attributes);
        // Once we have updated the group stack, we will execute the user Closure and
        // merge in the groups attributes when the route is created. After we have
        // run the callback, we will pop the attributes off of this group stack.
        call_user_func($callback, $this);

        array_pop($this->groupStack);
    }

    /**
     * format the route item list
     * @param null $key
     * @return array
     */
    public function groups()
    {
        $tags = func_get_args();
        $groups = array();
        foreach ($this->group as $id => $group) {
            if($tags && !in_array($id, $tags)) continue;
            $groups[$id] = $group;
            unset($groups[Arr::get($group, 'pid')]);
            foreach ($this->routes as $route){
                $route->group == $id && $groups[$id]['routes'][] = $route;
            }
        }
        if(func_num_args() == 1){

            if(isset($groups[$tags[0]])){
                return $groups[$tags[0]];
            }
        }
        return $groups;
    }

    /**
     * get child router group
     * @param $pid
     * @return array
     */
    public function child($pid)
    {
        $groups = array();
        foreach ($this->group as $id => $group) {
            if(isset($group['pid']) && $group['pid'] == $pid) {
            $groups[$id] = $group;
            unset($groups[Arr::get($group, 'pid')]);
            foreach ($this->routes as $route){
                $route->group == $id && $groups[$id]['routes'][] = $route;
            }
            }
        }
        return $groups;
    }

    /**
     * Load routes from file
     * @param $routes
     * @throws NotFoundException
     */
    public function load($routes)
    {
        if(is_readable($routes)){

            require($routes);

            return $this;
        }else{

            throw new NotFoundException('Unable routing from '. basename($routes));
        }
    }

    /**
     * Maps a Method and URL pattern to a Callback.
     *
     * @param string $method HTTP method(s) to match
     * @param string $route URL pattern to match
     * @param callback $callback Callback object
     * @return Route
     */
    protected function register($method, $route, $properties)
    {
        //Merge the property
        //Prepare the route Methods.
        if (is_string($method) && (strtoupper($method) == 'ANY')) {
            $methods = static::$methods;
        } else {
            $methods = array_map('strtoupper', (array) $method);
            // Ensure the requested Methods are valid ones.
            $methods = array_intersect($methods, static::$methods);
        }

        // Pre-process the Action information.
        $properties = $this->parseAction($properties);

        if ($this->groupStack) {
            $properties['group'] = end($this->groupStack);
        }

        $route = $this->parseRoute($route, $properties);

        $properties['regex'] = str_replace(array_keys($this->regex), array_values($this->regex), $route);

        return $this->addPushToRoutes(new Route($methods, $route, $properties));
    }

    /**
     * dispatch to the router
     * @return mixed
     */
    public function dispatch(Request $request, Response $response)
    {
        //
        $this->request = $request;

        //
        $this->response = $response;

        // Get the Method and Path.
        $path = trim(urldecode($request->path()));

        // Execute the Routes matching loop.
        $route = $this->router = $this->callRouter($path, $method = $request->method());
        // Found a valid Route; process it.
        $route->method($method);

        //if(!in_array($method, $route->methods())) throw new \InvalidArgumentException('The route '.$path. ' not allow '.$method. ' method');
            if($route->group()) {

                if (!$group = Arr::get($this->group, $route->group())) {
                    throw new NotFoundException('Does not define ' . $route->group() . ' of router group');
                }

                /**
                 * construct callback
                 */
                if ($callable = Arr::get($group, 'call')) {

                    if (is_array($callable)) {
                        $callable = $this->Fire(array_shift($callable), array_shift($callable));
                    } else {
                        $callable = $this->Fire($callable);
                    }

                    if ($callable instanceof Response\ResponseInterface || $callable instanceof  Redirect) {
                        return $callable;
                    }
                }
            }

            /**
             * construct instance and include hook
             */
            $instance = $this->Fire($route->calling(), $route->args());
            if(is_string($instance)){
                return $response->make($instance);
            }else{
                return $instance;
            }
    }

    /**
     * find router
     *
     * @param $path
     * @param $method
     * @return mixed
     * @throws NotFoundException
     */
    protected function callRouter($path, $method)
    {
        if(isset($this->routes[$path])) {
            $router = $this->routes[$path];
            if ($this->Matching($path, $router, $method)) {
                return $router;
            }
        }
        foreach ($this->routes as $router) {
            if ($this->Matching($path, $router, $method)) {
                return $router;
            }
        }
        throw new NotFoundException("Invalid Request: $path");
    }

    /**
     * @param $path
     * @param $route Route
     * @param $method
     * @return bool
     */
    private function Matching($url, $route, $method)
    {
        if(!in_array($method, $route->methods())) return false;
        if (preg_match('#^'.$route->regex().'$#', $url, $matches)) {
            $route->url(array_shift($matches));
            $route->parameters(array_merge($route->parameters(), $this->parameters($matches)));
            return true;
        }
        return false;
    }

    /**
     * ThroughRoute
     * @param $callback
     * @param array $parameter
     * @return mixed
     */
    protected function Fire($callable, $parameters = [])
    {
        if($callable instanceof Response\ResponseInterface ||  $callable instanceof Redirect){
            return $callable;

        }else if($callable instanceof \Closure) {
            return call_user_func_array($callable, $parameters);

        }else if(is_array($callable) || is_object($callable)){
            return print_r($callable);

        }else if(is_string($callable) && strpos($callable, '@')){
            return app()->call($callable, $parameters);

        }else if(is_string($callable)){
            echo $callable;
        }

        throw new NotFoundException("Invalid router callable $callable of the {$this->router->url()}");
    }

    /**
     * addPushRoutes
     * @param $route
     */
    public function addPushToRoutes(Route $router)
    {
        if($id = $router->id()){
            if(isset($this->routes[$id])){
                throw new NotFoundException("The route [$id] exist");
            }
           return $this->routes[$id] = $router;
        }
        return $this->routes[] = $router;
    }


    /**
     * parse pattern of the route path
     * @param $pattern
     * @param $property
     * @return string
     */
    private function parseRoute($route, $property)
    {
        $prefix = Arr::get($property,'prefix') ?: Arr::get(Arr::get($property,'group'),'prefix');
        $route = '/'.trim(trim($prefix,'/').'/'.trim($route, '/'),'/');
        return $route;
    }

    /**
     * Parse the Route Action into a standard array.
     *
     * @param  \Closure|array  $property
     * @return array
     */
    protected function parseAction($property)
    {
        if (is_string($property) || is_callable($property)) {
            // A string or Closure is given as Action.
            return array('call' => $property);
        } else if(is_array($property) && !isset($property['call'])) {
            // Find the Closure in the Action array.
            $property['call'] = $this->findClosure($property);
        }
        return $property;
    }

    /**
     * Find the Closure in an action array.
     *
     * @param  array  $action
     * @return \Closure
     */
    protected function findClosure(array $action)
    {
        return Arr::first($action, function($key, $value){
            return is_callable($value);
        });
    }
    /**
     * Update the group stack with the given attributes.
     *
     * @param  array  $attributes
     * @return void
     */
    protected function updateGroupStack(array $attributes)
    {
        if (! empty($this->groupStack)) {
            $attributes = $this->mergeGroup($attributes, end($this->groupStack));
            $attributes['pid'] = Arr::get(end($this->groupStack),'id');
        }
        $id = $attributes['id'] = Arr::get($attributes,'id', crc32(serialize($attributes)));

        /*
        if(isset($this->group[$id])){
            throw new SystemException('The Route Group exist');
        }
        */
        $attributes['name'] = Arr::get(end($attributes), 'name', $id);
        $this->group[$id] = $this->groupStack[] = $attributes;
    }

    /**
     * Merge the given group attributes.
     *
     * @param  array  $new
     * @param  array  $old
     * @return array
     */
    private function mergeGroup($new, $old)
    {
        $new['namespace'] = static::formatUsesPrefix($new, $old);

        $new['prefix'] = static::formatGroupPrefix($new, $old);

        return array_replace_recursive(Arr::except($old, ['namespace', 'prefix']), $new);
    }

    /**
     * Format the uses prefix for the new group attributes.
     *
     * @param  array  $new
     * @param  array  $old
     * @return string|null
     */
    protected static function formatUsesPrefix($new, $old)
    {
        if (isset($new['namespace'])) {
            return isset($old['namespace'])
                ? trim($old['namespace'], '\\').'\\'.trim($new['namespace'], '\\')
                : trim($new['namespace'], '\\');
        }

        return isset($old['namespace']) ? $old['namespace'] : null;
    }

    /**
     * Format the prefix for the new group attributes.
     *
     * @param  array  $new
     * @param  array  $old
     * @return string|null
     */
    protected static function formatGroupPrefix($new, $old)
    {
        $oldPrefix = isset($old['prefix']) ? $old['prefix'] : null;

        if (isset($new['prefix'])) {
            return trim($oldPrefix, '/').'/'.trim($new['prefix'], '/');
        }

        return $oldPrefix;
    }

    /**
     * Get the prefix from the last group on the stack.
     *
     * @return string
     */
    private function getLastGroupPrefix()
    {
        if (! empty($this->groupStack)) {
            $last = end($this->groupStack);

            return isset($last['prefix']) ? $last['prefix'] : '';
        }

        return '';
    }


    /**
     * Format Parameters
     * @param $parameters
     * @return array
     */
    private function parameters($parameters)
    {
        if ($parameters) {
            return array_map(function ($value) {
                return trim(is_string($value) ? rawurldecode($value) : $value, '/');
            }, $parameters);
        }
        return array();
    }



    /*
    private function compilerRoute($route)
    {
        $path = explode('/', $route->getPattern());

        $params = array();

        $path = array_map(function($p) use ($route){
                if(strpos($p, ':')){
                    //parse_str(strtr($p, ':|', '=&'), $params);
                    //$route->setParameters($params);
                    list($key, $val) = explode(':', $p);

                    if(isset($this->regex[$key])){
                        return  $this->regex[$key];
                    }
                }

            return $p;
        },$path);
    }
    */
}