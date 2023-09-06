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
     * Application name;
     */
    protected $application = null;
    
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
        'captcha'   => \VM\Captcha\Captcha::class,
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
     * @param $provider 
     * @return void
     */
    public function register($provider){
       
        $provider = new $provider($this);

        method_exists($provider, 'register') && $provider->register();

        if (!$provider->isDeferred() && method_exists($provider, 'boot')) {

            $provider->callBootingCallbacks();

            $this->call([$provider, 'boot']);

            $provider->callBootedCallbacks();
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
        $this->register(\Illuminate\Events\EventServiceProvider::class);

        if(is_array($this->config['providers'])){
            foreach($this->config['providers'] as $provider){
                $this->register($provider);
            }
        }
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
        $this['application'] == 'varimax' && $this->registerConsoleCommand();
    }

    /**
     * Dispatch HTTP
     *
     * @return \VM\Http\Response\Response string
     * @throws \ErrorException
     */
    protected function run()
    {
        return $this->router->load(app_path('routes'), function($router){
            $dispatch = $router->dispatch($this->request, $this->response);
            if($dispatch instanceof \VM\Http\Response\Response) {
               return $dispatch->send();
            }
            return $this->response->make($dispatch)->send();
        });
    }
}