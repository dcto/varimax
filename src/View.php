<?php

/**
 * Varimax The Full Stack PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11-21:21
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
     * twig template dir
     * @var string
     */
    protected $dir = '.';

    /**
     *
     * @var
     */
    protected $twig;

    /**
     * twig template cache
     *
     * @var string
     */
    protected $cache;

    /**
     * twig file extension
     * @var string
     */
    protected $append;

    /**
     * 模板公共变量
     * @var array
     */
    protected $variables = array();

    /**
     * [init 初始化模板引擎]
     *
     * @return \Twig_Environment
     * @version v1
     *
     */
    public function __construct()
    {
        $this->dir = config('view.dir', _DIR_._DS_.'View');
        $this->cache = config('view.cache');
        $this->append = ltrim(config('view.append', 'twig'),'.');

        $loader = new \Twig_Loader_Filesystem($this->dir);

        /**
         * 添加模板路径
         * $loader->addPath($templateDir3);
         * $loader->prependPath($templateDir4);
         */
        $this->twig = new \Twig_Environment($loader, array(

            //用来保存编译后模板的绝对路径，缺省值为false，也就是关闭缓存。
            'cache' => config('view.cache', 'false'),

            //生成的模板会有一个__toString()方法，可以用来显示生成的Node（缺省为false）
            'debug' => false,

            //当用Twig开发时，是有必要在每次模板变更之后都重新编译的。如果不提供一个auto_reload参数，他会从debug选项中取值
            'auto_reload' => getenv('DEBUG')? true : false,

            //模板的字符集，缺省为utf-8。
            'charset' => config('app.charset', 'utf-8'),

            //如果设置为false，Twig会忽略无效的变量（无效指的是不存在的变量或者属性/方法），并将其替换为null。如果这个选项设置为true，那么遇到这种情况的时候，Twig会抛出异常。
            'strict_variables' => false,

            /**
             * 如果设置为true, 则会为所有模板缺省启用自动转义（缺省为true）。
             * 在Twig 1.8中，可以设置转义策略（html或者js，要关闭可以设置为false）。
             * 在Twig 1.9中的转移策略，可以设置为css，url，html_attr，甚至还可以设置为回调函数。
             * 该函数需要接受一个模板文件名为参数，且必须返回要使用的转义策略，回调命名应该避免同内置的转义策略冲突。
             */
            'autoescape' => true,

            /**
             * 用于指出选择使用什么优化方式（缺省为-1，代表使用所有优化；设置为0则禁止）。
             */
            'optimizations' => -1,
        ));

        /*
        $lexer = new \Twig_Lexer($this->twig, array(
            'tag_comment' => array('{#', '#}'),
            'tag_block' => array('{%', '%}'),
            'tag_variable' => array('{^', '^}'),
            'interpolation' => array('#{', '}'),
        ));
        $this->twig->setLexer($lexer);
        */
        /**
         * 注册扩展方法
         * @var \Twig_Environment
         *
         * $this->twig = new Twig_Environment($loader,array('debug'=>true));
         * $this->twig->addExtension(new Twig_Extension_Debug());
         */
        /**
         * 注册全局变量
         */
        $this->twig->addGlobal('_VM_', _VM_);
        $this->twig->addGlobal('_APP_',_APP_);
        $this->twig->addGlobal('lang', app('lang'));
        $this->twig->addGlobal('router', app('router')->route());
        $this->twig->addGlobal('request', app('request'));

        //注册模板扩展
        //$this->twig->addExtension(new \nochso\HtmlCompressTwig\Extension());

        /**
         * 注册全局可用php函数
         * @example {{php_function()}}
         */
        $this->twig->addFunction(new \Twig_SimpleFunction('php_*',
            function() {
                $args = func_get_args();

                $function = array_shift($args);

                return call_user_func_array($function, $args);
            },
            array('pre_escape' => 'html', 'is_safe' => array('html'))
            )
        );

        /**
         * 注册全局可用数
         * @example {{php_function()}}
         */
        $this->twig->addFunction(new \Twig_SimpleFunction('*',
                function() {
                    $args = func_get_args();

                    $function = array_shift($args);

                    return call_user_func_array($function, $args);
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
        $this->twig->addFunction(new \Twig_SimpleFunction('dump', $dump,  array('pre_escape' => 'html', 'is_safe' => array('html'))));

        /**
         * [$debug 注册debug函数]
         * @var [type]
         */
        $debug = function($variable){
            echo "<pre>".print_r($variable)."</pre>";
        };

        $this->twig->addFunction(new \Twig_SimpleFunction('debug', $debug, array('pre_escape' => 'html', 'is_safe' => array('html'))));

        /**
         * 注册过滤器
         */
        $this->twig->addFilter(new \Twig_SimpleFilter('dump', $dump));
        $this->twig->addFilter(new \Twig_SimpleFilter('debug', $debug));

        /**
         * [$suffix 截取字符串]
         * @var [type]
         */
        $this->twig->addFilter(new \Twig_SimpleFilter('len',function($string, $length, $suffix = false){
            return $string = mb_strlen($string)>$length
            ? ($suffix ? mb_substr($string, 0, $length).$suffix : mb_substr($string, 0, $length))
            : $string;
        }));


        return $this->twig;
    }

    /**
     * paths for twig
     * @return View
     */
    public function paths()
    {
        return $this->path(func_get_args());
    }

    /**
     * set path for twig
     * @param $paths
     * @param bool $keep keep old path
     */
    public function path($paths, $keep = true)
    {
        if (!\is_array($paths)) {
            $paths = [$paths];
        }

        $keep && $paths = array_merge($paths, $this->twig()->getLoader()->getPaths()) ;

        $this->twig()->getLoader()->setPaths($paths);

        return $this;
    }

    /**
     * set view cache dir
     * @param $dir
     * @return $this
     */
    public function cache($dir)
    {
        $this->twig()->setCache($dir);

        return $this;
    }

    /**
     * getTwig
     *
     * @return \Twig_Environment
     */
    public function twig()
    {
        return $this->twig;
    }

    /**
     * [assign 模板变量赋值方法]
     *
     * @param      $var [变量名]
     * @param null $val [变量值]
     */
    public function assign($var, $val = null)
    {
        if(is_array($var))
        {
            foreach($var as $key => $v)
            {
                $this->variables[$key] = $v;
            }
        }else{
            $this->variables[$var] = $val;
        }
    }

    /**
     * [render 模板渲染]
     *
     * @param $template
     * @param $variable
     * @return string
     */
    public function render($template, $variables)
    {
        $template = sprintf($template .'%s'. $this->append, '.');
        $variables = array_merge($this->variables, (array) $variables, (array) Controller::$assign);
        return $this->twig()->load($template)->render($variables);
    }

    /**
     * [show]
     *
     * @param $template
     * @param $variable
     */
    public function show($template, $variables)
    {
        return make('response')->make($this->render($template, $variables));
    }

    /**
     * flush cache
     * @return mixed
     */
    public function flush()
    {
        return make('file')->cleanDirectory(config('view.cache'));
    }
}
