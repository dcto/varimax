<?php
/**
 * Varimax The Full Stack PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 22:03
 * SITE: https://www.varimax.cn/
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
    private $driver = array(
            'null'  => null,
            'apc'   => null,
            'files' => null,
            'redis' => null
    );


    public function __construct($driver = null)
    {
        if($driver){
            $this->setDefaultDriver($driver);
        }
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
        $driver = $driver ? $this->setDefaultDriver($driver) : $this->getDefaultDriver();
        $driver = $driver ?: 'null';
        if(!$this->driver[$driver] instanceof Driver){
           $this->driver[$driver] = $this->$driver();
        }
        return $this->driver[$driver];
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
       if(!isset($this->driver[$driver])){
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
     * @author 11.
     */
    public function __call($method, array $parameters = [])
    {
        return call_user_func_array([$this->driver(), $method], $parameters);
    }
}