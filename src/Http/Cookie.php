<?php

/**
 * Varimax The Full Stack PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 22:03
 * SITE: https://www.varimax.cn/
 */


namespace VM\Http;

use Illuminate\Contracts\Container\BindingResolutionException;
use Symfony\Component\HttpFoundation;

class Cookie{
    /**
     * cookie expire time
     * 
     * @var int
     */
    protected $expire;

    /**
     * cookie path
     * @var string
     */
    protected $path;

    /**
     * cookie domain
     * 
     * @var mixed
     */
    protected $domain;

    /**
     * secure https ssl
     * 
     * @var false
     */
    protected $secure;

    /**
     * cookie http Only attribute
     * 
     * @var true
     */
    protected $httpOnly;

    /**
     * cookie raw encode
     * 
     * @var false
     */
    protected $raw;

    /**
     * chrome 70 version after attribute for cros site 
     * avaliable value null|'none'|'Lax'|'Strict'
     * 
     * @var mixed
     */
    protected $sameSite;
    
    /**
     * 返回cookie实例
     *
     * @param  string  $name
     * @param  string  $value
     * @param  int     $minutes
     * @param  string  $path
     * @param  string  $domain
     * @param  bool    $secure
     * @param  bool    $httpOnly
     * @param  bool    $raw
     * @param  null|string   $sameSite
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    public function make($name, $value, $expire = null, $path = null, $domain = null, $secure = null, $httpOnly = null, $raw = null, $sameSite = null)
    {
        $path       = isset($path) ? $path : $this->config('path', '/');
        $expire     = isset($expire) ? $expire : $this->config('expire', 0);
        $domain     = isset($domain) ? $domain : $this->config('domain', null);
        $secure     = isset($secure) ? $secure : $this->config('secure', false);
        $httpOnly   = isset($httpOnly) ? $httpOnly : $this->config('httpOnly', true);
        $raw        = isset($raw) ? $raw : $this->config('raw', false);
        $sameSite   = isset($sameSite) ? $sameSite : $this->config('sameSite', null);
        
        $expire = ($expire == 0) ? 0 : time() + ($expire * 60);

        return new HttpFoundation\Cookie($this->name($name), $value, $expire, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }

    /**
     * cookie name
     *
     * @param $name
     * @return string
     */
    private function name($name)
    {
        if($prefix = config('cookie.prefix')){
            if(\Str::startsWith($name, $prefix)){
                return $name;
            }else{
                return $prefix.$name;
            }
        }
        return $name;
    }

    /**
     * [set 设置cookie]
     *
     * @param            $name
     * @param null       $value
     * @param int        $expire
     * @param string     $path
     * @param null       $domain
     * @param bool       $secure
     * @param bool       $httpOnly
     * @param bool       $raw
     * @param string     $sameSite
     * @return $this
     */
    public function set($name, $value, $expire = null, $path = null, $domain = null, $secure = null, $httpOnly = null, $raw = null, $sameSite = null)
    {
        if(config('cookie.encrypt')) $value = \Crypt::en($value);
        $response = make('response')->make();
        $response->headers->setCookie($this->make($name, $value, $expire, $path, $domain, $secure, $httpOnly, $raw, $sameSite));
        $response->sendHeaders();

        return $this;
    }

    /**
     * [has 判断cookie是否存在]
     */
    public function has($name)
    {
        return ! is_null($this->get($this->name($name)));
    }

    /**
     * [get 获取cookie]
     *
     * @param $name
     * @return mixed
     * @author 11.
     */
    public function get($name, $default = null)
    {
        $value = make('request')->cookies->get($this->name($name), $default);
        if(config('cookie.encrypt')) return \Crypt::de($value);
        return $value;
    }

    /**
     * [all 返回全部cookie]
     *
     * @return mixed
     * @author 11.
     */
    public function all(...$name)
    {
        $cookies = make('request')->cookies->all();
        return $name ? \Arr::only($cookies, array_map(function($n){
            return $this->name($n);
        }, \Arr::flatten($name))) : $cookies;
    }


    /**
     * remove cookie for alias delete method
     * 
     * @param mixed $name 
     * @return true 
     * @throws BindingResolutionException 
     */
    public function del($name)
    {
      return $this->delete($name);
    }

    /**
     * 
     * @param mixed $key 
     * @param mixed|null $value 
     * @return void 
     */
    protected function config($key, $default = null)
    {
        return isset($this->$key) ? $this->$key : config('cookie.'. $key, $default);
    }
    

    /**
     * delete cookie
     * 
     * @param mixed $name 
     * @return mixed 
     * @throws BindingResolutionException 
     */
    public function delete($name)
    {
        $response = make('response')->make();
        $response->headers->clearCookie($this->name($name));
        $response->sendHeaders();
    }

    /**
     * remove cookie alais name clear
     * 
     * @param mixed $name 
     * @return true 
     * @throws BindingResolutionException 
     */
    public function remove(...$name)
    {
        return $this->clear($name);
    }

    /**
     * [clear 删除cookie]
     *
     * @param $name
     */
    public function clear(...$name)
    {
        $response = make('response')->make();
        $names = \Arr::flatten($name);

        foreach($names as $name){
            $response->headers->clearCookie($this->name($name));
        }

        $response->sendHeaders();
        return true;
    }


    /**
     * Get the path and domain, or the default values.
     *
     * @param  string  $path
     * @param  string  $domain
     * @param  bool    $secure
     * @return array
     */
    protected function getPathAndDomain($path, $domain, $secure = false)
    {
        return [$path, $domain, $secure];
    }

    /**
     * set path
     * @param int $value 
     * @return $this 
     */
    public function path($value)
    {
        $this->path = $value;
        return $this;
    }

    /**
     * set domain
     * @param int $value 
     * @return $this 
     */
    public function domain($value)
    {
        $this->domain = $value;
        return $this;
    }

    /**
     * set expire attribute
     * @param int $value 
     * @return $this 
     */
    public function expire(int $value)
    {
        $this->expire = $value;
        return $this;
    }

    /**
     * set expire attribute
     * @param int $value 
     * @return $this 
     */
    public function secure(bool $value)
    {
        $this->secure = $value;
        return $this;
    }

    /**
     * set httpOnly attribute
     * @param int $value 
     * @return $this 
     */
    public function httpOnly(bool $value)
    {
        $this->httpOnly = $value;
        return $this;
    }


    /**
     * set raw attribute
     * @param int $value 
     * @return $this 
     */
    public function raw(bool $value)
    {
        $this->raw = $value;
        return $this;
    }


    /**
     * set sameSite attribute
     * @param int $value 
     * @return $this 
     */
    public function sameSite($value)
    {
        if($value){
            $this->secure = true;
            $this->sameSite = $value;
        }
        return $this;
    }
}