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

use VM\Http\Request\Upload;
use VM\Exception\NotFoundException;
use Symfony\Component\HttpFoundation;
use Illuminate\Contracts\Support\Arrayable;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;

class Request extends HttpFoundation\Request implements Arrayable, \ArrayAccess
{
    /**
     * createGlobalForm
     */
    public function __construct()
    {
        if ('cli-server' === \PHP_SAPI) {
            if (\array_key_exists('HTTP_CONTENT_LENGTH', $_SERVER)) {
                $_SERVER['CONTENT_LENGTH'] = $_SERVER['HTTP_CONTENT_LENGTH'];
            }
            if (\array_key_exists('HTTP_CONTENT_TYPE', $_SERVER)) {
                $_SERVER['CONTENT_TYPE'] = $_SERVER['HTTP_CONTENT_TYPE'];
            }
        }
        
        parent::__construct($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER);

        /**
         * application/x-www-form-urlencoded
         */
        if ($this->getContentType() == 'form' && in_array(strtoupper($this->server->get('REQUEST_METHOD', 'GET')), array('PUT', 'DELETE', 'PATCH'))) {
            parse_str($this->getContent(), $data);
            $this->request = new HttpFoundation\ParameterBag($data);
        /**
         * application/json
         */
        }else if($this->getContentType() == 'json'){
            
            $this->request = new HttpFoundation\ParameterBag((array) json_decode($this->getContent(), true));
        }
    }

    /**
     * [is 判断当前路径是否匹配]
     *
     * @return bool
     */
    public function is(...$args)
    {
        foreach ($args as $arg) {
            if (\Str::is($arg, urldecode($this->path()))) {
                return true;
            }
        }

        return false;
    }

    /**
     * [获取当前 URL]
     * @param null [构建 URL 参数 非=当前URL, 空=根URL, /=根URL+'/',  ?=构建URL,  @=获得路由URL,]
     * @example url() uri()
     * @example url('') baseUrl
     * @example url('/') baseUrl + '/'
     * @example url('/abc', 'a','b');
     * @example url('?', array('a'=>'b','c'=>'d'), 'c=d');
     * @example url('@index'),  url('@', 'index'), url('@', 'index', ...$pattern) ;
     * @return string
     */
    public function url()
    {
        $args = func_get_args();
        $tags = array_shift($args);
        if (func_num_args() == 0) return preg_replace('/\?.*/', '', $this->getUri());
        $baseUrl = $this->baseUrl();
        if (!$tags) return $baseUrl;

        switch ($tags[0]) {
            case '/':
                return $tags == '/' ? $baseUrl . '/' : $baseUrl . $tags . ($args ? '/' . join('/', $args) : '');
                break;

            case '?':
                $url = $tags == '?' ? $tags : rtrim($tags, '&') . '&';
                foreach ($args as $arg) {
                    $url .= (is_array($arg) ?  http_build_query($arg) : $arg) . '&';
                }
                return $baseUrl . rtrim($url, '&');
                break;

            case '@':
                /*
                    * @var $router \VM\Routing\Route
                    */
                $router = make('router');
                /*
                    * @var $route \VM\Routing\Route
                    */
                $route = $tags == '@' ? array_shift($args) : ltrim($tags, '@');
                if (!$route = $router->router($route)) throw new NotFoundException("Unable route: $tags");
                
                return $baseUrl . '/' . trim($route->url($args), '/');

                /*
                if (!strpos($route->url(), ':')) return $baseUrl . '/' . trim($route->url(), '/');
                $url = preg_replace("/\([^)]+\)/", '%s', $route->url());
                return $baseUrl . '/' . trim(vsprintf($url, $args));
                */
                break;

            default:
                foreach ($args as $arg) {
                    $tags .= '&' . (is_array($arg) ?  http_build_query($arg) : $arg);
                }
                return $baseUrl . $tags;
        }
    }

    /**
     * [uri 获取当前url包含所有参数]
     * 
     * 符号说明: & 附加URL参数
     * 符号说明: ! 去除指定参数
     * 符号说明: @ 保留URL参数
     * 符号说明: ? 重构URL参数
     * 符号说明: ~ 清空URL参数值
     * 
     * @example uri('&', 'page')
     * @example uri('!', 'page')
     * @example uri('@', 'page')
     * @example uri('?', 'page=1&pagesize=2', 'a=b&1=2')
     * @example uri('~', 'page')
     *
     */
    public function uri(...$args)
    {
        $input = $this->all();
        if($args){

            switch($args[0]){

                case '&':
                    array_shift($args);
                    array_map(function($arg) use(&$input){
                        if(is_array($arg)) {
                            $input = array_merge($input, $arg);
                        }else{
                            $param = array();
                            parse_str(trim($arg, '&'), $param);
                            $input = array_merge($input, $param);
                        }
                    }, $args);
                break;

                case '!':
                    array_shift($args);
                    $input = \Arr::except($input, \Arr::flatten($args));    
                break;

                case '@':
                    array_shift($args);
                    $input = \Arr::only($input, \Arr::flatten($args));    
                break; 

                case '?':
                    array_shift($args);
                    $input = array();
                    array_map(function($arg) use(&$input){
                        if(is_array($arg)) {
                            $input = array_merge($input, $arg);
                        }else{
                            $param = array();
                            parse_str(trim($arg, '&'), $param);
                            $input = array_merge($input, $param);
                        }
                    }, $args);

                break;

                default:
                array_map(function($arg) use(&$input){
                    if(is_array($arg)) {
                        $input = array_merge($input, $arg);
                    }else{
                        $param = array();
                        parse_str(trim($arg, '&'), $param);
                        $input = array_merge($input, $param);
                    }
                }, $args);
            }
        }

        return  $input ? $this->url().'?' . http_build_query($input) : $this->url();
    }

    /**
     * [has 是否存在]
     *
     * @param $key
     * @return bool
     */
    public function has($key, $number = 1)
    {
        $input = $this->all();
        if(is_array($key)){
            $count = 0;
            foreach($key as $k){
                if(isset($input[$k])){
                    $count++;
                    if($count >= $number) return true;
                } 
            }
            return false;
        }else{
            return isset($input[$key]);
        }
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
     * [all 返回所有]
     *
     * @return array
     * @author 11.
     */
    public function all()
    {
        return array_replace(
            $this->getInputSource()->all(),
            $this->attributes->all(),
            $this->files->all()
        );
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
     * [not 排除返回]
     * @return array
     */
    public function not(...$key)
    {
        return $this->except($key);
    }

    /**
     * get request keys
     * 
     * @return array 
     * @throws SuspiciousOperationException 
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
    public function take(...$key)
    {
        return $key ? \Arr::only($this->all(), \Arr::flatten($key)) : $this->all();
    }

    /**
     * Fill the input keys
     * 
     * @param mixed $key 
     * @param mixed|null $value 
     * @return array 
     * @throws SuspiciousOperationException 
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
     * @param null $keys
     */
    public function only(...$key)
    {
        return \Arr::only($this->all(), \Arr::flatten($key));
    }

    /**
     * [input方法别名]
     *
     * @param $key
     * @param null|\Closure $default
     * @return mixed
     */
    public function input($key = null, $default = null)
    {
        $input = $this->all();
  
        if($key){
            if(is_array($key)) {
                $input = \Arr::only($input, $key);
            }else if($key){ 
                $input = isset($input[$key]) ? $input[$key] : null;
            }
        }
        return $default instanceof \Closure ? $default($input) : ($input?:$default);
    }
    
    /**
     * TRIM别名
     * @param $key
     * @return bool
     */
    public function null($key)
    {
        return $this->trim($key);
    }

    /**
     * [trim 判断真空]
     * @param $key
     * @return bool
     */
    public function trim(...$key)
    {
        return $this->filter($key);
    }


    /**
     *
     * @param null $key
     * @return array
     */
    public function except(...$key)
    {
        return $key ? \Arr::except($this->all(), \Arr::flatten($key)) : $this->all();
    }

    /**
     * Merge new input into the current request's input array.
     *
     * @param  array  $input
     * @return $this
     */
    public function merge(...$args)
    {
        $this->getInputSource()->add(\Arr::flatten($args));

        return $this;
    }

    /**
     * [filter take方法加强版，整理返回过滤数组空值,多参数获取]
     * @return array
     */
    public function filter()
    {
        return array_filter(call_user_func_array(array($this, 'all'), func_get_args()), function ($v) {
            return $v !== false && !is_null($v) && ($v != '' || $v == '0');
        });
    }

    /**
     * Replace the input for the current request.
     *
     * @param  array  $args
     * @return $this
     */
    public function replace(...$args)
    {
        $this->getInputSource()->replace(\Arr::flatten($args));

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
    public function hostPort()
    {
        return $this->getHttpHost();
    }

    /**
     * 获取完整主机请求地址
     * @return string
     */
    public function httpHost()
    {
        return $this->getScheme() . '://' . $this->hostPort();
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
            'iOS(iPhone)'       => '(iPhone)',
            'iOS(iPad)'         => '(iPad)',
            'Android'           => '(Android)',
            'Windows Phone'     => '(Windows Phone)|(IEMobile)',
            'Windows 3.11'      => 'Win16',
            'Windows 95'        => '(Windows 95)|(Win95)|(Windows_95)',
            'Windows 98'        => '(Windows 98)|(Win98)',
            'Windows 2000'      => '(Windows NT 5.0)|(Windows 2000)',
            'Windows XP'        => '(Windows NT 5.1)|(Windows XP)',
            'Windows 2003'      => '(Windows NT 5.2)',
            'Windows Vista'     => '(Windows NT 6.0)|(Windows Vista)',
            'Windows 7'         => '(Windows NT 6.1)|(Windows 7)',
            'Windows NT 4.0'    => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)',
            'Windows ME'        => 'Windows ME',
            'Mac OSX'           => '(Mac_PowerPC)|(Macintosh)',
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

        return $userAgent;
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
            return is_array($key) ? \Arr::get($this->cookies->all(), $key) : $this->cookies->get($key, $default);
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
            return is_array($key) ? \Arr::get(make('session')->all(), $key) : make('session')->get($key, $default);
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
        $agent = $this->header('User-Agent');
        if ($type) return stripos($agent, $type);
        if (stripos($agent, 'MSIE')) return 'MSIE';
        if (stripos($agent, 'Chrome')) return 'Chrome';
        if (stripos($agent, 'Firefox')) return 'Firefox';
        if (stripos($agent, 'Safari')) return 'Safari';
        if (stripos($agent, 'Netscape')) return 'Netscape';
        return null;
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
        if ($key) {
            return \Arr::get($this->files(), $key, $default);
        }
        return \Arr::first($this->files());
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
     * @throws SuspiciousOperationException 
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
        $pattern = $this->getPathInfo();

        return $pattern == '' ? '/' : $pattern;
    }

    /**
     * [root 获取根路径]
     *
     * @return string
     * @author 11.
     */
    public function root()
    {
        return rtrim($this->getSchemeAndHttpHost() . $this->getBasePath(), '/');
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
        return $this->httpHost() . $this->getBaseUrl();
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
     *
     * @param bool $subDomain 是否带二级域名
     * 
     * @return mixed|string
     */
    public function domain($subDomain = true)
    {
        if (filter_var($host = $this->getHost(), FILTER_VALIDATE_IP) || $subDomain) return $host;

        preg_match("/[^\.\/]+\.[^\.\/]+$/", $host, $matches);

        return current($matches);
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
     * @param mixed $source 
     * @param mixed $key 
     * @param mixed|null $default 
     * @return mixed 
     */
    protected function retrieve($source, $key, $default = null)
    {
        return $this->getItemSource($source, $key, $default);
    }

    /**
     * [getItemBySource]
     *
     * @param $source
     * @param $key
     * @param $default
     * @return mixed
     */
    protected function getItemSource($source, $key, $default = null)
    {
        if (is_null($key)) {
            return $this->$source->all();
        }

        return $this->$source->get($key, $default, true);
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
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->all());
    }

    /**
     * Get the value at the given offset.
     *
     * @param  string  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return data_get($this->all(), $offset);
    }

    /**
     * Set the value at the given offset.
     *
     * @param  string  $offset
     * @param  mixed  $value
     * @return mixed
     */
    public function offsetSet($offset, $value)
    {
        return $this->getInputSource()->set($offset, $value);
    }

    /**
     * Remove the value at the given offset.
     *
     * @param  string  $offset
     * @return mixed
     */
    public function offsetUnset($offset)
    {
        return $this->getInputSource()->remove($offset);
    }

    /**
     * Create an request from a HttpFoundation instance.
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @return \VM\Http\Request
     */
    public static function createFromBase(HttpFoundation\Request $request)
    {
        if ($request instanceof static) {
            return $request;
        }

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
