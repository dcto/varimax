<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class Curl
 *
 * @method static resource curl()
 * @method static VM\Http\Curl\Curl port(int $port = 80)
 * @method static VM\Http\Curl\Curl get(string $url, array $data = array())
 * @method static VM\Http\Curl\Curl put(string $url, array $data = array())
 * @method static VM\Http\Curl\Curl post(string $url, array $data = array())
 * @method static VM\Http\Curl\Curl patch(string $url, array $data = array())
 * @method static VM\Http\Curl\Curl delete(string $url, array $data = array())
 * @method static VM\Http\Curl\Curl timeout(int $time)
 * @method static VM\Http\Curl\Curl options(mixed $key, string $var = null)
 * @method static VM\Http\Curl\Curl headers(string $key, array $var = null)
 * @method static VM\Http\Curl\Curl cookies(string $key, array $var)
 * @method static VM\Http\Curl\Curl referer(string $referer)
 * @method static VM\Http\Curl\Curl userAgent(string $userAgent)
 * @method static VM\Http\Curl\Curl verbose(bool $on = true)
 * @method static VM\Http\Curl\Curl retry(int $times = 0)
 * @method static string debug()
 * @method static close()
 * @method static Response send()
 */
class Curl extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'curl';
    }
}
