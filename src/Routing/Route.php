<?php

/**
 * Varimax The Slim PHP Frameworks.
 *
 * Github: https://github.com/dcto/varimax
 */

namespace VM\Routing;

/**
 * The Route class is responsible for routing an HTTP request to an assigned Callback function.
 * @method \VM\Routing\Route|string url(array ...$args)
 * @method string hash()
 * @method \VM\Routing\Route|string name(string $name = null)
 * @method \VM\Routing\Route|string call(string $class)
 * @method \VM\Routing\Route|array args(array $args = [])
 * @method \VM\Routing\Route|string menu(mixed $flag = null)
 * @method \VM\Routing\Route|string lang(string $lang = null)
 * @method \VM\Routing\Route|string icon(string $icon = null)
 * @method \VM\Routing\Route|string regex(string $regex = null)
 * @method \VM\Routing\Route|string group(string $group = null)
 * @method \VM\Routing\Route|string method(string $method = null)
 * @method \VM\Routing\Route|string callable(string $callable = null)
 * @method \VM\Routing\Route|string namespace(string $namespace = null)
 * @method \VM\Routing\Route|string controller(string $class = null)
 * @method \VM\Routing\Route|string action(string $method = null)
 */
class Route implements \ArrayAccess, \JsonSerializable 
{
    /**
     * @var int id
     */
    protected $id;

    /**
     * @var string current request url
     */
    protected $url;

    /**
     * @var string  hash
     */
    protected $hash;

    /**
     * @var string
     */
    protected $name;

    /** 
     * @var string 
     */
    protected $call;

    /**
     * @var array
     */
    protected $args = [];

    /**
     * @var $hidden boolean
     */
    protected $menu;

    /**
     * @var string current route language
     */
    protected $lang;

    /**
     * @var string route icon for menu
     */
    protected $icon;

    /**
     * @var string Matching regular expression
     */
    protected $regex;

    /**
     * @var string group name
     */
    protected $group;

    /**
     * @var string The matched HTTP method
     */
    protected $method;

    /**
     * @var array Supported HTTP methods
     */
    protected $methods = [];

    /**
     * @var array
     */
    protected $pipeline = [];

    /**
     * @var string callable namespace
     */
    protected $namespace;

    /** 
     * @var string 
     * */
    protected $controller;

    /**
     *  @var string 
    */
    protected $action;

    /**
     * Constructor.
     * @param array $method HTTP method(s)
     * @param string $path URL pattern
     * @param array $args options
     */
    public function __construct($methods, $path, array $args = [])
    {
        $this->id  = $args['id'] ?? $path == '/' ? '.' : join('.',array_map(function($p){
            return ($i = strpos($p, ':')) ? ltrim(substr($p, 0 ,$i), '(') : $p;
        }, explode('/', trim($path, '/'))));

        $this->url       = $path;
        $this->name      = $args['name'] ?? null;
        $this->hash      = crc32($this->id);
        $this->call      = $args['call'] ?? null;
        $this->lang      = $args['lang'] ?? $args['group']['lang'] ?? null;
        $this->menu      = $args['menu'] ?? $args['group']['menu'] ?? null;
        $this->regex     = $args['regex'] ?? null;
        $this->group     = $args['group']['id'] ?? null;
        $this->methods   = $methods;
        $this->namespace = $args['namespace'] ?? $args['group']['namespace'] ?? null;

        array_push($this->pipeline, ... (array) $args['pipeline'] ??= null, ... (array) $args['group']['pipeline'] ??= null);
    }

    /**
     * @param null $id
     * @return $this|int|mixed
     */
    public function id($id = null)
    {
        if(!$id) return $this->id;
        $this->id = $id;
        $this->name ??= $id;
        return $this;
    }

    /**
     * 替换URL参数返回URL
     * @param array ...$args
     * @return self|string
     */
    public function url(...$args)
    {
        if (!$args) return $this->url;

        if ($this->regex) {
            return \preg_replace_array('/\(.*?\)/', $args, $this->url);
        }
        return \Uri::uri($this->url)->set(...$args);
    }

    /**
     * Get Set args;
     * @param array|string $key
     * @param string|null $value
     * @return self|array
     */
    public function args($key = null, $value = null)
    {
        if($key) {
           is_array($key) ? $this->args = $key : $this->args[$key] = $value;
           return $this;
        }
        return $this->args;
    }

    /**
     * set property
     * @param string $property
     * @param string|array $value
     * @return self
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
     * @return string
     */
    public function callable()
    {
        if(is_string($this->call) && strpos($this->call, '@')){
            list($this->controller, $this->action) = explode('@', $this->call);
            return trim($this->namespace, '\\').'\\'.$this->call;
        }
        return $this->call;
    }

    /**
     * @param string $property
     * @param mixed $arguments
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function __call($item, $arguments)
    {
        if(!$arguments){
            return $this->$item;
        }else if(sizeof($arguments) == 1){
            if (property_exists($this, $item)) {
                $this->$item = current($arguments);
                return $this;
            }
            throw new  \InvalidArgumentException('Invalid Property Of [Route::' . '$' .$item .']' );
        }
        return $this;
    }

    public function __get($property) 
    {
        return $this->$property;
    }

    public function __set($property, $value)
    {
        $this->$property = $value;
        return $this;
    }

    public function __toString()
    {
        return $this->url;
    }
    
    public function toArray()
    {
        return get_object_vars($this);
    }

    public function offsetExists($property): bool{
        return isset($this->$property);
    } 
    
    #[\ReturnTypeWillChange]
    public function offsetGet($property)
    {
        return $this->$property;
    }
    
    public function offsetSet($property, $value) :void 
    {
        $this->$property = $value;
    }

    public function offsetUnset($property) :void
    {
        unset($this->$property);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
    
    public function __destruct()
    {
        make('router')->addPushToRoutes($this);
    }
}
