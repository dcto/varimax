<?php

namespace VM\Services;

/**
* @package Services
*/
abstract class ServiceProvider extends \Illuminate\Support\ServiceProvider 
{
    /**
     * deferred boot service provider
     * @var bool
     */
    protected $defer = false;

    /**
     * Determine if the provider is deferred.
     * @return bool
     */
    public function isDeferred()
    { 
        return $this->defer;
    }
}