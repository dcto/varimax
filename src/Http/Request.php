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

class Request extends HttpFoundation\Request implements Arrayable, \ArrayAccess
{
    /**
     * 重构Request方法
     */
    public function __construct(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null)
    {
        /**
         * 命令行模式获取参数
         */
        if ('cli-server' === php_sapi_name()) {
            if (array_key_exists('HTTP_CONTENT_LENGTH', $_SERVER)) {
                $_SERVER['CONTENT_LENGTH'] = $_SERVER['HTTP_CONTENT_LENGTH'];
            }
            if (array_key_exists('HTTP_CONTENT_TYPE', $_SERVER)) {
                $_SERVER['CONTENT_TYPE'] = $_SERVER['HTTP_CONTENT_TYPE'];
            }
        }

        /**
         * 初始化请求对象
         */
        parent::__construct(
            array_merge($_GET, $query),
            array_merge($_POST, $request),
            array_merge(array(), $attributes),
            array_merge($_COOKIE, $cookies),
            array_merge($_FILES, $files),
            array_merge($_SERVER, $server)
        );

        /**
         * 后置处理
         */
        if (0 === strpos($this->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded') && in_array(strtoupper($this->server->get('REQUEST_METHOD', 'GET')), array('PUT', 'DELETE', 'PATCH'))) {
            parse_str($this->getContent(), $data);
            $this->request = new HttpFoundation\ParameterBag($data);
        }
    }

    /**
     * [is 判断当前路径是否匹配]
     *
     * @return bool
     * @author 11.
     */
    public function is()
    {
        foreach (func_get_args() as $pattern) {
            if (\Str::is($pattern, urldecode($this->path()))) {
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
                if (!$route = $router->router($route)) throw new NotFoundException("The $tags route does not found");
                
                return $route->url($args);
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
     * @param null $cast [URL参数]
     * 符号说明: ! 去除指定参数
     * 符号说明: & 附加URL指定参数
     * 符号说明: @ 保留URL指定参数
     *
     */
    public function uri($cast = null)
    {
        $query = $this->all();

        if (is_array($cast)) {
            $query = array_merge($query, $cast);
        } else {
            switch (substr($cast, 0, 1)) {
                case '!':
                    unset($query[ltrim($cast, '!')]);
                    break;

                case '&':
                    $cast = trim($cast, '?&');
                    parse_str($cast, $queryArray);
                    $query = array_merge($query, $queryArray);
                    break;

                case '@':
                    $cast = ltrim($cast, '@');
                    $query = array_key_exists($cast, $query) ? array($cast => $query[$cast]) : array();
                    break;
            }
        }

        $uri = $query ? '?' . http_build_query($query) : '';

        return $uri = $this->url() . $uri;
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
     * [input方法别名]
     *
     * @param $key
     * @param null $default
     * @return mixed
     * @author 11.
     */
    public function input($key = null, $default = null)
    {
        $input = $this->getInputSource()->all() + $this->query->all() + $this->attributes->all();

        return is_array($key) ? \Arr::only($input, $key) : \Arr::get($input, $key, $default);
    }


    /**
     * [has 是否存在]
     *
     * @param $key
     * @return bool
     * @author 11.
     */
    public function has($key)
    {
        $keys = is_array($key) ? $key : func_get_args();

        $input = $this->all();

        foreach ($keys as $key) {
            if (!isset($input[$key]) || !strlen($input[$key])) {
                return false;
            }
        }
        return true;
    }

    /**
     * [get input方法别名]
     *
     * @param            $key
     * @param null $default
     * @return mixed
     * @author 11.
     */
    public function get($key = null, $default = null)
    {
        return $this->input($key, $default);
    }

    /**
     *
     * 整理后get函数,过滤决对空值
     * @param null $key
     * @param null $default
     * @return bool|mixed
     */
    public function got($key = null, $default = null)
    {
        $input = $this->all();
        if (!isset($input[$key]) || trim($input[$key]) == '') {
            return null;
        }

        return $input[$key] ?: $default;
    }

    /**
     * 修改参数
     * @param $key
     * @param $this
     */
    public function set($key, $val)
    {
        $this->getInputSource()->set($key, $val);

        return $this;
    }


    /**
     * [all 返回所有]
     *
     * @return array
     * @author 11.
     */
    public function all($key = null, $filter = false)
    {
        if ($key && $key[0] == '!') {
            return $this->not(ltrim($key, '!'));
        }
        return $this->input($key);
        //return array_replace_recursive($this->input(), $this->files->all());
    }

    /**
     * 设定变量
     * @param $query
     * @return $this
     */
    public function put($key, $val = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->attributes->set($key, $val);
            }
        } else {
            $this->attributes->set($key, $val);
        }

        return $this;
    }

    /**
     * [not 排除返回]
     * @return array
     */
    public function not()
    {
        //return array_diff_key($this->all(), array_fill_keys($key, null));
        return \Arr::except($this->all(), func_get_args());
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
    public function trim($key)
    {
        return trim($this->get($key)) == '';
    }


    /**
     *
     * @param null $key
     * @return array
     */
    public function except($key = null)
    {
        return $this->not($key);
    }

    /**
     * Merge new input into the current request's input array.
     *
     * @param  array  $input
     * @return $this
     */
    public function merge(array $input)
    {
        $this->getInputSource()->add($input);

        return $this;
    }

    /**
     * Replace the input for the current request.
     *
     * @param  array  $input
     * @return $this
     */
    public function replace(array $input)
    {
        $this->getInputSource()->replace($input);

        return $this;
    }

    /**
     * [take get方法加强版,支持单参数、数组、多参数数组]
     * @param $key
     * @return array|mixed
     */
    public function take()
    {
        $key = null;
        if (($num = func_num_args()) > 0) {
            $key = $num == 1 ? func_get_arg(0) : func_get_args();
        }
        if (is_array($key)) {
            return array_intersect_key($this->all(), array_fill_keys($key, null));
        } else {
            return $this->get($key);
        }
    }


    /**
     * [filter take方法加强版，整理返回过滤数组空值,多参数获取]
     * @return array
     * @author dc.T
     * @version v1.0
     */
    public function filter()
    {
        return array_filter(call_user_func_array(array($this, 'take'), func_get_args()), function ($v) {
            return $v !== false && !is_null($v) && ($v != '' || $v == '0');
        });
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
    public function only($keys = [])
    {
        return \Arr::only($this->all(), $keys);
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

        //var_dump(preg_match('@MeeGo@i', 'Mozilla/5.0 (MeeGo; NokiaN9) AppleWebKit/534.13 (KHTML, like Gecko) NokiaBrowser/8.5.0 Mobile Safari/534.13'));

        //die;
        // Loop through $oses array
        foreach ($oses as $os => $pattern) {
            // Use regular expressions to check operating system type
            if (preg_match('@' . $pattern . '@i', $userAgent)) {
                // Operating system was matched so return $oses key
                return $os;
            }
        }

        return $userAgent;
    }


    public function device()
    {
        $devices = array(
            'Mobile' => array('iPhone', '')
        );
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


    public function referer()
    {
        return $this->header('referer');
    }

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
     * @author 11.
     */
    public function header($key = null, $default = null)
    {
        return $this->retrieve('headers', $key, $default);
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
        return $this->retrieve('server', $key, $default);
    }

    /**
     * [cookie 重构cookie方法适应Facades调用]
     *
     * @param $key
     * @param $default
     * @return mixed
     * @author 11.
     */
    public function cookie($key = null)
    {
        if ($key) {
            return $this->cookies->get($key);
        } else {
            return $this->cookies->all();
        }
    }

    /**
     * [获取session方法]
     *
     * @param null $key
     * @return mixed
     */
    public function session($key = null)
    {
        if ($key) {
            return make('session')->get($key);
        } else {
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
        return \Str::contains($this->header('CONTENT_TYPE'), '/json');
    }

    /**
     * testing request accept
     * @param $contains
     * @return bool
     */
    public function accept($contains)
    {
        return \Str::contains($this->header('accept'), $contains);
    }

    /**
     * [json Get the JSON payload for the request.]
     *
     * @param null $key
     * @param null $default
     * @return mixed|\Symfony\Component\HttpFoundation\ParameterBag
     * @author 11.
     */
    public function json($key = null, $default = null)
    {
        if (!isset($this->json)) {
            $this->json = new HttpFoundation\ParameterBag((array)json_decode($this->getContent(), true));
        }

        if (is_null($key)) {
            return $this->json;
        }

        return \Arr::get($this->json->all(), $key, $default);
    }

    /**
     * [method 获取当前请求方式]
     *
     * @param null $type
     * @return bool|string
     */
    public function method($type = null)
    {
        $method = $this->getMethod();

        return $type ? strtoupper($type) == $method : $method;
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
     * ajax 判断ajax请求
     * @return bool
     * @author: 11
     */
    public function ajax()
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
     * @return null|string
     */
    public function language()
    {
        return $this->getPreferredLanguage();
    }

    /**
     * [scheme 获取请求方式]
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
     * [retrieve]
     *
     * @param $source
     * @param $key
     * @param $default
     * @return mixed
     * @author 11.
     */
    protected function retrieve($source, $key, $default)
    {
        if (is_null($key)) {
            return $this->$source->all();
        }

        return $this->$source->get($key, $default, true);
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

    /**
     * {@inheritdoc}
     */
    public function duplicate(array $query = null, array $request = null, array $attributes = null, array $cookies = null, array $files = null, array $server = null)
    {
        return parent::duplicate($query, $request, $attributes, $cookies, array_filter((array) $files), $server);
    }


    /**
     * [getInputSource 获取请求方法]
     *
     * @return mixed|HttpFoundation\ParameterBag
     * @author 11.
     */
    protected function getInputSource()
    {
        if ($this->isJson()) {
            return $this->json();
        }
        return $this->method() == 'GET' ? $this->query : $this->request;
    }
}
