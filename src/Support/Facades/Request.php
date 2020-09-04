<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class Request
 *
 * @method static is()
 * @method static os()
 * @method static url()
 * @method static uri(string $case = null)
 * @method static get(string $key = null, mixed $default = null, boolean $deep = false)
 * @method static set(string $key, mixed $val)
 * @method static all(string|array $key = null)
 * @method static not()
 * @method static has(string|array $key)
 * @method static put(string|array $key, $val = null)
 * @method static only(array $keys)
 * @method static host()
 * @method static httpHost()
 * @method static take(mixed $key = null)
 * @method static tidy(mixed $key = null)
 * @method static json(string $key = null, mixed $default = null)
 * @method static input(string $key = null, mixed $default = null)
 * @method static query()
 * @method static refer()
 * @method static referer()
 * @method static merge(array $input)
 * @method static replace(array $input)
 * @method static header(string $key = null, mixed $default = null)
 * @method static server(string $key = null, mixed $default = null)
 * @method static browser(string $type = null)
 * @method static hasFile(string $key)
 * @method static cookie(string $key = null)
 * @method static accept($contains)
 * @method static isJson()
 * @method static method(string $type = null)
 * @method static ip()
 * @method static ips()
 * @method static path()
 * @method static root()
 * @method static ajax()
 * @method static baseUrl()
 * @method static language()
 * @method static scheme()
 * @method static domain(bool $subDomain = true)
 * @method static segment()
 * @method static segments()
 * @method static secure()
 * @method static \VM\Http\Request\Upload file(string $key = null, mixed $default = null)
 * @method static \VM\Http\Request\Upload files(string|array $name = null)
 *
 * Symfony Request
 * @method static setFactory(callable|null $callable)
 * @method static overrideGlobals()
 * @method static setTrustedProxies(array $proxies)
 * @method static getTrustedProxies()
 * @method static setTrustedHeaderName(string $key, string $value)
 * @method static getTrustedHeaderName(string $key)
 * @method static normalizeQueryString(string $qs)
 * @method static enableHttpMethodParameterOverride()
 * @method static getHttpMethodParameterOverride()
 * @method static getSession()
 * @method static hasPreviousSession()
 * @method static setSession(\VM\Http\Session\SessionInterface $session)
 * @method static getClientIps()
 * @method static getClientIp()
 * @method static getScriptName()
 * @method static getPathInfo()
 * @method static getBasePath()
 * @method static getBaseUrl()
 * @method static getScheme()
 * @method static getHost()
 * @method static getPort()
 * @method static getUser()
 * @method static getPassword()
 * @method static getUserInfo()
 * @method static getHttpHost()
 * @method static getRequestUri()
 * @method static getSchemeAndHttpHost()
 * @method static getUri()
 * @method static getUriForPath(string $path)
 * @method static getRelativeUriForPath(string $path)
 * @method static getQueryString()
 * @method static isSecure()
 * @method static setMethod(string $method)
 * @method static getMethod()
 * @method static getRealMethod()
 * @method static getMimeType(string $format)
 * @method static getFormat(string $mimeType)
 * @method static setFormat(string $format, string|array $mimeTypes)
 * @method static getRequestFormat(string $default = 'html')
 * @method static setRequestFormat(string $format)
 * @method static getContentType()
 * @method static setDefaultLocale(string $locale)
 * @method static getDefaultLocale()
 * @method static setLocale(string $locale)
 * @method static getLocale()
 * @method static isMethod(string $method)
 * @method static isMethodSafe()
 * @method static isMethodCacheable()
 * @method static getContent(bool $asResource = false)
 * @method static getETags()
 * @method static isNoCache()
 * @method static getPreferredLanguage(array $locales = null)
 * @method static getLanguages()
 * @method static getCharsets()
 * @method static getEncodings()
 * @method static getAcceptableContentTypes()
 * @method static isXmlHttpRequest()
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