<?php

namespace VM\Services;

/**
 * Varimax The Slim PHP Frameworks.
 * varimax.cn
 * *
 * Github: https://github.com/dcto/varimax
 */

use Hashids\Hashids;

class HashIdsServiceProvider extends ServiceProvider
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