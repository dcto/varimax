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

use Illuminate\Pagination\Paginator;

class PaginationServiceProvider extends ServiceProvider
{
    /**
     * @return mixed
     */
    public function boot()
    {
        Paginator::viewFactoryResolver(function () {
            return $this->app['view'];
        });

        Paginator::currentPathResolver(function () {
            return $this->app['request']->url();
        });

        Paginator::currentPageResolver(function ($pageName = 'page') {
            $page = $this->app['request']->input($pageName);

            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return (int) $page;
            }

            return 1;
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('page', function(...$args){
            return new Paginator(...$args);
        });
    }

}