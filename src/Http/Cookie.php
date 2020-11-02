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

use Symfony\Component\HttpFoundation;

class Cookie{


    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $expire;

    /**
     * @var string
     */
    protected $domain;

    /**
     * @var boolean
     */
    protected $secure = false;

    /**
     * @var boolean
     */
    protected $httpOnly = true;

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
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    public function make($name, $value, $expire = 0, $path = null, $domain = null, $secure = false, $httpOnly = true)
    {

        $this->path   = $path ?: config('cookie.path', '/');
        $this->expire = $expire ?: config('cookie.expire', 0);
        $this->domain = $domain ?: config('cookie.domain', null);
        $this->secure = $secure ?: config('cookie.secure', false);
        $this->httpOnly = $httpOnly ?: config('cookie.httpOnly', true);

        list($path, $domain, $secure) = $this->getPathAndDomain($path, $domain, $secure);

        $expire = ($expire == 0) ? 0 : time() + ($expire * 60);

        return new HttpFoundation\Cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
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
    public function all(...$key)
    {
        
        return  \Arr::only($this->cookies->all(), \Arr::flatten($key));
    }

    /**
     * [del 删除cookie]
     *
     * @param $name
     * @author 11.
     */
    public function del($name)
    {
        $response = make('response')->make();
        $response->headers->clearCookie($this->name($name));
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
        return [$path ?: $this->path, $domain ?: $this->domain, $secure ?: $this->secure];
    }
}