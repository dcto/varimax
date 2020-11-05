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
    public function make($name, $value, $expire = 0, $path = null, $domain = null, $secure = false, $httpOnly = true, $raw = false, $sameSite = null)
    {

        $path   = $path ?: config('cookie.path', '/');
        $expire = $expire ?: config('cookie.expire', 0);
        $domain = $domain ?: config('cookie.domain', null);
        $secure = $secure ?: config('cookie.secure', false);
        $httpOnly = $httpOnly ?: config('cookie.httpOnly', true);
        $raw = $raw ?: config('cookie.raw', false);
        $sameSite = $sameSite ?: config('cookie.sameSite', null);
        
        $expire = ($expire == 0) ? 0 : time() + ($expire * 60);

        return new HttpFoundation\Cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }

    /**
     * cookie name
     *
     * @param $name
     * @return string
     */
    private function name($name)
    {
        return config('cookie.prefix', '').$name;
    }

    /**
     * [set 设置cookie]
     *
     * @param            $name
     * @param null       $value
     * @param int        $expire
     * @param string     $path
     * @param null       $domain
     * @param bool|false $secure
     * @param bool|true  $httpOnly
     * @return $this
     */
    public function set($name, $value, $expire = null, $path = null, $domain = null, $secure = null, $httpOnly = null)
    {
        if(config('cookie.encrypt')) $value = \Crypt::en($value);
        $response = make('response')->make();
        $response->headers->setCookie($this->make($this->name($name), $value, $expire, $path, $domain, $secure, $httpOnly));
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
        return $name ? \Arr::only($cookies, \Arr::flatten($name)) : $cookies;
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
      return $this->clear($name);
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
        return response()->make()->headers->clearCookie($this->name($name));
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
}