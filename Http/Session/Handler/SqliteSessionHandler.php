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
 * NativeSqliteSessionHandler.
 *
 * Driver for the sqlite session save handler provided by the SQLite PHP extension.
 *
 * @author Drak <drak@zikula.org>
 */
class SqliteSessionHandler extends \SessionHandler
{
    /**
     * Constructor.
     *
     * @param string $savePath Path to SQLite database file itself.
     * @param array  $options  Session configuration options.
     */
    public function __construct($savePath = null, array $options = array())
    {
        if (!extension_loaded('sqlite')) {
            throw new \RuntimeException('PHP does not have "sqlite" session module registered');
        }
        $savePath = $savePath ?: path(config('database.sqlite.database'));
        ini_set('session.save_handler', 'sqlite');
        ini_set('session.save_path', $savePath);
        $this->setOptions($options);
    }
    /**
     * Set any sqlite ini values.
     *
     * @see http://php.net/sqlite.configuration
     */
    protected function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            if (in_array($key, array('sqlite.assoc_case'))) {
                ini_set($key, $value);
            }
        }
    }
}