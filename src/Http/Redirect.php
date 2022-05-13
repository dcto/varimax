<?php

/**
 * Varimax The Full Stack PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 22:03
 * SITE: https://www.varimax.cn/
 */


namespace VM\Http;


use VM\Http\Response\Redirect as RedirectResponse;


class Redirect extends RedirectResponse implements Response\Response
{
    /**
     * The session store instance.
     *
     * @var \VM\Http\Session
     */
    protected $session;

    /**
     * The URL generator instance.
     *
     * @var \VM\Http\UrlGenerator
     */
    protected $generator;

    /**
     * Create a new Redirect instance.
     *
     * @param  \VM\Http\UrlGenerator  $generator
     * @return void
     */
    public function __construct(UrlGenerator $generator)
    {
        $this->generator = $generator;
    }
    /**
     * Create a new redirect response to the "home" route.
     *
     * @param  int  $status
     * @return RedirectResponse
     */
    public function home($status = 302)
    {
        return $this->to('/', $status);
    }

    /**
     * Create a new redirect response to the previous location.
     *
     * @param  int    $status
     * @param  array  $headers
     * @param  mixed  $fallback
     * @return RedirectResponse
     */
    public function back($status = 302, $headers = [], $fallback = false)
    {
        return $this->createRedirect($fallback ?: $this->generator->previous(), $status, $headers);
    }

    /**
     * Create a new redirect response to the current URI.
     *
     * @param  int    $status
     * @param  array  $headers
     * @return RedirectResponse
     */
    public function refresh($status = 302, $headers = [])
    {
        return $this->to($this->generator->getRequest()->url(), $status, $headers);
    }

    /**
     * Create a new redirect response, while putting the current URL in the session.
     *
     * @param  string  $path
     * @param  int     $status
     * @param  array   $headers
     * @param  bool    $secure
     * @return RedirectResponse
     */
    public function guest($path, $status = 302, $headers = [], $secure = null)
    {
        $this->session->put('url.intended', $this->generator->full());

        return $this->to($path, $status, $headers, $secure);
    }

    /**
     * Create a new redirect response to the previously intended location.
     *
     * @param  string  $default
     * @param  int     $status
     * @param  array   $headers
     * @param  bool    $secure
     * @return RedirectResponse
     */
    public function intended($default = '/', $status = 302, $headers = [], $secure = null)
    {
        $path = $this->session->get('url.intended', $default);

        return $this->to($path, $status, $headers, $secure);
    }

    /**
     * Create a new redirect response to the given path.
     *
     * @param  string  $path
     * @param  int     $status
     * @param  array   $headers
     * @param  bool    $secure
     * @return RedirectResponse
     */
    public function to($path, $status = 302, $headers = [], $secure = null)
    {
        return $this->createRedirect($this->generator->to($path, [], $secure), $status, $headers);
    }

    /**
     * Create a new redirect response to an external URL (no validation).
     *
     * @param  string  $path
     * @param  int     $status
     * @param  array   $headers
     * @return RedirectResponse
     */
    public function away($path, $status = 302, $headers = [])
    {
        return $this->createRedirect($path, $status, $headers);
    }

    /**
     * Create a new redirect response to the given HTTPS path.
     *
     * @param  string  $path
     * @param  int     $status
     * @param  array   $headers
     * @return RedirectResponse
     */
    public function secure($path, $status = 302, $headers = [])
    {
        return $this->to($path, $status, $headers, true);
    }

    /**
     * Create a new redirect response to a named route.
     *
     * @param  string  $route
     * @param  array   $parameters
     * @param  int     $status
     * @param  array   $headers
     * @return RedirectResponse
     */
    public function router($route, $parameters = [], $status = 302, $headers = [])
    {
        return $this->to($this->generator->router($route, $parameters), $status, $headers);
    }

    /**
     * Create a new redirect response to a named route.
     *
     * @param  string  $route
     * @param  array   $parameters
     * @param  int     $status
     * @param  array   $headers
     * @return RedirectResponse
     */
    public function route($route, $parameters = [], $status = 302, $headers = [])
    {
        return $this->router($route, $parameters, $status, $headers);
    }

    /**
     * Create a new redirect response to a controller action.
     *
     * @param  string  $action
     * @param  array   $parameters
     * @param  int     $status
     * @param  array   $headers
     * @return RedirectResponse
     */
    public function action($action, $parameters = [], $status = 302, $headers = [])
    {
        return $this->to($this->generator->action($action, $parameters), $status, $headers);
    }

    /**
     * Create a new redirect response.
     *
     * @param  string  $path
     * @param  int     $status
     * @param  array   $headers
     * @return RedirectResponse
     */
    protected function createRedirect($path, $status, $headers)
    {
        return tap(new RedirectResponse($path, $status, $headers), function ($redirect) {
            /**
             * @var $redirect RedirectResponse
             */
            if (isset($this->session)) {
                $redirect->setSession($this->session);
            }

            $redirect->setRequest($this->generator->getRequest());
        });
    }

    /**
     * Determine if the given path is a valid URL.
     *
     * @param  string  $path
     * @return bool
     */
    public function isValidUrl($path)
    {
        if (!\Str::startsWith($path, ['#', '//', 'mailto:', 'tel:', 'http://', 'https://'])) {
            return filter_var($path, FILTER_VALIDATE_URL) !== false;
        }

        return true;
    }

    /**`
     * Get the URL generator instance.
     *
     * @return \VM\Http\UrlGenerator
     */
    public function getUrlGenerator()
    {
        return $this->generator;
    }

    /**
     * Set the active session store.
     *
     * @param  \VM\Http\Session  $session
     * @return void
     */
    public function setSession(Session $session)
    {
        $this->session = $session;
    }
}
