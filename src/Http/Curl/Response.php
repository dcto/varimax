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
 * Parses the response from a Curl request into an object containing
 * the response body and an associative array of headers
 **/

class Response implements \ArrayAccess
{

    /**
     * current url
     * @var string
     */
    public $url;

    /**
     * http status code
     *
     * @var int
     */
    public $code = 200;

    /**
     * http content type
     *
     * @var string
     */
    public $type;

    /**
     * An associative array containing the response's headers
     *
     * @var array
     **/
    public $head = array();

    /**
     * curl info
     *
     * @var array
     */
    public $info = array();

    /**
     * The body of the response without the headers block
     *
     * @var string
     **/
    public $body;

    /**
     * json data when response of json context
     */
    public $data;

    /**
     * Accepts the result of a curl request as a string
     *
     * <code>
     * $response = new Response(curl_exec($curl_handle));
     * echo $response->body;
     * echo $response->headers['Status'];
     * </code>
     *
     * @param string $response
     **/
    public function __construct(array $info,  $context)
    {
        $this->url = $info['url'];
        $this->code = $info['http_code'];
        $this->type = $info['content_type'];
        $this->info = $info;
        $this->context($context);
    }

    public function url()
    {
        return $this->url;
    }

    public function code()
    {
        return $this->code;
    }

    public function type()
    {
        return $this->type;
    }

    public function info($item = null)
    {
        return $item ? isset($this->info[$item]) && $this->info[$item] : $this->info;
    }

    public function head()
    {
        return $this->head;
    }


    public function body()
    {
        return $this->body;
    }


    /**
     * get head and body
     * @param $context
     */
    private function context($context)
    {
        $this->head = substr($context, 0, $this->info['header_size']);

        $this->body = substr($context, $this->info['header_size']);

        $this->data = json_decode($this->body, true, 512);
    }

    /**
     * Was an 'info' header returned.
     * @return bool
     */
    public function isInfo()
    {
        return $this->code >= 100 && $this->code < 200;
    }
    /**
     * Was an 'OK' response returned.
     * @return bool
     */
    public function ok()
    {
        return $this->code >= 200 && $this->code < 300;
    }
    /**
     * Was a 'redirect' returned.
     * @return bool
     */
    public function redirect()
    {
        return $this->code >= 300 && $this->code < 400;
    }
    /**
     * Was an 'error' returned (client error or server error).
     * @param string $type = server|client
     * @return bool
     */
    public function error($type = null)
    {
        if($type == 'server') {
            return $this->code >= 500 && $this->code < 600;
        }else if ($type == 'client'){
            return $this->code >= 400 && $this->code < 500;
        }else{
            return $this->code >= 400 && $this->code < 600;
        }
    }

    public function isJson()
    {
        return $this->data && is_array($this->data);
    }

    public function toArray()
    {
        return $this->data;
    }

    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }
    
    public function __set($name, $value)
    {
        return $this->data[$name] = $value;
    }

    public function __isset ($key) {
        return isset($this->data[$key]);
    }

    public function __unset($key) {
        unset($this->data[$key]);
    }

    public function offsetSet($offset,$value) {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }
    
    public function offsetUnset($offset) {
        if ($this->offsetExists($offset)) {
            unset($this->data[$offset]);
        }
    }
    public function offsetGet($offset) {
        return $this->offsetExists($offset) ? $this->data[$offset] : null;
    }

    /**
     * Returns the response body
     *
     * <code>
     * $curl = new Curl;
     * $response = $curl->get('google.com');
     * echo $response;  # => echo $response->body;
     * </code>
     *
     * @return string
     **/
    public function __toString() {
        return (string) $this->body;
    }

}
