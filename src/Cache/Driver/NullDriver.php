<?php
/**
 * Varimax The Slim PHP Frameworks.
 *
 * Github: https://github.com/dcto/varimax
 */


namespace VM\Cache\Driver;

class NullDriver extends Driver
{
    use RetrievesMultipleKeys;

    /**
     * Check an item from the cache by key.
     *
     * @param  string|array $key
     * @return mixed
     */
    public function has($key){}

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     * @return mixed
     */
    public function get($key){}

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param  string  $key 缓存键
     * @param  mixed   $value 缓存值
     * @param  int     $time 缓存时间
     * @return bool
     */
    public function set($key, $value, $time = 86400){}

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return int
     */
    public function increment($key, $value = 1){}

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return int
     */
    public function decrement($key, $value = 1){}

    /**
     * 持久化保存
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function save($key, $value){}

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return void
     */
    public function del($key){}

    /**
     * Remove all items from the cache.
     *
     * @return void
     */
    public function flush(){}

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function prefix(){}
}