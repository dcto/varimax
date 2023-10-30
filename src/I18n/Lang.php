<?php
/**
 * Varimax The Slim PHP Frameworks.
 * varimax.cn
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-19 16:50
 * SITE: https://www.varimax.cn/
 */

namespace VM\I18n;

/**
 * @package VM\I18n
 */
class Lang implements \ArrayAccess
{
    /**
     * @var string
     */
    protected $i18n;

    /**
     * Language Package
     *
     * @var array
     */
    protected $item = array();

    /**
     * Temp load keys
     * @var array
     */
    protected $keys = array();

    /**
     * Temp with args
     * @var array
     */
    protected $args = array();

    /**
     * Lang constructor.
     */
    public function __construct()
    {
        $this->load($this->i18n());
    }

    /**
     * @param null $lang
     * @return mixed
     */
    public function i18n($lang = null)
    {
        return $this->locale($lang);
    }


    /**
     * Get lang or set lang
     * @param null $lang
     * @return mixed
     */
    public function locale($lang = null)
    {
        return $lang ? $this->setLocale($lang) : $this->getLocale();
    }

    /**
     * Set locale
     * @param $lang
     * @return $this
     */
    public function setLocale($lang)
    {
        $this->i18n = $lang;
        app('cookie')->set('i18n', $lang);
        return $this;
    }

    /**
     * Get locale
     *
     * @return mixed
     */
    public function getLocale()
    {
        if(isset($_GET['i18n']) && $i18n = $_GET['i18n']) {
            $this->setLocale($i18n);
        }else{
            if(PHP_SAPI != 'cli' ){
                $this->i18n = $this->i18n ?: route()->lang();
                $this->i18n = $this->i18n ?: app('cookie')->get('i18n');
                $this->i18n = $this->i18n ?: $this->detect();
            }
            $this->i18n = config('i18n.'. $this->i18n) ? $this->i18n : config('app.language', key((array) config('i18n')));
        }
        return $this->i18n;
    }

    /**
     *
     * Detect language from browser
     *
     * @return mixed|string
     */
    public function detect()
    {
        $language = app('request')->language();
        if($i18ns = config('i18n')){
            foreach ($i18ns as $locale) {
                if (strstr($language, $locale)) {
                    return $locale;
                }
            }
        }
        return $language;
    }

    /**
     * Determine if the given configuration value exists.
     * @param  string  $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->item[$key]);
    }

    /**
     * Get the specified configuration value.
     *
     * @param string $key
     * @param array $args
     * @return string 
     */
    public function get($key, ...$args)
    {
        return strstr($key, ',') ? implode('', array_map(function($k)use($args){
           return $this->take($k, $args);
       }, explode(',', $key))) : $this->take($key, $args);
    }

    /**
     * Set a given configuration value.
     *
     * @param  array|string  $key
     * @param  mixed  $value
     * @return self
     */
    public function set($key, $value = null)
    {
        $item = is_array($key) ? $key : [$key => $value];
        $item = array_dot($item, '', '.');
        $this->item = array_merge($this->item, $item);
        return $this;
    }

    /**
     * add lang onto item value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return self
     */
    public function add($key, $value = null)
    {
        return $this->set($key, $value);
    }

    /**
     * raw the language array
     * @param string $key
     * @param mixed $default
     * @return array
     */
    public function raw($key, $default = '')
    {
        return $key ? data_get(array_undot($this->item), $key, $default) : array_undot($this->item);
    }
    
    /**
     * get raw of $this->item;
     *
     * @return array
     */
    public function all()
    {
        return $this->item;
    }
    
    /**
     * load language
     *
     * @param null $lang
     * @return $this
     * @throws \ErrorException
     */
    public function load($lang, $reload = false)
    {
        $cache = runtime('lang',_APP_, $lang.'.php');
        if (!$reload && !config('app.debug', getenv('DEBUG')) && is_file($cache)) {
            $this->item = require($cache);
        } else {
            if (!is_dir($cache_dir = dirname($cache))) {
                if (!mkdir($cache_dir, 0755, true)) {
                    throw new \ErrorException('Can not create i18n directory ' . $cache_dir);
                }
            }
            is_file($file = root('i18n', $lang.'.ini')) && $this->set(parse_ini_file($file, true));
            is_file($file = app_dir('I18n', $lang.'.ini')) && $this->set(parse_ini_file($file, true));
            file_put_contents($cache, '<?php return '. str_replace([" ", PHP_EOL], '', var_export($this->all(), TRUE)).';');
        }
        return $this;
    }

    /**
     * flush cache
     */
    public function flush()
    {
        return make('file')->cleanDirectory(runtime('lang'));
    }

    
    /**
     * Take lang string
     * @param $key
     * @param $args
     * @return string
     */
    protected function take($key, $args = [])
    {
        $this->keys = $this->args = [];
        if(isset($this->item[$key])){
            return $args ? $this->replacements($this->item[$key], $args) : str_replace('%s', '', $this->item[$key]);
        }
        return $key;
    }

    /**
     * Make the place-holder replacements on a line.
     *
     * @param  string  $line
     * @param  array   $replaces
     * @return string
     */
    protected function replacements($lang, array $args)
    {
        return vsprintf($lang, array_pad($args, substr_count($lang,'%s'), ''));
    }

    /**
     * Determine if the given configuration option exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function offsetExists($key) : bool
    {
        return $this->has($key);
    }

    /**
     * Get a configuration option.
     *
     * @param  string  $key
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        array_push($this->keys, $key);
        return $this;
    }

    /**
     * Set a configuration option.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value) : void
    {
        $this->set($key, $value);
    }

    /**
     * Unset a configuration option.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key) : void
    {
        unset($this->item[$key]);
    }

    /**
     * 动态调用 (lang->alert()->id())
     * @param $name
     * @param $arguments
     * @return mixed|string
     */
    public function __call($key, $args)
    {
        array_push($this->keys, $key);
        $this->args = $args;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->take(implode('.', $this->keys), $this->args);
    }
}