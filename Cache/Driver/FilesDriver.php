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
use VM\FileSystem\FileSystem;

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

    /**
     * @var FileSystem
     */
    private $fileSystem;



    public function __construct($prefix = null)
    {
        $this->dir =  runtime(config('cache.files.dir', config('dir.cache')));

        $this->prefix($prefix?:config('cache.files.prefix'));

        $this->cache = $this->dir.'/'.$this->prefix();

        $this->fileSystem = app('file');
    }

    /**
     * get key
     * @param $key
     */
    public function file($key = '')
    {
        return $this->file.hash('crc32b',$key);
    }

    /**
     * Retrieve an item and expiry time from the cache by key.
     *
     * @param  string  $key
     * @return array
     */
    protected function load($key)
    {
        $path = path();

        // If the file doesn't exists, we obviously can't return the cache so we will
        // just return null. Otherwise, we'll get the contents of the file and get
        // the expiration UNIX timestamps from the start of the file's contents.
        try {
            $expire = substr(
                $contents = $this->fileSystem->get($path, true), 0, 10
            );
        } catch (\Exception $e) {
            return ['data' => null, 'time' => null];
        }

        // If the current time is greater than expiration timestamps we will delete
        // the file and return null. This helps clean up the old files and keeps
        // this directory much cleaner for us as old files aren't hanging out.
        if (time() >= $expire) {
            $this->del($key);

            return ['data' => null, 'time' => null];
        }

        $data = unserialize(substr($contents, 10));

        // Next, we'll extract the number of minutes that are remaining for a cache
        // so that we can properly retain the time for things like the increment
        // operation that may be performed on the cache. We'll round this out.
        $time = ceil($expire - time());

        return compact('data', 'time');
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
       return $this->fileSystem->has($this->cache($key));
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     * @return mixed
     */
    public function get($key)
    {
        return \Arr::get($this->load($key), 'data');
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
        if (! $this->fileSystem->exists($dir = dirname($this->cache($key)))) {
            $this->fileSystem->mkDir($dir, 0755, true, true);
        }
        $value = $this->expiration($time).serialize($value);
        return $this->fileSystem->put($this->cache($key), $value, true);
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
        return $this->fileSystem->del($this->cache($key));
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {
        return $this->fileSystem->deleteDirectory($this->dir, true);
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function prefix($prefix = null)
    {
        return $prefix ? $this->prefix = $prefix : $this->prefix;
    }

}