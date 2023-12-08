<?php

/**
 * Varimax The Slim PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 21:49
 * SITE: https://www.varimax.cn/
 */

namespace VM\Routing;

use VM\Exception\NotFoundException;

/**
 * Router class will load requested Controller / Closure based on URL.
 */
class Router
{
    /**
     * @var array
     */
    private $alias = array();

    /** 
     * @var array 
     */
    private $groups = array();

    /**
     * the current found Route
     * @var Route
     */
    private $router = null;

    /**
     * Array of routes
     * @var array
     */
    private $routes = [];

    /**
     * @var \VM\Http\Request
     */
    private $request;

    /**
     * An array of HTTP request Methods.
     * @var array $methods
     */
    private $methods = ['GET', 'POST', 'PUT', 'HEAD', 'PATCH', 'DELETE', 'OPTIONS'];

    /**
     * @var array
     * Retrieve the additional Routing Patterns from configuration.
     */
    private $regex = array(
        ':*'    =>  ':.+',
        ':str'  =>  ':[\w-]+',
        ':int'  =>  ':[1-9]\d+',
        ':num'  =>  ':[0-9.-]+',
        ':any'  =>  ':[\w!@$^&+-=|]+',
        ':hex'  =>  ':[a-f0-9]+',
        ':hash' =>  ':[a-z0-9]+',
        ':uuid' =>  ':[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'
    );

    /**
     * Array of Route Groups
     * @var array $groupStack
     */
    private $groupStack = [];


    public function __construct(\VM\Http\Request $request)
    {
        $this->request = $request;
    }

    /**
     * Defines a route with or without Callback and Method.
     *
     * @param string $method
     * @param array @args
     */
    public function __call($method, $args)
    {
        if (in_array($method = strtoupper($method), array_merge($this->methods, ['ANY']))) {
            return $this->register($method, array_shift($args), array_shift($args));
        }else{
            throw new \InvalidArgumentException('Invalid Call Router::'.$method);
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

    Method     |  Path                |  Action   |
    ------------------------------------------------
    GET        |  /test               |  index    |
    GET        |  /test/(:id)         |  select   |
    POST       |  /test/create        |  create   |
    PUT/PATCH  |  /test/update/(:id)  |  update   |
    DELETE     |  /test/delete/(:id)  |  delete   |
    */

    /**
     * Defines a Resourceful Routes Group to a target Controller.c
     *
     * @param string $basePath The base path of the resourceful routes group
     * @param string $controller The target Resourceful Controller's name.
     */
    public function resource($basePath, $controller)
    {
        $this->register('GET',                 $basePath,                         ['call' =>$controller .'@index']);
        $this->register('GET',                 $basePath.'/(id:str)',             ['call' =>$controller .'@select']);
        $this->register('POST',                $basePath.'/create',               ['call' =>$controller .'@create']);
        $this->register(array('PUT', 'PATCH'), $basePath.'/update/(id:str)',      ['call' =>$controller .'@update']);
        $this->register('DELETE',              $basePath.'/delete/(id:str)',      ['call' =>$controller .'@delete']);
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
    public function regex($key, $regex = null)
    {
        $regex && $this->regex[$key] = $regex;
        return $this->regex[$key];
    }

    /**
     * alias id to route
     *
     * @param $key
     * @param null $route
     * @return bool|mixed|null
     */
    public function alias($key, $route = null)
    {
        if($route){
            if(isset($this->alias[$key])){
                throw new NotFoundException('Cannot redeclare route id '. $key);
            }
            $this->alias[$key] = $route;
            return $this;
        }else{
            if (!isset($this->alias[$key])) {
                return $key;
            }
            return $this->alias($this->alias[$key]);
        }
    }

    /**
     * The Router alias name
     * @param null $id
     * @return array|object|Route
     */
    public function route($route = null)
    {
        return $this->router($route);
    }

    /**
     * return route object default return current route
     * @param null $id
     * @return array|object|Route
     */
    public function router($route = null)
    {
        if($route) {
            if (isset($this->routes[$route])) {
                return $this->routes[$route];
            } else {
                $route = $this->alias($route);

                if (isset($this->routes[$route])) {
                    return $this->routes[$route];
                }
                throw new NotFoundException('Unknown router  [' . $route . ']');
            }

        }else{
            return $this->router;
        }
    }

    /**
     * return routes array access current params route name
     * @param array ...$ids
     * @return array
     */
    public function routes(...$ids)
    {
        return $ids ? array_intersect_key($this->routes, array_flip($ids)) : $this->routes;
    }

    /**
     * Get groups with routes
     * @param string $routes with the routes
     * @param  callable $where filter the groups
     * @return array
     */
    public function groups($routes = 'routes', $where = null)
    {
        $groups = $where ? array_filter($this->groups, $where, ARRAY_FILTER_USE_BOTH) : $this->groups;
        array_walk($groups, fn(&$g)=>$g[$routes] = array_filter($this->routes, fn($r)=>$r['group'] == $g['id']));
        return $groups;
    }

    /**
     * Get the nested by group
     * 
     * @param string $children
     * @param array $routes; 
     * @return array 
     */
    public function tree($node = null, $children = 'group')
    {
        list($trees, $groups) = [null, $this->groups()];
        foreach($groups as $key => $group){
            if(isset($groups[$group['pid']])){
                $groups[$group['pid']][$children][$group['id']] = &$groups[$key];
            }else{
                $trees[$key] = &$groups[$key];
            }
        }
        return data_get($trees, $node, []);
    }

    

    /**
     * Load routes from file
     * @param $routes
     * @throws NotFoundException
     */
    public function through($routes, $callback = null)
    {
        array_map(function($route){
               require($route.'.php');
        }, (array) $routes);
        return $callback ? $callback($this->dispatch($this->request)) 
        : $this->dispatch($this->request);
    }

    /**
     * Maps a Method and URL pattern to a Callback.
     *
     * @param string $method HTTP method(s) to match
     * @param string $path URL pattern to match
     * @param callback $callback Callback object
     * @return Route
     */
    protected function register($method, $path, $properties)
    {
        $methods = $method == 'ANY' ? $this->methods : array_intersect(array_map('strtoupper', (array) $method), $this->methods);
        
        // Pre-process the Action information.
        $properties = $this->parseAction($properties);

        $properties['group'] = $this->getLastGroup();

        $path = $this->parsePath($path, $properties);

        $path = $path == '' ? '/' : $path;

        if(strpos($path, ':') !== false){
            $properties['regex'] = str_replace(array_keys($this->regex), array_values($this->regex), $path);
            $properties['regex'] = str_replace(['(', ':'], ['(?P<', '>'], $properties['regex']);
        }
        return new Route($methods, $path, $properties);
    }

    /**
     * dispatch to the router
     * @return mixed
     */
    public function dispatch(\VM\Http\Request $request)
    {        
        // Get Http Request Path.
        $path = is_safe($request->path(),  'trim', 'urldecode', 'addslashes', 'strip_tags');

        //Disable Request when xss or sql inject
        if($path !== $request->path()) throw new \InvalidArgumentException('403 Bad Request');

        // Get Route in the Routes stack
        $this->router = $this->callRouter($path);

        // Found a valid Route; process it.
        if(!$this->router) throw new NotFoundException('Unknown route ['.$path.']');

        // Check request method
        if(!in_array($method = $request->method(), $this->router->methods())) throw new NotFoundException('Invalid Request Method');

        // Set Route method;
        $this->router->method($method);
        
        return $this->router;
    }

    /**
     * find router
     * @param $path
     * @param $method
     * @return Route
     * @throws NotFoundException
     */
    protected function callRouter($path)
    {
        if(isset($this->routes[$path])) {
            return $this->routes[$path];
        }else{
            foreach ($this->routes as $key => $route) {
                if(strpos($key, '/(') === false) continue;
                if($this->matchRouter($path, $route)) return $route;
            }
        }
    }

    /**
     * @param $path
     * @param $route Route
     * @param $method
     * @return bool
     */
    protected function matchRouter($path, &$route)
    {
        if (preg_match('#^'.$route->regex().'$#', $path, $matches)) {
            $route->url = array_shift($matches);
            $route->args += array_filter($matches, "is_string", ARRAY_FILTER_USE_KEY);
            return true;
        }
        return false;
    }

    /**
     * addPushRoutes
     *
     * @param $route
     */
    public function addPushToRoutes(Route $route)
    {
        if($url = $route->url){
            if(isset($this->routes[$url])){
                $this->routes[$url]->methods = array_unique(array_merge($this->routes[$url]->methods , $route->methods));
            }else{
                $this->alias($route->id, $route->url);
                $this->routes[$url] = $route;
            }
        }else{
            $this->routes[$route->id] = $route;
        }
        return $this;
    }


    /**
     * Parse url pattern of the route
     * @param $pattern
     * @param $property
     * @return string
     */
    protected function parsePath($route, $property)
    {
        if(isset($property['group']['prefix'])){
            return $property['group']['prefix'].rtrim($route, '/');
        }
        return rtrim($route, '/');
    }

    /**
     * Parse the Route Action into a standard array.
     *
     * @param  \Closure|array  $property
     * @return array
     */
    protected function parseAction($property)
    {
        if (is_string($property) || is_callable($property) || is_object($property)) {
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
        foreach ($action as $value) {
            if (is_callable($value))  return $value;
        }
        return null;
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
        $this->updateGroupStack($attributes);
        /*
         * Once we have updated the group stack, we will execute the user Closure and
         *  merge in the groups attributes when the route is created. After we have
         * run the callback, we will pop the attributes off of this group stack.
         */ 
        call_user_func($callback, $this);
        array_pop($this->groupStack);
    }
    /**
     * Update the group stack with the given attributes.
     *
     * @param  array  $attributes
     * @return void
     */

    /**
     * Update the group stacks
     * @param array $attributes 
     * @return void 
     * @throws InvalidArgumentException 
     */
    private function updateGroupStack(array $attributes)
    {
        $attributes['id'] =  data_get($attributes, 'id', crc32(serialize($attributes)));
        $attributes['name'] = data_get($attributes, 'name', $attributes['id']);
        $attributes = $this->mergeGroup($attributes,  $this->getLastGroup());
        $this->groupStack[] = $this->groups[$attributes['id']] =  $attributes;
    }


    /**
     * Merge the given group attributes.
     * @param  array  $new
     * @param  array  $old
     * @return array
     */
    private function mergeGroup(array $new, array $old)
    {
        $new['pid'] = $old['id'] ?? null;
        $new['prefix'] = $this->formatGroupPrefix($new);
        $new['pipeline'] = $this->formatPipeline($new, $old);
        $new['namespace'] = $this->formatNameSpace($new, $old);
        return $old ? array_replace_recursive(array_diff_assoc($old, ['id', 'pid', 'name', 'prefix']), $new) : $new;
    }

    /**
     * Format group pipeline.
     * @param  array  $new
     * @param  array  $old
     * @return array
     */
    private function formatPipeline($new, $old)
    {
        $pipeline = [];
        isset($old['pipeline']) && $pipeline = array_merge($pipeline, (array) $old['pipeline']);
        isset($new['pipeline']) && $pipeline = array_merge($pipeline, (array) $new['pipeline']);
        return $pipeline;
    }

    /**
     * Format group namespace.
     * @param  array  $new
     * @param  array  $old
     * @return string|null
     */
    private function formatNameSpace(array $new, array $old)
    {
        if (isset($new['namespace'])) {
            if(isset($old['namespace'])){
                return $new['namespace'][0] == '\\' 
                //当以\开头表示重写命名空间前缀
                ? trim($new['namespace'], '\\')
                //否则默认继承上级分组空间前缀
                : trim($old['namespace'], '\\').'\\'.trim($new['namespace'], '\\');
            }else{
                return $new['namespace'];
            }
        }
        return $old['namespace'] ?? null;
    }

    /**
     * Format group prefix
     * @param  array  $new
     * @param  array  $old
     * @return string
     */
    private function formatGroupPrefix(array $group)
    {
        if(isset($group['prefix'])){
            //当以/开头表示重写组前缀
            return $group['prefix'][0] == '/' ? rtrim($group['prefix'], '/') 
            //否则默认继承上级分组前缀
            : $this->getLastGroup('prefix').'/'.trim($group['prefix'], '/');
        }
        return rtrim($this->getLastGroup('prefix'), '/');
    }


    /**
     * Get last group stack or with key
     * @param string|null $key 
     * @return mixed 
     */
    private function getLastGroup(string $key = null)
    {
        if($group = end($this->groupStack)){
            return $key ? $group[$key] ?? null : $group;
        }
        return [];
    }
}
