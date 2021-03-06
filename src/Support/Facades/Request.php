<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class Request
 *
 * @method static \VM\Http\Request is()
 * @method static \VM\Http\Request os()
 * @method static \VM\Http\Request url()
 * @method static \VM\Http\Request uri()
 * @method static \VM\Http\Request get(mixed ...$key)
 * @method static \VM\Http\Request set(string $key, string $val)
 * @method static \VM\Http\Request all()
 * @method static \VM\Http\Request not(mixed ...$key)
 * @method static \VM\Http\Request has(string|array $key, $number = 1)
 * @method static \VM\Http\Request put(string|array $key, $val = null)
 * @method static \VM\Http\Request only(mixed ...$key)
 * @method static \VM\Http\Request host()
 * @method static \VM\Http\Request httpHost()
 * @method static \VM\Http\Request take(mixed ...$key)
 * @method static \VM\Http\Request tidy(mixed $key = null)
 * @method static \VM\Http\Request fill(array $key, $value)
 * @method static \VM\Http\Request json(string $key = null, mixed $default = null)
 * @method static \VM\Http\Request input(string $key = null, null|\Closure $default = null)
 * @method static \VM\Http\Request except(mixed ...$args)
 * @method static \VM\Http\Request replace(mixed ...$args)
 * @method static \VM\Http\Request mobile()
 * @method static \VM\Http\Request weixin()
 * @method static \VM\Http\Request query()
 * @method static \VM\Http\Request refer()
 * @method static \VM\Http\Request referer()
 * @method static \VM\Http\Request merge(array $input)
 * @method static \VM\Http\Request replace(array $input)
 * @method static \VM\Http\Request header(string $key = null, mixed $default = null)
 * @method static \VM\Http\Request bearer()
 * @method static \VM\Http\Request token()
 * @method static \VM\Http\Request server(string $key = null, mixed $default = null)
 * @method static \VM\Http\Request browser(string $type = null)
 * @method static \VM\Http\Request hasFile(string $key)
 * @method static \VM\Http\Request cookie(string $key = null)
 * @method static \VM\Http\Request accept($contains)
 * @method static \VM\Http\Request isJson()
 * @method static \VM\Http\Request method(string $type = null)
 * @method static \VM\Http\Request ip()
 * @method static \VM\Http\Request ips()
 * @method static \VM\Http\Request path()
 * @method static \VM\Http\Request root()
 * @method static \VM\Http\Request ajax()
 * @method static \VM\Http\Request baseUrl()
 * @method static \VM\Http\Request language()
 * @method static \VM\Http\Request scheme()
 * @method static \VM\Http\Request domain(bool $subDomain = true)
 * @method static \VM\Http\Request segment()
 * @method static \VM\Http\Request segments()
 * @method static \VM\Http\Request secure()
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
 * @method static \VM\Http\Request getSession()
 * @method static \VM\Http\Request hasPreviousSession()
 * @method static \VM\Http\Request setSession(\VM\Http\Session\SessionInterface $session)
 * @method static \VM\Http\Request getClientIps()
 * @method static \VM\Http\Request getClientIp()
 * @method static \VM\Http\Request getScriptName()
 * @method static \VM\Http\Request getPathInfo()
 * @method static \VM\Http\Request getBasePath()
 * @method static \VM\Http\Request getBaseUrl()
 * @method static \VM\Http\Request getScheme()
 * @method static \VM\Http\Request getHost()
 * @method static \VM\Http\Request getPort()
 * @method static \VM\Http\Request getUser()
 * @method static \VM\Http\Request getPassword()
 * @method static \VM\Http\Request getUserInfo()
 * @method static \VM\Http\Request getHttpHost()
 * @method static \VM\Http\Request getRequestUri()
 * @method static \VM\Http\Request getSchemeAndHttpHost()
 * @method static \VM\Http\Request getUri()
 * @method static \VM\Http\Request getUriForPath(string $path)
 * @method static \VM\Http\Request getRelativeUriForPath(string $path)
 * @method static \VM\Http\Request getQueryString()
 * @method static \VM\Http\Request isSecure()
 * @method static \VM\Http\Request setMethod(string $method)
 * @method static \VM\Http\Request getMethod()
 * @method static \VM\Http\Request getRealMethod()
 * @method static \VM\Http\Request getMimeType(string $format)
 * @method static \VM\Http\Request getFormat(string $mimeType)
 * @method static \VM\Http\Request setFormat(string $format, string|array $mimeTypes)
 * @method static \VM\Http\Request getRequestFormat(string $default = 'html')
 * @method static \VM\Http\Request setRequestFormat(string $format)
 * @method static \VM\Http\Request getContentType()
 * @method static \VM\Http\Request setDefaultLocale(string $locale)
 * @method static \VM\Http\Request getDefaultLocale()
 * @method static \VM\Http\Request setLocale(string $locale)
 * @method static \VM\Http\Request getLocale()
 * @method static \VM\Http\Request isMethod(string $method)
 * @method static \VM\Http\Request isMethodSafe()
 * @method static \VM\Http\Request isMethodCacheable()
 * @method static \VM\Http\Request getContent(bool $asResource = false)
 * @method static \VM\Http\Request getETags()
 * @method static \VM\Http\Request isNoCache()
 * @method static \VM\Http\Request getPreferredLanguage(array $locales = null)
 * @method static \VM\Http\Request getLanguages()
 * @method static \VM\Http\Request getCharsets()
 * @method static \VM\Http\Request getEncodings()
 * @method static \VM\Http\Request getAcceptableContentTypes()
 * @method static \VM\Http\Request isXmlHttpRequest()
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
