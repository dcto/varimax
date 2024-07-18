<?php

namespace VM\Services;

/**
 * Varimax The Slim PHP Frameworks.
 * varimax.cn
 * *
 * Github: https://github.com/dcto/varimax
 */

use VM\Context;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Queue\EntityResolver;
use Illuminate\Database\Eloquent\QueueEntityResolver;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseTransactionsManager;
use Illuminate\Database\Events\ConnectionEstablished;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        if(!$this->app->config['database.connections']){
            return;
        }
        
        $this->registerQueryEvents();

        $this->registerQueryExecuted();

        $this->registerCollectMacros();

        //set setEventDispatcher
        Model::setEventDispatcher($this->app['events']);

        //set ConnectionResolver
        Model::setConnectionResolver($this->app['db']);

        PaginationServiceProvider::paginator();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        if(!$this->app->config['database.connections']){
            return;
        }

        Model::clearBootedModels();
        
        $this->registerConnectionEvent();

        $this->registerConnectionService();

        $this->registerDatabasesResolver();
       
        $this->registerQueueableEntityResolver();
    }

    /**
     * Connection Event
     */
    protected function registerConnectionEvent(){
        // $this->app['events']->listen(ConnectionEstablished::class, function ($db) {
        //     if (defined("SWOOLE_VERSION")) {
        //         $this->app->log->debug("booting [%s] connections...");
        //     }
        // });
    }


    /**
     * Register the connection service to application
     */
    protected function registerDatabasesResolver()
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
            return new DatabaseManager($app, $app['db.factory']);
        });

        //set setEventDispatcher
        if ($this->app->bound('events')) {
            $this->app['db']->setEventDispatcher($this->app['events']);
        }
    }

    /**
     * Register the primary database bindings.
     *
     * @return void
     */
    protected function registerConnectionService()
    {
        $this->app->bind('db.connection', function ($app) {
            return $app['db']->connection();
        });

        $this->app->bind('db.schema', function ($app) {
            return $app['db']->connection()->getSchemaBuilder();
        });

        $this->app->singleton('db.transactions', function () {
            return new DatabaseTransactionsManager;
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
        Model::macro('atDate', function($column, $date, $symbol = '~'){
            $date = strstr($date, $symbol) ? array_map('trim', explode($symbol, $date)): array($date, $date);
            $atDate[0] = \Carbon\Carbon::parse($date[0])->startOfDay()->toDateTimeString();
            $atDate[1] = \Carbon\Carbon::parse($date[1])->endOfDay()->toDateTimeString();
             /** @var Builder $this */
            return $this->whereBetween($column, $atDate);
        });

    }

    /**
     * Register Query Events
     * 
     * @todo addition listen to query event
     */
    protected function registerQueryEvents()
    {       
        $this->app['db']->beforeExecuting(function($query, $bindings, &$connection){
            if (pcid()) {
                if (!$pdo = Context::get('pdo')) {
                    $connection->reconnect();
                    Context::put('pdo', $pdo = $connection->getPdo());
                }
                $connection->setPdo($pdo);

                defer(fn()=>Context::delete('pdo'));
            }
        });
    }

    /**
     * Register Query Logs
     */
    protected function registerQueryExecuted()
    {	
        $this->app['db']->listen(function($query) {
            $log = "($query->time ms) ".$this->formatQueryString($query->sql, $query->connection->prepareBindings($query->bindings) );                
            if($query->time > 200){
                $this->app->log->dir('db.'. $query->connectionName, 'slow')->warning($log);
            }
            if($this->app->config->get('app.log') > 1){
                $this->app->log->dir('db.'. $query->connectionName, _APP_)->debug($log);
            }
        });
    }


    /**
     * format sql query string
     * @param string $query
     * @param array $bindings
     * @return string
     */
    protected function formatQueryString(string $query, array $bindings) {
        return $bindings ? vsprintf(str_replace("?", "'%s'", $query),  $bindings) : $query;
    }


    /**
     * The collection nested methods 
     * collection($array)->top($id=1);   //get collection parent
     * collection($array)->tops($id=1);  //get collection parents
     * collection($array)->sub($pid=1);  //get collection children
     * collection($array)->subs($pid=1); //get collection childrens
     * collection($array)->get(['id', 'pid', 'name'])->subs($pid=1)->tree('children'); //build collection to tree
     * @return array
     * @version 20230320
     */
    protected function registerCollectMacros(){
        
        Collection::macro('top', function($id, $col = 'id'){ 
            /** @var Collection $this */
            return $this->where($col, $id);
        });
        Collection::macro('sub', function($id, $col = 'pid'){
            /** @var Collection $this */
            return $this->where($col, $id);
        });

        Collection::macro('tops', function($id, $col = 'pid'){
            /** @var Collection $this */
            $parents = collect([]);
            $parent = $this->where('id', $id)->first();
            while(!is_null($parent)) {
                $parents->push($parent);
                $parent = $this->where('id', $parent[$col])->first();
            }
            return $parents;
        });
       
        Collection::macro('subs', function($id = 0, $col = 'pid'){
            $childs = collect([]);
            /**
             * @var Collection $this
             */
            $child = $this->where($col, $id)->toBase();// $child = $this->where($col, $id);
            while($child->count()){
                $childs->push(...$child);
                $child = $this->whereIn($col, $child->pluck('id'));
            }
            return $childs;
        });

        Collection::macro('toTree', function(string $child = 'subs', string $level = 'deep', string $parent = 'pid', string $keyBy = 'id'){
            $trees = [];
            /**
             * 迭代算法
             */
            // $this->filter(fn($item)=>$item->pid == $pid)->each(function($item, $id) use(&$pid, &$trees, $child, $parent, $level, $keyBy){
            //     $pid = $item['id'];
            //     $item['deep'] = $level;
            //     $item[$child] = $this->toTree($child, $parent, $level+1, $keyBy);
            //     $this->forget($id);
            //     if($keyBy){
            //         $trees[$item[$keyBy]] = $item->toArray();
            //     }else{
            //         $trees[] = $item->toArray();
            //     }
            // });

            /**
            * 无限级树结构
            * 鸣谢@kid 解决层级level问题。
            * @author  dc.To
            * @version 20231107
            * @var Collection $this
            */
            $items = $this->keyBy($keyBy)->toArray();
            foreach($items as $item){
                if(isset($items[$item[$parent]])){
                    $items[$item[$keyBy]][$level] = $items[$item[$parent]][$level]+1; //@kid
                    $items[$item[$parent]][$child][] = &$items[$item[$keyBy]];
                    // $items[$item[$parent]][$child][$item[$keyBy]] = &$items[$item[$keyBy]];
                }else{
                    $items[$item[$keyBy]][$level] = 0; //@kid
                    $trees[] = &$items[$item[$keyBy]];
                    // $trees[$item[$keyBy]] = &$items[$item[$keyBy]];
                }
            }
       
            // $this->keyBy($keyBy)->each(function($item) use(&$trees, $parent, $child, $keyBy, $level){
            //     if( isset($items[$item[$parent]]) ){
            //         $this[$item[$parent]][$child][$item[$keyBy]] = &$this[$item[$keyBy]];

            //     }else{
            //         $trees[$item[$keyBy]] = &$this[$item[$keyBy]];
            //     }
            // });
            return $trees;
        });
    }
}