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
        //return call_user_func_array(array(app('request'), 'url'), func_get_args());
    }
}

if(!function_exists('uri')) {
    /**
     * request uri
     * @return string
     */
    function uri(...$args)
    {
        return app('request')->uri(...$args);
        //return call_user_func_array(array(app('request'), 'uri'), func_get_args());
    }
}

if(!function_exists('lang')) {
    /**
     * lang instace helper
     * @return \VM\I18n\Lang|string
     */
    function lang(...$args)
    {
        return !$args ? app('lang') : app('lang')->get(...$args);
        //return call_user_func_array(array(app('lang'), 'get'), func_get_args());
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
        return $paths ? _DOC_._DS_.join(_DS_, array_map(function($arg){
            return trim($arg, _DS_);
        }, $paths)) : _DOC_;
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
     * @return \VM\Http\Session
     */
    function session($k = false, $v = false)
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

if(!function_exists('domain')){
/**
 * Get domain with subdomain or null
 * @param mixed $host 
 * @return array|string|int|null|false 
 */
function domain($host = null, $subDomain = true){
    $host = $host ? trim($host, ' /') : $_SERVER['SERVER_NAME'];
    $host = filter_var($host, FILTER_VALIDATE_URL) ? parse_url($host, PHP_URL_HOST) : $host;

    if($subDomain || substr_count($host, '.') < 2 || filter_var($host, FILTER_VALIDATE_IP)){
        return $host;
    }else{
        $ltd = strlen(pathinfo($host, PATHINFO_EXTENSION));
        $host = explode('.', $host);
        $domain = array();
        array_unshift($domain, array_pop($host));
        $ltd == 2 && array_unshift($domain, array_pop($host));
        array_unshift($domain, array_pop($host));
        return implode('.', $domain);
    }
}
}

if(!function_exists('DB')) {
    /**
     * \DB::table function
     * @param $table
     * @return \Illuminate\Database\Query\Builder
     */
    function DB($table)
    {
        return \DB::table($table);
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
     * @return \VM\Cache\Driver\Driver
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
        return substr($paths[0],0, 1) == '/' ? ltrim(join('/', $paths),'/') : _APP_.'/'.join('/', $paths);
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
    function random($length = 8, $codes = null)
    {
        $codes = $codes ? (is_array($codes) ? $codes : str_split($codes)) : array_merge(array_merge(range('A', 'Z'),range('a','z'),range(0, 9)));
        shuffle($codes);
        return implode(array_slice($codes, 0, $length));
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

if(!function_exists('is_str')) {   
    /**  
     * 验证字符串是否为数字,字母,中文和下划线构成  
     * @param string $username  
     * @return bool  
     */
    function is_str($str)
    {
        return preg_match('/^[\x{4e00}-\x{9fa5}\w_]+$/u', $str);
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
        return !empty($string) && is_string($string) && is_array(json_decode($string, true)) && json_last_error() == 0;
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

if(!function_exists('is_intval')) {  
    /**  
     * 是否为整数  
     * @param int $number  
     * @return boolean  
     */ 
    function is_intval($number)
    {
      return preg_match('/^[-\+]?\d+$/', $number);
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
        return ctype_digit($number);
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
        return preg_match('/^[-\+]?\d+(\.\d+)?$/', $number);
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
       return preg_match('/^\d+(\.\d+)?$/', $number);
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
     * @param string $str  
     * @return boolean  
     */
    function is_chinese($str)
    {
        return preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $str);
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
    function is_date($date, $format = 'Y-m-d')
    {
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

if(!function_exists('symbol')){
    /**
    * 货币符号
    * @param  string $currency
    * @return string 
    */
    function symbol($currency)
        {
            $symbols = array(
                'AED' => '&#1583;.&#1573;', 
                'AFN' => '&#65;&#102;',
                'ALL' => '&#76;&#101;&#107;',
                'AMD' => '',
                'ANG' => '&#402;',
                'AOA' => '&#75;&#122;', 
                'ARS' => '&#36;',
                'AUD' => '&#36;',
                'AWG' => '&#402;',
                'AZN' => '&#1084;&#1072;&#1085;',
                'BAM' => '&#75;&#77;',
                'BBD' => '&#36;',
                'BDT' => '&#2547;', 
                'BGN' => '&#1083;&#1074;',
                'BHD' => '.&#1583;.&#1576;', 
                'BIF' => '&#70;&#66;&#117;', 
                'BMD' => '&#36;',
                'BND' => '&#36;',
                'BOB' => '&#36;&#98;',
                'BRL' => '&#82;&#36;',
                'BSD' => '&#36;',
                'BTN' => '&#78;&#117;&#46;', 
                'BWP' => '&#80;',
                'BYR' => '&#112;&#46;',
                'BZD' => '&#66;&#90;&#36;',
                'CAD' => '&#36;',
                'CDF' => '&#70;&#67;',
                'CHF' => '&#67;&#72;&#70;',
                'CLF' => '', 
                'CLP' => '&#36;',
                'CNY' => '&#165;',
                'COP' => '&#36;',
                'CRC' => '&#8353;',
                'CUP' => '&#8396;',
                'CVE' => '&#36;', 
                'CZK' => '&#75;&#269;',
                'DJF' => '&#70;&#100;&#106;', 
                'DKK' => '&#107;&#114;',
                'DOP' => '&#82;&#68;&#36;',
                'DZD' => '&#1583;&#1580;', 
                'EGP' => '&#163;',
                'ETB' => '&#66;&#114;',
                'EUR' => '&#8364;',
                'FJD' => '&#36;',
                'FKP' => '&#163;',
                'GBP' => '&#163;',
                'GEL' => '&#4314;', 
                'GHS' => '&#162;',
                'GIP' => '&#163;',
                'GMD' => '&#68;', 
                'GNF' => '&#70;&#71;', 
                'GTQ' => '&#81;',
                'GYD' => '&#36;',
                'HKD' => '&#36;',
                'HNL' => '&#76;',
                'HRK' => '&#107;&#110;',
                'HTG' => '&#71;', 
                'HUF' => '&#70;&#116;',
                'IDR' => '&#82;&#112;',
                'ILS' => '&#8362;',
                'INR' => '&#8377;',
                'IQD' => '&#1593;.&#1583;', 
                'IRR' => '&#65020;',
                'ISK' => '&#107;&#114;',
                'JEP' => '&#163;',
                'JMD' => '&#74;&#36;',
                'JOD' => '&#74;&#68;', 
                'JPY' => '&#165;',
                'KES' => '&#75;&#83;&#104;', 
                'KGS' => '&#1083;&#1074;',
                'KHR' => '&#6107;',
                'KMF' => '&#67;&#70;', 
                'KPW' => '&#8361;',
                'KRW' => '&#8361;',
                'KWD' => '&#1583;.&#1603;', 
                'KYD' => '&#36;',
                'KZT' => '&#1083;&#1074;',
                'LAK' => '&#8365;',
                'LBP' => '&#163;',
                'LKR' => '&#8360;',
                'LRD' => '&#36;',
                'LSL' => '&#76;', 
                'LTL' => '&#76;&#116;',
                'LVL' => '&#76;&#115;',
                'LYD' => '&#1604;.&#1583;', 
                'MAD' => '&#1583;.&#1605;.', //?
                'MDL' => '&#76;',
                'MGA' => '&#65;&#114;', 
                'MKD' => '&#1076;&#1077;&#1085;',
                'MMK' => '&#75;',
                'MNT' => '&#8366;',
                'MOP' => '&#77;&#79;&#80;&#36;', 
                'MRO' => '&#85;&#77;', 
                'MUR' => '&#8360;', 
                'MVR' => '.&#1923;', 
                'MWK' => '&#77;&#75;',
                'MXN' => '&#36;',
                'MYR' => '&#82;&#77;',
                'MZN' => '&#77;&#84;',
                'NAD' => '&#36;',
                'NGN' => '&#8358;',
                'NIO' => '&#67;&#36;',
                'NOK' => '&#107;&#114;',
                'NPR' => '&#8360;',
                'NZD' => '&#36;',
                'OMR' => '&#65020;',
                'PAB' => '&#66;&#47;&#46;',
                'PEN' => '&#83;&#47;&#46;',
                'PGK' => '&#75;', 
                'PHP' => '&#8369;',
                'PKR' => '&#8360;',
                'PLN' => '&#122;&#322;',
                'PYG' => '&#71;&#115;',
                'QAR' => '&#65020;',
                'RON' => '&#108;&#101;&#105;',
                'RSD' => '&#1044;&#1080;&#1085;&#46;',
                'RUB' => '&#1088;&#1091;&#1073;',
                'RWF' => '&#1585;.&#1587;',
                'SAR' => '&#65020;',
                'SBD' => '&#36;',
                'SCR' => '&#8360;',
                'SDG' => '&#163;', 
                'SEK' => '&#107;&#114;',
                'SGD' => '&#36;',
                'SHP' => '&#163;',
                'SLL' => '&#76;&#101;', 
                'SOS' => '&#83;',
                'SRD' => '&#36;',
                'STD' => '&#68;&#98;', 
                'SVC' => '&#36;',
                'SYP' => '&#163;',
                'SZL' => '&#76;', 
                'THB' => '&#3647;',
                'TJS' => '&#84;&#74;&#83;',
                'TMT' => '&#109;',
                'TND' => '&#1583;.&#1578;',
                'TOP' => '&#84;&#36;',
                'TRY' => '&#8356;', // New Turkey Lira (old symbol used)
                'TTD' => '&#36;',
                'TWD' => '&#78;&#84;&#36;',
                'TZS' => '',
                'UAH' => '&#8372;',
                'UGX' => '&#85;&#83;&#104;',
                'USD' => '&#36;',
                'UYU' => '&#36;&#85;',
                'UZS' => '&#1083;&#1074;',
                'VEF' => '&#66;&#115;',
                'VND' => '&#8363;',
                'VUV' => '&#86;&#84;',
                'WST' => '&#87;&#83;&#36;',
                'XAF' => '&#70;&#67;&#70;&#65;',
                'XCD' => '&#36;',
                'XDR' => '',
                'XOF' => '',
                'XPF' => '&#70;',
                'YER' => '&#65020;',
                'ZAR' => '&#82;',
                'ZMK' => '&#90;&#75;', 
                'ZWL' => '&#90;&#36;',
            );
            return isset($symbols[$currency]) ? $symbols[$currency] : $currency;
        }
}