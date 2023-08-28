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
     * Application Booted
     * @var bool
     */
    protected $boot;

    /**
     * Application aliases
     *
     * @var array
     */
    protected $aliases = [
        'config'    => \VM\Config\Config::class,
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
        'curl'      => \VM\Http\Curl\Curl::class,
        'file'      => \VM\FileSystem\FileSystem::class,
        'log'       => \VM\Logger\Logger::class,
        'arr'       => \Illuminate\Support\Arr::class,
        'str'       => \Illuminate\Support\Str::class,
    ];

    /**
     * Service Provider
     *
     * @var array
     */
    protected $services = [
        'id'=>\VM\Services\HashIdsServiceProvider::class,
        'db' =>[\VM\Services\DatabaseServiceProvider::class, \VM\Services\PaginationServiceProvider::class]
    ];

    /**
     * Provider of loaded
     *
     * @var array
     */
    protected $providers = [
    ];

    /**
     * Bootstrap The Application
     */
    static public function boostrap()
    {
        static::setInstance($container = new self);

        $container->instance('app', static::$instance);

        $container->instance(static::class, $container);

        $container->registerExceptionHandle();

        $container->registerConfigEnvironment();

        $container->registerServiceProviders();

        $container->registerFacades();
        
        $container->boot = true;

        PHP_SAPI == 'cli' ? $container->cli() : $container->run();
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
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * @param  bool $force
     * @return \Illuminate\Support\ServiceProvider
     */
    public function register($providers, $force = false)
    {   
        foreach((array) $providers as $provider){
            if (!$provider instanceof ServiceProvider || $force) {
                $provider = new $provider($this);
                if (method_exists($provider, 'register')) {
                    $provider->register();
                }
            }
            
            $this->providers[get_class($provider)] = $provider;

            if (!$provider->isDeferred() && method_exists($provider, 'boot')) {
                $provider->boot();
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
        $this->register(\Illuminate\Events\EventServiceProvider::class);
        if(is_array($providers = $this['config']['providers'])){
            foreach ($providers as $alias => $provider) {
                if (!is_int($alias)) {
                    $this->services[$alias] = $provider;
                } else {
                    $this->register($provider);
                }
            }
        }
    }

    /**
     * Dispatch Command Request
     * @todo resolve the command cli mode
     */
    public function registerConsoleCommand()
    {
        \VM\Console\Command::register();
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
     * Dispatch Cli Mode Request
     */
    public function cli()
    {
        'varimax'==_APP_ && $this->registerConsoleCommand();
    }

    /**
     * Dispatch HTTP
     *
     * @return \VM\Http\Response\Response string
     * @throws \ErrorException
     */
    public function run()
    {
        return with($this->router->load(app_path('routes')), function($router){
            $dispatch = $router->dispatch($this->request, $this->response);
            if($dispatch instanceof \VM\Http\Response\Response) {
               return $dispatch->send();
            }
            return $this->response->make($dispatch)->send();
        });
    }
}