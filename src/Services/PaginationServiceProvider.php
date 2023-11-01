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
     * pageName
     */
    static private $pageName = 'page';

    /**
     * PageType
     * @var string
     */
    static private $paginator = Paginator::class; 

    /**
     * Register the Paginator service.
     *
     * @return void
     */
    public function register()
    {
        $this->booting(fn()=>static::paginator());
        $this->app->singleton('page', static::$paginator);
    }

    /**
     * Register Paginator Factory.
     * 
     * @return void
     */
    static public function paginator()
    {
        Paginator::viewFactoryResolver(fn() => app('view'));
        Paginator::currentPathResolver(fn() => app('request')->url());
        Paginator::currentPageResolver(fn() => input(static::$pageName, function($page){
            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return (int) $page;
            }
            return 1;
        }));
    }

    /**
     * Register the CursorPaginator service.
     *
     * @return void
     */
    static public function cursor($pageName = 'page')
    {
        static::$pageName = $pageName;
        static::$paginator = CursorPaginator::class;
        return static::class;
    }


    /**
     * Register the LengthAwarePaginator service.
     *
     * @return void
     */
    static public function lengthAware($pageName = 'page')
    {
        static::$pageName = $pageName;
        static::$paginator = LengthAwarePaginator::class;
        return static::class;
    }
}