<?php
/**
 * Varimax The Slim PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 22:03
 * SITE: https://www.varimax.cn/
 */

namespace VM\Routing;

/**
 * The Route class is responsible for routing an HTTP request to an assigned Callback function.
 * @method \VM\Routing\Route|string url(string $url = null)
 * @method \VM\Routing\Route|string hash(string $hash = null)
 * @method \VM\Routing\Route|string name(string $name = null)
 * @method \VM\Routing\Route|string menu(mixed $flag = null)
 * @method \VM\Routing\Route|string lang(string $lang = null)
 * @method \VM\Routing\Route|string icon(string $icon = null)
 * @method \VM\Routing\Route|string regex(string $regex = null)
 * @method \VM\Routing\Route|string group(string $group = null)
 * @method \VM\Routing\Route|string method(string $method = null)
 * @method \VM\Routing\Route|array methods(array $methods = null)
 * @method \VM\Routing\Route|string call(string $callable = null)
 * @method \VM\Routing\Route|string callable(string $callable = null)
 * @method \VM\Routing\Route|string namespace(string $namespace = null)
 * @method \VM\Routing\Route|string controller(string $controller = null)
 * @method \VM\Routing\Route|string action(string $action = null)
 * @method \VM\Routing\Route|array parameters(mixed $parameters = null)
 */
class Route implements \ArrayAccess
{
    /**
     * @var int id
     */
    public $id;

    /**
     * @var string current request url
     */
    public $url;

    /**
     * @var string  hash
     */
    public $hash;

    /**
     * @var string
     */
    public $name;

    /**
     * @var $hidden boolean
     */
    public $menu;

    /**
     * @var string current route language
     */
    public $lang;

    /**
     * @var string route icon for menu
     */
    public $icon;

    /**
     * @var string Matching regular expression
     */
    public $regex;

    /**
     * @var string group name
     */
    public $group;

    /**
     * @var string The matched HTTP method
     */
    public $method;

    /**
     * @var array Supported HTTP methods
     */
    public $methods;

    /**
     * @var array
     */
    public $pipeline;

    /**
     * @var string current route callable
     */
    public $callable;

    /**
     * @var string callable namespace
     */
    public $namespace;

    /**
     * @var string current controller
     */
    public $controller;

    /**
     * @var string current action
     */
    public $action;

    /**
     * @var array http request parameters
     */
    public $parameters = array();

    /**
     * Constructor.
     *
     * @param string|array $method HTTP method(s)
     * @param string $url URL pattern
     * @param string|array $args Callback function or options
     */
    public function __construct($method, $path, array $args = array())
    {
        $this->id  =  isset($args['id']) ? $args['id'] : join('.', array_map(function($item){
            if(($i = strpos($item, ':')) > 0) {
                return substr($item, 1, $i - 1);
            }else{
                return $item ;
            }
        }, explode('/', trim($path, '/'))));
        $this->url       =   $path;
        $this->hash      =   hash('crc32b', $this->id);
        $this->methods   =   array_map('strtoupper', is_array($method) ? $method : array($method));

        if($args){
            $this->lang      =   isset($args['lang']) ? $args['lang'] : ( isset($args['group']['lang']) ? $args['group']['lang'] : '' );
            $this->menu      =   isset($args['menu']) ? $args['menu'] : ( isset($args['group']['menu']) ? $args['group']['menu'] : '' );
            $this->regex     =   isset($args['regex']) ? $args['regex'] : null;
            $this->group     =   isset($args['group']['id']) ? $args['group']['id'] : $this->group;
            $this->pipeline  =   isset($args['pipeline']) ? $args['pipeline'] : ( isset($args['group']['pipeline']) ? (array) $args['group']['pipeline'] : [] );
            $this->callable  =   isset($args['call']) ? $args['call']: $this->callable;
            $this->namespace =   isset($args['namespace']) ? $args['namespace'] : ( isset($args['group']['namespace']) ? $args['group']['namespace'] : $this->namespace );
        }

        if(is_string($this->callable)){
            if($callable = substr(strrchr($this->callable, "\\"), 1)) {
                $this->namespace = chop($args['call'], $this->callable =  $callable);
            }
            $this->calling();
        }
    }

    /**
     * @param null $id
     * @return $this|int|mixed
     */
    public function id($id = null)
    {
        if(!$id){
            return $this->id;
        }
        $this->id = $id;

        !$this->name && $this->name = $id;

        return $this;
    }

    /**
     * 替换URL
     */
    public function url(...$args)
    {
        isset($args[0]) && is_array($args[0]) && $args = $args[0];
        if($args){
                if($this->regex){
                    $this->url = preg_replace_array('/\(.*?\)/', $args, $this->url);
                }else{
                    $this->url = array_shift($args);
                }
                $this->args($args);
                return $this;
        }else{
                return $this->url;
        }
    }


    /**
     * set property
     * @param $property
     * @param $value
     * @return $this
     */
    public function set($property, $value)
    {
        $this->$property = $value;
        return $this;
    }

    /**
     * get property
     *
     * @param $property
     * @return mixed
     */
    public function get($property)
    {
        return $this->$property;
    }

    /**
     * Fire The Route
     * @param $callback
     * @param array $parameter
     * @return mixed
     */
    public function fire()
    {
        if($this->callable instanceof \VM\Http\Response){
            return $this->callable;

        }else if($this->callable instanceof \Closure) {
            return call_user_func_array($this->callable, $this->parameters);

        }else if(is_string($this->callable) && strpos($this->callable, '@')){
            return app()->call($this->calling(), $this->parameters);

        }else{
            return $this->callable;
        }
    }

    /**
     * @return string
     */
    public function calling()
    {
        if(is_string($this->callable) && strpos($this->callable, '@')){
            list($this->controller, $this->action) = explode('@', $this->callable);
            return trim($this->namespace, '\\').'\\'.$this->callable;
        }
        return $this->callable;
    }

    /**
     * @param $item
     * @return mixed
     */
    private function _property($item)
    {
        $properties = array('call'=>'callable', 'args'=>'parameters');
        return isset($properties[$item]) ? $properties[$item] : $item;
    }

    /**
     * @param $property
     * @param $arguments
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function __call($method, $arguments)
    {
        if(!$arguments){
            return $this->get($this->_property($method));

        }else if(sizeof($arguments) == 1){
            if (property_exists($this, $method = $this->_property($method))) {
                $this->set($method, current($arguments));
                return $this;
            }
            throw new  \InvalidArgumentException('Invalid Property Of [Route::' . '$' .$method .']' );
        }
        return $this;
    }

    /**
     * mixed property
     * @param mixed $name 
     * @return mixed 
     */
    public function __get($property) 
    {
        return $this->_property($property);
    }

    /**
     * toString url
     */
    public function __toString()
    {
        return $this->url();
    }
    
    public function offsetExists($property): bool{
        return isset($this->$property);
    } 
    
    public function offsetGet($property):mixed{
        return $this->$property;
    }
    
    public function offsetSet($property, $value):void{
        $this->$property = $value;
    }

    public function offsetUnset($property):void{
        unset($this->$property);
    }
    
    /**
     * Push to router
     */
    public function __destruct()
    {       
        $this->name = $this->name ?: $this->id;
        $this->calling();
        make('router')->addPushToRoutes($this);
    }
}
