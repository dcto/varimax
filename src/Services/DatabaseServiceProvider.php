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

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Collection;

class DatabaseServiceProvider extends \Illuminate\Database\DatabaseServiceProvider
{
    /**
     * Register the service provider.
     * @return void
     */
    public function register(){
        parent::register();
        $this->booted(function(){
            $this->registerQueryEvents();
            $this->registerNestCollect();
            $this->registerDbQueryLogs();
        });
    }

    /**
     * Register Nest Collection Into Illuminate Builder 
     * @return void
     */
    protected function registerNestCollect(){
        Collection::macro('top', function($id, $col = 'id'){return $this->where($col, $id);});
        Collection::macro('sub', function($id, $col = 'pid'){return $this->where($col, $id);});
        Collection::macro('tops', function($id, $col = 'pid'){
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
            $child = $this->where($col, $id);
            while($child->count()){
                $childs->push(...$child);
                $child = $this->whereIn($col, $child->pluck('id'));
            }
            return $childs;
        });
        Collection::macro('tree', function($name = 'sub', $col = 'pid'){
            $trees = [];
            $items = $this->keyBy('id')->toArray();
            foreach($items as $k => $item){
                if(isset($items[$item[$col]])){
                    $items[$item[$col]][$name][] = &$items[$k];
                }else{
                    $trees[] = &$items[$k];
                }
            }
            return $trees;
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