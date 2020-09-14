<?php
/**
 * Get the available container instance.
 *
 * @param  string  $make
 * @param  array   $parameters
 * @return mixed|\VM\Application
 */
if(!function_exists('app')){
    function app($make = null, $parameters = [])
    {
        if (is_null($make)) {
            return \VM\Application::getInstance();
        }

        return \VM\Application::getInstance()->make($make, $parameters);
    }
}

/**
 * Gets the value of an environment variable. Supports boolean, empty and null.
 *
 * @param  string  $key
 * @param  mixed   $default
 * @return mixed
 */
if (!function_exists('env')) {

    function env($key, $default = null)
    {
        $value = getenv($key);
        return $value ?: $default;
    }
}

/**
 * make alias name for app
 * @param null $make
 * @param array $parameters
 * @return mixed
 */
if(!function_exists('make')) {
    function make($make = null, $parameters = [])
    {
        return app($make, $parameters);
    }
}

/**
 * log
 * @return mixed
 */
if(!function_exists('log')) {
    function logs()
    {
        return make('log')->dir(func_get_args());
    }
}


/**
 * request url
 * @return mixed
 */
if(!function_exists('url')) {
    function url()
    {
        return call_user_func_array(array(app('request'), 'url'), func_get_args());
    }
}

/**
 * request uri
 * @return mixed
 */
if(!function_exists('uri')) {
    function uri()
    {
        return call_user_func_array(array(app('request'), 'uri'), func_get_args());
    }
}


/**
 * get the language
 * @return mixed
 */
if(!function_exists('lang')) {
    function lang()
    {
        return call_user_func_array(array(app('lang'), 'get'), func_get_args());
    }
}

/**
 * get config
 * @return string
 */
if(!function_exists('config')) {

    function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('config');
        }

        if (is_array($key)) {
            return app('config')->set($key);
        }

        return app('config')->get($key, $default);
    }
}
/**
 * get path
 *
 * @return string
 */
if(!function_exists('root')) {
    function root()
    {
        $args = func_get_args();
        if(!$args) return _ROOT_;
        $dir = _ROOT_;

        array_map(function ($arg) use (&$dir) {
            $dir .= '/' . trim($arg, '/');
        }, $args);

        if($args[0] == 'runtime'){
        }
        return $dir;
    }
}

/**
 * root alias name
 */
if(!function_exists('path')) {
    function path()
    {
        $args = func_get_args();
        return call_user_func_array("root", $args);
    }
}

/**
 * Get the path to the base of the install.
 *
 * @param  string  $path
 * @return string
 */
if (! function_exists('base_path')) {
    function base_path($path = '')
    {
        return root($path);
    }
}

/**
 * runtime DIR
 */
if(!function_exists('runtime')) {
    function runtime()
    {
        $args = func_get_args();
        $args && $args[0] = trim($args[0],'/');
        array_unshift($args, 'runtime');
        return call_user_func_array("root", $args);
    }
}

/**
 * request object
 * @return \VM\Http\Request
 */
if(!function_exists('request')) {
    function request($key = null)
    {
        if(is_null($key)) {
            return make('request');
        }
        return make('request')->get($key);
    }
}
/**
 * response
 * @param $content
 * @param int $code
 * @param array $header
 * @return \VM\Http\Response\Base
 */
if(!function_exists('response')) {
    function response($content = null, $status = 200, $header = array())
    {
        if (func_num_args() === 0) return make('response');
        return make('response')->make($content, $status, $header);
    }
}

/**
 * redirect to url
 * @param $url
 * @param int $status
 * @param array $headers
 * @return \VM\Http\Redirect
 */
if(!function_exists('redirect')) {
    function redirect($url = null, $status = 302, $headers = [])
    {
        return $url ? make('redirect')->to($url, $status, $headers) : make('redirect');
    }
}
/**
 * session object
 * @return \VM\Http\Session
 */
if(!function_exists('session')) {
    function session($k = false, $v = false)
    {
        if ($k && $v) {
            return make('session')->set($k, $v);
        } else if ($k) {
            return make('session')->get($k);
        } else {
            return make('session');
        }
    }
}
/**
 * Create a new cookie instance.
 *
 * @param  string  $name
 * @param  string  $value
 * @param  int     $minutes
 * @param  string  $path
 * @param  string  $domain
 * @param  bool    $secure
 * @param  bool    $httpOnly
 * @return \VM\Http\Cookie
 */
if(!function_exists('cookie')) {
    function cookie($name = null, $value = null, $minutes = 0, $path = null, $domain = null, $secure = false, $httpOnly = true)
    {
        /*
         * @var $cookie \VM\Http\Cookie
         */

        if (is_null($name)) {
            return app('cookie');
        } else if ($name && is_null($value)) {
            return app('cookie')->get($name);
        } else {
            return app('cookie')->set($name, $value, $minutes, $path, $domain, $secure, $httpOnly);
        }
    }
}

/**
 * \DB::table function
 * @param $table
 * @return \Illuminate\Database\Query\Builder
 */
if(!function_exists('DB')) {
    function DB($table)
    {
        return \DB::table($table);
    }
}

/**
 * 执行Javascript
 * @param $script string 脚本
 * @param $status int 状态
 * @param headers array 响应头
 */
if(!function_exists('javascript')) {
    function javascript($script, $status = 200, $headers = array())
    {
        return response('<script type="text/javascript">'.$script.'</script>', $status, $headers);
    }
}


/**
 * view response
 * @param $view
 * @param array $data
 * @param int $status
 * @param array $headers
 * @return \VM\View
 */
if(!function_exists('view')) {
    function view($view, $data = [], $status = 200, array $headers = [])
    {
        return make('response')->view($view, $data, $status, $headers);
    }
}

/**
 * json response
 * @param array $data
 * @param int $status
 * @param array $headers
 * @return \VM\Http\Response\Json
 */
if(!function_exists('json')) {
    function json($data = [], $status = 200, array $headers = [])
    {
        return make('response')->json($data, $status, $headers);
    }
}

/**
 * input method
 * @param null $key
 * @param null $default
 * @return string
 */
if(!function_exists('input')) {
    function input($key = null, $default = '')
    {
        return make('request')->get($key, $default);
    }
}
/**
 * @param bool $key
 * @param bool $value
 * @param int $time
 * @return \VM\Cache\Driver\Driver
 */
if(!function_exists('cache')) {
    function cache($key = false, $default = null)
    {
        if ($key) {
            return make('cache')->get($key, $default);
        }
        return make('cache');
    }
}

/**
 * www目录访问路径
 * @param $path
 * @return string
 */
function www($path)
{
    $url = '/';
    if(\Str::startsWith($path, '/')){
        $url = $url.str_replace('//','/', trim($path, '/'));
    }else{
        $url = $url.str_replace('//', '/', trim(strtolower(_APP_).'/'.$path, '/'));
    }


    return $url;
}


/**
 * redis
 * @param string $server
 * @return \Redis
 */
if(!function_exists('redis')) {
    function redis($server = 'default')
    {
        return make('cache')->redis($server);
    }
}
/**
 * random string
 * @param int $length
 * @return string
 */
if(!function_exists('random')) {
    function random($length = 8, $code = null)
    {
        $code = $code ? (is_array($code) ? $code : str_split($code)) : array_merge(array_merge(range('A', 'Z'),range('a','z'),range(0, 9)));
        shuffle($code);
        return implode(array_slice($code, 0, $length));
    }
}

/**
 * 文件大小单位转换
 * @param $bytes
 * @param int $decimals
 * @return string
 */
if(!function_exists('readable_size')) {
    function readable_size($bytes, $decimals = 0)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        $floor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $floor)) . $units[$floor];
    }
}

/**
 * 格式化数字
 * @param int $number 数字
 * @param int $decimals 小数点
 * @return string
 */
if(!function_exists('readable_number')) {
    function readable_number($number, $decimals = 1)
    {
       if ($number >= 100 && $number < 100000) {
            return sprintf('%01.' . $decimals . 'f', (sprintf('%01.0f', $number) / 1000)) . 'k';
        } elseif ($number >= 100000 && $number < 100000000) {
            return sprintf('%01.' . $decimals . 'f', (sprintf('%01.0f', $number) / 1000000)) . 'm';
        } elseif ($number >= 100000000) {
            return sprintf('%01.' . $decimals . 'f', (sprintf('%01.0f', $number) / 1000000000)) . 'b';
        }
        return $number;
    }
}

//截取字符串
/**
 * @param $string
 * @param int $length
 * @param string $symbol
 * @return array|string
 */
if(!function_exists('strcut')) {
    function strcut($string, $length = 255, $symbol = '...')
    {
        $str_array = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
        //$str1 = implode('',array_reverse($string));
        if (count($str_array) > $length) {
            return implode('', array_splice($str_array, 0, $length)) . $symbol;
        }
        return $string;
    }
}

/**
 * 增强array_key_exists 支持多键名检测
 * @param array $keys
 * @param array $array
 * @return bool
 */
if(!function_exists('array_keys_exists')) {
    function array_keys_exists(array $keys, array $array)
    {
        return !array_diff_key(array_flip($keys), $array);
    }
}

/**
 * 还原array_dot函数
 * @param $dotNotationArray string
 */
if (!function_exists('array_undot')) {
    function array_undot($dotNotationArray)
    {
        $array = [];
        foreach ($dotNotationArray as $key => $value) {
            array_set($array, $key, $value);
        }
        return $array;
    }
}

if(!function_exists('dump')) {
    function dump()
    {
        $args = func_get_args();

        // 调用栈,debug_backtrace()
        $backtrace = debug_backtrace();

        $file = $backtrace[0]['file'];
        $line = $backtrace[0]['line'];
        echo "<b>$file: $line</b><hr />";
        echo "<pre>";
        foreach ($args as $arg) {
            var_dump($arg);
        }
        echo "</pre>";
        die;
    }
}

/**
 * 终断输出
 */
if(!function_exists('abort')) {
    function abort($code, $message = '', array $headers = [])
    {
        if ($code instanceof Response) {
            throw new HttpResponseException($code);
        } elseif ($code instanceof \Illuminate\Contracts\Support\Responsable) {
            throw new HttpResponseException($code->toResponse(request()));
        }

        app()->abort($code, $message, $headers);
    }

if(!function_exists('is_phone')) {
    /**  
     * 验证字符串是否为手机号  
     * @param string $phone  
     * @return bool  
     */
    function is_phone($phone)
    {
        return (strlen($phone) != 11 || !preg_match("/^1[345678]\d{9}$/",$phone));
    }
}

if(!function_exists('is_str')) {   
    /**  
     * 验证字符串是否为数字,字母,中文和下划线构成  
     * @param string $username  
     * @return bool  
     */
    function is_str($str)
    {
        if (preg_match('/^[\x{4e00}-\x{9fa5}\w_]+$/u', $str)) {
            return true;
        } else {
            return false;
        }
    }
}

if(!function_exists('is_json')){

    /**  
     * 验证字符串是否为json格式  
     * @param string $$json  
     * @return bool  
     */
    function is_json($json)
    {

    }
}

if(!function_exists('is_email')) {   
    /**  
     * 是否为一个合法的email  
     * @param sting $email  
     * @return boolean  
     */
    function is_email($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return false;
        }
    }
}
if(!function_exists('is_url')) {   
    /**  
     * 是否为一个合法的url  
     * @param string $url  
     * @return boolean  
     */
    function is_url($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return true;
        } else {
            return false;
        }
    }
}
if(!function_exists('is_ip')) {   
    /**  
     * 是否为一个合法的ip地址  
     * @param string $ip  
     * @return boolean  
     */
    function is_ip($ip)
    {
        if (ip2long($ip)) {
            return true;
        } else {
            return false;
        }
    }
}
if(!function_exists('is_number')) {   
    /**  
     * 是否为整数  
     * @param int $number  
     * @return boolean  
     */
    function is_number($number)
    {
        if (preg_match('/^[-\+]?\d+$/', $number)) {
            return true;
        } else {
            return false;
        }
    }
}
if(!function_exists('is_positive_number')) {   
    /**  
     * 是否为正整数  
     * @param int $number  
     * @return boolean  
     */
    function is_positive_number($number)
    {
        if (ctype_digit($number)) {
            return true;
        } else {
            return false;
        }
    }
}

if(!function_exists('is_decimal')) { 
    /**  
     * 是否为小数  
     * @param float $number  
     * @return boolean  
     */
    function is_decimal($number)
    {
        if (preg_match('/^[-\+]?\d+(\.\d+)?$/', $number)) {
            return true;
        } else {
            return false;
        }
    }
}
if(!function_exists('is_positive_decimal')) { 
    /**  
     * 是否为正小数  
     * @param float $number  
     * @return boolean  
     */
    function is_positive_decimal($number)
    {
        if (preg_match('/^\d+(\.\d+)?$/', $number)) {
            return true;
        } else {
            return false;
        }
    }
}

if(!function_exists('is_english')) { 
    /**  
     * 是否为英文  
     * @param string $str  
     * @return boolean  
     */
    function is_english($str)
    {
        if (ctype_alpha($str))
            return true;
        else
            return false;
    }
}

if(!function_exists('is_chinese')) { 
    /**  
     * 是否为中文  
     * @param string $str  
     * @return boolean  
     */
    function is_chinese($str)
    {
        if (preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $str))
            return true;
        else
            return false;
    }
}
if(!function_exists('is_image')) { 
    /**  
     * 判断是否为图片  
     * @param string $file  图片文件路径  
     * @return boolean  
     */
    function is_image($file)
    {
        if (file_exists($file) && getimagesize($file === false)) {
            return false;
        } else {
            return true;
        }
    }
}
if(!function_exists('is_card')) { 
    /**  
     * 是否为合法的身份证(支持15位和18位)  
     * @param string $card  
     * @return boolean  
     */
    function is_card($card)
    {
        if (preg_match('/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$/', $card) || preg_match('/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{4}$/', $card))
            return true;
        else
            return false;
    }
}

if(!function_exists('is_date')) { 
    /**  
     * 验证日期格式是否正确  
     * @param string $date  
     * @param string $format  
     * @return boolean  
     */
    function is_date($date, $format = 'Y-m-d')
    {
        $t = date_parse_from_format($format, $date);
        if (empty($t['errors'])) {
            return true;
        } else {
            return false;
        }
    }   
}
}