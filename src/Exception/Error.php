<?php

namespace VM\Exception;

/**
 * Class ErrorException
 * @package VM\Exception
 */
class Error extends \Exception
{
    /**
     * HTTP STATUS
     *
     * @var int
     */
    protected $status = 500;

    /**
     * HTTP Exception Message
     * @var string
     */
    protected $message = 'Unknown Exception';

    /**
     * Error Code
     * @var array
     */
     static private $codes = array(
        E_COMPILE_ERROR => 4001,
        E_COMPILE_WARNING => 4002,
        E_CORE_ERROR => 4003,
        E_CORE_WARNING => 4004,
        E_DEPRECATED => 4005,
        E_ERROR => 4006,
        E_NOTICE => 4007,
        E_PARSE => 4008,
        E_RECOVERABLE_ERROR => 4009,
        E_STRICT => 4010,
        E_USER_DEPRECATED => 4011,
        E_USER_ERROR => 4012,
        E_USER_NOTICE => 4013,
        E_USER_WARNING => 4014,
        E_WARNING => 4015,
        4016 => 4016,
    );

    /**
     * ERROR NAME
     * @var array
     */
    static private $error = array(
        0 => 'Error',
        4016 => 'Error',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_DEPRECATED => 'Deprecated Warning',
        E_ERROR => 'Fatal Error',
        E_NOTICE => 'Notice',
        E_PARSE => 'Parse Error',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_STRICT => 'Strict Warning',
        E_USER_DEPRECATED => 'User Deprecated Warning',
        E_USER_ERROR => 'User Error',
        E_USER_NOTICE => 'User Notice',
        E_USER_WARNING => 'User Warning',
        E_WARNING => 'Warning',
    );

    /**
     * ErrorException constructor.
     * @see http://php.net/manual/en/errorexception.construct.php
     * @param string $message
     * @param int $code
     * @param int $severity
     * @param string $file
     * @param int $line
     * @param E|null $previous
     */
    /*
    public function __construct($message = null, $code = 0, $severity = 1, $file = __FILE__, $line = __LINE__, Exception $previous = null)
    {
        $message = $message?:$this->message;
        parent::__construct($message, $code, $severity, $file, $line, $previous);
    }
    */

    /**
     * GET HTTP STATUS CODE
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }


    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}";
    }


    /**
     * 方      法：是否是致命性错误
     * 参      数：array $error
     * 返      回：boolean
     */
    public static function isFatalError($error)
    {
        $fatalErrors = array(
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_CORE_WARNING,
            E_COMPILE_ERROR,
            E_COMPILE_WARNING
        );
        return isset($error['type']) && in_array($error['type'], $fatalErrors);
    }

    /**
     * 方      法：根据原始的错误代码得到本地的错误代码
     * 参      数：int $code
     * 返      回：int $localCode
     */
    public static function codes($code)
    {
        return isset(self::$codes[$code]) ? self::$codes[$code] : self::$codes[4016];
    }

    /**
     * 方      法：根据原始的错误代码获取用户友好型名称
     * 参      数：int
     * 返      回：string $name
     */
    public static function error($code)
    {
        return isset(self::$error[$code]) ? self::$error[$code] : self::$error[4016];
    }
}