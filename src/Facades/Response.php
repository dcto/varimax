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
 * @method static \VM\Http\Response make(string $context = '', int $status = 200 , array $headers = [])
 * @method static \VM\Http\Response json(array $context = [], int $status = 200, array $headers = [], string $callback, int $options = 0)
 * @method static \VM\Http\Response html(string $context = '', array $data = [], int $status = 200, array $headers = [])
 * @method static \VM\Http\Response xml(array $context = [], string $root='root', int $status = 200, array $headers = [])
 * @method static \VM\Http\Response raw(string $context = '', int $status = 200 , array $headers = [])
 * @method static \VM\Http\Response redirect(string $url, int $status = 302, array $headers = [])
 * @method static \VM\Http\Response download(string $file, string $name = null)
 * @method static \VM\Http\Response header(string $name, string $value = null) 
 * @method static \VM\Http\Response|\Symfony\Component\HttpFoundation\ResponseHeaderBag headers(...$headers) 
 * @method static \VM\Http\Response withHeader(string $name, string $value)
 * @method static \VM\Http\Response withHeaders(...$headers)
 * @method static \VM\Http\Response cookie(string $name, string $Value)
 * @method static array getCookies() 
 * @method static \VM\Http\Response withCookie(string $name, string $value)
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
