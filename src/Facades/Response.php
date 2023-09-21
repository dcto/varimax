<?php

/**
 * Varimax The Slim PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 22:03
 * SITE: https://www.varimax.cn/
 */


use Illuminate\Support\Facades\Facade;

/**
 * Class Response
 *
 * @method static VM\Http\Response make(string $content = '', int $status = 200 , array $headers = [])
 * @method static VM\Http\Response raw(string $content = '', int $status = 200 , array $headers = [])
 * @method static VM\Http\Response xml(array $data = [], string $root='root', int $status = 200, $options = 0)
 * @method static VM\Http\Response json(array $data = [], int $status = 200, array $headers = [], $options = 0)
 * @method static VM\Http\Response html(string $html, array $data = [], int $status = 200, array $headers = [])
 * @method static VM\Http\Response redirect(string $url, int $status = 302)
 * @method static VM\Http\Response redirect(string $url, int $status = 302, array $headers = [])
 * @method static VM\Http\Response download(string $file, string $name = null)
 */
class Response extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'response';
    }
}
