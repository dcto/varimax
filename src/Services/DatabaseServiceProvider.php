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
use Illuminate\Support\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Contracts\Queue\EntityResolver;
use Illuminate\Database\Eloquent\QueueEntityResolver;
use Illuminate\Database\Connectors\ConnectionFactory;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->register(EventServiceProvider::class);

        $this->registerConnectionServices();
        
        $this->registerQueueableEntityResolver();

        $this->registerQueryEvents();

        $this->registerDbQueryLogs();

        $this->registerNestCollect();

        Model::clearBootedModels();
        
        Model::setConnectionResolver($this->app['db']);

        Model::setEventDispatcher($this->app['events']);
        
        PaginationServiceProvider::paginator();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerDatabaseServices();
    }


    /**
     * Register the database service to application
     */
    protected function registerDatabaseServices()
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
    }

    /**
     * Register the primary database bindings.
     *
     * @return void
     */
    protected function registerConnectionServices()
    {

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
     * The collection nested methods 
     * collection($array)->top($id=1);   //get collection parent
     * collection($array)->tops($id=1);  //get collection parents
     * collection($array)->sub($pid=1);  //get collection children
     * collection($array)->subs($pid=1); //get collection childrens
     * collection($array)->get(['id', 'pid', 'name'])->subs($pid=1)->tree('children'); //build collection to tree
     * 
     * @return array
     * @version 20230320
     */
    protected function registerNestCollect(){
        Collection::macro('top', function($id, $col = 'id'){return $this->where($col, $id);});
        Collection::macro('tops', function($id, $col = 'pid'){
            $parents = collect([]);
            $parent = $this->where('id', $id)->first();
            while(!is_null($parent)) {
                $parents->push($parent);
                $parent = $this->where('id', $parent[$col])->first();
            }
            return $parents;
        });
        Collection::macro('sub', function($id, $col = 'pid'){return $this->where($col, $id);});
        Collection::macro('subs', function($id = 0, $col = 'pid'){
            $childs = collect([]);
            $child = $this->where($col, $id);
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
            * 引用方法分类算法
            * 鸣谢 @kid
            * @author  dc.To
            * @version 20231107
            */
            $items = $this->keyBy($keyBy)->toArray();
            foreach($items as $item){
                if(isset($items[$item[$parent]])){
                    $items[$item[$keyBy]][$level] = $items[$item[$parent]][$level]+1; //鸣谢 @kid
                    $items[$item[$parent]][$child][$item[$keyBy]] = &$items[$item[$keyBy]];
                }else{
                    $items[$item[$keyBy]][$level] = 0; //鸣谢 @kid
                    $trees[$item[$keyBy]] = &$items[$item[$keyBy]];
                }
            }
            return $trees;
        });
    }

    /**
     * Register Query Events
     * 
     * @todo addition listen to query event
     */
    protected function registerQueryEvents()
    {
        /*
        $this->app['db']->listen(function($query){

        });
        */
    }

    /**
     * Register Query Logs
     */
    protected function registerDbQueryLogs()
    {
        if(getenv('ENV') || getenv('DEBUG') || $this->app['config']['app.log'] > 0){
            $this->app['db']->listen(function($query) {
                $sql = vsprintf(str_replace("?", "'%s'", $query->sql), $query->bindings);                
                if($query->time > 500){
                    $this->app['log']->dir('db.'. $query->connectionName, 'slow')->warning('['.$query->time.' ms] '.$sql);
                }
                if($this->app['config']['app.log'] > 1){
                    $this->app['log']->dir('db.'. $query->connectionName, _APP_)->debug('['.$query->time.' ms] '.$sql);
                }
            });
        }
    }
}