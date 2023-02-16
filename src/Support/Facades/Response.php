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
 * @method static VM\Http\Response\Base error(int $status = 200, string $content = '')
 * @method static VM\Http\Response\Base abort(int $status = 200, string $content = '')
 * @method static VM\Http\Response\Base make(string $content = '', int $status = 200 , array $headers = [])
 * @method static VM\Http\Response\Base show(string $content = '', int $status = 200 , array $headers = [])
 * @method static VM\Http\Response\Base view(string $view, array $data = [], int $status = 200, array $headers = [])
 * @method static VM\Http\Response\Json json(array $data = [], int $status = 200, array $headers = [], $options = 0)
 * @method static VM\Http\Response\Json jsonp(Closure $callback, array $data = [], int $status = 200, array $headers = [], $options = 0)
 * @method static VM\Http\Response\Redirect url(string $url, int $status = 302, array $headers = [])
 * @method static VM\Http\Response\Redirect route(string $tag, int $status = 302, array $headers = [])
 * @method static VM\Http\Response\Redirect redirect(string $url, int $status = 302, array $headers = [])
 * @method static VM\Http\Response\Streamed stream(Closure $callback, int $status = 200, array $headers = [])
 * @method static VM\Http\Response\Streamed download(string $file, string $name = null, array $headers = [], string $disposition = 'attachment')
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
