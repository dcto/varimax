<?php

namespace VM\Services;

/**
* @package Services
* @method void boot
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


    /**
     * Get bootingCallbacks 
     * @return array
     */
    public function getBootingCallbacks()
    {
        return $this->bootingCallbacks;
    }

    /**
     * Get bootedCallbacks
     * @return array
     */
    public function getBootedCallbacks()
    {
        return $this->bootedCallbacks;
    }
}