<?php

/**
 * Varimax The Slim PHP Frameworks.
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
     * cookie path
     * @var string
     */
    protected $path;

    /**
     * set cookie prefix
     * 
     * @var string
     */
    protected $prefix;

    /**
     * cookie expire time
     * 
     * @var int
     */
    protected $expire;

    /**
     * cookie domain
     * 
     * @var mixed
     */
    protected $domain;

    /**
     * secure https ssl
     * 
     * @var bool
     */
    protected $secure;


    /**
     * encrypt cookie value
     * 
     * @var bool
     */
    protected $encrypt;

    /**
     * cookie http Only attribute
     * 
     * @var bool
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
     * constract the cookie module
     * 
     * @return void 
     * @throws BindingResolutionException 
     */
    public function __construct()
    {
        $this->path     = $this->config('path', '/');
        $this->prefix   = $this->config('prefix', '');
        $this->expire   = $this->config('expire', 0);
        $this->domain   = $this->config('domain', null);
        $this->secure   = $this->config('secure', false);
        $this->encrypt  = $this->config('encrypt', false);
        $this->httpOnly = $this->config('httpOnly', true);
        $this->raw      = $this->config('raw', false);
        $this->sameSite = $this->config('sameSite', null);
    }

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
    protected function make($name, $value, $expire = null, $path = null, $domain = null, $secure = null, $httpOnly = null, $raw = null, $sameSite = null)
    {
        isset($path) && $this->path = $path;
        isset($expire) && $this->expire = $expire;
        isset($domain) && $this->domain = $domain;
        isset($secure) && $this->secure = $secure;
        isset($httpOnly) && $this->$httpOnly = $httpOnly;
        isset($raw) && $this->$raw = $raw;
        isset($sameSite) && $this->$sameSite = $sameSite;

        $value = $this->encrypt() ? \Crypt::en($value) : $value;
        $expire = $this->expire() > 0 ? time() + ($this->expire() * 60) : 0;
        return new HttpFoundation\Cookie($this->name($name), $value, $expire, $this->path, $this->domain, $this->secure, $this->httpOnly, $this->raw, $this->sameSite);
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
        $response = make('response')->make();
        $response->headers->setCookie(
                $this->make($name, $value, $expire, $path, $domain, $secure, $httpOnly, $raw, $sameSite)
        );
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
        if($this->encrypt()) return \Crypt::de($value);
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
        $cookies = $name ? \Arr::only($cookies, array_map(function($n){
            return $this->name($n);
        }, \Arr::flatten($name))) : $cookies;

        if($this->encrypt()){
            array_walk($cookies, function(&$v, $k){
                if($this->prefix()){
                    $v = \Str::startsWith($k, $this->prefix()) ? \Crypt::de($v) : $v;
                }else{
                    $v = $k != 'PHPSESSID' ? \Crypt::de($v) : $v;
                }
            });
        }

        if($this->prefix()){
            foreach($cookies as $key => $cookie){
                $cookies[\Str::replaceFirst($this->prefix(), '', $key)] = $cookie;
                unset($cookies[$key]);
            }
        }

        return $cookies;
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
    public function path($value = null)
    {
        return $this->attribute(__FUNCTION__, $value);
    }

    /**
     * set cookie prefix
     * @param string $value 
     * @return $this 
     */
    public function prefix($value = null)
    {
        return $this->attribute(__FUNCTION__, $value);
    }

    /**
     * set domain
     * @param int $value 
     * @return $this 
     */
    public function domain($value = null)
    {
        return $this->attribute(__FUNCTION__, $value);
    }

    /**
     * set expire attribute
     * @param int $value 
     * @return $this 
     */
    public function expire(int $value = null)
    {
        return $this->attribute(__FUNCTION__, $value);
    }

    /**
     * set expire attribute
     * @param int $value 
     * @return $this 
     */
    public function secure(bool $value = null)
    {
        return $this->attribute(__FUNCTION__, $value);
    }

    /**
     * set cookie encrypt value
     * @param int $value 
     * @return $this 
     */
    public function encrypt(bool $value = null)
    {
        return $this->attribute(__FUNCTION__, $value);
    }
    /**
     * set httpOnly attribute
     * @param int $value 
     * @return $this 
     */
    public function httpOnly(bool $value = null)
    {
        return $this->attribute(__FUNCTION__, $value);
    }


    /**
     * set raw attribute
     * @param int $value 
     * @return $this 
     */
    public function raw(bool $value = null)
    {
        return $this->attribute(__FUNCTION__, $value);
    }


    /**
     * set sameSite attribute
     * @param int $value 
     * @return $this 
     */
    public function sameSite($value = null)
    {
        $value && $this->secure(true);
        return $this->attribute(__FUNCTION__, $value);
    }

    /**
     * set attribute
     * @param mixed $attribute 
     * @param mixed|null $value 
     * @return mixed 
     */
    public function attribute($attribute, $value = null)
    {
        if(isset($value)){
            $this->$attribute = $value;
            return $this;
        }else{
            return $this->$attribute;
        }
    }
}