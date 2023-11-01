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
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;

class PaginationServiceProvider extends ServiceProvider
{

    protected $defer = true;

    /**
     * PageType
     * @var string
     */
    static private $paginator = Paginator::class; 

    /**
     * Boot Service
     * @return void
     */
    public function boot()
    {echo 'ddd';
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
     * Register the Paginator service.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('page', static::$paginator);
        // $this->app->alias(static::$paginator, 'page');
    }

    /**
     * Register the CursorPaginator service.
     *
     * @return void
     */
    static public function cursor()
    {
        static::$paginator = CursorPaginator::class;
        return static::class;
    }


    /**
     * Register the LengthAwarePaginator service.
     *
     * @return void
     */
    static public function lengthAware()
    {
        static::$paginator = LengthAwarePaginator::class;
    }
}