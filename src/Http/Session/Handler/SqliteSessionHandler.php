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
     * database
     *
     * @var
     */
    protected $dbs;


    /**
     * session table name
     *
     * @var string
     */
    protected $table;

    /**
     * session database
     *
     * @var string
     */
    protected $database;

    /**
     * Constructor.
     *
     * @param string $savePath Path to SQLite database file itself.
     * @param array  $options  Session configuration options.
     */
    public function __construct(array $options = array())
    {
        $this->database = config('session.save_path', config('database.connections.sqlite.database', runtime('session','session.db')));

        $this->table = config('database.connections.sqlite.prefix', 'vm_').'session';

        $this->setOptions($options);

        $this->handler();
    }

    /**
     * @return \PDO
     *
     */
    protected function handler()
    {
        if (!$this->dbs instanceof \PDO) {

            $this->dbs = new \PDO("sqlite:".$this->database, null, null, array(\PDO::ATTR_PERSISTENT => true));

            if(!is_file($this->database)){
                $this->handler()->exec("PRAGMA encoding = 'UTF8';PRAGMA temp_store = 2;PRAGMA auto_vacuum = 0;PRAGMA count_changes = 1;PRAGMA cache_size = 9000;");
                $this->table();
            }
        }

        return $this->dbs;
    }


    public function create_sid()
    {
        return uniqid(sprintf('%08x', mt_rand(0, 2147483647)));
    }

    public function open($save_path, $session_name)
    {
        return is_object($this->handler());
    }


    public function read($session_id)
    {
        $data = null;
        $sth = $this->handler()->query("SELECT `value` FROM `".$this->table."` WHERE `id`='{$session_id}' AND `expire` > strftime('%s','now') LIMIT 1", \PDO::FETCH_NUM);
        if (!empty($sth)) {
            list($data) = $sth->fetch();
            unset($sth);
        }
        return $data;
    }

    public function write($id = null, $data = null) {
        $expire = ceil($this->expire + $this->nowTime);
        return $this->handler()->exec("REPLACE INTO `".$this->table."` VALUES('{$id}','{$data}',{$expire})");
    }

    public function gc($expire = 0) {
        return $this->handler()->exec("DELETE FROM `".$this->table."` WHERE `expire` < strftime('%s','now'); VACUUM;");
    }

    public function destroys($session_id) {
        return $this->handler()->exec("DELETE FROM `".$this->table."` WHERE `id` = '{$session_id}'");
    }

    public function close()
    {
        return true;
    }

    /**
     * @return mixed
     */
    protected function table()
    {
        if(!$this->handler()->query("SELECT `name` FROM `sqlite_master` WHERE `type` = 'table' and `name` FROM ".$this->table)){

            if(!$this->handler()->exec("CREATE TABLE IF NOT EXISTS `".$this->table."` (`id` VARCHAR PRIMARY KEY ON CONFLICT FAIL NOT NULL COLLATE 'NOCASE',`value` TEXT NOT NULL,`expire` INTEGER NOT NULL);")) {
                return null;
            }
        }
        return $this->table;
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