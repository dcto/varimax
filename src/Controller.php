<?php

/**
 * Varimax The Full Stack PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 22:03
 * SITE: https://www.varimax.cn/
 */

namespace VM;

/**
 *
 * Class Controller
 *
 * @package VM
 */

abstract class Controller
{
    /**
     * [$controller]
     * @var string
     */
    static $controller;

    /**
     * [$action]
     * @var string
     */
    static $action;

    /**
     * [$router]
     * @var \VM\Routing\Route
     */
    static $router;

    /**
     * [Global variable for view]
     * @var array
     */
    static $assign = array();


    public function __construct()
    {
        if(PHP_SAPI != 'cli'){

            static::$router = make('router')->route();

            static::$assign['CONTROLLER'] = static::$controller = static::$router->controller();

            static::$assign['ACTION'] = static::$action = static::$router->action();

            $this->on();
        }
    }

    /**
     * the controller start hook
     */
    protected function on(){}

    /**
     * the controller after hook
     */
    protected function off(){}

    /**
     * make
     * @param $abstract
     * @param array $parameters
     * @return mixed
     */
    protected function make($abstract, array $parameters = [])
    {
        return make($abstract, $parameters);
    }

    /**
     * [view]
     *
     * @param       $template
     * @param array $params
     *
     */
    protected function view($template, $variable = [])
    {
        return make('view')->show($template, $variable);
    }


    /**
     * [assign]
     *
     * @param      $var
     * @param null $val
     *
     */
    protected function assign($var, $val = null)
    {
        make('view')->assign($var, $val);
    }

    public function __destruct()
    {
        $this->off();
    }
}
