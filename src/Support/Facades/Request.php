<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class Request
 *
 * @method static bool is()
 * @method static string os()
 * @method static string url()
 * @method static string uri()
 * @method static mixed get(mixed ...$key)
 * @method static \VM\Http\Request set(string $key, string $val)
 * @method static array all()
 * @method static array toArray()
 * @method static array not(mixed ...$key)
 * @method static int has(string|array $key, $number = 1)
 * @method static \VM\Http\Request put(string|array $key, $value = null)
 * @method static \VM\Http\Request set(string|array $key, $value = null)
 * @method static array keys()
 * @method static bool must(string|array $key)
 * @method static array only(mixed ...$key)
 * @method static string host()
 * @method static string httpHost()
 * @method static array take(mixed ...$key)
 * @method static array tidy(mixed $key = null)
 * @method static array fill(array $key, $value)
 * @method static array trim(array $key, $value)
 * @method static \VM\Http\Request merge(array $input)
 * @method static string json(string $key = null, mixed $default = null)
 * @method static mixed input(string $key = null, null|\Closure $default = null)
 * @method static array except(mixed ...$args)
 * @method static bool exists(mixed ...$args)
 * @method static array replace(mixed ...$args)
 * @method static string mobile()
 * @method static string weixin()
 * @method static string query()
 * @method static string refer()
 * @method static string referer()
 * @method static string header(string $key = null, mixed $default = null)
 * @method static string bearer()
 * @method static string token()
 * @method static string cookie(string $key = null)
 * @method static string server(string $key = null, mixed $default = null)
 * @method static string browser(string $type = null)
 * @method static string|array file(string $key)
 * @method static bool accept($contains)
 * @method static bool isJson()
 * @method static bool|string method(string $type = null)
 * @method static string ip()
 * @method static array ips()
 * @method static string path()
 * @method static string root()
 * @method static bool ajax()
 * @method static string baseUrl()
 * @method static string language()
 * @method static string scheme()
 * @method static string domain(bool $subDomain = true)
 * @method static string segment()
 * @method static mixed segments()
 * @method static bool secure()
 * @method static \VM\Http\Request\Upload file(string $key = null, mixed $default = null)
 * @method static \VM\Http\Request\Upload files(string|array $name = null)
 *
 * Symfony Request
 * @method static \VM\Http\Request setFactory(callable|null $callable)
 * @method static \VM\Http\Request overrideGlobals()
 * @method static \VM\Http\Request setTrustedProxies(array $proxies)
 * @method static \VM\Http\Request getTrustedProxies()
 * @method static \VM\Http\Request setTrustedHeaderName(string $key, string $value)
 * @method static \VM\Http\Request getTrustedHeaderName(string $key)
 * @method static \VM\Http\Request normalizeQueryString(string $qs)
 * @method static \VM\Http\Request enableHttpMethodParameterOverride()
 * @method static \VM\Http\Request getHttpMethodParameterOverride()
 * @method static \Symfony\Component\HttpFoundation\Session|null getSession()
 * @method static bool hasPreviousSession()
 * @method static \VM\Http\Request setSession(\VM\Http\Session\SessionInterface $session)
 * @method static array getClientIps()
 * @method static string|null getClientIp()
 * @method static string getScriptName()
 * @method static string getPathInfo()
 * @method static string getBasePath()
 * @method static string getBaseUrl()
 * @method static string getScheme()
 * @method static string getHost()
 * @method static string getPort()
 * @method static string getUser()
 * @method static string getPassword()
 * @method static string getUserInfo()
 * @method static string getHttpHost()
 * @method static string getRequestUri()
 * @method static string getSchemeAndHttpHost()
 * @method static string getUri()
 * @method static string getUriForPath(string $path)
 * @method static string getRelativeUriForPath(string $path)
 * @method static string getQueryString()
 * @method static bool isSecure()
 * @method static \VM\Http\Request setMethod(string $method)
 * @method static string getMethod()
 * @method static string getRealMethod()
 * @method static string getMimeType(string $format)
 * @method static string getFormat(string $mimeType)
 * @method static \VM\Http\Request setFormat(string $format, string|array $mimeTypes)
 * @method static string getRequestFormat(string $default = 'html')
 * @method static \VM\Http\Request setRequestFormat(string $format)
 * @method static string getContentType()
 * @method static \VM\Http\Request setDefaultLocale(string $locale)
 * @method static string getDefaultLocale()
 * @method static \VM\Http\Request setLocale(string $locale)
 * @method static string getLocale()
 * @method static bool isMethod(string $method)
 * @method static bool isMethodSafe()
 * @method static bool isMethodCacheable()
 * @method static string|resource getContent(bool $asResource = false)
 * @method static array getETags()
 * @method static bool isNoCache()
 * @method static string getPreferredLanguage(array $locales = null)
 * @method static array getLanguages()
 * @method static array getCharsets()
 * @method static array getEncodings()
 * @method static array getAcceptableContentTypes()
 * @method static bool isXmlHttpRequest()
 */
class Request extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'request';
    }
}
