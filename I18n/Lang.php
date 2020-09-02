<?php

/**
 * Varimax The Full Stack PHP Frameworks.
 * varimax.cn
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-19 16:50
 * SITE: https://www.varimax.cn/
 */

namespace VM\I18n;


class Lang
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
     * 临时语言选择器
     * @var array
     */
    protected $keys = array();

    /**
     * 当前临时调用参数
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
     * @param null $lang
     * @return mixed
     */
    public function locale($lang = null)
    {
        return $lang ? $this->setLocale($lang) : $this->getLocale();
    }


    /**
     * Set locale
     *
     * @param $lang
     * @return $this
     */
    public function setLocale($lang)
    {
        $this->i18n = $lang;

        make('cookie')->set('i18n', $lang);

        return $this;
    }

    /**
     * Get locale
     *
     * @return mixed
     */
    public function getLocale()
    {
        if($i18n = make('request')->get('i18n')) {

            $this->setLocale($i18n);

        }else{

            $i18n = make('cookie')->get('i18n');
            $i18n = $i18n ?: make('router')->route()->lang();
            $i18n = $i18n ?: config('i18n.'.$i18n = $this->detect()) ? $i18n : null;
            $i18n = $i18n ?: config('app.language.'.$i18n, 'zh-CN');
        }

        return $this->i18n = $i18n;
    }


    /**
     *
     * Detect language of system
     *
     * @return mixed|string
     */
    public function detect()
    {
        $language = make('request')->language();

        if(strstr($language, '_')){
            return str_replace('_', '-', $language);
        }else {
            foreach (config('i18n') as $locale => $name) {
                if (strstr($language, $locale)) {
                    return $locale;
                }

            }
        }
        return $language;
    }

        /**
         * Determine if the given configuration value exists.
         *
         * @param  string  $key
         * @return bool
         */
    public function has($key)
    {
        return Arr::has($this->item, $key);
    }

    /**
     * Get the specified configuration value.
     *
     * @param  array|string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $args = func_get_args();

        $key = array_shift($args);

        return $this->take($key, $args);

        //return Arr::get($this->item, $key, $default);
    }

    /**
     * Set a given configuration value.
     *
     * @param  array|string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function set($key, $value = null)
    {

        $item = is_array($key) ? $key : [$key => $value];

        $item =\Arr::dot($item, '', '.');

        $this->item = array_merge($this->item, $item);

        return $this;

    }

    /**
     * restore lang to array
     *
     * @return array
     */
    public function arr($key = null)
    {
        $item = \Arr::undot($this->item);

        return $key ? \Arr::get($item, $key) : $item;
    }

    /**
     * Prepend a value onto an array configuration value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function prepend($key, $value)
    {
        $array = $this->get($key);

        array_unshift($array, $value);

        $this->set($key, $array);

        return $this;
    }

    /**
     * Push a value onto an array configuration value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function push($key, $value)
    {
        $array = $this->get($key);

        $array[] = $value;

        $this->set($key, $array);

        return $this;
    }

    /**
     * Take lang string
     * @param $key
     * @param $args
     * @return mixed|string
     */
    public function take($key, $args)
    {
        if(isset($this->item[$key])){
            return $args ? $this->replacements($this->item[$key], $args) : $this->item[$key];
        }
        return $key;
    }

    /**
     * Get all of the configuration item for the application.
     *
     * @return array
     */
    public function all()
    {
        return $this->item;
    }


    /**
     * To json
     * @return string
     */
    public function json($key = null)
    {
        if($key){
            return json_encode(\Arr::get($this->item, $key));
        }

        return json_encode($this->all());
    }

    /**
     * Make the place-holder replacements on a line.
     *
     * @param  string  $line
     * @param  array   $replaces
     * @return string
     */
    private function replacements($lang, array $args)
    {
        $args = array_pad($args, substr_count($lang,'%s'), '');

        return vsprintf($lang, $args);
    }

    /**
     * Determine if the given configuration option exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Get a configuration option.
     *
     * @param  string  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        echo $key;
        return $this->get($key);
    }

    /**
     * Set a configuration option.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Unset a configuration option.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->set($key, null);
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
        try {

            $cache = runtime('lang',_APP_, $lang.'.php');

            if (!$reload && !config('app.debug', getenv('DEBUG')) && is_file($cache)) {

                $this->item = require($cache);

            } else {

                if (!is_dir($cache_dir = dirname($cache))) {
                    if (!mkdir($cache_dir, 0755, true)) {
                        throw new \ErrorException('Can not create i18n dir ' . $cache_dir);
                    }
                }

                if(is_file($file = root('i18n', $lang.'.ini'))) {
                    $this->set(parse_ini_file($file, true));
                }

                if(is_file($file = root(_APP_._DS_.'i18n'._DS_.$lang.'.ini'))){
                    $this->set(parse_ini_file($file, true));
                }


                file_put_contents($cache, '<?php return ' . str_replace(array("\r\n", "\n", "\r", "\t", " "), '', var_export($this->all(), TRUE)).';');

                return $this;
            }
        }catch (\Exception $e){
            throw new \InvalidArgumentException('Unable Load ['.$lang.'] Language Package ');
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
     * 动态方法调用语言包 (lang->alert()->id())
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
        $lang = $this->take(implode('.', $this->keys), $this->args);
        $this->keys = $this->args = array();
        return (string) $lang;
    }
}