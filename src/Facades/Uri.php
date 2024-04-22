<?php

use Illuminate\Support\Facades\Facade;

/**
* @method static string getScheme()
* @method static string getDomain()
* @method static string getAuthority()
* @method static string getUserInfo()
* @method static string getHost()
* @method static string getPort()
* @method static string getPath()
* @method static string getQuery()
* @method static string getFragment
* @method static \VM\Http\Uri uri(string $uri = '') parse uri method
* @method static \VM\Http\Uri set(mixed ...$args) ['/'=...$paths, '?'=...$params, '&'=...add $params, '!'=...del $params, '~'=...keep $params,  '#'=>...$fragment]
* @method static \VM\Http\Uri withScheme(string $scheme)
* @method static \VM\Http\Uri withUserInfo(string $user, string $password = null)
* @method static \VM\Http\Uri withHost(string $host)
* @method static \VM\Http\Uri withPort(null|int|string $port)
* @method static \VM\Http\Uri withPath(string $path)
* @method static \VM\Http\Uri withQuery(string $query)
* @method static \VM\Http\Uri withQueryValue(string $key, string $value)
* @method static \VM\Http\Uri withFragment(string $fragment)
* @method static \VM\Http\Uri composeComponents(string $scheme, string $user, string $password, string $host, null|int|string $port, string $path, string $query, string $fragment)
* @method static bool isDefaultPort()
* @method static null|int getDefaultPort()
*/
class Uri extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'uri';
    }
}
