<?php

/**
 * Varimax The Full Stack PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 22:03
 * SITE: https://www.varimax.cn/
 */


namespace VM\Http\Session\Handler;
/**
 * NativeMemcacheSessionHandler.
 *
 * Driver for the memcache session save handler provided by the memcache PHP extension.
 *
 * @see http://php.net/memcache
 *
 * @author Drak <drak@zikula.org>
 */
class MemcacheSessionHandler extends \SessionHandler
{
    /**
     * Constructor.
     *
     * @param string $savePath Path of memcache server.
     * @param array  $options  Session configuration options.
     */
    public function __construct($savePath = null, array $options = array())
    {
        if (!extension_loaded('memcache')) {
            throw new \RuntimeException('PHP does not have "memcache" session module registered');
        }
        $savePath = $savePath ?: sprintf('tcp://%s:%s?timeout=%s&persistent=%s',
            config('cache.memcache.host', '127.0.0.1'),
            config('cache.memcache.port', '11211'),
            config('cache.memcache.timeout', '5')
        );
        ini_set('session.save_handler', 'memcache');
        ini_set('session.save_path', $savePath);
        $this->setOptions($options);
    }
    /**
     * Set any memcached ini values.
     *
     * @see http://php.net/memcache.ini
     */
    protected function setOptions(array $options)
    {
        $validOptions = array_flip(array(
            'memcache.allow_failover', 'memcache.max_failover_attempts',
            'memcache.chunk_size', 'memcache.default_port', 'memcache.hash_strategy',
            'memcache.hash_function', 'memcache.protocol', 'memcache.redundancy',
            'memcache.session_redundancy', 'memcache.compress_threshold',
            'memcache.lock_timeout',
        ));
        foreach ($options as $key => $value) {
            if (isset($validOptions[$key])) {
                ini_set($key, $value);
            }
        }
    }
}