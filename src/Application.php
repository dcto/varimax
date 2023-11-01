<?php
/**
 * Varimax The Slim PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 21:49
 * SITE: https://www.varimax.cn/
 */
namespace VM;
/**
 * Class Application
 *
 * @package VM
 */
class Application extends \Illuminate\Container\Container
{    
    /**
     * Bootstrap The Application
     */
    static public function boostrap()
    {
        static::setInstance(new static);

        static::$instance->regiseterAbstractAliases();

        static::$instance->registerConfigEnvironment();

        static::$instance->registerExceptionHandle();

        static::$instance->registerServiceProviders();

        \App::setFacadeApplication(static::$instance);
    
        PHP_SAPI == 'cli' ? static::$instance->cli() : static::$instance->run();
    }

    /**
     * Make an instance of the applicationr
     * @param string $abstract
     * @param array $parameters
     * @param bool $events 
     * @return object
     */
    public function make($abstract, $parameters = [], $events = false){
        /**
         * bind abstract
         */
        $this->singletonIf($abstract = $this->getAlias($abstract));
        /**
         * resolve $abstract with parameters
         */
        return $this->resolve($abstract, $parameters, $events || !$this->resolved($abstract)); 
    }

    /**
     * Register service provider with the application
     * @param string $service 
     * @param bool $reboot
     * @return void
     */
    public function register($service, $reboot = false){
        if($this->bound($service) && !$reboot) return true;
        /**
         * @var \VM\Services\ServiceProvider
         */
        $service = new $service($this);

        method_exists($service, 'register') && $service->register();
        
        if($service->isDeferred()){
            $this->resolving(array_key_last($this->bindings), 
            fn()=>$this->bootServiceProvider($service));
        }else{
           $this->bootServiceProvider($service);
        }
    }

    /**
     * Bootstrap service provider
     * @param \VM\Services\ServiceProvider
     * @return void
     */
    protected function bootServiceProvider($provider)
    {
        $provider->callBootingCallbacks();
        method_exists($provider, 'boot') && $this->call([$provider, 'boot']);
        $provider->callBootedCallbacks();
    }

    /**
    * Register all of aliases
    * @return void 
    */
    private function regiseterAbstractAliases(){
        array_map(function($abstract){
            $this->alias(__NAMESPACE__.'\\'.$abstract, strtolower(class_basename($abstract)));
        },['Config\Config','Cache\Cache','Crypt\Crypt','Routing\Router',
            'Http\Request','Http\Response','Http\Session','Http\Cookie','Http\Curl\Curl',
            'I18n\Lang','Logger\Log','FileSystem\File']);
    }

    /**
     * Register all of the config base service providers.
     *
     * @return void
     */
    private function registerServiceProviders()
    {
        array_map([$this, 'register'], array_merge([
            \VM\Services\PaginationServiceProvider::class,
            \VM\Services\DatabaseServiceProvider::class,
        ], (array) $this->config['service']));
    }

    /**
     * Dispatch Command Request
     * @todo resolve the command cli mode
     */
    private function registerConsoleCommand()
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
    protected function cli()
    {
        _APP_ == 'varimax' && $this->registerConsoleCommand();
    }

    /**
     * Dispatch Request To Response 
     * @return \VM\Http\Response
     */
    protected function run()
    {
        return $this->router->through(app_dir('routes'),  function($route){
            (new \VM\Pipeline($this))->send($this->request)
            ->through(array_replace($this['config']['pipeline'], $route->pipeline))
            ->then(fn()=>$route->fire())->prepare($this->request)->send();
        });
    }
}