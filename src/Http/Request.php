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

use VM\Http\Request\Upload;
use Symfony\Component\HttpFoundation;
use Illuminate\Contracts\Support\Arrayable;

class Request extends HttpFoundation\Request implements Arrayable, \ArrayAccess
{

    /**
     * @param array|null                $query      The GET parameters
     * @param array|null                $request    The POST parameters
     * @param array|null                $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array|null                $cookies    The COOKIE parameters
     * @param array|null                $files      The FILES parameters
     * @param array|null                $server     The SERVER parameters
     * @param string|resource|null $content    The raw body data
     */
    public function __construct(array $query = null, array $request = null, array $attributes = null, array $cookies = null, array $files = null, array $server = null, $content = null)
    {
        /**
         * Dectect The PHP_SAPI Runing Mode
         */
        if ('cli-server' === \PHP_SAPI) {
            if (\array_key_exists('HTTP_CONTENT_LENGTH', $_SERVER)) {
                $_SERVER['CONTENT_LENGTH'] = $_SERVER['HTTP_CONTENT_LENGTH'];
            }
            if (\array_key_exists('HTTP_CONTENT_TYPE', $_SERVER)) {
                $_SERVER['CONTENT_TYPE'] = $_SERVER['HTTP_CONTENT_TYPE'];
            }
        }
        /**
         * Initialize Request
         */
        $this->initialize($query ?? $_GET, $request ?? $_POST, $attributes ?? [], $cookies ?? $_COOKIE, $files ?? $_FILES, $server ?? $_SERVER, $content);

        /**
         * Format Request Content Source
         */
        $this->setContent();
    }


    /**
     * 构建URL参数
     * 符号说明: / 构建path路径
     * 符号说明: ? 重构URL参数
     * 符号说明: & 附加URL参数
     * 符号说明: ! 去除指定URL参数
     * 符号说明: ~ 保留指定URL参数
     * 符号说明: @ 获取指定路由url
     * 符号说明: # 设置URL锚点
     * @param mixed ...$args 
     * @example url() baseUrl
     * @example url('/abc', 'a','b', ['c','d'], ...$args);
     * @example uri('?id=1', ['page'=>1], 'pageSize=2', ...$args)
     * @example uri('&id=1', ['page'=>1], 'pageSize=2' ...$args)
     * @example uri('!id', 'page', ['pageSize'], ...$args)
     * @example uri('~id', 'page', ['pageSize'], ...$args)
     * @example url('@index');  url('@index', ...$args); url('@', 'index', ...$args) ;
     * @example url('@items.list', 333, '/type/cat', '?user=admin', ['page'=>1], ...$args);
     * @return string
     */
    public function url(...$args)
    {
        $tag = null;
        foreach ($args as $i => $arg) {
            if(is_string($arg)) {
                str_contains('/?&!~@#', $arg[0]) && $tag = $arg[0];
            }
            unset($args[$i]);
            $args[$tag][] = $arg;
        }

        $url = \Uri::uri($this->root());
        if (isset($args['@'])) {
            $url = $url->set(app('router')->route(trim(array_shift($args['@']), '@'))->url(...$args['@']));
            unset($args['@']);
        }

        return array_reduce($args, fn($url, $arg)=>$url->set(...$arg), $url);
    }

    /**
     * Get uri string
     * @param mixed ...$args
     * @return string
     */
    public function uri(...$args)
    {
        return $args ? \Uri::uri($this->getUri())->set(...$args) : $this->getUri() ;
    }

    /**
     * check the input exists by the one of key
     * @param string|array $key 
     * @param int $number
     * @return bool 
     */
    public function has($key)
    {
        return $this->exists($key);
    }

    /**
     * [get input方法别名]
     *
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $default instanceof \Closure ? $default(parent::get($key)) : parent::get($key, $default);
    }

    /**
     * 修改参数
     * @param $key
     * @param $value
     * @param $this
     */
    public function set($key, $value = null)
    {
        return $this->put($key, $value);
    }

    /**
     * Get all request items 
     * 
     * @param string|array filter request item  [query, files, request, attributes]
     * 
     * @return array
     */
    public function all()
    {
        return array_replace($this->query->all(), $this->request->all(), $this->attributes->all());
        
    }

    /**
     * 设定变量
     * @param $query
     * @return $this
     */
    public function put($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->attributes->set($k, $v);
            }
        } else {
            $this->attributes->set($key, $value);
        }
        return $this;
    }

    /**
     * Delete Item In Input Souce
     * 
     * @param mixed $key 
     * 
     * @return $this 
     */
    public function del(...$keys)
    {
        return $this->delete(...$keys);
    }

    /**
     * [not 排除返回]
     * @return array
     */
    public function not(...$keys)
    {
        return $this->except(...$keys);
    }

    /**
     * the contain alias name
     * @param array|string $key
     * @return bool
     */
    public function must($key)
    {
        return $this->contain(is_array($key) ? $key : func_get_args());
    }

    /**
     * get request keys
     * 
     * @return array 
     */
    public function keys()
    {
        return array_keys($this->all());
    }

    /**
     * [json Get the JSON payload for the request.]
     *
     * @param null $key
     * @param null $default
     * @return array|object
     */
    public function json($options = null, $depth = 512)
    {
        return json_encode($this->all(), $options, $depth);
    }

    /**
     * [take get方法加强版,支持单参数、数组、多参数数组]
     * @param $key
     * @return array|mixed
     */
    public function take(...$keys)
    {
        return $keys ? $this->only(...$keys) : $this->all();
    }

    /**
     * Fill the input keys
     * 
     * @param mixed $key 
     * @param mixed|null $value 
     * @return array 
     */
    public function fill(array $key, $value = null)
    {
        return array_intersect_key($this->all(), array_fill_keys($key, $value));
    }

    /**
     * [filter 方法别名]
     * @return array
     */
    public function tidy()
    {
        return $this->filter();
    }

    /**
     * 指定提取
     * @param array $keys
     * @return array
     */
    public function only(...$keys)
    {
        return array_include($this->all(), $keys);
    }

    /**
     * [Input Method]
     *
     * @param mixed $key
     * @param null|\Closure $default
     * @return mixed
     */
    public function input($key = null, $default = null)
    {
        $value = is_array($key) ? $this->only($key) : $this->get($key);
        if($default instanceof \Closure){
            return $default($value);
        }
         return is_scalar($value) && strlen($value) ? $value : $default;
    }

    /**
     * filter alias name
     * @param $key
     * @return bool
     */
    public function trim()
    {
        return $this->filter();
    }

    /**
     *
     * @param null $key
     * @return array
     */
    public function except(...$keys)
    {
        return array_exclude($this->all(), $keys);
    }

    /**
     * Merge new input into the current request's input array.
     *
     * @param  array  $key
     * @return $this
     */
    public function merge($key)
    {
        $this->getInputSource()->add(is_array($key) ? $key : func_get_args());
        return $this;
    }

    /**
     * [filter empty]
     * @return array
     */
    public function filter()
    {
        return array_filter($this->all(), fn($value)=>strlen($value));
    }

    /**
     * Remove From Input Source Item
     * @param mixed $args 
     * @return $this 
     */
    public function delete(...$keys)
    {
       array_map(function($item){
           $this->query->remove($item);
           $this->getInputSource()->remove($item);
       }, array_flat($keys));
        return $this;
    }

    /**
     * check the input exists by the one of key
     * @param string|array $key 
     * @param int $number 
     * @return bool 
     */
    public function exists($key, int $number = 1)
    {
        $keys = (array) $key;
        $value = $this->filter();
        $count = 0;
        foreach($keys as $key){
            if(isset($value[$key])) $count +=1;
            if($count >= $number) return true;
        }
        return false;
    }

    /**
     * the contain alias method
     * @param array|string $key
     * @return bool
     */
    public function include(...$keys)
    {
        return $this->contain($keys);
    }

    /**
     * check input must contain by the keys
     * @param mixed ...$keys
     * @return bool
     */
    public function contain(...$keys)
    {
        return !array_diff_key(array_flip(array_flat($keys)), $this->filter());
    }

    /**
     * Replace the input for the current request.
     *
     * @param  array  $key
     * @return self
     */
    public function replace(...$keys)
    {
        $this->getInputSource()->replace(...array_flat($keys));

        return $this;
    }

    /**
     * 获取主机
     * @return string
     */
    public function host()
    {
        return $this->getHost();
    }

    /**
     * 获取主机加端口
     * @return string
     */
    public function port()
    {
        return $this->getHttpHost();
    }

    /**
     * 获取完整主机请求地址
     * @return string
     */
    public function httpHost()
    {
        return $this->getScheme() . '://' . $this->port();
    }


    /**
     * alias getOS()
     * @return mixed|string
     */
    public function os()
    {
        return $this->getOS();
    }

    /**
     * [os 获取操作系统类型]
     * @return mixed|string
     * @version v1.0
     */
    public function getOS()
    {
        $oses = array(
            'Windows 10'       => '(Windows NT 10)|(Windows 10)',
            'Windows 8.1'       => '(Windows NT 6.3)|(Windows 8)',
            'Windows 8'         => '(Windows NT 6.2)|(Windows 8)',
            'Windows 7'         => '(Windows NT 6.1)|(Windows 7)',
            'Windows Vista'     => '(Windows NT 6.0)|(Windows Vista)',
            'Windows 2003'      => '(Windows NT 5.2)',
            'Windows XP'        => '(Windows NT 5.1)|(Windows XP)',
            'Windows NT'        => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)',
            'Windows ME'        => 'Windows ME',
            'Windows 2000'      => '(Windows NT 5.0)|(Windows 2000)',
            'Windows 98'        => '(Windows 98)|(Win98)',
            'Windows 95'        => '(Windows 95)|(Win95)|(Windows_95)',
            'Windows 3.11'      => 'Win16',
            'Mac OSX'           => '(Mac_PowerPC)|(Macintosh)|(MAC OS X)',
            'iPhone'            => '(iPhone)',
            'iPad'              => '(iPad)',
            'Android'           => '(Android)',
            'Windows Phone'     => '(Windows Phone)|(IEMobile)',
            'Open BSD'          => 'OpenBSD',
            'Linux'             => '(Linux)|(X11)',
            'Sun OS'            => 'SunOS',
            'MeeGo'             => 'MeeGo',
            'QNX'               => 'QNX',
            'BeOS'              => 'BeOS',
            'OS/2'              => 'OS/2',
            'Robot'             => '(bot|curl|spider|slurp|crawler|fetch|facebook)',
        );

        $userAgent = $this->header('User-Agent');

        foreach ($oses as $os => $pattern) {
            // Use regular expressions to check operating system type
            if (preg_match('@' . $pattern . '@i', $userAgent)) {
                // Operating system was matched so return $oses key
                return $os;
            }
        }

        return null;
    }

    /**
     * get device
     * 
     * @return string 
     */
    public function device()
    {
        return $this->os();
    }

    /*
    * 判断是否为手机
    * @return bool
    */
    public function mobile(){ 
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if ($this->server('HTTP_X_WAP_PROFILE')){
            return true;
        } 
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if ($this->server('HTTP_VIA')){ 
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
        } 
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if ($this->server('HTTP_USER_AGENT')){
            $clientkeywords = array ('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'); 
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($this->server('HTTP_USER_AGENT')))){
                return true;
            } 
        } 
        // 协议法，因为有可能不准确，放到最后判断
        if ($this->server('HTTP_ACCEPT')){ 
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($this->server('HTTP_ACCEPT'), 'vnd.wap.wml') !== false) && (strpos($this->server('HTTP_ACCEPT'), 'text/html') === false || (strpos($this->server('HTTP_ACCEPT'), 'vnd.wap.wml') < strpos($this->server('HTTP_ACCEPT'), 'text/html')))){
                return true;
            } 
        } 
        return false;
    }
    
    /**
     * 判断是否为微信
     */
    public function weixin() { 
        return strpos($this->server('HTTP_USER_AGENT'), 'MicroMessenger') !== false;
    }

    /**
     * query get QueryString
     * @return string
     */
    public function query()
    {
        return urldecode($this->server('QUERY_STRING'));
    }

    /**
     * Get http referer
     * @return mixed 
     */
    public function referer()
    {
        return $this->header('referer');
    }

    /**
     * Referer alias name
     * @return mixed 
     */
    public function refer()
    {
        return $this->referer();
    }

    /**
     * [header]
     *
     * @param null $key
     * @param null $default
     * @return mixed
     */
    public function header($key = null, $default = null)
    {
        return $this->getItemSource('headers', $key, $default);
    }

    /**
     * Get Request bearer Token
     * @param \VM\Http\Type|null $var 
     * @return void 
     */
    public function bearer()
    {
        return str_replace('Bearer ', '', $this->token());
    }

    /**
     * Get Request bearer Token
     * @return mixed 
     */
    public function token($header = 'Authorization')
    {
        return $this->header($header);
    }

    /**
     * [server 获取server]
     *
     * @param null $key
     * @param null $default
     * @return mixed
     * @author 11.
     */
    public function server($key = null, $default = null)
    {
        return $this->getItemSource('server', $key, $default);
    }

    /**
     * [cookie 重构cookie方法适应Facades调用]
     *
     * @param $key
     * @param $default
     * @return mixed
     */
    public function cookie($key = null, $default = null)
    {
        if($key){
            return is_array($key) ? data_get($this->cookies->all(), $key) : $this->cookies->get($key, $default);
        }else{
            return $this->cookies->all();
        } 
    }

    /**
     * [获取session方法]
     *
     * @param null $key
     * @return mixed
     */
    public function session($key = null, $default = null)
    {
        if($key){
            return is_array($key) ? data_get(make('session')->all(), $key) : make('session')->get($key, $default);
        }else{
            return make('session')->all();
        }
    }

    /**
     * [browser 获取浏览器类型]
     * @param null $type
     * @return mixed|string
     * @version v1.0
     */
    public function browser($type = null)
    {
        $userAgent = $this->header('User-Agent');
        if ($type) return stripos($userAgent, $type);
        $browsers = [
            'Edge'=>'Microsoft Edge',
            '360SE'=>'360SE', 
            'QQ'=>'QQ Browser', 
            'MetaSr'=>'Sogou Explorer', 
            'LBBrowser'=>'LieBao Browser', 
            'UBrowser'=>'UC Browser', 
            'Triden'=>'Internet Explorer', 
            'Chrome'=>'Chrome', 
            'Firefox'=>'Firefox', 
            'Opera'=>'Opera',
            'Safari'=>'Safari',
            'Netscape'=>'Netscape'
        ];
        $browsers =  array_merge($browsers, (array) config('browerser', []));
        foreach($browsers as $tag => $browser){
            if (stripos($userAgent, $tag)) return $browser;
        }
    }


    /**
     * [file 获取上传文件]
     *
     * @param null $key
     * @param null $default
     * @return array
     * @author 11.
     */
    public function file($key = null, $default = null)
    {
        return $key ? with($this->files()[$key],  $default) : current($this->files());
    }


    /**
     * [files 获取上传文件]
     * @return array
     */
    public function files()
    {
        return $this->objectification_files($this->files->all());
    }


    /**
     * [isJson 判断是否为json]
     *
     * @return bool
     * @author 11.
     */
    public function isJson()
    {
        return $this->getMimeType('json');
    }

    /**
     * testing request accept
     * @param $contains
     * @return bool
     */
    public function accept($contains)
    {
        return \Str::contains($this->headers->get('accept'), $contains);
    }

    /**
     * Get Http Request Method
     * 
     * @param mixed|null $type 
     * @return bool|string 
     */
    public function method($type = null)
    {
        return $type ? $this->isMethod($type) : $this->getMethod();   
    }

    /**
     * 获取客户端IP地址
     * @param integer $long 返回类型 0 返回IP地址 1 返回IPV4地址整数类型
     * @param boolean $adv 是否进行高级模式获取（使用CDN模式下启用、有可能被伪装）
     * @return mixed
     */
    public function ip($long = 0, $adv = true)
    {
        static $ip = null;
        //if (!is_null($ip)) return $ip;
        if ($adv) {
            //特别注意,使用本项,必须保证客户端不是直接访问源服务器,前面一定的有CDN接入层
            //实际上在CDN确定的情况下,写死
            //如果客户端是直接访问源服务器,除REMOTE_ADDR外都可能被伪造
            if (isset($_SERVER['HTTP_X_REAL_IP'])) {
                //大多数CDN,本项取
                $ip = $_SERVER['HTTP_X_REAL_IP'];
            } elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
                //百度云加速、Cloudflare,本项取
                $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
            } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                //某些CDN会把客户端真实IP写入本项,","号分割的第一个
                $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos    =   array_search('unknown', $arr);
                if (false !== $pos) unset($arr[$pos]);
                $ip     =   trim($arr[0]);
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip     =   $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                //客户端直接访问源服务器,客户端真实IP取本项
                $ip     =   $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $ips = sprintf("%u", ip2long($ip));
        return  $ips ? ($long ? $ips : $ip) : ($long ? 0 : '0.0.0.0');
    }

    /**
     * [ips 获取客户端所有IP]
     *
     * @return array
     * @author 11.
     */
    public function ips()
    {
        return $this->getClientIps();
    }


    /**
     * [path 获取当前pathInfo]
     *
     * @return string
     * @author 11.
     */
    public function path()
    {
        return $this->getPathInfo();
    }

    /**
     * [root 获取根路径]
     *
     * @return string
     * @author 11.
     */
    public function root()
    {
        return $this->getBaseUrl() ?: '/';
    }

    /**
     * alias isAjax method
     * 
     * @return bool
     */
    public function ajax()
    {
       return $this->isAjax();
    }

    /**
     * ajax 判断ajax请求
     * @return bool 
     */
    public function isAjax()
    {
        return $this->isXmlHttpRequest();
    }

    /**
     * [baseUrl 获取根URL]
     * @return [type] [description]
     */
    public function baseUrl()
    {
        return rtrim($this->getSchemeAndHttpHost(). $this->root(), '/');
    }

    /**
     * get browser language
     * 
     * @return null|string
     */
    public function language()
    {
        return $this->getPreferredLanguage();
    }

    /**
     * get secure http request
     * 
     * @return [type] [description]
     */
    public function scheme()
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    /**
     * 获取当前域名
     * @param bool|int $subDomain 是否带二级域名
     * @return string
     */
    public function domain($subDomain = true)
    {
        return $subDomain ? $this->host() : trim(substr($this->host(), strpos($this->host(), '.')), '.');
    }

    /**
     * [segment 根据索引获取path]
     *
     * @param      $index [从1开始]
     * @param null $default
     * @return mixed
     * @author 11.
     */
    public function segment($index, $default = null)
    {
        return \Arr::get($this->segments(), $index - 1, $default);
    }

    /**
     * [segments 分解PATH]
     *
     * @return array
     * @author 11.
     */
    public function segments()
    {
        $segments = explode('/', $this->path());
        return array_values(array_filter($segments, function ($v) {
            return $v != '';
        }));
    }

    /**
     * [secure 判断是否是安全请求]
     *
     * @return bool
     * @author 11.
     */
    public function secure()
    {
        return $this->isSecure();
    }

    /**
    * Set Pathinfo
    * @param  string $path 
    * @return self 
    * @version 20240206
    */
    public function setPathinfo($path)
    {
        $this->pathInfo = $path;
        return $this;
    }

    /**
     * Get Request Source With From Content Type
     * @return void 
     */
    public function setContent($data = [])
    {
        switch ($this->getContentType()) {
            case 'json':
                $data = json_decode($this->getContent(), true);
                break;
            case 'form':
                if (in_array($this->method, array('PUT', 'DELETE', 'PATCH'))){
                    parse_str($this->getContent(), $data);
                }
                break;
        }
        $data && $this->request->add($data);
    }

    /**
     * Convert the given array
     *
     * @param  array  $files
     * @return array
     */
    protected function objectification_files(array $files)
    {
        return array_map(function ($file) {
            if (is_null($file) || (is_array($file) && empty(array_filter($file)))) {
                return $file;
            }

            return is_array($file)
                ? $this->objectification_files($file)
                : Upload::createFromBase($file);
        }, $files);
    }

    /**
     * 
     * @param mixed $item 
     * @param mixed $key 
     * @param mixed|null $default 
     * @return mixed 
     */
    protected function retrieve($item, $key, $default = null)
    {
        return $this->getItemSource($item, $key, $default);
    }

    /**
     * [getItemBySource]
     *
     * @param $item
     * @param $key
     * @param $default
     * @return mixed
     */
    protected function getItemSource($item, $key = null, $default = null)
    {
        return $key ? $this->$item->get($key, $default, true) : $this->$item->all();
    }

    /**
     * [getInputSource ]
     *
     * @return mixed|HttpFoundation\ParameterBag
     * @author 11.
     */
    protected function getInputSource()
    {
        return $this->method('GET') ? $this->query : $this->request;
    }

    /**
     * Get all of the input and files for the request.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->all();
    }

    /**
     * Determine if the given offset exists.
     *
     * @param  string  $offset
     * @return bool
     */
    public function offsetExists($offset) : bool
    {
        return array_key_exists($offset, $this->all());
    }

    /**
     * Get the value at the given offset.
     *
     * @param  string  $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Set the value at the given offset.
     *
     * @param  string  $offset
     * @param  mixed  $value
     * @return mixed
     */
    public function offsetSet($offset, $value) : void
    {
         $this->set($offset, $value);
    }

    /**
     * Remove the value at the given offset.
     *
     * @param  string  $offset
     * @return mixed
     */
    public function offsetUnset($offset) : void
    {
        $this->delete($offset);
    }

    /**
     * Create an request from a HttpFoundation instance.
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @return \VM\Http\Request
     */
    public static function createFromBase(HttpFoundation\Request $request)
    {
        if ($request instanceof static) return $request;
        $content = $request->content;
        $request = (new static)->duplicate(
            $request->query->all(),
            $request->request->all(),
            $request->attributes->all(),

            $request->cookies->all(),
            $request->files->all(),
            $request->server->all()
        );

        $request->content = $content;
        $request->request = $request->getInputSource();
        return $request;
    }
}
