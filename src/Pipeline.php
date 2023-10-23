<?php

namespace VM;

/**
 * Varimax The Slim PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2023-09-10
 */

abstract class Pipeline {
    /**
    * The Pipeline handle method
    * @param \VM\Http\Request $request
    * @param \Closure $next
    * @param array $guards
    */
    abstract public function handle($request, \Closure $next, ...$guards);
}