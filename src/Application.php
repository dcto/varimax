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
    /**
     * Application aliases items
     */
    protected $aliases = [
        'app'       => \VM\Application::class,
        'config'    => \VM\Config\Config::class,
        'router'    => \VM\Routing\Router::class,
        'request'   => \VM\Http\Request::class,
        'response'  => \VM\Http\Response::class,
        'redirect'  => \VM\Http\Redirect::class,
        'cookie'    => \VM\Http\Cookie::class,
        'session'   => \VM\Http\Session::class,
        'cache'     => \VM\Cache\Cache::class,
        'crypt'     => \VM\Crypt\Crypt::class,
        'lang'      => \VM\I18n\Lang::class,
        'curl'      => \VM\Http\Curl\Curl::class,
        'file'      => \VM\FileSystem\FileSystem::class,
        'log'       => \VM\Logger\Logger::class
    ];

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
     * @return mixed
     */
    public function make($abstract, $parameters = []){
        if(!$this->bound($abstract = $this->getAlias($abstract))){
            $this->singleton($abstract);
        }
        return parent::make($abstract, $parameters);
    }

    /**
     * Register a service provider with the application
     * @param $service 
     * @return void
     */
    public function register($service){
       
        if($this->resolved($service)) return true;
        
        $service = new $service($this);

        method_exists($service, 'register') && $service->register();

        if (!$service->isDeferred() && method_exists($service, 'boot')) {

            $service->callBootingCallbacks();

            $this->call([$service, 'boot']);

            $service->callBootedCallbacks();
        }
    }

    /**
    * Register all of aliases
    * @return void 
    */
    private function regiseterAbstractAliases(){
        foreach($this->aliases as $alias => $abstract){
            $this->alias($abstract, $alias);
        }
    }

    /**
     * Register all of the config base service providers.
     *
     * @return void
     */
    protected function registerServiceProviders()
    {
        array_map([$this, 'register'], $this->config['service']+[
            \VM\Services\MacroableServiceProvider::class,
            \Illuminate\Events\EventServiceProvider::class,
        ]);
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
            ->through(array_merge($this['config']['pipeline'], $route->pipeline))
            ->then(fn()=>$route->fire())->prepare($this->request)->send();
        });
    }
}