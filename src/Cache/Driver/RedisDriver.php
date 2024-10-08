<?php
/**
 * Varimax The Slim PHP Frameworks.
 *
 * Github: https://github.com/dcto/varimax
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
     * connection pool
     * @var \Swoole\ConnectionPool
     */
    private $pool;

    /**
     * @var \Redis
     */
    private $client;


    public function __construct($server = 'default')
    {
        if(pcid()){
            $this->pool = new \Swoole\ConnectionPool(fn()=>$this->connection($server));
        }else{
            $this->client = $this->connection($server);
        }
        
    }

    /**
     * @param string $server
     * @return mixed|\Redis
     */
    public function connection($server)
    {
        if(!$config = config('cache.driver.redis.'. $server)) throw new \ErrorException('Unable load ['.$server.'] redis server configure.');
        
        $client = new \Redis();

        if($config['persistent'] ?? false){
            $client->pconnect($config['host'], $config['port'] ?? 6379, $config['timeout'] ?? 0.5);
        }else {
            $client->connect($config['host'], $config['port'] ?? 6379, $config['timeout'] ?? 0.5);
        }
        if ($config['auth'] ?? false){
            $client->auth($config['auth']);
        }
        
        foreach ($config['options'] ?? [] as $name => $value) {
            $client->setOption($name, $value);
        }

        return $client;
    }


    /**
     * get redis client
     * @return \Redis
     */
    public function client()
    {
        return pcid() ? $this->pool->get() : $this->client;
    }

    /**
     * Check an item from the cache by key.
     *
     * @param  string|array $key
     * @return mixed
     */
    public function has($key)
    {
        return $this->client()->exists($key);
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return take($this->client()->get($key),  $default);
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
        return $time ? $this->client()->set($key, $value, $time) : $this->client()->set($key, $value);
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

        $values = $this->client()->mget($keys);

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

       return $this->client()->exec();
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
        return $this->client()->incrBy($key, $value);
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
        return $this->client()->decrBy($key, $value);
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
        $this->client()->set($key, $value);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function del($key)
    {
        return (bool) $this->client()->del($key);
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {
        return $this->client()->flushDB();
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function prefix($prefix = false)
    {
        return $this->client()->_prefix($prefix);
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
        return call_user_func_array([$this->client(), $method], $parameters);

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
