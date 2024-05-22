<?php
/**
 * Varimax The Slim PHP Frameworks.
 *
 * Github: https://github.com/dcto/varimax
 */

namespace VM\Cache\Driver;

class ApcDriver extends Driver
{
    use RetrievesMultipleKeys;

    /**
     * A string that should be prepended to keys.
     *
     * @var string
     */
    protected $prefix;


    public function __construct()
    {
        if(!function_exists('apcu_fetch')) throw new \ErrorException('Unsupport apc cache extension, resolve in the offical https://pecl.php.net/package/APCu ');

        $this->prefix = config('cache.driver.apcu.prefix', '');
    }

    /**
     * Get the cache key name with prefix.
     *
     * @return string
     */
    protected function name($key)
    {
        return $this->prefix.$key;
    }

    /**
     * Check an item from the cache by key.
     *
     * @param  string|array $key
     * @return mixed
     */
    public function has($key)
    {
        return apcu_exists($this->name($key));
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     * @return mixed
     */
    public function get($key)
    {
        return apcu_fetch($this->name($key));
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
        return apcu_store($this->name($key), $value, $time);
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return $this
     */
    public function increment($key, $value = 1)
    {
        apcu_inc($this->name($key), $value);

        return $this;
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return $this
     */
    public function decrement($key, $value = 1)
    {
        apcu_dec($this->name($key), $value);

        return $this;
    }

    /**
     * 持久化保存
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return $this
     */
    public function save($key, $value)
    {
        apcu_store($this->name($key), $value, 0);

        return $this;
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function del($key)
    {
        return apcu_delete($this->name($key));
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {
        apcu_clear_cache();
        return $this;
    }
}