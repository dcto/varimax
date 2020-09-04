<?php

namespace VM\Http;


use VM\Http\Response\Base;
use VM\Http\Response\Json;
use VM\Http\Response\Redirect;
use VM\Http\Response\Streamed;
use VM\Http\Response\BinaryFile;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Class Response
 * @package VM\Http
 */
class Response
{


    /**
     * [error Response]
     * @param $status [错误状态]
     * @param null $message [错误信息]
     * @return Base
     */
    public function error($status, $message = null)
    {
        $message = $message ?: Base::$statusTexts[$status];

        return $this->make($message, $status);
    }


    /**
     * [error Response]
     * @param $status [错误状态]
     * @param null $message [错误信息]
     * @return Base
     */
    public function abort($status = 200, $message = null)
    {
        die($this->error($status, $message)->send());
    }


    /**
     * [make Response]
     *
     * @param string $content [响应x内容]
     * @param int    $status [状态值]
     * @param array  $headers [header]
     * @author 11.
     * @return Base
     */
    public function make($content = '', $status = 200 , array $headers = [])
    {
        if(!is_string($content)){
            $content = print_r($content, true);
        }
        return new Base($content, $status, $headers);
    }


    /**
     * [show make别名]
     *
     * @param string $content
     * @param int    $status
     * @param array  $headers
     * @author 11.
     */
    public function show($content = '', $status = 200 , array $headers = [])
    {
       return $this->make($content, $status, $headers);
    }


    /**
     * [view 视图响应]
     *
     * @param array $data [传入值]
     * @param int   $status [状在值]
     * @param array $headers [header]
     * @author 11.
     */
    public function view($view, $data = [], $status = 200, array $headers = [])
    {
       return $this->make(make('view')->render($view, $data), $status, $headers);
    }

    /**
     * [json Json格式响应]
     *
     * @param array $data [传入值]
     * @param int   $status [状态值]
     * @param array $headers [header]
     * @param int   $options [其他设置]
     * @return Json
     * @author 11.
     */
    public function json($data = [], $status = 200, array $headers = [], $options = 0)
    {
        if ($data instanceof Arrayable && ! $data instanceof \JsonSerializable) {
            $data = $data->toArray();
        }
        return new Json($data, $status, $headers, $options);
    }


    /**
     * [jsonp Jsonp响应格式]
     *
     * @param array $data [传入值]
     * @param int   $status [状态值]
     * @param array $headers [header]
     * @param int   $options [其他设置]
     * @return Json
     * @author 11.
     */
    public function jsonp($callback, $data = [], $status = 200, array $headers = [], $options = 0)
    {
        return $this->json($data, $status, $headers, $options)->setCallback($callback);
    }

    /**
     * [url 跳转]
     *
     * @param       $url [地址]
     * @param int   $status [状态值]
     * @param array $headers [header]
     * @return Redirect|string
     * @author 11.
     */
    public function url($url, $status = 302, array $headers = [])
    {
        if(!filter_var($url, FILTER_VALIDATE_URL)){
            return $this->abort('Invalid URL: '. $url, 404);
        }
        return $this->redirect($url, $status, $headers);
    }

    /**
     * [route 跳转到路由器]
     *
     * @param       $tag [路由地址名]
     * @param int   $status [状态值]
     * @param array $headers [header]
     * @return Redirect
     * @author 11.
     */
    public function route($tag, $status = 302, array $headers = [])
    {
        $url = make('router')->router($tag)->route;

        return $this->redirect($url, $status, $headers);
    }


    /**
     * [stream 数据流响应]
     *
     * @param \Closure $callback [回调]
     * @param int      $status [状态值]
     * @param array    $headers [header]
     * @return Streamed
     * @author 11.
     */
    public function stream($callback, $status = 200, array $headers = [])
    {
        return new Streamed($callback, $status, $headers);
    }


    /**
     * [download 响应下载]
     *
     * @param \SplFileInfo|string $file [文件地址]
     * @param null                $name [文件名]
     * @param array               $headers header
     * @param string              $disposition
     * @return BinaryFile
     * @author 11.
     */
    public function download($file, $name = null, array $headers = [], $disposition = 'attachment')
    {
       $response = new BinaryFile($file, 200, $headers, true, $disposition);
        if (! is_null($name)) {
             $response->setContentDisposition($disposition, $name, str_replace('%', '', \Illuminate\Support\Str::ascii($name)));
        }
        return $response;
    }


    /**
     * [redirect 跳转]
     *
     * @param       $url [地址]
     * @param int   $status [状态值]
     * @param array $headers [header]
     * @return Redirect
     * @author 11.
     */
    public function redirect($url, $status = 302, $headers = [])
    {
        return new Redirect($url, $status, $headers);
    }

}
