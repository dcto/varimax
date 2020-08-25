<?php
/**
 * Varimax The Full Stack PHP Frameworks.
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
class Route
{
    /**
     * @var int id
     */
    private $id;

    /**
     * @var string current request url
     */
    private $url;

    /**
     * @var string  hash
     */
    private $hash;

    /**
     * @var string
     */
    private $name;

    /**
     * @var $hidden boolean
     */
    private $menu;

    /**
     * @var string current route language
     */
    private $lang;

    /**
     * @var string route icon for menu
     */
    private $icon;

    /**
     * @var string Matching regular expression
     */
    private $regex;

    /**
     * @var string group name
     */
    private $group;

    /**
     * @var string The matched HTTP method
     */
    private $method;

    /**
     * @var array Supported HTTP methods
     */
    private $methods;

    /**
     * @var string current route callable
     */
    private $callable;

    /**
     * @var string callable namespace
     */
    private $namespace;

    /**
     * @var string current controller
     */
    private $controller;

    /**
     * @var string current action
     */
    private $action;

    /**
     * @var array http request parameters
     */
    private $parameters = array();

    /**
     * Constructor.
     *
     * @param string|array $method HTTP method(s)
     * @param string $url URL pattern
     * @param string|array $args Callback function or options
     */
    public function __construct($method, $path, $args = array())
    {
        $this->id        =   isset($args['id']) ? $args['id'] : $path;
        $this->url       =   rtrim(preg_replace('/\(.*?\)/', '', $path),'/');
        $this->name      =   isset($args['name']) ? $args['name'] : (isset($args['id']) ? $args['id'] : $this->name);
        $this->hash      =   hash('crc32b',$this->id);
        $this->lang      =   isset($args['lang']) ? $args['lang'] : isset($args['group']['lang']) ? $args['group']['lang'] : '';
        $this->menu      =   isset($args['menu']) ? $args['menu'] : isset($args['group']['menu']) ? $args['group']['menu'] : '';
        $this->regex     =   isset($args['regex']) ? $args['regex'] : $path;
        $this->group     =   isset($args['group']['id']) ? $args['group']['id'] : $this->group;
        $this->methods   =   array_map('strtoupper', is_array($method) ? $method : array($method));
        $this->callable  =   isset($args['call']) ? $args['call']: $this->callable;
        $this->namespace = isset($args['namespace']) ? $args['namespace'] : (isset($args['group']['namespace']) ? $args['group']['namespace'] : $this->namespace);

        if(is_string($this->callable)){
            if($callable = substr(strrchr($this->callable, "\\"), 1)) {
                $this->namespace = chop($args['call'], $this->callable =  $callable);
            }
            if(strpos($this->callable, '@')){
                list($this->controller, $this->action) = explode('@', $this->callable);
            }
        }

        if (in_array('GET', $this->methods) && !in_array('HEAD', $this->methods)) {
            $this->methods[] = 'HEAD';
            $this->methods[] = 'OPTIONS';
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
        !isset($this->name) && $this->name = $id;
        return $this;
    }

    /**
     * set parameters
     *
     * @return mixed
     */
    public function args()
    {
        if(!($args = func_get_args())){
            return $this->parameters;
        }else{
            $this->parameters = array_merge($this->parameters, $args);
            return $this;
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
}
