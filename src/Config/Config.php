<?php
/**
 * Varimax The Slim PHP Frameworks.
 *
 * Github: https://github.com/dcto/varimax
 */

namespace VM\Config;

/**
 * @package Config
 */
class Config implements \ArrayAccess
{
    /**
     * All of the configuration item.
     *
     * @var array
     */
    protected $item = [
        'app'=>['key'=>'VM','log'=>0, 'charset'=>'utf-8', 'language'=>'zh_CN', 'timezone'=>'PRC'],
        'dir'=>['app'=>_DIR_, 'www'=>_WWW_],
        'database'=>[],'cache'=>[],'cookie'=>[],'session'=>[],'service'=>[],'pipeline'=>[]
    ];

    /**
     * Temp get keys
     */
    protected $keys = '*';

    /**
     * Create a new configuration repository.
     *
     * @param  array  $item
     * @return void
     */
    public function __construct()
    {
        $this->item = array_replace_recursive($this->item, ...array_map(function($config){
                return is_file($config) ? require $config : [];
        }, [root('config', 'config.php'), app_dir('config.php'),  root('config', getenv('ENV').'.php')] ));
    }

    /**
     * Determine if the given configuration value exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->item[$key]);
    }

    /**
     * Get the specified configuration value.
     *
     * @param  array|string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return data_get($this->item, $key, $default);
    }

    /**
     * Set a given configuration value.
     *
     * @param  array|string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function set($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];
        foreach ($keys as $key => $value) {
            data_set($this->item, $key, $value);
        }
        return $this;
    }

    /**
     * dynamic addition config key from runtime
     * @param string $key
     * @param array $value
     * @return $this
     */
    public function add($key,  $value = null)
    {
        if (is_null($value) && !isset($this->item[$key])) {
            array_map(fn($config)=>is_file($config) && $this->set($key, require($config)), 
            [root('config',$key.'.php'), app_dir('config',$key.'.php')]);
            return $this;
        } else if(is_string($value) && is_file($value)){
            return $this->set($key, require($value));
        }else{
            return $this->set($key, $value);
        }
    }

    /**
     * Prepend a value onto an array configuration value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function prepend($key, $value)
    {
        $array = $this->get($key);

        array_unshift($array, $value);

        $this->set($key, $array);

        return $this;
    }

    /**
     * Push a value onto an array configuration value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function push($key, $value)
    {
        $array = $this->get($key);
        $array[] = $value;
        $this->set($key, $array);
        return $this;
    }

    /**
     * Get all of the configuration item for the application.
     *
     * @return array
     */
    public function all()
    {
        return $this->item;
    }

    /**
     * Determine if the given configuration option exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function offsetExists($key) : bool
    {
        return $this->has($key);
    }

    /**
     * Get a configuration option.
     *
     * @param  string  $key
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set a configuration option.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value) : void
    {
        $this->set($key, $value);
    }

    /**
     * Unset a configuration option.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key) : void
    {
        $this->set($key, null);
    }
}
