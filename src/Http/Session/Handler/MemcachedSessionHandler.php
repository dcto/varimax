<?php

/**
 * Varimax The Slim PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 22:03
 * SITE: https://www.varimax.cn/
 */


namespace VM\Http\Session\Handler;
/**
 * NativeMemcachedSessionHandler.
 *
 * Driver for the memcached session save handler provided by the memcached PHP extension.
 *
 * @see http://php.net/memcached.sessions
 *
 * @author Drak <drak@zikula.org>
 */
class MemcachedSessionHandler extends \SessionHandler
{
    /**
     * Constructor.
     *
     * @param string $savePath Path of memcache server.
     * @param array  $options  Session configuration options.
     */
    public function __construct($savePath = null, array $options = array())
    {
        if (!extension_loaded('memcached')) {
            throw new \RuntimeException('PHP does not have "memcached" session module registered');
        }
        $savePath = $savePath ?: sprintf('%s:%s',
            config('cache.memcache.host', '127.0.0.1'),
            config('cache.memcache.port', '11211')
        );
        $options['memcached.sess_prefix'] = config('session.prefix','vm:session');
        ini_set('session.save_handler', 'memcached');
        ini_set('session.save_path', $savePath);
        $this->setOptions($options);
    }

    /**
     * Set any memcached ini values.
     *
     * @see https://github.com/php-memcached-dev/php-memcached/blob/master/memcached.ini
     */
    protected function setOptions(array $options)
    {
        $validOptions = array_flip(array(
            'memcached.sess_locking', 'memcached.sess_lock_wait',
            'memcached.sess_prefix', 'memcached.compression_type',
            'memcached.compression_factor', 'memcached.compression_threshold',
            'memcached.serializer',
        ));
        foreach ($options as $key => $value) {
            if (isset($validOptions[$key])) {
                ini_set($key, $value);
            }
        }
    }
}