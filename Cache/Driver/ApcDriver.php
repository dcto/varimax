<?php
/**
 * Varimax The Full Stack PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 22:03
 * SITE: https://www.varimax.cn/
 */


namespace VM\Cache\Driver;

class ApcDriver extends Driver
{
    use RetrievesMultipleKeys;

    /**
     * Indicates if APCu is supported.
     *
     * @var bool
     */
    protected $apcu = false;

    /**
     * A string that should be prepended to keys.
     *
     * @var string
     */
    protected $prefix;


    public function __construct()
    {
        $this->apcu = function_exists('apcu_fetch');

        $this->prefix = config('cache.apcu.prefix', 'vm:');
    }

    /**
     * Check an item from the cache by key.
     *
     * @param  string|array $key
     * @return mixed
     */
    public function has($key)
    {
        return $this->apcu ? apcu_exists($this->prefix.$key) : apc_exists($this->prefix.$key);
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->apcu ? apcu_fetch($this->prefix.$key) : apc_fetch($this->prefix.$key);
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $time
     * @return bool|array
     */
    public function set($key, $value, $time = 86400)
    {
        return $this->apcu ? apcu_store($this->prefix.$key, $value, $time) : apc_store($this->prefix.$key, $value, $time);
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return int
     */
    public function increment($key, $value = 1)
    {
        return $this->apcu ? apcu_inc($this->prefix.$key, $value) : apc_inc($this->prefix.$key, $value);
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return int
     */
    public function decrement($key, $value = 1)
    {
        return $this->apcu ? apcu_dec($this->prefix.$key, $value) : apc_dec($this->prefix.$key, $value);
    }

    /**
     * 持久化保存
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return bool|array
     */
    public function save($key, $value)
    {
        return $this->apcu ? apcu_store($this->prefix.$key, $value, 0) : apc_store($this->prefix.$key, $value, 0);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function del($key)
    {
        return $this->apcu ? apcu_delete($this->prefix.$key) : apc_delete($this->prefix.$key);
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {
        $this->apcu ? apcu_clear_cache() : apc_clear_cache('user');
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function prefix($prefix = false)
    {
        return $prefix ? $this->prefix = $prefix : $this->prefix;
    }
}