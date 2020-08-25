<?php
/**
 * Varimax The Full Stack PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 22:03
 * SITE: https://www.varimax.cn/
 */


namespace VM\Logger;


use DateTime;
use RuntimeException;
use Psr\Log\LogLevel;
use Psr\Log\AbstractLogger;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;


class Logger extends AbstractLogger{
    /**
     * logs dir
     *
     * @var string
     */
    protected $LogsDir = '/logs';

    /**
     * options
     *
     * @var array
     */
    protected $options = array (
        'extension'      => 'log',
        'dateFormat'     => 'Y-m-d H:i:s.u',
        'filename'       => false,
        'flushFrequency' => false,
        'prefix'         => 'log-',
        'logFormat'      => false,
        'appendContext'  => true,
    );
    /**
     * Path to the log file
     * @var string
     */
    private $logFilePath;

    /**
     * Current minimum logging threshold
     * @var integer
     */
    protected $logLevelThreshold = LogLevel::DEBUG;

    /**
     * The number of lines logged in this instance's lifetime
     * @var int
     */
    private $logLineCount = 0;

    /**
     * Log Levels
     * @var array
     */
    protected $logLevels = array(
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT     => 1,
        LogLevel::CRITICAL  => 2,
        LogLevel::ERROR     => 3,
        LogLevel::WARNING   => 4,
        LogLevel::NOTICE    => 5,
        LogLevel::INFO      => 6,
        LogLevel::DEBUG     => 7
    );

    /**
     * This holds the file handle for this instance's log file
     * @var resource
     */
    private $fileHandle;

    /**
     * This holds the last line logged to the logger
     *  Used for unit tests
     * @var string
     */
    private $lastLine = '';

    /**
     * Octal notation for default permissions of the log file
     * @var integer
     */
    private $permissions = 0777;

    /**
     * Class constructor
     *
     * @param string $logDirectory      File path to the logging directory
     * @param string $logLevelThreshold The LogLevel Threshold
     * @param array  $options
     *
     * @internal param string $logFilePrefix The prefix for the log file name
     * @internal param string $logFileExt The extension for the log file
     */
    public function __construct($logDirectory = null, $logLevelThreshold = LogLevel::DEBUG, array $options = array())
    {
        $logDirectory && $this->LogsDir = $logDirectory;
        $this->logLevelThreshold = $logLevelThreshold;
        $this->options = array_merge($this->options, $options);
        $this->LogsDir = runtime($this->LogsDir);
    }

    /**
     * Log a message to file
     *
     * @param $path
     * @param string $level
     * @return $this
     */
    public function dir($dir, $file = false)
    {
        $dirName = pathinfo($dir, PATHINFO_DIRNAME);
        $fileName = pathinfo($dir, PATHINFO_FILENAME);

        if(substr($dir, -1, 1) == '/'){
            $dirName = $dir;
            $fileName = date('Ymd');
        }else if($file === false){
            $fileName = $fileName . '-' . date('Ymd');
        }else if(is_string($file)){
            $dirName = $dir;
            $fileName = $file;
        }

        $this->options['filename'] = $fileName;
        if(!is_dir($dirName = $this->LogsDir.DIRECTORY_SEPARATOR.$dirName)){
            mkdir($dirName, $this->permissions, true);
        }

        if(strpos($dirName, 'php://') === 0) {
            $this->setLogToStdOut($dirName);
            $this->setFileHandle('w+');
        } else {
            $this->setLogFilePath($dirName);
            if(file_exists($this->logFilePath) && !is_writable($this->logFilePath)) {
                throw new RuntimeException('The file could not be written to. Check that appropriate permissions have been set.');
            }
            $this->setFileHandle('a');
        }

        if (!$this->fileHandle) {
            throw new RuntimeException('The file could not be opened. Check permissions.');
        }

        $this->setLogFilePath($dirName);

        return $this;
    }

    /**
     * log name
     * @param $fileName
     */
    public function file($fileName)
    {
        $this->options['filename'] = $fileName;

        return $this;
    }

    /**
     * Indents the given string with the given indent.
     *
     * @param  string $string The string to indent
     * @param  string $indent What to use as the indent.
     * @return string
     */
    protected function indent($string, $indent = '    ')
    {
        return $indent.str_replace("\n", "\n".$indent, $string);
    }


    /**
     * @param $stdOutPath
     * @return $this
     */
    public function setLogToStdOut($stdOutPath) {
        $this->logFilePath = $stdOutPath;

        return $this;
    }

    /**
     * @param $logDirectory
     * @return $this
     */
    public function setLogFilePath($logDirectory) {
        if ($this->options['filename']) {
            if (strpos($this->options['filename'], '.log') !== false || strpos($this->options['filename'], '.txt') !== false) {
                $this->logFilePath = $logDirectory.DIRECTORY_SEPARATOR.$this->options['filename'];
            }
            else {
                $this->logFilePath = $logDirectory.DIRECTORY_SEPARATOR.$this->options['filename'].'.'.$this->options['extension'];
            }
        } else {
            $this->logFilePath = $logDirectory.DIRECTORY_SEPARATOR.$this->options['prefix'].date('Y-m-d').'.'.$this->options['extension'];
        }

        return $this;
    }

    /**
     * @param $writeMode
     *
     * @internal param resource $fileHandle
     */
    public function setFileHandle($writeMode) {
        $this->fileHandle = fopen($this->logFilePath, $writeMode);
        return $this;
    }

    /**
     * Sets the date format used by all instances of KLogger
     *
     * @param string $dateFormat Valid format string for date()
     */
    public function setDateFormat($dateFormat)
    {
        $this->options['dateFormat'] = $dateFormat;

        return $this;
    }

    /**
     * Sets the Log Level Threshold
     *
     * @param string $logLevelThreshold The log level threshold
     */
    public function setLogLevelThreshold($logLevelThreshold)
    {
        $this->logLevelThreshold = $logLevelThreshold;

        return $this;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return $this
     */
    public function log($level, $message, array $context = array())
    {
        if ($this->logLevels[$this->logLevelThreshold] < $this->logLevels[$level]) {
            return $this;
        }
        $message = $this->formatMessage($level, $message, $context);

        $this->write($message);

        return $this;
    }

    /**
     * Writes a line to the log without prepending a status or timestamp
     *
     * @param string $message Line to write to the log
     * @return void
     */
    public function write($message)
    {
        if (null !== $this->fileHandle) {
            if (fwrite($this->fileHandle, $message) === false) {
                throw new RuntimeException('The file could not be written to. Check that appropriate permissions have been set.');
            } else {
                $this->lastLine = trim($message);
                $this->logLineCount++;

                if ($this->options['flushFrequency'] && $this->logLineCount % $this->options['flushFrequency'] === 0) {
                    fflush($this->fileHandle);
                }
            }
        }
    }

    /**
     * Get the file path that the log is currently writing to
     *
     * @return string
     */
    public function getLogFilePath()
    {
        return $this->logFilePath;
    }

    /**
     * Get the last line logged to the log file
     *
     * @return string
     */
    public function getLastLogLine()
    {
        return $this->lastLine;
    }

    /**
     * Formats the message for logging.
     *
     * @param  string $level   The Log Level of the message
     * @param  string $message The message to log
     * @param  array  $context The context
     * @return string
     */
    protected function formatMessage($level, $message, $context)
    {
        if (is_array($message)) {
            $message = var_export($message, true);
        } elseif ($message instanceof Jsonable) {
            $message = $message->toJson();
        } elseif ($message instanceof Arrayable) {
            $message = var_export($message->toArray(), true);
        }

        if ($this->options['logFormat']) {
            $parts = array(
                'date'          => $this->getTimestamp(),
                'level'         => strtoupper($level),
                'level-padding' => str_repeat(' ', 9 - strlen($level)),
                'priority'      => $this->logLevels[$level],
                'message'       => $message,
                'context'       => json_encode($context),
            );
            $message = $this->options['logFormat'];
            foreach ($parts as $part => $value) {
                $message = str_replace('{'.$part.'}', $value, $message);
            }

        } else {
            $message = "[{$this->getTimestamp()}] [{$level}] {$message}";
        }

        if ($this->options['appendContext'] && ! empty($context)) {
            $message .= PHP_EOL.$this->indent($this->contextToString($context));
        }

        return $message.PHP_EOL;
    }

    /**
     * Gets the correctly formatted Date/Time for the log entry.
     *
     * PHP DateTime is dump, and you have to resort to trickery to get microseconds
     * to work correctly, so here it is.
     *
     * @return string
     */
    private function getTimestamp()
    {
        $originalTime = microtime(true);
        $micro = sprintf("%06d", ($originalTime - floor($originalTime)) * 1000000);
        $date = new DateTime(date('Y-m-d H:i:s.'.$micro, $originalTime));

        return $date->format($this->options['dateFormat']);
    }

    /**
     * Takes the given context and coverts it to a string.
     *
     * @param  array $context The Context
     * @return string
     */
    protected function contextToString($context)
    {
        $context = is_object($context) ? json_decode(json_encode($context), true) : $context;

        $export = '';
        if(is_array($context)){
            foreach ($context as $key => $value) {
                $export .= "{$key}: ";
                $export .= preg_replace(array(
                    '/=>\s+([a-zA-Z])/im',
                    '/array\(\s+\)/im',
                    '/^  |\G  /m'
                ), array(
                    '=> $1',
                    'array()',
                    '    '
                ), str_replace('array (', 'array(', var_export($value, true)));
                $export .= PHP_EOL;
            }
        }else{
            $export = $context;
        }
        return str_replace(array('\\\\', '\\\''), array('\\', '\''), rtrim($export));
    }

    /**
     * Class destructor
     */
    public function __destruct()
    {
        if ($this->fileHandle) {
            fclose($this->fileHandle);
        }
    }
}