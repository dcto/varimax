<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class Log
 */
class Mail extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mail';
    }
}
