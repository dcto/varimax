<?php
/**
 * Varimax The Slim PHP Frameworks.
 *
 * Github: https://github.com/dcto/varimax
 */


namespace VM\Cache;

use VM\Cache\Driver\Driver;
use VM\Cache\Driver\ApcDriver;
use VM\Cache\Driver\NullDriver;
use VM\Cache\Driver\RedisDriver;
use VM\Cache\Driver\FilesDriver;
use VM\Cache\Driver\RetrievesMultipleKeys;

class Cache
{
    use RetrievesMultipleKeys;

    /**
     * 驱动器
     * @var array
     */
    private $drivers = array(
        'null'  => false,
        'apc'   => false,
        'files' => false,
        'redis' => false
    );


    public function __construct($driver = 'null')
    {
        $this->setDefaultDriver($driver);
    }

    /**
     * [空缓存 当关闭缓存时使用]
     *
     * @return NullDriver
     */
    public function null()
    {
        return new NullDriver();
    }

    /**
     * [apc APC缓存]
     *
     * @param string $prefix
     * @return ApcDriver
     */
    public function apc($prefix = 'vm:')
    {
        return new ApcDriver($prefix);
    }

    /**
     * [File 文件缓存]
     *
     * @param null $prefix
     * @return FilesDriver
     * @author 11.
     */
    public function files($prefix = 'vm.')
    {
        return new FilesDriver($prefix);
    }

    /**
     * [Redis 实例]
     *
     * @param string $server 连接的服务器名称
     * @return RedisDriver|\Redis
     * @author 11.
     */
    public function redis($name = 'default')
    {
        return new RedisDriver($name);
    }


    /**
     * Get a cache store instance by name.
     *
     * @param  string|null  $name
     * @return Driver
     */
    public function driver($driver = null)
    {
        $driver ??= $this->getDefaultDriver();
        if(!$this->drivers[$driver] instanceof Driver){
           $this->drivers[$driver] = $this->$driver();
        }
        return $this->drivers[$driver];
    }

    /**
     * Get the default cache driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return config('cache.default', 'files');
    }


    /**
     * Set the default cache driver name.
     *
     * @param  string  $name
     */
    public function setDefaultDriver($driver)
    {
       if(!isset($this->drivers[$driver])){
            throw new \InvalidArgumentException('Invalid '.$driver.' cache driver.');
        }
       return config('cache.default', $driver);
    }


    /**
     * [command 全局调用]
     *
     * @param       $method
     * @param array $parameters
     * @return $this->driver()
     */
    public function __call($method, array $parameters = [])
    {
        return call_user_func_array([$this->driver(), $method], $parameters);
    }
}