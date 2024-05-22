<?php

/**
 * Varimax The Slim PHP Frameworks.
 *
 * Github: https://github.com/dcto/varimax
 */

namespace VM\Http;

use \Symfony\Component\HttpFoundation\Cookie as HttpCookie;

 /**
  * @package Cookie
  */
class Cookie{

    /**
     * cookie path
     * @var string
     */
    protected $path = '/';

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
    protected $expire = 0;

    /**
     * cookie domain
     * 
     * @var mixed
     */
    protected $domain = '.';

    /**
     * secure https ssl
     * 
     * @var bool
     */
    protected $secure;

    /**
     * cookie http Only attribute
     * 
     * @var bool
     */
    protected $httpOnly = true;

    /**
     * cookie raw encode
     * 
     * @var false
     */
    protected $raw = false;

    /**
     * chrome 70 version after attribute for cros site 
     * avaliable value null|'none'|'Lax'|'Strict'
     * 
     * @var mixed
     */
    protected $sameSite= 'Lax';

    /**
     * encrypt cookie value
     * 
     * @var bool
     */
    protected $encrypt = false;

    /**
     * constract the cookie module
     */
    public function __construct()
    {
        foreach(config('cookie') as $k => $v){
           $this->$k = $v;
        }
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
     * @return HttpCookie
     */
    public function make($name, $value, $expire = null, $path = null, $domain = null, $secure = null, $httpOnly = null, $raw = null, $sameSite = null)
    {
        $value = $this->encrypt ? app('crypt')->en($value) : $value;
        $expire = $expire ?? $this->expire;
        return new HttpCookie($this->name($name), $value, ($expire > 0 ? time() + $expire : $expire), 
            $path ?? $this->path, 
            $domain ?? $this->domain, 
            $secure ?? $this->secure, 
            $httpOnly ?? $this->httpOnly, 
            $raw ?? $this->raw, 
            $sameSite ?? $this->sameSite
        );
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
     * @param string     $value
     * @param int        $expire
     * @param string     $path
     * @param null       $domain
     * @param bool       $secure
     * @param bool       $httpOnly
     * @param bool       $raw
     * @param string     $sameSite
     * @return $this
     */
    public function set($name, $value)
    {
        app('response')->withCookie($name, $value);
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
        if($value = app('request')->cookies->get($this->name($name))){
            return $this->encrypt ? app('crypt')->de($value) : $value;
        }
        return $default;
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

        if($this->encrypt){
            array_walk($cookies, function(&$v, $k){
                if($this->prefix){
                    $v = \Str::startsWith($k, $this->prefix) ? \Crypt::de($v) : $v;
                }else{
                    $v = $k != 'PHPSESSID' ? \Crypt::de($v) : $v;
                }
            });
        }

        if($this->prefix){
            foreach($cookies as $key => $cookie){
                $cookies[\Str::replaceFirst($this->prefix, '', $key)] = $cookie;
                unset($cookies[$key]);
            }
        }
        return $cookies;
    }


    /**
     * remove cookie for alias remove method
     * 
     * @param mixed $name 
     * @return true 
     * @throws BindingResolutionException 
     */
    public function del($cookie)
    {
        return $this->remove($cookie);
    }

    /**
     * Removes a cookie from the array, but does not unset it in the browser.
     * 
     * @param mixed $cookies 
     * @return true 
     */
    public function remove(...$cookies)
    {
        array_map(function($cookie){
            app('response')->headers()->removeCookie($this->name($cookie));
        }, $cookies);
        return $this;
    }

    /**
     * Clears a cookie in the browser.
     * @param $cookies
     */
    public function clear(...$cookies)
    {
        array_map(function($cookie){
            app('response')->headers()->clearCookie($this->name($cookie));
        }, $cookies);
        return $this;
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