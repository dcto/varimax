<?php
/**
 * Varimax The Full Stack PHP Frameworks.
 * varimax.cn
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-19 15:42
 * SITE: https://www.varimax.cn/
 */

namespace VM\Service;

use Illuminate\Database\Eloquent\Model;

class DatabaseServiceProvider extends \Illuminate\Database\DatabaseServiceProvider
{

    /**
     * 延迟加载
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
        Model::setConnectionResolver($this->app['db']);

        Model::setEventDispatcher($this->app['events']);
    }

    protected function connect()
    {
        /*
        $capsule = new \Illuminate\Database\Capsule\Manager ();

        foreach((array) config('database') as $db => $config)
        {
            if(!is_array($config)) throw new \InvalidArgumentException('Invalid database configure of '.$db);

            $capsule->addConnection($config);
        }

        $capsule->bootEloquent();

        $capsule->setEventDispatcher(new \Illuminate\Events\Dispatcher());

        $capsule->setAsGlobal();



        if(config('app.debug') || config('app.log')) {
            $capsule::connection()->enableQueryLog();
            $capsule::connection()->listen(function($query)use($capsule){
                \Log::dir('db/'.$capsule::connection()->getName())->debug("[time:$query->time] ".vsprintf(str_replace('?', '\'%s\'', $query->sql), $query->bindings));
            });
        }
        */
    }

}