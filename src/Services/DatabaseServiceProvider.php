<?php

namespace VM\Services;

/**
 * Varimax The Slim PHP Frameworks.
 * varimax.cn
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-19 15:42
 * SITE: https://www.varimax.cn/
 */
use VM\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Query\Builder;
use Illuminate\Contracts\Queue\EntityResolver;
use Illuminate\Database\Eloquent\QueueEntityResolver;
use Illuminate\Database\Connectors\ConnectionFactory;

class DatabaseServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        Model::setConnectionResolver($this->app['db']);

        Model::setEventDispatcher($this->app['events']);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        Model::clearBootedModels();
        $this->registerConnectionServices();
        $this->registerQueueableEntityResolver();
        $this->registerQueryEvents();
        $this->registerDbQueryLogs();
    }

    /**
     * Register the primary database bindings.
     *
     * @return void
     */
    protected function registerConnectionServices()
    {
        // The connection factory is used to create the actual connection instances on
        // the database. We will inject the factory into the manager so that it may
        // make the connections while they are actually needed and not of before.
        $this->app->singleton('db.factory', function ($app) {
            return new ConnectionFactory($app);
        });

        // The database manager is used to resolve various connections, since multiple
        // connections might be managed. It also implements the connection resolver
        // interface which may be used by other components requiring connections.
        $this->app->singleton('db', function ($app) {
            return new \Illuminate\Database\DatabaseManager($app, $app['db.factory']);
        });

        $this->app->bind('db.connection', function ($app) {
            return $app['db']->connection();
        });

        $this->app->singleton('db.transactions', function ($app) {
            return new \Illuminate\Database\DatabaseTransactionsManager;
        });
    }

    /**
     * Register the queueable entity resolver implementation.
     *
     * @return void
     */
    protected function registerQueueableEntityResolver()
    {
        $this->app->singleton(EntityResolver::class, function () {
            return new QueueEntityResolver;
        });
    }
    
    /**
     * Register Query Extend
     */
    protected function registerQueryExtends()
    {
        /**
         * toSql extend
         */
        Builder::macro('getSql', function () {
            $bindings = $this->getBindings();
            $sql = str_replace('?', "'%s'", $this->toSql());
            return sprintf($sql, ...$bindings);
        });

        /**
         * whereDateTime extend
         */
        Builder::macro('atDate', function($column, $date, $symbol = '~'){
            $date = strstr($date, $symbol) ? array_map('trim', explode($symbol, $date)): array($date, $date);
            $atDate[0] = \Carbon\Carbon::parse($date[0])->startOfDay()->toDateTimeString();
            $atDate[1] = \Carbon\Carbon::parse($date[1])->endOfDay()->toDateTimeString();

            return $this->whereBetween($column, $atDate);
        });

    }

    /**
     * Register Query Events
     *
     *
     * @todo addition listen to query event
     */
    protected function registerQueryEvents()
    {
        /*
        \DB::listen(function($query){

        });
        */
    }

    /**
     * Register Query Logs
     */
    protected function registerDbQueryLogs()
    {
        if(getenv('ENV') || $this->app['config']['app.log'] > 1){
            $this->app['db']->listen(function($query) {
                $sql = vsprintf(str_replace('?', '%s', $query->sql), $query->bindings);                
                if($query->time > 500){
                    $this->app['log']->dir('db.'. $query->connectionName, 'slow')->warning('['.$query->time.' ms] '.$sql);
                }
                if(getenv('ENV') || $this->app['config']['app.log'] > 1){
                    $this->app['log']->dir('db.'. $query->connectionName, _APP_)->debug('['.$query->time.' ms] '.$sql);
                }
            });
        }
    }
}