<?php
/**
 * Varimax The Slim PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 21:49
 * SITE: https://www.varimax.cn/
 */

namespace VM\Exception;


class E {

    /**
     * Debug Level
     *
     * @var integer
     */
    static private $debug = 2;

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
        if(self::$debug = getenv('DEBUG')){
            //错误级别
            error_reporting(E_ALL);
            //开启错误
            ini_set('display_errors', 'On');
        }

        //预留内存
        self::$memory && str_repeat('*', self::$memory);

        //截获未捕获的异常
        set_exception_handler(function($e){
             self::onException($e);
        });

        //截获各种错误
        set_error_handler(function($code, $message, $file, $line){
            self::onError($code, $message, $file, $line);
        });

        //截获致命性错误
        register_shutdown_function(function(){
            self::onShutdown();
        });
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
         self::logException($e);
         self::display($e);
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
        self::$memory = null;

        //将错误变成异常抛出 统一交给异常处理函数进行处理
        if(error_reporting() & $code) {
            self::onException(new \ErrorException($message, $code, 1, $file, $line));
        }
    }

    /**
     * 截获致命性错误
     */
    static public function onShutdown()
    {
        //释放备用内存供下面处理程序使用
        self::$memory = null;

        //最后一条错误信息
        if(is_null($e = error_get_last()) === false) {
            self::onError($e['type'], $e['message'], $e['file'], $e['line']);
        }
    }


    /**
     * 获取异常调用
     *
     * @param $code
     * @param $e
     * @return string
     */
    final static function debugBacktrace(\Exception $e)
    {
        /**
         * @var $e \Exception
         */
        return array_map(function($trace){ 
            isset($trace['class']) && $trace['function'] = $trace['class'].$trace['type'].$trace['function'];
            $trace['function'] .= '('.join(', ' , array_map(function($arg){
                if(in_array(gettype($arg),['array', 'object', 'boolean'])){
                    if($arg instanceof \Closure) return '\Closure';
                    if(is_object($arg)) return get_class($arg);
                    if(is_bool($arg)) return $arg ? 'True' : 'False';
                    return json_encode($arg, true);
                }else{
                    return is_string($arg) ? "'".$arg."'" : $arg;
                }
            }, $trace['args'])).')';
            return array('file' => str_replace(_DOC_,'', isset($trace['file']) ? $trace['file'] : '\Closure'), 'line' => isset($trace['line']) ? $trace['line'] : 0, 'function' => $trace['function']);
        },$e->getTrace());
    }

    /**
     * 记录异常信息s
     * @param $e \Exception
     */
    final static private function logException($e)
    {
        global $argv;
            $_ERROR = array(
                '[TIME]'       =>     date('Y-m-d H:i:s'),
                '[CODE]'       =>     Exception::codes($e->getCode()),
                '[FILE]'       =>     $e->getFile(),
                '[LINE]'       =>     $e->getLine(),
                '[INFO]'       =>     Exception::error($e->getCode()).' '.$e->getMessage(),
                '[METHOD]'     =>     PHP_SAPI=='cli' ? PHP_SAPI : $_SERVER['REQUEST_METHOD'],
                '[REMOTE]'     =>     PHP_SAPI=='cli' ? PHP_SAPI : $_SERVER["REMOTE_ADDR"],
                '[REQUEST]'    =>     PHP_SAPI=='cli' ? __FILE__.implode(' ', $argv) : 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"],
                '[REFERER]'    =>     isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
                '[USER-AGENT]' =>     isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : '',
                '[BACKTRACES]' =>     PHP_EOL.$e->getTraceAsString()
            );
            array_walk($_ERROR, function (&$v, $k) { $v = $k.' '.$v;});
            if(!is_dir($logDir = runtime('logs','e',_APP_))){
                mkdir($logDir, 0777, true);
            }
            file_put_contents($logDir._DS_.date('Ymd').'.log', implode(PHP_EOL, $_ERROR).PHP_EOL.str_repeat('=',100).PHP_EOL.PHP_EOL, FILE_APPEND);

    }


    /**
     * display exception
     *
     * @param $e \Exception
     */
    final static function display($e)
    {
        if (PHP_SAPI == 'cli') {
            echo $e->getFile() . "\t[LINE]:" . $e->getLine() . "\t" . '[ERROR]:' . $e->getMessage() . PHP_EOL . PHP_EOL;
        }else{
            ob_get_contents() && ob_end_clean();
            http_response_code($e instanceof Exception ? $e->getStatus() : 500);

            if (self::$debug == 2) {
                $debugBacktrace = self::debugBacktrace($e);
                    echo '<html><head><title>' . $e->getMessage() . '</title><meta name="robots" content="none" /><style type="text/css">body {font: 12pt verdana; margin: 10px auto;}div {background: #f5f5f5; border-radius: 5px; line-height: 200%; margin-bottom: 1em; padding: 1em;}table {background: #aaa;}.stack {background-color: #ffc;}.title {background-color: #eee;}</style></head><body><div id="title"><b>' . Exception::error($e->getCode()) . '</b>: ' . $e->getMessage() . '</div>';
                    if ($debugBacktrace) {
                        echo '<div id="debug"><p><b>Debug Backtrace &copy;Varimax</b></p><table cellpadding="5" cellspacing="1" width="100%" class="table"><tbody>';
                        if (is_array($debugBacktrace)) {
                            echo '<tr class="title"><td>No.</td><td>File</td><td>Line</td><td>Code</td></tr>';
                            foreach ($debugBacktrace as $k => $error) {
                                $k++;
                                echo "<tr class=\"stack\"><td>{$k}</td><td>{$error['file']}</td><td>{$error['line']}</td><td>{$error['function']}</td></tr>";
                            }
                        } else {
                            echo '<tr><td><ul>' . $debugBacktrace . '</ul></td></tr>';
                        }
                        echo '</tbody></table></div>';
                    }
                    echo '</body></html>';

            }else if(self::$debug == 1){
                die($e->getMessage());
            }
        }
        exit();
    }
}