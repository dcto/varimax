<?php

/**
 * Varimax The Full Stack PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 21:49
 * SITE: https://www.varimax.cn/
 */

namespace VM\Cache\Driver;

use VM\Exception\SystemException;


class FilesDriver extends Driver implements DriverInterface
{
    use RetrievesMultipleKeys;

    /**
     * cache dir
     *
     * @var string
     */
    private $dir;

    /**
     * cache file
     * @var string
     */
    private $file;

    /**
     * @var string
     */
    private $prefix;



    public function __construct($prefix = null)
    {
        $this->dir =  config('cache.driver.files.dir', runtime('cache'));

        $this->prefix(config('cache.driver.files.prefix', $prefix));


        make('file')->mkDir($this->dir, 0755, true, true);


        $this->file = $this->dir.'/'.$this->prefix();
    }

    /**
     * get file name
     *
     * @param $key
     */
    public function file($key, $time = null)
    {
        $file = $this->file.hash('crc32b', $key);


        if($time){

            if(is_file($file)){
                make('file')->delete($file);
            }

            make('file')->touch($file, time() + $time);

        }else if(is_file($file) && time() > filemtime($file)){

            make('file')->delete($file);
        }

        return $file;
    }


    /**
     * Get the expiration time based on the given minutes.
     *
     * @param  int  $time
     * @return int
     */
    protected function expiration($time = 0)
    {
        $time = time() + $time;

        if ($time === 0 || $time > 9999999999) {
            return 9999999999;
        }
        return (int) $time;
    }

    /**
     * Check an item from the cache by key.
     *
     * @param  string|array $key
     * @return mixed
     */
    public function has($key)
    {
       return $this->file($key) && make('file')->has($this->file($key));
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if(make('file')->has($this->file($key))){
            return unserialize(make('file')->get($this->file($key)));
        }
        return $default;
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param  string  $key 缓存键
     * @param  mixed   $value 缓存值
     * @param  int     $time 缓存时间
     * @return bool
     */
    public function set($key, $value, $time = 86400)
    {
        return make('file')->put($this->file($key, $time), serialize($value), true);
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return int
     */
    public function increment($key, $value = 1, $time = 9999999999)
    {
        if(!$val = $this->get($key)) return $this->set($key, $value, $time);

        if(is_numeric($val)){
            $value = $value + intval($val);
        }else{
            throw new SystemException('The cache key '.$key. ' can not increment, it\'s not a integer');
        }
        return $this->set($key, $value);
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
        return $this->increment($key, $value * -1);
    }

    /**
     * 持久化保存
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return bool
     */
    public function save($key, $value)
    {
        return $this->set($key, $value, 9999999999);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function del($key)
    {
        return make('file')->del($this->file($key));
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {
        return make('file')->deleteDirectory($this->dir, true);
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function prefix($prefix = null)
    {
        if($prefix){
            $this->prefix = $prefix;

            return $this;

        }else{
            return $this->prefix;
        }
    }

}