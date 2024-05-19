<?php
#!/usr/bin/php

if(!function_exists('app')){
    /**
     * Get the available container instance.
     * @param string|null $make
     * @param array $parameters
     * @return object|\VM\Application
     */
    function app($make = null, $parameters = [], $events = false)
    {
        return is_null($make) 
        ? \VM\Application::getInstance() 
        : \VM\Application::getInstance()->make($make, $parameters, $events);
    }
}

if(!function_exists('make')) {
    /**
     * make alias name for app
     * @param string|null $make
     * @param array $parameters
     * @param bool $events
     * @return object|\VM\Application
     */
    function make(...$args)
    {
        return app(...$args);
    }
}

if(!function_exists('i18n')) {
    /**
     * Get current i18n helper
     * @param null $i18n
     * @param array $i18n
     * @return mixed
     */
    function i18n($i18n = null)
    {
        return app('lang')->i18n($i18n);
    }
}

if(!function_exists('url')) {
    /**
     * request url helper
     * @return string
     */
    function url(...$args)
    {
        return app('request')->url(...$args);
    }
}

if(!function_exists('uri')) {
    /**
     * request uri
     * @return string|\VM\Http\Uri
     */
    function uri($uri = null)
    {  
        return app('uri')->uri($uri ?? app('request')->uri());
    }
}

if(!function_exists('lang')) {
    /**
     * lang instace helper
     * @return \VM\I18n\Lang|string
     */
    function lang(...$args)
    {
        return $args ? app('lang')->get(...$args) : app('lang');
    }
}

if (! function_exists('call')) {
    /**
     * Call a callback with the arguments.
     *
     * @param mixed $callback
     * @return null|mixed
     */
    function call($callback, array $args = [])
    {
        $result = null;
        if ($callback instanceof \Closure) {
            $result = $callback(...$args);
        } elseif (is_object($callback) || (is_string($callback) && function_exists($callback))) {
            $result = $callback(...$args);
        } elseif (is_array($callback)) {
            [$object, $method] = $callback;
            $result = is_object($object) ? $object->{$method}(...$args) : $object::$method(...$args);
        } else {
            $result = call_user_func_array($callback, $args);
        }
        return $result;
    }
}

if(!function_exists('route')) {
    /**
     * get the route
     * @param string $id 路由ID
     * @return \VM\Routing\Route
     */
    function route($id = null)
    {
        return app('router')->route($id);
    }
}

if(!function_exists('config')) {
    /**
     * get config
     * @return string|\VM\Config\Config
     */
    function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('config');

        }else if (is_array($key)) {
            return app('config')->set($key);
        }
        return app('config')->get($key, $default);
    }
}

if(!function_exists('root')) {
    /**
     * Get Root Paths
     * @param string|array $paths
     * @return string
     */
    function root(...$paths)
    {
        return _DOC_._DS_.join(_DS_, array_map(fn($p)=>trim($p, _DS_), array_flat($paths) ));
    }
}

if(!function_exists('app_dir')) {
    /**
     * Get app paths
     * @param string $paths
     * @return string
     */
    function app_dir(...$paths)
    {
        return root(_APP_, ...$paths);
    }
}

if (! function_exists('base_path')) {
    /**
     * The root alias helper
     *
     * @param  string  $path
     * @return string
     */
    function base_path(...$args)
    {
        return root(...$args);
    }
}

if(!function_exists('runtime')) {
    /**
     * app runtime dir
     * @param string paths
     * @return string
     */
    function runtime(...$paths)
    {
        $path = root(__FUNCTION__, ...$paths);
        file_exists($dir = pathinfo($path, PATHINFO_EXTENSION) ? dirname($path) : $path) || mkdir($dir, 0777, true);
        return $path;
    }
}

if(!function_exists('request')) {
    /**
     * request instace
     * @return \VM\Http\Request|string
     */
    function request($key = null, $default = '')
    {
        return is_null($key) ? app('request') : app('request')->get($key, $default);
    }
}

if(!function_exists('response')) {
    /**
     * response
     * @param $context
     * @param int $status
     * @param array $header
     * @return \VM\Http\Response
     */
    function response($context = null, $status = 200, $header = array())
    {
        return is_null($context) ? app('response') : app('response')->make($context, $status, $header);
    }
}

if(!function_exists('redirect')) {
    /**
     * redirect to url
     * @param $url
     * @param int $status
     * @param array $headers
     * @return \VM\Http\Response
     */
    function redirect($url, $status = 302, $headers = [])
    {
        return app('response')->redirect($url, $status, $headers);
    }
}

if(!function_exists('session')) {
    /**
     * session object
     * @return \VM\Http\Session|mixed
     */
    function session($k = null, $v = null)
    {
        if ($k && $v) {
            return app('session')->set($k, $v);
        } else if ($k) {
            return app('session')->get($k);
        } else {
            return app('session');
        }
    }
}

if(!function_exists('cookie')) {
    /**
     * Cookie instance helper
     * @param  string  $name
     * @param  string  $value
     * @return \VM\Http\Cookie|string
     */
    function cookie($name = null, $value = null)
    {
        if($name && $value){
            return app('cookie')->set($name, $value);
        }else if($name){
            return app('cookie')->get($name);
        }else{
            return app('cookie');
        }
    }
}


if(!function_exists('Db')) {
    /**
     * \DB::table function
     * @param $table
     * @return \Illuminate\Database\Query\Builder
     */
    function Db($table)
    {
        return \DB::table($table);
    }
}


if (! function_exists('run')) {
    function run(callable $callable, ...$args) {
     return \Swoole\Coroutine\run($callable, ...$args);
    }
}

if (! function_exists('co')) {
    /**
     * @return false|int
     */
    function co(callable $callable, ...$args)
    {
        return \Swoole\Coroutine::create($callable, ...$args);
    }
}

if (! function_exists('go')) {
    /**
     * @return bool|int
     */
    function go(callable $callable, ...$args)
    {
        return co($callable, ...$args);
    }
}

if (! function_exists('defer')) {
    /**
     * @return void
     */
    function defer(callable $callable): void
    {
        \Swoole\Coroutine::defer($callable);
    }
}

if (! function_exists('WaitGroup')) {
    /**
     * @return \Swoole\Coroutine\WaitGroup
     */
    function WaitGroup($delta = 0)
    {
       return new \Swoole\Coroutine\WaitGroup($delta);
    }
}

if (! function_exists('wg')) {
    /**
     * @return \Swoole\Coroutine\WaitGroup
     */
    function wg($delta = 0) {
        return WaitGroup($delta);
    }   
}

if (! function_exists('channel')) {
    /**
     * @param int $size
     * @return \Swoole\Coroutine\Channel
     */
    function channel($size = 0) {
        return new \Swoole\Coroutine\Channel($size);
    }
}

if (! function_exists('chan')) {
    /**
     * @param int $size
     * @return \Swoole\Coroutine\Channel
     */
    function chan($size = 0) {
        return channel($size);
    }
}

if (! function_exists('coid')) {
    /**
     * @return int
     */
    function coid(): int {
        return \Swoole\Coroutine::getCid();
    }
}

if(!function_exists('javascript')) {
    /**
     * javascript output
     * @param $script string 脚本
     * @param $status int 状态
     * @param headers array 响应头
     */
    function javascript($script, $status = 200, $headers = array())
    {
        return response('<script type="text/javascript">'.$script.'</script>', $status, $headers);
    }
}

if(!function_exists('input')) {
    /**
     * request input
     * @param null $key
     * @param null $default
     * @return array|string
     */
    function input($key = null, $default = '')
    {
        return is_null($key) ? app('request')->all() : app('request')->input($key, $default);
    }
}

if(!function_exists('cache')) {
    /**
     * @param bool $key
     * @param bool $value
     * @return \VM\Cache\Driver\Driver|mixed
     */
    function cache($key = null, $default = null)
    {
        if (is_null($key)) return make('cache');
        $cache = make('cache')->get($key);
        return $default instanceof \Closure ? $default($cache) : ($cache?:$default);
    }
}

if(!function_exists('www')) {
    /**
     * www path
     * @param string ...$paths
     * @return string
     */
    function www(...$paths)
    {
        return root('www', ...$paths);
    }
}

if(!function_exists('redis')) {
    /**
     * redis
     * @param string $server
     * @return \Redis
     */
    function redis($server = 'default')
    {
        return make('cache')->redis($server);
    }
}

if(!function_exists('random')) {
    /**
     * random string
     * @param int $length
     * @param string $codes
     * @return string
     */
    function random($length = 8)
    {
        return substr(str_shuffle("abcdefghijklmnopqrstuvwxyz1234567890"), 0, $length);
    }
}

if(!function_exists('readable_size')) {
    /**
     * 文件大小单位转换
     * @param $bytes
     * @param int $decimals
     * @return string
     */
    function readable_size($bytes, $decimals = 0)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $floor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $floor)) . $units[$floor];
    }
}

if(!function_exists('readable_number')) {
    /**
     * 格式化数字
     * @param int $number 数字
     * @param int $decimals 小数点
     * @return string
     */
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

if(!function_exists('truncate')) {
    /**
     * 截取字符串
     * @param string $string
     * @param int $length
     * @param string $symbol
     * @return string
     */
    function truncate($string, $length = 255, $symbol = '')
    {
        return mb_strimwidth($string, 0, $length, $symbol);
    }
}

if(!function_exists('decimal')){
    /**
     * @param mixed $value 值
     * @param int $decimals 保留位数
     * @return float
     */
    function decimal($value, $decimals = 2)
    {
        //$value = number_format($value, $decimals, '.', '');
        //return strpos($value, '.') ? rtrim(rtrim($value, '0'), '.') : $value;
        return number_format($value, $decimals, '.', '');
    }
}


if(!function_exists('dump')) {
    /**
     * dump mixed
     * @param mixed $args
     * @return void
     */
    function dump(...$args)
    {
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

if(!function_exists('array_keys_exists')) {
    /**
     * 增强array_key_exists 支持多键名检测
     * @param array $keys
     * @param array $array
     * @return bool
     */
    function array_keys_exists(array $keys, array $array)
    {
        return !array_diff_key(array_flip($keys), $array);
    }
}

if (!function_exists('array_include')) {
    /**
     * 提取数组中指定键名的值
     * @param array $array
     * @param array $keys
     * @return array
     */
    function array_include(array $array, ...$keys) 
    {
        return array_intersect_key($array, array_flip(array_flat($keys)));
    }
}

if (!function_exists('array_exclude')) {
    /**
     * 提取排除数组中指定键名的值
     * @param array $array
     * @param array $keys
     * @return array
     */
    function array_exclude(array $array, ...$keys) 
    {
        return array_diff_key($array, array_flip(array_flat($keys)));
    }
}

if (!function_exists('array_dot')) {
    /**
     * Flatten a multi-dimensional associative array with dots.
     * @param  array   $array
     * @param  string  $prepend
     * @return array
     */
    function array_dot($array, $prepend = '', $trim = null)
    {
        $arr = [];
        foreach ($array as $key => $value) {
            if (is_array($value) && ! empty($value)) {
                $arr = array_merge($arr, array_dot($value, $prepend. ($trim ? trim($key, $trim) : $key).'.'));
            } else {
                $arr[$prepend.$key] = $value;
            }
        }
        return $arr;
    }
}

if (!function_exists('array_undot')) {
    /**
     * array Undot
     * @param $dotNotationArray
     * @return array
     */
    function array_undot($dotNotationArray)
    {
        $array = [];
        foreach ($dotNotationArray as $key => $value) {
            data_set($array, $key, $value);
        }
        return $array;
    }
}

if (!function_exists('array_flat')) {
    /**
     * array_flat
     * @param array $array
     * @return array
     */
    function array_flat(array $array) {
        return iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator($array)), false);
    }
}

if(!function_exists('is_str')) {   
    /**  
     * 验证字符串是否为数字,字母,中文和下划线构成  
     * @param string $username  
     * @return bool  
     */
    function is_str(string $string)
    {
        return ctype_alnum(str_replace('_', '', $string));
    }
}

if(!function_exists('is_json')){
    /**  
     * 验证字符串是否为json格式  
     * @param string $string  
     * @return bool  
     */
    function is_json($string)
    {
        return !empty($string) && is_string($string) && json_decode($string);
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
        return filter_var($email, FILTER_VALIDATE_EMAIL);
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
     return filter_var($url, FILTER_VALIDATE_URL);
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
        return ip2long($ip);
    }
}

if(!function_exists('is_luhn')){
    /**
     * 是否为一个合法的银行账号
     * LUHN算法
     * @param int $number
     * @return boolean
     */
    function is_luhn($number)
    {
        if(!is_numeric($number)) return false;
		$number_checksum = '';
		foreach (str_split(strrev((string) $number)) as $i => $d) {
			$number_checksum .= $i %2 !== 0 ? $d * 2 : $d;
		}
		return array_sum(str_split($number_checksum)) % 10 === 0;
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
        return ctype_alpha($str);
    }
}

if(!function_exists('is_chinese')) { 
    /**  
     * 是否为中文  
     * @param string $string  
     * @return boolean  
     */
    function is_chinese(string $string)
    {
        return preg_match("/\p{Han}+/u", $string);
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
        return is_file($file) && getimagesize($file);
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
        return preg_match('/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$/', $card) || preg_match('/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{4}$/', $card);
    }
}

if(!function_exists('is_date')) { 
    /**  
     * 验证日期格式是否正确  
     * @param string $date  
     * @param string $format  
     * @return boolean  
     */
    function is_date($date, $format = 'Y-m-d') {
        return !date_parse_from_format($format, $date)['errors'];
    }   
}

if(!function_exists('is_safe')) { 
    /**  
     * 验证安全输入  
     * @param string $input  
     * @param mixed $callback  
     * @return string
     */
    function is_safe($input, ...$filters) 
    {   
        array_map(function($f) use(&$input){
            $input = $f($input);
        }, $filters);
        return trim($input);
    }
}

if(!function_exists('symbol')) {
    /**
    * Get currency symbol
    * @param  string $currency
    * @return string 
    */
    function symbol($currency, $locale = 'en_US') 
    {
        return (new NumberFormatter($locale."@currency=$currency", NumberFormatter::CURRENCY ))->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
    }
}

if(!function_exists('unicode2char')){
    /**
     * 将unicode转换成字符
     * @param int $unicode
     * @return string UTF-8字符
     **/
    function unicode2char($unicode){
        if($unicode < 128)     return chr($unicode);
        if($unicode < 2048)    return chr(($unicode >> 6) + 192) .
                                      chr(($unicode & 63) + 128);
        if($unicode < 65536)   return chr(($unicode >> 12) + 224) .
                                      chr((($unicode >> 6) & 63) + 128) .
                                      chr(($unicode & 63) + 128);
        if($unicode < 2097152) return chr(($unicode >> 18) + 240) .
                                      chr((($unicode >> 12) & 63) + 128) .
                                      chr((($unicode >> 6) & 63) + 128) .
                                      chr(($unicode & 63) + 128);
        return false;
    }
}

if(!function_exists('char2unicode')){
    /**
     * 将字符转换成unicode
     * @param string $char 必须是UTF-8字符
     * @return int
     **/
    function char2unicode($char){
        switch (strlen($char)){
            case 1 : return ord($char);
            case 2 : return (ord($char[1]) & 63) |
                            ((ord($char[0]) & 31) << 6);
            case 3 : return (ord($char[2]) & 63) |
                            ((ord($char[1]) & 63) << 6) |
                            ((ord($char[0]) & 15) << 12);
            case 4 : return (ord($char[3]) & 63) |
                            ((ord($char[2]) & 63) << 6) |
                            ((ord($char[1]) & 63) << 12) |
                            ((ord($char[0]) & 7)  << 18);
            default :
                trigger_error('Character is not UTF-8!', E_USER_WARNING);
                return false;
        }
    }
}