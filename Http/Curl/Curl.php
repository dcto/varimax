<?php
/**
 * Varimax The Full Stack PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 22:03
 * SITE: https://www.varimax.cn/
 */


namespace VM\Http\Curl;

/**
 * An object-oriented wrapper of the PHP cURL extension.
 *
 * This library requires to have the php cURL extensions installed:
 * https://php.net/manual/curl.setup.php
 *
 * Example of making a get request with parameters:
 *
 * ```php
 * $curl = new Curl\Curl();
 * $curl->get('http://www.example.com/search', array(
 *     'q' => 'keyword',
 * ));
 * ```
 *
 * Example post request with post data:
 *
 * ```php
 * $curl = new Curl\Curl();
 * $curl->post('http://www.example.com/login/', array(
 *     'username' => 'root',
 *     'password' => 'root',
 * ));
 * ```
 *
 * @see https://php.net/manual/curl.setup.php
 */
class Curl
{

    /**
     * @var resource Contains the curl resource created by `curl_init()` function.
     */
    private $curl;

    /**
     * request status
     * @var int
     */
    private $status = 401;

    /**
     * header
     * @var array
     */
    private $headers = array();

    /**
     * cookie
     * @var array
     */
    private $cookies = array();

    /**
     * 选项配置
     * @var array
     */
    private $options = array();

    /**
     * 当前请求地址及参数
     * @var
     */
    private $request;

    /**
     * 超时时间
     * @var int
     */
    private $timeout = 30;

    /**
     * 重试次数
     * @var int
     */
    private $retries = 1;


    /**
     * 跳转次数
     * @var int
     */
    private $tracers = array();

    /**
     * @var string
     */
    private $userAgent = 'VM HTTP Request /1.1';

    /**
     * Constructor ensures the available curl extension is loaded.
     * @throws \ErrorException
     */
    public function __construct()
    {
        if (!extension_loaded('curl')) {
            throw new \ErrorException('The cURL extensions is not loaded, make sure you have installed the cURL extension: https://php.net/manual/curl.setup.php');
        }
        $this->options[CURLOPT_HEADER] = 1;
        $this->options[CURLOPT_USERAGENT] = $this->userAgent;
        $this->options[CURLOPT_ENCODING] = '';
        $this->options[CURLOPT_TIMEOUT] = $this->timeout + 5; //超时时间
        $this->options[CURLOPT_CONNECTTIMEOUT] = $this->timeout; //连接超时
        $this->options[CURLOPT_IPRESOLVE] = 1;
        $this->options[CURLOPT_FAILONERROR] = 1; //当 HTTP 状态码大于等于 400，TRUE 将将显示错误详情。 默认情况下将返回页面，忽略 HTTP 代码。
        $this->options[CURLOPT_RETURNTRANSFER] = 1; //返回结果
        $this->options[CURLOPT_FOLLOWLOCATION] = 1; //跟随重定向 这是递归的,可以使用参数CURLOPT_MAXREDIRS设置重定向次数
        $this->options[CURLOPT_MAXREDIRS] = 5; //重定向次数

        $this->options[CURLOPT_SSL_VERIFYPEER] = false; //略过验证平等证书
        $this->options[CURLOPT_SSL_VERIFYHOST] = false; //略过验证平等证书

    }

    /**
     * Initializer for the curl resource.
     *
     * Is called by the __construct() of the class or when the curl request is reseted.
     */
    public function curl()
    {
        if(!is_resource($this->curl)){
            $this->curl = curl_init();
        }
        return $this->curl;
    }


    /**
     * 设定端口
     * @param int $port
     * @return $this
     */
    public function port($port = 80)
    {
        $this->options(CURLOPT_PORT, $port);
        return $this;
    }

    /**
     * @param $url
     * @param $data
     * @return $this
     * @throws \Exception
     */
    public function get($url, $data = null)
    {
        $this->options(CURLOPT_HTTPGET, true);

        if($data){
            $this->options(CURLOPT_URL, $this->request = $url.'?'.$this->queryString($data));
        }else{
            $this->options(CURLOPT_URL, $this->request = $url);
        }

        return $this;
    }

    /**
     * @param $url
     * @param $data
     * @return $this
     * @throws \Exception
     */
    public function post($url, $data = null)
    {
        $this->options(CURLOPT_URL, $this->request = $url);

        $this->payload($data);

        return $this;
    }

    /**
     * @param $url
     * @param $data
     * @param bool $payload
     * @return $this;
     */
    public function put($url, $data = array(), $payload = false)
    {
        if($data){
            if(!$payload){
                $url = $url.'?'.$this->queryString($data);
            }else{
                $this->payload($data);
            }
        }
        $this->options(CURLOPT_URL, $this->request = $url);
        $this->options(CURLOPT_CUSTOMREQUEST, 'PUT');
        return $this;
    }

    /**
     * @param $url
     * @param $data
     * @param bool $payload
     */
    public function patch($url, $data = array(), $payload = false)
    {
        if($data){
            if(!$payload){
                $url = $url.'?'.$this->queryString($data);
            }else{
                $this->payload($data);
            }
        }
        $this->options(CURLOPT_URL, $this->request = $url);
        $this->options(CURLOPT_CUSTOMREQUEST, 'PATCH');
        return $this;
    }

    /**
     * @param $url
     * @param $data
     * @param bool $payload
     * @return $this
     */
    public function delete($url, $data = array(), $payload = false)
    {
        if($data){
            if(!$payload){
                $url = $url.'?'.$this->queryString($data);
            }else{
                $this->payload($data);
            }
        }
        $this->options(CURLOPT_URL, $this->request = $url);
        $this->options(CURLOPT_CUSTOMREQUEST, 'DELETE');
        return $this;
    }

    /**
     * set time out
     * @param $time
     * @return $this
     */
    public function timeout($time)
    {
        $this->timeout = $time;

        $this->options(CURLOPT_TIMEOUT, $time);

        return $this;
    }


    /**
     * Set customized curl options.
     *
     * To see a full list of options: http://php.net/curl_setopt
     *
     * @see http://php.net/curl_setopt
     * @param integer|array $key The curl option constant e.g. `CURLOPT_AUTOREFERER`, `CURLOPT_COOKIESESSION`
     * @param mixed $var The value to pass for the given $option.
     *
     * @return $this
     */
    public function options($key, $var = null)
    {
        if(is_array($key)){
            $this->options = array_merge($this->options, $key);
        }else{
            $this->options[$key] = $var;
        }
        return $this;
    }

    /**
     * Provide optional header informations.
     *
     * In order to pass optional headers by key value pairing:
     *
     * ```php
     * $curl = new Curl();
     * $curl->headers('X-Requested-With', 'XMLHttpRequest');
     * $curl->headers(['X-Requested-With: XMLHttpRequest');
     * $curl->get('http://example.com/request.php');
     * ```
     *
     * @param string $key The header key.
     * @param string $var The value for the given header key.
     */
    public function headers($key, $var = null)
    {
        if(is_array($key)){
            foreach($key as $k => $v){
                $this->headers = $k.': '.$v;
            }

        }else{
            $this->headers[] = $key.': '.$var;
        }
        $this->options(CURLOPT_HTTPHEADER, $this->headers);
        return $this;
    }

    /**
     * Set contents of HTTP Cookie header.
     *
     * @param string $key The name of the cookie.
     * @param string $value The value for the provided cookie name.
     */
    public function cookies($key, $var)
    {
        $this->cookies[$key] = $var;
        $this->options(CURLOPT_COOKIE, http_build_query($this->cookies, '', '; '));
        return $this;
    }

    /**
     * Set the HTTP referer header.
     *
     * The $referer informations can help identify the requested client where the requested was made.
     *
     * @param string $referer An url to pass and will be set as referer header.
     */
    public function referer($referer)
    {
        $this->options(CURLOPT_REFERER, $referer);
        return $this;
    }

    /**
     * Provide a User Agent.
     *
     * In order to provide you cusomtized user agent name you can use this method.
     *
     * ```php
     * $curl = new Curl();
     * $curl->userAgent('My John Doe Agent 1.0');
     * $curl->get('http://example.com/request.php');
     * ```
     *
     * @param string $userAgent The name of the user agent to set for the current request.
     */
    public function userAgent($userAgent)
    {
        $this->options(CURLOPT_USERAGENT, $userAgent);
        return $this;
    }

    /**
     * Enable verbositiy.
     *
     * @param string $on
     */
    public function verbose($on = true)
    {
        $this->options(CURLOPT_VERBOSE, $on);
        return $this;
    }

    /**
     * 出错重试次数
     * @param int $times
     * @return $this
     */
    public function retry($times = 1)
    {
        $this->retries = $times;
        return $this;
    }



    /**
     * check php run at safe model
     *
     * @return bool
     */
    protected function _safeMode()
    {
        return ini_get('safe_mode') || ini_get('open_basedir');
    }

    /**
     * @param $curl
     * @param $header
     * @return int
     */
    protected function _headerCallback($curl, &$header)
    {
        $len = strlen($header);
        $header = explode(':', $header, 2);
        if (count($header) < 2) return $len;
        $headers[strtolower(trim($header[0]))] = trim($header[1]);
        return $len;
    }


    /**
     * Build QueryString
     * @param $data
     * @return string
     */
    protected function queryString($data)
    {
        if(is_array($data) || is_object($data)){
            $data = http_build_query($data);
            //$this->headers('Content-Length', strlen($data));
        }
        return $data;
    }

    /**
     * @param array|object|string $data
     */
    protected function payload($data)
    {
        $this->options(CURLOPT_POST, true);
        if (is_array($data) || is_object($data)) {
            $skip = false;
            foreach ($data as $key => $value) {
                // If a value is an instance of CurlFile skip the http_build_query
                // see issue https://github.com/php-mod/curl/issues/46
                // suggestion from: https://stackoverflow.com/a/36603038/4611030
                if ($value instanceof \CurlFile) {
                    $skip = true;
                }
            }

            if (!$skip) {
                $data = http_build_query($data);
            }
        }
        $this->headers('Content-Length', strlen($data));
        $this->options(CURLOPT_POSTFIELDS, $data);

        return $this;
    }

    /**
     * Makes an HTTP request of the specified $method to a $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @return Response
     * @throws \Exception|\InvalidArgumentException
     */
    public function send() {

        //判断安全模式运行CURLOPT_FOLLOWLOCATION无效
        $this->_safeMode() &&  $this->options(CURLOPT_FOLLOWLOCATION, false);
        curl_setopt_array($this->curl(), $this->options);
        $response = null;
        //while((!$this->status || $this->isError()) && ( -- $this->retries >= 0)){
             $response = $this->curl_redirect_exec();
        //}
        $this->close();

        return $response;
    }

    /**
     * @param $ch
     * @param $redirects
     * @param bool $curlopt_header
     * @return Response
     * Recursive cURL with redirect and open_basedir
     * @see http://stackoverflow.com/questions/3890631/php-curl-with-curlopt-followlocation-error
     */
    public function curl_redirect_exec()
    {
        $context = curl_exec($this->curl());
        $info = curl_getinfo($this->curl());
        $this->status = $info['http_code'];
        if($context === false){
            $context = curl_error($this->curl());
        }

        if ($this->isRedirect()) {
            if($url = filter_var($info['redirect_url'], FILTER_VALIDATE_URL)){
                $this->tracers[] = $url;
                curl_setopt($this->curl(), CURLOPT_URL, $url);
                return self::curl_redirect_exec();
            }
        }
        return new Response($info, $context);
    }

    /**
     * debug curl
     * @return mixed
     */
    public function debug()
    {
        echo "<pre>";

        print_r($this);

        die;
    }

    /**
     * Closing the current open curl resource.
     */
    public function close()
    {
        if(is_resource($this->curl)){
            curl_close($this->curl);
        }
    }

    /**
     * Was an 'info' header returned.
     * @return bool
     */
    public function isInfo()
    {
        return $this->status >= 100 && $this->status < 200;
    }
    /**
     * Was an 'OK' response returned.
     * @return bool
     */
    public function isSuccess()
    {
        return $this->status >= 200 && $this->status < 300;
    }
    /**
     * Was a 'redirect' returned.
     * @return bool
     */
    public function isRedirect()
    {
        return $this->status >= 300 && $this->status < 400;
    }
    /**
     * Was an 'error' returned (client error or server error).
     * @return bool
     */
    public function isError()
    {
        return $this->status >= 400 && $this->status < 600;
    }
    /**
     * Was a 'client error' returned.
     * @return bool
     */
    public function isClientError()
    {
        return $this->status >= 400 && $this->status < 500;
    }
    /**
     * Was a 'server error' returned.
     * @return bool
     */
    public function isServerError()
    {
        return $this->status >= 500 && $this->status < 600;
    }
}