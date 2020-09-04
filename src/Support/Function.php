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
    function log()
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
 * Gets the value of an environment variable. Supports boolean, empty and null.
 *
 * @param  string  $key
 * @param  mixed   $default
 * @return mixed
 */
if (! function_exists('env')) {

    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return value($default);
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'empty':
            case '(empty)':
                return '';

            case 'null':
            case '(null)':
                return;
        }
        return $value;
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
    function cache($key = false, $value = false, $time = 86400)
    {
        if ($key && $value) {
            return make('cache')->set($key, $value, $time);
        } else if ($key) {
            return make('cache')->get($key);
        }
        return make('caches');
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
if(!function_exists('cut_str')) {
    function cut_str($string, $length = 255, $symbol = '...')
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
}