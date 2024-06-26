<?php

/**
 * Varimax The Slim PHP Frameworks.
 *
 * Github: https://github.com/dcto/varimax
 */

namespace VM\Exception;


class E {

    /**
     * Debug Level
     *
     * @var integer
     */
    static private $debug = 0;

    /**
     * 备用内存大小
     * @var int
     */
    static private $memory = 262144;

    /**
     * 注册异常拦截
     */
    static public function register()
    {
        if(static::$debug = getenv('DEBUG')){
            //错误级别
            error_reporting(E_ALL);
            //开启错误
            ini_set('display_errors', 'On');
        }

        //预留内存
        static::$memory && str_repeat('*', static::$memory);

        //截获各种错误
        set_error_handler(array(__CLASS__,'onError'));

        //截获未捕获的异常
        set_exception_handler(array(__CLASS__,'onException'));

        //截获致命性错误
        register_shutdown_function([__CLASS__,'onShutdown']);
    }

    /**
     * 注销异常拦截
     */
    static public function restore()
    {
        restore_error_handler();
        restore_exception_handler();
    }


    /**
     * 处理截获的未捕获的异常
     * @param $e \Exception
     */
    static public function onException($e)
    {
        error_log($e, 4);
        static::$debug || static::logException($e);
    }

    /**
     * 捕获常规错误
     *
     * @param $code
     * @param $message
     * @param $file
     * @param $line
     */
    static public function onError($code, $message, $file, $line)
    {
       static::$memory = null;
       static::onException(new \ErrorException($message, $code, 1, $file, $line));
        
    }

    /**
     * 截获致命性错误
     */
    static public function onShutdown()
    {
        //释放备用内存供下面处理程序使用
       static::$memory = null;

        //最后一条错误信息
        if(is_null($e = error_get_last()) === false) {
           static::onError($e['type'], $e['message'], $e['file'], $e['line']);
        }
    }


    /**
     * 获取异常调用
     *
     * @param $code
     * @param $e \Exception|Object
     * @return array
     */
    final static function debugBacktrace($e)
    {
        /**
         * @var $e \Exception
         */
        return array_map(function($trace){ 
            isset($trace['class']) && $trace['function'] = $trace['class'].$trace['type'].$trace['function'];
            if(isset($trace['args'])){
                $trace['function'] .= '('.join(', ' , array_map(function($arg){
                    if(in_array(gettype($arg),['array', 'object', 'boolean'])){
                        if(is_object($arg)) return get_class($arg);
                        if(is_bool($arg)) return $arg ? 'True' : 'False';
                        return json_encode($arg, true);
                    }else{
                        return is_string($arg) ? "'".$arg."'" : $arg;
                    }
                }, $trace['args'])).')';
            }
            return array('file' => str_replace(_DOC_,'', isset($trace['file']) ? $trace['file'] : ''), 'line' => isset($trace['line']) ? $trace['line'] : 0, 'function' => $trace['function']);
        
        }, $e->getTrace());
    }

    /**
     * 记录异常信息s
     * @param $e \Exception
     */
    final static function logException($e)
    {
        global $argv;
            $_ERROR = array(
                '[TIME]'       =>     date('Y-m-d H:i:s'),
                '[CODE]'       =>     Error::codes($e->getCode()),
                '[FILE]'       =>     $e->getFile(),
                '[LINE]'       =>     $e->getLine(),
                '[INFO]'       =>     $e->getMessage(),
                '[METHOD]'     =>     PHP_SAPI=='cli' ? PHP_SAPI : $_SERVER['REQUEST_METHOD'],
                '[REMOTE]'     =>     PHP_SAPI=='cli' ? PHP_SAPI : $_SERVER["REMOTE_ADDR"],
                '[REQUEST]'    =>     PHP_SAPI=='cli' ? __FILE__.implode(' ', $argv) : 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"],
                '[REFERER]'    =>     isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
                '[USER-AGENT]' =>     isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : '',
                '[BACKTRACES]' =>     PHP_EOL.$e->getTraceAsString()
            );
            array_walk($_ERROR, function (&$v, $k) { $v = $k.' '.$v;});
            if(!is_dir($logDir =  _DOC_._DS_.'runtime'._DS_.'logs'._DS_.'e'._DS_._APP_ )){
                mkdir($logDir, 0777, true);
            }
            file_put_contents($logDir._DS_.date('Ymd').'.log', join(PHP_EOL, $_ERROR).PHP_EOL.PHP_EOL, FILE_APPEND);

    }


    /**
     * 
     * @param $e \Exception
     * @return string
     */
    final static function HtmlException($e)
    {
        $debugBacktrace = null;
        if (static::$debug > 1){
            if(is_array($debugBacktrace = static::debugBacktrace($e))){ 
                $c = count($debugBacktrace = array_slice($debugBacktrace, 0, -5));
                $debugBacktrace = array_reduce($debugBacktrace, function($r, $v) use(&$c){
                    return $r.vsprintf('<tr><td align="center">'.$c--.'</td><td>%s</td><td>%d</td><td>%s</td></tr>', $v);
                }, '<table cellpadding="8" cellspacing="1" bgcolor="#aaa" width="100%"><thead><tr bgcolor="#eee"><th>No.</th><th>File</th><th>Line</th><th>Code</th></tr></thead><tbody bgcolor="#ffc">').'</tbody></table>';
            }
        }
        return str_replace(['$error', '$file', '$title', '$line', '$backtrace'], [Error::error($e->getCode()),  str_replace(_DOC_,'',$e->getFile()), $e->getMessage(), $e->getLine(), $debugBacktrace], '<html><head><title>$error</title></head><body style="background: #eee; padding: 1em;"><div><b>File:</b> $file (Line: $line)</div> <div><b>$error: </b>$title</div><div><h4>Debug Backtrace &copy;Varimax</h4>$backtrace</div></body></html>');
    }

    /**
    * display exception
    * @param $e \Exception
    * @version 20240511
    */
    final static function html($e)
    {
        ob_get_contents() && ob_end_clean();
        return static::HtmlException($e);
    }
}