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
     * @var string
     */
    private $url = null;

    /**
     * @var array
     */
    private $alias = array();

    /*
     * @var $group
     */
    private $group = array();

    /**
     * Matched Route, the current found Route, if any.
     *
     * @var $router Route
     */
    private $router = null;

    /**
     * Array of routes
     *
     * @var $routes array
     */
    private $routes = array();

    /**
     * @var \VM\Http\Request
     */
    private $request;

    /**
     * An array of HTTP request Methods.
     *
     * @var array $methods
     */
    private static $methods = ['GET', 'POST', 'PUT', 'HEAD', 'PATCH', 'DELETE', 'OPTIONS'];

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
     *
     * @var array $groupStack
     */
    private $groupStack = array();


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
        if (($method == 'any') || in_array($method = strtoupper($method), static::$methods)) {
            return $this->register($method, array_shift($args), array_shift($args));
        }else{
            throw new \InvalidArgumentException('Invalid Router::'.$method);
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
        if($regex){
            $this->regex[$key] = $regex;
        }

        return isset($this->regex[$key]) ? $this->regex[$key] : null;
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
                throw new NotFoundException('Unknown route [' . $route . ']');
            }

        }else{
            return $this->router;
        }
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
     * format the route item list
     * @param null $node
     * @return array
     */
    public function groups($node = null, $recursion = true)
    {
        $groups = $recursion ? $this->tree() : $this->group;
        
        return $node ? data_get($groups, $node, array()) : $groups;
    }

    /**
     * Get the groups by pid
     * 
     * @param bool 
     * @return array 
     */
    public function tree()
    {
        $groups = array();
        foreach($this->group as $key => $group){
            if(isset($this->group[$group['pid']])){
                $this->group[$group['pid']]['group'][$group['id']] = &$this->group[$key];
            }else{
                $groups[$key] = &$this->group[$key];
            }
        }
        return $groups;
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
     * Test the route match
     * @todo 未完善
     */
    public function test(Route $route)
    {
        //验证匹配
        if(!preg_match('#^'.$this->regex.'$#', $this->url)){
            throw new NotFoundException('Invalid url: '. $route->url());
        }
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
        if (is_string($method) && (strtoupper($method) == 'ANY')) {
            $methods = static::$methods;
        } else {
            // Ensure the requested Methods are valid ones.
            $methods = array_intersect(array_map('strtoupper', (array) $method), static::$methods);
        }
        // Pre-process the Action information.
        $properties = $this->parseAction($properties);

        if ($this->groupStack) {
            $properties['group'] = end($this->groupStack);
        }

        $path = $this->parseRoute($path, $properties);

        if(strpos($path, '(') !== false){
            // $properties['regex'] = preg_replace_callback("/\(([^()]+)\)/", function($matches) {
            // }, $path);
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
        $path == $request->path() || die(header("HTTP/1.0 404 Not Found"));

        // Get Http Request Method
        $method = $request->method();

        // Get Route in the Routes stack
       $this->router = $this->callRouter($path, $method);

        // Found a valid Route; process it.
        if(!$this->router) throw new NotFoundException();

        // Set Route method;
        $this->router->method($method);

        if(!in_array($method, $this->router->methods())) throw new NotFoundException();
        
        return $this->router;
    }

    /**
     * find router
     *
     * @param $path
     * @param $method
     * @return Route
     * @throws NotFoundException
     */
    protected function callRouter($path, $method)
    {
        if(isset($this->routes[$path])) {
            return $this->routes[$path];
        }else{
            foreach ($this->routes as $key => $route) {
                if(strpos($key, '/(') === false) continue;
                if($this->Matching($path, $route, $method)) return $route;
            }
        }
    }

    /**
     * @param $path
     * @param $route Route
     * @param $method
     * @return bool
     */
    protected function Matching($path, &$route, $method)
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
                $this->routes[$url]->methods = array_unique(array_merge( $this->routes[$url]->methods , $route->methods));
            }else{
                $this->alias($route->id, $route->url);
                $this->routes[$url] = $route;
            }
        }else{
            $this->routes[$route->id] = $route;
        }

        if($route->group){
            $this->group[$route->group]['routes'][$route->id] = $route;
        }
        return $this;
    }


    /**
     * parse pattern of the route path
     * @param $pattern
     * @param $property
     * @return string
     */
    protected function parseRoute($route, $property)
    {
        $prefix = data_get($property,'prefix') ?: data_get(data_get($property,'group'),'prefix');
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
            if (is_callable($value)) {
                return $value;
            }
        }
        return null;
        /*
        return Arr::first($action, function($key, $value){
            return is_callable($value);
        });
        */
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
    protected function updateGroupStack(array $attributes)
    {
        $attributes['id'] =  data_get($attributes, 'id', crc32(serialize($attributes)));
        $attributes['name'] = data_get($attributes, 'name', $attributes['id']);
        $attributes = $this->mergeGroup($attributes, end($this->groupStack));
        
        if($this->group && isset($this->group[$attributes['id']])){
            throw new \InvalidArgumentException('The Route Group exist');
        }

        $this->group[$attributes['id']] =  $this->groupStack[] = $attributes;
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
        $new['pid'] = isset($old['id']) ? $old['id'] : 0;

        $new['namespace'] = $this->formatNameSpace($new, $old);

        $new['prefix'] = $this->formatGroupPrefix($new);

        return $old ? array_replace_recursive(array_diff_assoc($old, ['id', 'pid', 'name', 'prefix']), $new) : $new;
    }

    /**
     * Format the uses prefix for the new group attributes.
     *
     * @param  array  $new
     * @param  array  $old
     * @return string|null
     */
    private function formatNameSpace($new, $old)
    {
        if (isset($new['namespace'])) {
            if(isset($old['namespace'])){
                if($new['namespace'][0] == '\\'){
                    return trim($new['namespace'], '\\');
                }else{
                    return trim($old['namespace'], '\\').'\\'.trim($new['namespace'], '\\');
                }
            }else{
                return $new['namespace'];
            }
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
    private function formatGroupPrefix(array $group)
    {
        if(isset($group['prefix'])){
            if($group['prefix'][0] == '/'){
                return $group['prefix'];
            }else{
                return rtrim($this->getLastGroupPrefix(),'/').'/'.trim($group['prefix'], '/') ;
            }
        }
        return $this->getLastGroupPrefix().'/';
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
    
}
