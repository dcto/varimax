<?php

/**
 * Varimax The Full Stack PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 21:49
 * SITE: https://www.varimax.cn/
 */

namespace VM\Config;

use ArrayAccess;
use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Support\Arr;

class Config implements ArrayAccess, ConfigContract
{
    /**
     * All of the configuration item.
     *
     * @var array
     */
    protected $item = [];

    /**
     * Create a new configuration repository.
     *
     * @param  array  $item
     * @return void
     */
    public function __construct(array $item = [])
    {
        if (is_file($config = root('config', 'config.php'))) {
            $this->item = (array) require($config);
        }
        if (is_file($config = _DIR_ . _DS_ . 'config.php')) {
            $this->set((array) require($config));
        }
        if(getenv('ENV')){
            if(is_file($config_env = root('config', getenv('ENV').'.php'))){
                $this->set((array) require($config_env));
            }
        }
        if (!$this->item) {
            throw new \ErrorException('Unable load config');
        }
    }

    /**
     * Determine if the given configuration value exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function has($key)
    {
        return Arr::has($this->item, $key);
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
        if(!isset($this->item[$item =\Str::before($key, '.')])){
            $this->add($item);
        }
        return Arr::get($this->item, $key, $default);
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

        $keys = Arr::dot($keys);

        foreach ($keys as $key => $value) {
            Arr::set($this->item, $key, $value);
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
            if (is_file($config = runtime('config', $key . '.php'))) {
                return $this->set($key, require($config));
            }
        } else if($value) {
            return $this->set($key, $value);
        }

        return $this;
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
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Get a configuration option.
     *
     * @param  string  $key
     * @return mixed
     */
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
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Unset a configuration option.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->set($key, null);
    }
}
