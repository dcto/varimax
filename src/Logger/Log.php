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
     * @param string $root      File path to the logging root
     * @param string $logLevelThreshold The LogLevel Threshold
     * @param array  $options
     *
     * @internal param string $logFilePrefix The prefix for the log file name
     * @internal param string $logFileExt The extension for the log file
     */
    public function __construct($root = null, $logLevelThreshold = LogLevel::DEBUG, array $options = array())
    {
        $this->options = array_merge($this->options, $options);
        $this->logLevelThreshold = $logLevelThreshold;
        $this->root =  $root ?? runtime($this->root);
    }

    /**
     * Log a message to file
     *
     * @param $path
     * @param string $level
     * @return $this
     */
    public function dir()
    {
        $this->logDir = join(_DS_, func_get_args());
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
     * Indents the given string with the given indent.
     *
     * @param  string $string The string to indent
     * @param  string $indent What to use as the indent.
     * @return string
     */
    public function indent($string, $indent = '    ')
    {
        return $indent . str_replace("\n", "\n" . $indent, $string);
    }


    /**
     * @param $stdOutPath
     * @return $this
     */
    protected function setLogToStdOut($message)
    {
        echo $message;
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
        $message = $this->formatMessage($level, $message, $context);
        $this->setLogToStdOut($message);
        $this->write($message);

        $this->close();

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
        $this->setLogHandle();
        if (fwrite($this->fileHandle, $message) === false) {
            throw new \RuntimeException('The file could not be written to. Check that appropriate permissions have been set.');
        } else {
            $this->lastLine = trim($message);
            $this->logLineCount++;

            if ($this->options['flushFrequency'] && $this->logLineCount % $this->options['flushFrequency'] === 0) {
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
     * @param  array  $context The context
     * @return string
     */
    protected function formatMessage($level, $message, $context)
    {
        if (is_array($message)) {
            $message = var_export($message, true);
        } elseif ($message instanceof \JsonSerializable) {
            $message = $message->jsonSerialize();
        } elseif ($message instanceof \ArrayAccess) {
            $message = var_export($message->toArray(), true);
        } else {
            $message = (string) $message;
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
                $message = str_replace('{' . $part . '}', $value, $message);
            }
        } else {
            $message = "[{$this->getTimestamp()}] [{$level}] {$message}";
        }

        if ($this->options['appendContext'] && !empty($context)) {
            $message .= PHP_EOL . $this->indent($this->contextToString($context));
        }

        return $message . PHP_EOL;
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
    protected function contextToString($context)
    {
        $context = is_object($context) ? json_decode(json_encode($context), true) : $context;

        $export = '';
        if (is_array($context)) {
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
        } else {
            $export = $context;
        }
        return str_replace(array('\\\\', '\\\''), array('\\', '\''), rtrim($export));
    }

    /**
     * close log file
     */
    public function close()
    {
        $this->logDir = $this->logFile = null;
        if ($this->fileHandle) {
            fclose($this->fileHandle);
        }
    }
}
