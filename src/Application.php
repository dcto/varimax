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

/**
 * Class Application
 *
 * @package VM
 */
class Application extends \Illuminate\Container\Container
{
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
    ];

    /**
     * Bootstrap The Application
     */
    static public function boostrap()
    {
        static::setInstance($container = new static);

        $container->instance('app', static::$instance);

        $container->registerExceptionHandle();

        $container->registerConfigEnvironment();

        $container->registerServiceProviders();

        PHP_SAPI == 'cli' ? $container->cli() : $container->run();
    }

    /**
     * Resolve make services.
     *
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     */
    public function make($abstract, $parameters = []){
        if($this->bound($abstract)){
            return $this->resolve($abstract, $parameters);
        }else{
            $this->singleton($abstract, $this->getAlias($abstract));
            return $this->resolve($abstract, $parameters);
        }
    }


    /**
     * Register all of the config base service providers.
     *
     * @return void
     */
    protected function registerServiceProviders()
    {
        foreach($this->config['providers'] as $provider){
            $provider = new $provider($this);
            if (method_exists($provider, 'register')) {
                $provider->register();
            }
            if (!$provider->isDeferred() && method_exists($provider, 'boot')) {
                $provider->boot();
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