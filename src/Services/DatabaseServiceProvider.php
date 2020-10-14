<?php

namespace VM\Services;

/**
 * Varimax The Full Stack PHP Frameworks.
 * varimax.cn
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-19 15:42
 * SITE: https://www.varimax.cn/
 */

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;

class DatabaseServiceProvider extends \Illuminate\Database\DatabaseServiceProvider
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

        $this->registerQueryExtends();

        $this->registerQueryEvents();

        config('app.log')>1 && $this->registerQueryLogs() ;
    }

    /**
     * register Query Extend
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
    protected function registerQueryLogs()
    {
        \DB::listen(function($query) {

            foreach ($query->bindings as $i => $binding) {
                if ($binding instanceof \DateTime) {
                    $query->bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
                } else {
                    if (is_string($binding)) {
                        $query->bindings[$i] = "'$binding'";
                    }
                }
            }

            $sql = vsprintf(str_replace('?', '%s', $query->sql), $query->bindings);

            \Log::dir('db', _APP_)->debug('['.$query->time.' ms] '.$sql);

            if( $query->time > 100 ){
                \Log::dir('db/slow', _APP_)->warning('['.$query->time.' ms] '.$sql);
            }
        });
    }
}