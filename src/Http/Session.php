<?php

namespace VM\Http;

class Session implements  \IteratorAggregate, \Countable
{
    /**
     * The session handler implementation.
     *
     * @var \SessionHandlerInterface
     */
    private $handler;

    /**
     * The session value encrypt
     * @var bool
     */
    private $encrypt = false;

    /**
     * Session store started status.
     *
     * @var bool
     */
    private $started = false;

    /**
     * @var array
     */
    private $options = array(
        'name',
        'referer_check',
        'serialize_handler',
        'use_cookies',
        'use_only_cookies',
        'use_trans_sid',
        'cache_limiter',
        'cookie_domain',
        'cookie_httponly',
        'cookie_lifetime',
        'cookie_path',
        'cookie_secure',
        'entropy_file',
        'entropy_length',
        'gc_divisor',
        'gc_maxlifetime',
        'gc_probability',
        'hash_bits_per_character',
        'hash_function',
        'upload_progress.enabled',
        'upload_progress.cleanup',
        'upload_progress.prefix',
        'upload_progress.name',
        'upload_progress.freq',
        'upload_progress.min-freq',
        'url_rewriter.tags'
    );

    /**
     * Create a new session instance.
     *
     * @param  string $name
     * @param  \SessionHandlerInterface $handler
     * @param  string|null $id
     * @return void
     */
    public function __construct($options = array())
    {
        $this->started = isset($_SESSION);

        $this->encrypt = config('session.encrypt', false);

        $this->setOptions(array_merge(config('session.options', []), $options));

        if(!$this->isStarted() && config('session.start')){

            $this->start();

        }
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {

        if($this->isStarted()) return $this;

        try {

            if (\PHP_SESSION_ACTIVE === session_status()) {
                throw new \RuntimeException('Failed to start the session: already started by PHP.');
            }

            if (ini_get('session.use_cookies') && headers_sent($file, $line)) {
                throw new \RuntimeException(sprintf('Failed to start the session because headers have already been sent by "%s" at line %d.', $file, $line));
            }

            $this->sessionHandler(config('session.driver'));

            session_start();

            $this->started = isset($_SESSION);

            return $this;

        }catch (\RuntimeException $e){
            throw new $e;

        }catch (\Throwable $e){
            throw new $e;
        }
    }

    /**
     * [sessionHandler]
     *
     * @param null $handler
     * @param array $options
     * @return mixed
     */
    private function sessionHandler($handler = null)
    {
        if($this->handler instanceof \SessionHandlerInterface){
           return $this->handler;
        }

        if(!in_array($handler, $handlers = array('files', 'redis', 'sqlite', 'memcached'))){
            throw new \InvalidArgumentException('Invalid '.$handler.' session handler, only supports  '. implode(',', $handlers));
        }

        app()->alias(__NAMESPACE__.'\\Session\\Handler\\'.ucfirst($handler).'SessionHandler','session.'.$handler);

        $this->handler = app('session.'. $handler);

        session_set_save_handler($this->handler, false);

        return $this->handler;
    }


    /**
     * get handler
     *
     * @return \SessionHandlerInterface
     */
    public function handler()
    {
        return $this->handler;
    }

    /**
     * Get or regenerate current session ID.
     *
     * @param bool $newId
     *
     * @return string|$this
     */
    public function id($id = null)
    {
        if($id) {
            session_id($id);
            return $this;
        }
        return session_id() ?: null;
    }


    /**
     * @return string
     */
    public function getId()
    {
        return $this->id();
    }


    /**
     * @param string $id
     * @return string
     */
    public function setId($id)
    {
        return $this->id($id);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        $keys = is_array($name) ? $name : func_get_args();

        return \Arr::exists($_SESSION, $keys);
    }

    /**
     * {@inheritdoc}
     */
    public function get($name, $default = null)
    {
        if($this->isStarted()) {

            $value = \Arr::get($_SESSION, $name, $default);

            return $value ? ($this->encrypt ? make('crypt')->de($value) : $value) : $default;
        }else{
            throw new \RuntimeException('Unable to automatic start session, manual operation start it\'s');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value)
    {
        $value = $this->encrypt ? make('crypt')->en($value) : $value;

        return \Arr::set($_SESSION, $name, $value);
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
        return isset($_SESSION) ? $_SESSION : array();
    }

    /**
     * remove alias
     * @param $name
     * @return mixed
     */
    public function del($name)
    {
       return $this->remove($name);
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
    public function save()
    {

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
     * @param $name
     * @return mixed
     */
    public function delete($name)
    {
        return $this->remove($name);
    }

    /**
     * remove session
     *
     * @param string $name
     */
    public function remove($name)
    {
        $name = is_array($name) ? $name : func_get_args();
        return array_forget($_SESSION, $name);
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

        return true;
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
    public function count()
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
        $_SESSION = array();
    }

    /**
     * Destroy the session.
     */
    public function destroy()
    {
        $this->started = false;
        if ($this->id()) {
            session_unset();
            session_destroy();
            session_write_close();
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 4200,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
        }
    }

    /**
     * 重新生成session_id
     * @param bool $delete 是否删除关联会话文件
     * @return bool
     */
    public function regenerate($delete = false)
    {
       return session_regenerate_id($delete);
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * Set option to session
     *
     * @see http://php.net/session.configuration
     *
     * @param $key
     * @param $value
     */
    public function option($key, $value)
    {
        ini_set(\Str::contains($key, 'session.') ? $key : 'session.'.$key, $value);
    }

    /**
     * Sets session.* ini variables.
     *
     * For convenience we omit 'session.' from the beginning of the keys.
     * Explicitly ignores other ini keys.
     *
     * @param array $options Session ini directives array(key => value)
     *
     * @see http://php.net/session.configuration
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            in_array($key, $this->options) && ini_set('session.'.$key, $value);
        }
    }

    /**
     * Returns an iterator for attributes.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator()
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