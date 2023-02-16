<?php

namespace VM;

/**
 * Varimax The Slim PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 21:49
 * SITE: https://www.varimax.cn/
 */

use VM\Exception\HttpException;
use VM\Exception\NotFoundException;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

/**
 * Class Application
 *
 * @package VM
 */
class Application extends Container
{
    /**
     * @var string
     */
    const VERSION = 'v1.9';

    /**
     * Application Booted
     * @var bool
     */
    protected $boot;

    /**
     * Application aliases
     *
     * @var array
     */
    protected $aliases =  array(
        'config'   => \VM\Config\Config::class,
        'router'    => \VM\Routing\Router::class,
        'request'   => \VM\Http\Request::class,
        'response'  => \VM\Http\Response::class,
        'redirect'  => \VM\Http\Redirect::class,
        'cookie'    => \VM\Http\Cookie::class,
        'session'   => \VM\Http\Session::class,
        'captcha'   => \VM\Captcha\Captcha::class,
        'cache'     => \VM\Cache\Cache::class,
        'crypt'     => \VM\Crypt\Crypt::class,
        'lang'      => \VM\I18n\Lang::class,
        'view'      => \VM\View::class,
        'curl'      => \VM\Http\Curl\Curl::class,
        'file'      => \VM\FileSystem\FileSystem::class,
        'log'       => \VM\Logger\Logger::class,
    );

    /**
     * Service Provider
     *
     * @var array
     */
    protected $services = array(
        'id'=>\VM\Services\HashIdsServiceProvider::class,
        'db' =>[
            \VM\Services\DatabaseServiceProvider::class,
            \VM\Services\PaginationServiceProvider::class
        ]
    );

    /**
     * Provider of loaded
     *
     * @var array
     */
    protected $providers = array();


    /**
     * Bootstrap The Application
     */
    public function boot()
    {
        static::setInstance($this);

        $this->instance('app', $this);

        $this->instance(static::class, $this);

        $this->registerExceptionHandle();

        $this->registerConfigEnvironment();

        $this->registerServiceProviders();

        $this->registerFacades();
        
        $this->boot = true;

        PHP_SAPI == 'cli' ? $this->cli() : $this->run();
    }

    /**
     * Resolve the given type from the container.
     *
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     */
    public function make($abstract, array $parameters = [])
    {
        if(!$this->bound($abstract) && isset($this->services[$abstract])){

            $this->register($this->services[$abstract]);

        }else if(!$this->bound($abstract = $this->getAlias($abstract))){

            $this->singleton($abstract);
        }

        return parent::make($abstract, $parameters);
    }

    /**
     * Get Application Config
     *
     * @param $key
     * @param $value
     * @param null $default
     * @return mixed
     */
    public function config($key, $value, $default = null)
    {
        return $this->make('config')->get($key, $value, $default);
    }

    /**
     * Register service provider to the application.
     *
     * @param  \Illuminate\Support\ServiceProvider|array|string  $provider
     * @param  bool $force
     * @return \Illuminate\Support\ServiceProvider
     */
    public function register($providers, $force = false)
    {
        foreach ((array) $providers as $provider){

            if (!$provider instanceof ServiceProvider) {
                $provider = new $provider($this);
            }

            if (isset($this->providers[$item = get_class($provider)]) && !$force) {
                 $this->providers[$item];
            }

            if (method_exists($provider, 'register')) {
                $provider->register();
            }

            $this->providers[$item] = $provider;

            if (!$provider->isDeferred()) {
                if (method_exists($provider, 'boot')) {;
                    $provider->boot();
                }
            }
        }
    }


    /**
     * Register Facades
     */
    protected function registerFacades()
    {
        Facade::setFacadeApplication($this);
    }

    /**
     * Register all of the config base service providers.
     *
     * @return void
     */
    protected function registerServiceProviders()
    {
        $providers = $this['config']['providers'];

        if(is_array($providers)) {
            foreach ($providers as $alias => $provider) {
                if (!is_int($alias)) {
                    $this->services[$alias] = $provider;
                } else {

                    $this->register($provider);
                }
            }
        }else{
            throw new \ErrorException('Invalid config of providers');
        }
    }

    /**
     * [registerExceptionHandling]
     *
     */
    private function registerExceptionHandle()
    {
        \VM\Exception\E::register();
    }

    /**
     * [registerSystemEnvironment]
     */
    private function registerConfigEnvironment()
    {
        /**
         * setting timezone
         */
        if (is_string($timezone = $this['config']['app.timezone'])) {
            date_default_timezone_set($timezone);
        }

        /**
         * setting charset
         */
        if (is_string($charset = $this['config']['app.charset'])) {
            mb_internal_encoding($charset);
        }

    }

    /**
     * Dispatch HTTP Request
     *
     * @return \VM\Http\Response\Response string
     * @throws \ErrorException
     */
    public function run()
    {
        /**
         * dispatch
         */
        $dispatch = $this->make('router')->load(root(_APP_,'routes.php'))->dispatch($this->make('request') , $this->make('response'));

        /**
         * @var $dispatch \VM\Http\Response\Response
         */
        if($dispatch instanceof \VM\Http\Response\Response) {
            return $dispatch->send();
        }
        
        return $this->make('response')->make($dispatch)->send();
    }


    /**
     * Dispatch Command Request
     * @todo resolve the command cli mode
     */
    public function cmd()
    {
        return;
    }

    /**
     * Dispatch Cli Mode Request
     */
    public function cli()
    {

        return;

    }

    /**
     * GET Varimax The Slim PHP Frameworks Version"
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }


    /**
     * @param $code
     * @param string $message
     * @param array $headers
     * @throws NotFoundException
     * @throws HttpException
     */
    public function abort($code, $message = '', array $headers = [])
    {
        if ($code == 404) {
            throw new NotFoundException($message);
        }

        throw new HttpException($code, $message, null, $headers);
    }
}