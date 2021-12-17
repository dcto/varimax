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

/**
 * Class RedisDriver
 *
 * @package VM\Cache\Driver
 *
 * @see \Redis
 */
class RedisDriver extends Driver implements DriverInterface
{


    /**
     * @var \Redis
     */
    private $redis;

    /**
     * redis servers config
     *
     * @var array
     */
    private $config = array();



    public function __construct($server = 'default')
    {
        $this->config = config('cache.driver.redis.'. $server);
        if(!$this->config){
            throw new \ErrorException('Unable load ['.$server.'] redis server configure.');
        }
        $this->server();
    }


    /**
     * @return array
     */
    public function config($key = null, $defalut = null)
    {
        if($key){
            return isset($this->config[$key]) ? $this->config[$key] : $defalut;
        }
        return $this->config;
    }

    /**
     * @param string $server
     * @return mixed|\Redis
     */
    public function server()
    {
        if(!$this->redis instanceof \Redis){
            $this->redis = new \Redis();
            if($this->config('persistent')){
                $this->redis->pconnect($this->config('host'), $this->config('port'), $this->config('timeout'));
            }else {
                $this->redis->connect($this->config('host'), $this->config('port'), $this->config('timeout'));
            }
            if($this->config('password'))  $this->redis->auth($this->config('password'));
            $this->redis->select($this->config('database', 0));

            foreach((array) \Arr::get($this->config, 'options') as $key => $val) {
                $this->redis->setOption($key, $val);
            }
        }
        return $this->redis;
    }

    /**
     * Check an item from the cache by key.
     *
     * @param  string|array $key
     * @return mixed
     */
    public function has($key)
    {
        return $this->redis->exists($key);
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->redis->get($key);
        /*
        if (! is_null($value = $this->server()->get($key))) {
            return is_numeric($value) ? $value : unserialize($value);
        }
        */
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param  string  $key 缓存键
     * @param  mixed   $value 缓存值
     * @param  int     $time 缓存时间
     * @return bool
     */
    public function set($key, $value, $time = 0)
    {
        //$value = is_numeric($value) ? $value : serialize($value);
        return $time ? $this->redis->set($key, $value, $time) : $this->redis->set($key, $value);
    }

    /**
     * Retrieve multiple items from the cache by key.
     *
     * Items not found in the cache will have a null value.
     *
     * @param  array  $keys
     * @return array
     */
    public function gets(array $keys)
    {
        $return = array();

        $values = $this->redis->mget($keys);

        foreach ($values as $index => $value) {
            $return[$keys[$index]] = $value;//is_numeric($value) ? $value : unserialize($value);
        }
        return $return;
    }

    /**
     * Store multiple items in the cache for a given number of minutes.
     *
     * @param  array  $values
     * @param  int  $minutes
     */
    public function sets(array $values, $time = 0)
    {
        $this->server()->multi();

        foreach ($values as $key => $value) {
            $this->set($key, $value, $time);
        }

       return $this->redis->exec();
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
        return $this->redis->incrBy($key, $value);
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
        return $this->redis->decrBy($key, $value);
    }

    /**
     * 持久化保存
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function save($key, $value)
    {
       //$value = is_numeric($value) ? $value : serialize($value);
        $this->redis->set($key, $value);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function del($key)
    {
        return (bool) $this->redis->del($key);
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {
        return $this->redis->flushDB();
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function prefix($prefix = false)
    {
        return $this->redis->_prefix($prefix);
    }


    /**
     * [command 命令行运行]
     *
     * @param       $method
     * @param array $parameters
     * @author 11.
     */
    public function command($method, array $parameters = [])
    {
        return call_user_func_array([$this->redis, $method], $parameters);

    }


    /**
     * [__call 魔术调用redis方法]
     *
     * @param $method
     * @param $parameters
     * @return static
     * @author 11.
     */
    public function __call($method, $parameters)
    {
        return $this->command($method, $parameters);
    }
}
