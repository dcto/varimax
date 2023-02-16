<?php

namespace VM\Services;

/**
 * Varimax The Slim PHP Frameworks.
 * varimax.cn
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-19 16:53
 * SITE: https://www.varimax.cn/
 */

use Hashids\Hashids;

class HashIdsServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        if(is_file($config = root('config', 'id.php'))) {
            $this->mergeConfigFrom($config, "id");
        }

        $this->app->singleton('id', function () {
            return new Hashids(config('id.key',config('app.key', 'VM:')), config('id.len', 16), config('id.bet', join('', range(0,9)).join('',range('a','z'))));
        });

    }
}