<?php

/**
 * Varimax The Slim PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 22:03
 * SITE: https://www.varimax.cn/
 */

namespace VM\Logger;

use Psr\Log\LogLevel;
use Psr\Log\AbstractLogger;

class Log extends AbstractLogger
{
    /**
     * logs root dir
     */
    private $root = 'logs';

    /**
     * log  subdir
     *
     * @var string
     */
    private $logDir;

    /**
     * Path to the log file
     * @var string
     */
    private $logFile;

    /**
     * This holds the file handle for this instance's log file
     * @var resource
     */
    private $fileHandle;

    /**
     * Octal notation for default permissions of the log file
     * @var integer
     */
    private $permissions = 0777;
    
    /**
     * options
     *
     * @var array
     */
    private $options = array(
        'extension'      => 'log',
        'dateFormat'     => 'Y-m-d H:i:s.u',
        'flushFrequency' => false,
        'prefix'         => '',
        'logFormat'      => false,
        'appendContext'  => true,
    );

    /**
     * The number of lines logged in this instance's lifetime
     * @var int
     */
    private $lineCount = 0;

    /**
     * This holds the last line logged to the logger
     *  Used for unit tests
     * @var string
     */
    private $lastLine = '';

    /**
     * Current minimum logging threshold
     * @var integer
     */
    private $logLevelThreshold = LogLevel::DEBUG;

    /**
     * Log Levels
     * @var array
     */
    private $logLevels = array(
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
     * Class constructor
     *
     * @param string $root      File path to the logging root
     * @param string $logLevelThreshold The LogLevel Threshold
     * @param array  $options
     *
     * @internal param string $logFilePrefix The prefix for the log file name
     * @internal param string $logFileExt The extension for the log file
     */
    public function __construct($root = null, $logLevelThreshold = LogLevel::DEBUG)
    {
        $this->root = $root ?? runtime($this->root);
        $this->logLevelThreshold = $logLevelThreshold;
    }

    /**
     * Log a message to file
     *
     * @param $paths
     * @return $this
     */
    public function dir(...$paths)
    {
        $this->logDir = join(_DS_, $paths);
        if (pathinfo($this->logDir, PATHINFO_EXTENSION)) {
            $this->file(pathinfo($this->logDir, PATHINFO_BASENAME));
            $this->logDir = pathinfo($this->logDir, PATHINFO_DIRNAME);
        }
        $this->logDir = trim($this->logDir, _DS_);

        return $this;
    }

    /**
     * log name
     * @param $file
     */
    public function file($file = null)
    {
        if ($file) {
            if (!in_array(pathinfo($file, PATHINFO_EXTENSION), ['log', 'txt'])) {
                $file .= '.' . $this->options['extension'];
            }
            $this->logFile = $file;

            return $this;
        } else {
            return $this->logFile;
        }
    }


    /**
     * set and log path
     */
    public function logFile()
    {
        $logDir = rtrim($this->root . _DS_ . $this->logDir, _DS_) . _DS_;
        if ($this->logFile) {
            ltrim($this->logFile, _DS_);
        } else {
            $this->logFile = $this->options['prefix'] . date('Ymd') . '.' . $this->options['extension'];
        }
        return $logDir . $this->logFile;
    }

    /**
     * alias option
     *
     * @param $key
     * @param null $value
     * @return Logger
     */
    public function set($key, $value = null)
    {
        return $this->options($key, $value);
    }

    /**
     * Set Options
     *
     * @param $key
     * @param null $value
     */
    public function options($key, $value = null)
    {
        if (is_array($key)) {
            $this->options = array_merge($this->options, $key);
        } else if ($value) {
            $this->options[$key] = $value;
        } else {
            return $this->options[$key];
        }
        return $this;
    }

    /**
     * @param $stdOutPath
     * @return $this
     */
    protected function stdout($message, $level = LogLevel::INFO)
    {
        error_log($message, 4);
        return $this;
    }

    /**
     * @param $dir
     * @return $this
     */
    protected function setLogHandle()
    {
        $logDir = dirname($this->logFile());
        if (strpos($logDir, 'php://') === 0) {
            $this->setFileHandle('w+');
        } else {
            if (!is_dir($logDir)) {
                mkdir($logDir, $this->permissions, true);
            }
            $this->setFileHandle('a');
            if (file_exists($this->logFile()) && !is_writable($this->logFile())) {
                throw new \RuntimeException('The file could not be written to.['.$this->logFile().']');
            }
        }

        if (!$this->fileHandle) {
            throw new \RuntimeException('Check permissions of file. ['.$this->logFile().']');
        }
        return $this;
    }

    /**
     * @param $writeMode
     *
     * @internal param resource $fileHandle
     */
    protected function setFileHandle($writeMode)
    {
        $this->fileHandle = fopen($this->logFile(), $writeMode);
        stream_set_blocking($this->fileHandle, 0);
        return $this;
    }

    /**
     * Sets the date format used by all instances of KLogger
     *
     * @param string $dateFormat Valid format string for date()
     */
    protected function setDateFormat($dateFormat)
    {
        $this->options['dateFormat'] = $dateFormat;

        return $this;
    }

    /**
     * Sets the Log Level Threshold
     *
     * @param string $logLevelThreshold The log level threshold
     */
    protected function setLogLevelThreshold($logLevelThreshold)
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
        $context = $this->formatMessage($level, $message, $context);

        if ((PHP_SAPI == 'cli' || PHP_SAPI == 'cli-server') && getenv('DEBUG')) {
            $this->stdout($context);
        }else{
            $this->setLogHandle()->write($context);
            $this->close();
        }

        return $this;
    }

    /**
     * Writes a line to the log without prepending a status or timestamp
     *
     * @param string $message Line to write to the log
     * @return void
     */
    protected function write($message)
    {
        if (fwrite($this->fileHandle, $message.PHP_EOL) === false) {
            throw new \RuntimeException('The file could not be written to. Check that appropriate permissions have been set.');
        } else {
            $this->lastLine = trim($message);
            $this->lineCount++;
            if ($this->options['flushFrequency'] && $this->lineCount % $this->options['flushFrequency'] === 0) {
                fflush($this->fileHandle);
            }
        }
    }

    /**
     * Get the file path that the log is currently writing to
     *
     * @return string
     */
    public function getLogFile()
    {
        return $this->logFile();
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
     * @param  mixed  $context The context
     * @return string
     */
    protected function formatMessage($level, $message, array $context)
    {
        if ($this->options['logFormat']) {
            $parts = array(
                'date'          => $this->getTimestamp(),
                'level'         => strtoupper($level),
                'level-padding' => str_repeat(' ', 9 - strlen($level)),
                'priority'      => $this->logLevels[$level],
                'message'       => $message,
                'context'       => $this->formatToString($context)
            );
            $message = $this->options['logFormat'];
            foreach ($parts as $part => $value) {
                $message = str_replace('{' . $part . '}', $value, $message);
            }
        } else {
            $message = sprintf("[%s] [%s] %s %s", 
            $this->getTimestamp(), strtolower($level), $this->formatToString($message), $context ? $this->formatToString($context) : '');
        }

        return $message;
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
        return (new \DateTime())->format($this->options['dateFormat']);
    }

    /**
     * Takes the given context and coverts it to a string.
     *
     * @param  array $context The Context
     * @return string
     */
    protected function formatToString($context)
    {
        if (is_scalar($context)){
            return (string) $context;
        }else {
            if (is_object($context) && $context instanceof \Stringable) {
                return (string) $context;
            }
            return json_encode($context, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * close log file
     */
    public function close()
    {
        $this->logDir = $this->logFile = null;
        is_resource($this->fileHandle) && fclose($this->fileHandle);
    }
}
