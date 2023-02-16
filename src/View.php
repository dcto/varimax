<?php

/**
 * Varimax The Slim PHP Frameworks.
 * varimax
 * FILE: 2023
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2023-02-16
 * SITE: https://www.varimax.cn/
 */

namespace VM;

/**
 * Class View
 *
 * @package VM
 */
class View {
    /***
     * template paths
     * @var array
     */
    protected $dirs = [];

    /**
     * template cache
     *
     * @var string
     */
    protected $cache;

    /**
     * template variables
     * @var array
     */
    protected $assign = [];

    /**
     * template renderer
     * @var \Windwalker\Renderer\AbstractEngineRenderer
     */
    protected $engine = null;

    /**
     * Init view engine
     */
    public function __construct($engine = null)
    {
        $this->dirs = config('view.dir', _DIR_._DS_.'View');
        $this->cache = config('view.cache', runtime('view'));
        $this->engine =  $this->engine($engine ? $engine : config('view.engine', 'blade'));
        is_dir($this->cache) || mkdir($this->cache, 0777, true);
    }

    /**
     * add path to dirs
     * @return self
     */
    public function dir()
    {
        $this->dirs = array_merge($this->dirs, func_get_args());
        return $this;
    }

    /**
     * set view cache dir
     * @param $dir
     * @return $this
     */
    public function cache($dir)
    {
        $this->cache = $dir;
        return $this;
    }
    
    /**
     * [assign 模板变量赋值方法]
     *
     * @param      $var [变量名]
     * @param null $val [变量值]
     */
    public function assign($var, $val = null)
    {
        if(is_array($var)){
            $this->assign = array_merge_recursive($this->assign, $var);
        }else{
            $this->assign[$var] = $val;
        }
    }

    /**
     * set view engine
     * @param $engine string
     * @return 
     */
    private function engine($engine = null)
    {
       if($engine){
        if(!in_array($engine, $engines = ['php', 'edge', 'twig', 'blade', 'plates', 'mustcache'])){
            throw new \ErrorException('Only support ['. join(',',$engines) .'] template engine');
        }
        $this->$engine();
       }
       return $this->engine;
    }

    /**
     * [render 模板渲染]
     *
     * @param $template
     * @param $variable
     * @return string
     */
    public function render($template, array $variables = [])
    {
        return $this->engine()->render($template, array_merge($this->assign, Controller::$assign, $variables));
    }

    /**
     * flush cache
     * @return mixed
     */
    public function flush()
    {
        return make('file')->cleanDirectory($this->cache);
    }

    /**
     * [display 模版展示]
     *
     * @param $template
     * @param $variable
     */
    public function display($template, array $variables = [])
    {
        return make('response')->make($this->render($template, $variables));
    }

    /**
     * [Php 模板引擎]
     * @return \Windwalker\Renderer\PhpRenderer
     * @version 20230215
     */
    public function php()
    {
        $this->engine = new \Windwalker\Renderer\PhpRenderer($this->dirs);
        return $this;
    }

    /**
     * [Edge 模板引擎]
     * @return \Windwalker\Renderer\EdgeRenderer
     * @version 20230215
     */
    public function edge()
    {
        $this->engine = new \Windwalker\Renderer\EdgeRenderer($this->dirs, ['cache_path' => $this->cache]);
        return $this;
    }

    /**
     * [Blade 模板引擎]
     * @return \Windwalker\Renderer\BladeRenderer
     * @version 20230215
     */
    public function blade()
    {
        $this->engine = new \Windwalker\Renderer\BladeRenderer($this->dirs, ['cache_path' => $this->cache]);
        return $this;
    }

    /**
     * [plates 模板引擎]
     * @return \Windwalker\Renderer\PlatesRenderer
     * @version 20230215
     */
    public function plates()
    {
        $this->engine = new \Windwalker\Renderer\PlatesRenderer($this->dirs);
        return $this;
    }

    /**
     * [Mustache 模板引擎]
     * @return \Windwalker\Renderer\MustacheRenderer
     * @version 20230215
     */
    public function mustache()
    {
        $this->engine = new \Windwalker\Renderer\MustacheRenderer($this->dirs,[
            'cache' => $this->cache,
            'cache_file_mode' => 0666,
            'cache_lambda_templates' => true,
            'delimiters' => '<% %>',
        ]);
        return $this;
    }
    
    /**
     * [twig 模板引擎]
     * @return \Windwalker\Renderer\TwigRenderer
     * @version v2.*
     */
    public function twig(){
        /**
         * @var \Twig_Environment $this->engine 
         */
        $this->engine = new \Windwalker\Renderer\TwigRenderer($this->dirs, array(

            //生成的模板会有一个__toString()方法，可以用来显示生成的Node（缺省为false）
            'debug' => config('view.debug', false),

            //用来保存编译后模板的绝对路径，缺省值为false，也就是关闭缓存。
            'cache' => config('view.cache', false),

            //当用Twig开发时，是有必要在每次模板变更之后都重新编译的。如果不提供一个auto_reload参数，他会从debug选项中取值
            'auto_reload' => config('view.reload', false),

            //模板的字符集，缺省为utf-8。
            'charset' => config('app.charset', 'utf-8'),

            //如果设置为false，Twig会忽略无效的变量（无效指的是不存在的变量或者属性/方法），并将其替换为null。如果这个选项设置为true，那么遇到这种情况的时候，Twig会抛出异常。
            'strict_variables' => false,

            /**
             * 在Twig 1.8中，可以设置转义策略（html或者js，要关闭可以设置为false）。
             * 在Twig 1.9中的转移策略，可以设置为css，url，html_attr，甚至还可以设置为回调函数。
             * 该函数需要接受一个模板文件名为参数，且必须返回要使用的转义策略，回调命名应该避免同内置的转义策略冲突。
             */
            'autoescape' => config('view.autoescape', 'html'),

            /**
             * 用于指出选择使用什么优化方式（缺省为-1，代表使用所有优化；设置为0则禁止）。
             */
            'optimizations' => -1,
        ));

        /**
         * 注册全局变量
         * @var \Twig_Environment $this->engine
         */
        $this->engine->getEngine()->addGlobal('_VM_', _VM_);
        $this->engine->getEngine()->addGlobal('_APP_',_APP_);
        $this->engine->getEngine()->addGlobal('lang', app('lang'));
        $this->engine->getEngine()->addGlobal('route', app('router')->route());
        $this->engine->getEngine()->addGlobal('router', app('router'));
        $this->engine->getEngine()->addGlobal('request', app('request'));

        //注册模板扩展
        //$this->engine->getEngine()->addExtension(new \nochso\HtmlCompressTwig\Extension());

        /**
         * 注册全局可用函数
         * @example {{ function() }}
         */
        $this->engine->getEngine()->addFunction(new \Twig\TwigFunction('*',
                function(...$args){
                    return call_user_func_array(array_shift($args), $args);
                },
                array('pre_escape' => 'html', 'is_safe' => array('html'))
            )
        );

        /**
         * [$dump 注册调试函数]
         * @var [type]
         */
        $dump = function($variable){
               echo '<pre>'.var_dump($variable).'</pre>';
        };
        $this->engine->getEngine()->addFunction(new \Twig\TwigFunction('dump', $dump,  array('pre_escape' => 'html', 'is_safe' => array('html'))));

        /**
         * [$debug 注册debug函数]
         * @var [type]
         */
        $debug = function($variable){
            echo "<pre>".print_r($variable)."</pre>";
        };

        $this->engine->getEngine()->addFunction(new \Twig\TwigFunction('debug', $debug, array('pre_escape' => 'html', 'is_safe' => array('html'))));

        /**
         * 注册过滤器
         */
        $this->engine->getEngine()->addFilter(new \Twig\TwigFilter('dump', $dump));
        $this->engine->getEngine()->addFilter(new \Twig\TwigFilter('debug', $debug));

        /**
         * [$suffix 截取字符串]
         * @var [type]
         */
        $this->engine->getEngine()->addFilter(new \Twig\TwigFilter('len',function($string, $length, $suffix = false){
            return $string = mb_strlen($string)>$length
            ? ($suffix ? mb_substr($string, 0, $length).$suffix : mb_substr($string, 0, $length))
            : $string;
        }));

        return $this;
    }

}
