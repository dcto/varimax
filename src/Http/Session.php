<?php
/**
 * Varimax The Slim PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 22:03
 * SITE: https://www.varimax.cn/
 */

namespace VM\Http;


/**
 * @package Session
 */
class Session implements \IteratorAggregate, \Countable
{
    /**
     * @see http://php.net/session.configuration
     * @var array
     */
    private $options = ['use_trans_sid'=>1, 'use_cookies'=>1, 'use_only_cookies'=>0];

    public function __construct()
    {
        $this->options += config('session', []);
        ini_set('session.auto_start', 0);
        if(isset($this->options['auto_start'])){
            if(strtolower($this->options['auto_start']) != 'off' && boolval($this->options['auto_start'])){
                $this->start();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function start(array $options = [])
    {        
        if ($this->status()) {
            throw new \RuntimeException('Failed to start the session: already started.');
        }

        if (ini_get('session.use_cookies') && headers_sent($file, $line)) {
            throw new \RuntimeException(sprintf('Failed to start the session because headers have already been sent by "%s" at line %d.', $file, $line));
        }

        $this->options += $options;
        unset($this->options['auto_start']);
        session_start($this->options);
        return $this;
    }


    /**
     * Get or regenerate current session ID.
     * @param bool $newId
     * @return string|self
     */
    public function id($id = null)
    {
        return $id && session_id($id) === '' ? $this : session_id();
    }

    /**
     * {@inheritdoc}
     */
    public function has($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        return count(array_intersect($keys,array_keys($_SESSION)));
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        if($this->status()) {
             return data_get($_SESSION, $key, $default);
        }
        throw new \RuntimeException('Unable to automatic start session, manual operation start it\'s');
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        if($this->status()){
            data_set($_SESSION, $key, $value);
            return $this;
        }
        throw new \RuntimeException('Unable to automatic start session, manual operation start it\'s');
    }

    /**
     * Put a key / value pair or array of key / value pairs in the session.
     *
     * @param  string|array  $key
     * @param  mixed       $value
     * @return void
     */
    public function put($key, $value = null)
    {
        if (! is_array($key)) {
            $key = [$key => $value];
        }

        foreach ($key as $arrayKey => $arrayValue) {
            $this->set($arrayKey, $arrayValue);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $_SESSION ?? array();
    }

    /**
     * remove alias
     * @param $key
     * @return mixed
     */
    public function del($key)
    {
       return $this->remove($key);
    }

    /**
     * Push a value onto a session array.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function push($key, $value)
    {
        $array = $this->get($key, []);

        $array[] = $value;

        $this->put($key, $array);
    }

    /**
     * 持久化存储
     */
    public function save(\Closure $callback = null)
    {
        $callback && $callback($this->all());
        return $this;
    }

    /**
     * Flash a key / value pair to the session.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function flash($key, $value)
    {
        $this->put($key, $value);
        $this->push('_flash.new', $key);
        $this->removeFromOldFlashData([$key]);
    }

    /**
     * Flash an input array to the session.
     *
     * @param  array  $value
     * @return void
     */
    public function flashInput(array $value)
    {
        $this->flash('_old_input', $value);
    }
    /**
     * delete alias
     * @param $key
     * @return mixed
     */
    public function delete($key)
    {
        return $this->remove($key);
    }

    /**
     * remove session
     *
     * @param string $key
     */
    public function remove($key)
    {
        $keys = is_array($key) ? $key : func_get_args();
        foreach($keys as $key){
            unset($_SESSION[$key]);
        }
    }

    /**
     * @param array $attribute
     * @return bool
     */
    public function replace(array $attribute)
    {
        foreach($attribute as $k=>$v) {
            $this->set($k, $v);
        }
        return $this;
    }


    /**
     * 迁移Session
     * @param bool $destroy
     * @param null $lifetime
     */
    public function migrate($destroy = false, $lifetime = null)
    {

    }

    /**
     * Returns the number of attributes.
     *
     * @return int The number of attributes
     */
    public function count() : int
    {
        return count($_SESSION);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->destroy();
    }

    /**
     * flush session
     */
    public function flush()
    {
        $this->destroy();
        return $this;
    }

    /**
     * Destroy the session.
     */
    public function destroy()
    {
        if($this->status()){
            session_unset();
            session_destroy();
            session_write_close();
            session_abort();
        }
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '',time() - 4200, $params['path'],$params['domain'],$params['secure'],$params['httponly']);
        }
        return true;
    }

    /**
     * 重新生成session_id
     * @param bool $delete 是否删除关联会话文件
     * @return bool
     */
    public function regenerate($delete = true)
    {
       return session_regenerate_id($delete);
    }

    /**
     * {@inheritdoc}
     */
    public function status()
    {
        return \PHP_SESSION_ACTIVE === session_status();
    }

    /**
     * Returns an iterator for attributes.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator() : \ArrayIterator
    {
        return new \ArrayIterator($_SESSION);
    }

    /**
     * Remove the given keys from the old flash data.
     *
     * @param  array  $keys
     * @return void
     */
    protected function removeFromOldFlashData(array $keys)
    {
        $this->put('_flash.old', array_diff($this->get('_flash.old', []), $keys));
    }

    /**
     * Return decode session data
     * @param $session_data
     * @return array
     */
    public function decode($session_data) {
        $method = ini_get("session.serialize_handler");
        $return = array();
        $offset = 0;

        switch ($method) {
            case "php":
                while ($offset < strlen($session_data)) {
                    if (!strstr(substr($session_data, $offset), "|")) {
                        throw new \Exception("invalid data, remaining: " . substr($session_data, $offset));
                    }
                    $pos = strpos($session_data, "|", $offset);
                    $num = $pos - $offset;
                    $var = substr($session_data, $offset, $num);
                    $offset += $num + 1;
                    $data = unserialize(substr($session_data, $offset));
                    $return[$var] = $data;
                    $offset += strlen(serialize($data));
                }
                return $return;
                break;

            case "php_binary":
                while ($offset < strlen($session_data)) {
                    $num = ord($session_data[$offset]);
                    $offset += 1;
                    $var = substr($session_data, $offset, $num);
                    $offset += $num;
                    $data = unserialize(substr($session_data, $offset));
                    $return[$var] = $data;
                    $offset += strlen(serialize($data));
                }
                return $return;
                break;
            default:
                throw new \Exception("Unsupported session.serialize_handler: " . $method . ". Supported: php, php_binary");
        }
    }

}