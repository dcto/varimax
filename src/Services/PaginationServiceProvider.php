<?php

namespace VM\Services;

/**
 * Varimax The Slim PHP Frameworks.
 * varimax.cn
 * *
 * Github: https://github.com/dcto/varimax
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
     * The Page links style 
     */
    static private $pageTheme = null;

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
        app()->has('view') && Paginator::viewFactoryResolver(fn() => app('view'));
        Paginator::currentPathResolver(fn() => app('request')->url());
        Paginator::currentPageResolver(fn() => input(static::$pageName, fn($p)=>(int) $p));
        if(static::$pageTheme){
            $theme = 'use'.ucfirst(static::$pageTheme);
            method_exists('Paginator', $theme) && Paginator::$theme();
        }
    }

    /**
     * Register the CursorPaginator service.
     * @param string $pageName
     * @param string $pageTheme Tailwind|Bootstrap|BootstrapThree
     * @return void
     */
    static public function cursor($pageName = 'page', $pageTheme = null)
    {
        static::$pageName = $pageName;
        static::$pageTheme = $pageTheme;
        static::$paginator = CursorPaginator::class;
        return static::class;
    }


    /**
     * Register the LengthAwarePaginator service.
     * @param string $pageName
     * @param string $theme Tailwind|Bootstrap|BootstrapThree
     * @return void
     */
    static public function lengthAware($pageName = 'page', $pageTheme = null)
    {
        static::$pageName = $pageName;
        static::$pageTheme = $pageTheme;
        static::$paginator = LengthAwarePaginator::class;
        return static::class;
    }
}