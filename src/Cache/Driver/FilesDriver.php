<?php

/**
 * Varimax The Slim PHP Frameworks.
 *
 * Github: https://github.com/dcto/varimax
 */

namespace VM\Cache\Driver;

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
     * cache file prefix
     * @var string
     */
    private $prefix;

    /**
     * cahce file append
     * @var mixed
     */
    private $append;



    public function __construct($prefix = null)
    {
        $this->dir =  config('cache.driver.files.dir', runtime('cache'));

        $this->prefix(config('cache.driver.files.prefix', $prefix));
        
        $this->file = $this->dir.'/'.$this->prefix();

        $this->append = config('cache.driver.files.append', '.bin');

        make('file')->mkDir($this->dir, 0755, true, true);
    }

    /**
     * get file name
     *
     * @param $key
     */
    public function file($key)
    {
        return $this->file.hash('crc32b', $key).$this->append;
    }

    /**
     * Check an item from the cache by key.
     *
     * @param  string|array $key
     * @return mixed
     */
    public function has($key, $time = 0)
    {
        $file = $this->file($key);
        $exist = make('file')->has($file);
        if($exist && !is_null($time) && time() > make('file')->lastModified($file)){
            make('file')->delete($file);
            return false;
        }
        return $exist;
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if($this->has($key)){
            $value = make('file')->get($this->file($key));
        }
        return take(json_decode($value), $default);
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
        make('file')->put($this->file($key), json_encode($value), true);
        make('file')->touch($this->file($key), $this->time($time));
        return $this;
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return int
     */
    public function increment($key, $value = 1, $time = null)
    {
        $val = $this->get($key);
        if(!$val) return $this->set($key, $value, $time);
        $value = $val + intval($value);
        $this->set($key, $value, $time);
        return $this;
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return int
     */
    public function decrement($key, $value = 1, $time = null)
    {
        $this->increment($key, $value * -1, $time);

        return $this;
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

    /**
     * Get the expiration time based on the given minutes.
     *
     * @param  int  $time
     * @return int
     */
    protected function time(int $time = 0)
    {
        if ($time <1 || $time > 9999999999) {
            return 9999999999;
        }
        return (int) time() + $time;
    }
}