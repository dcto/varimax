<?php

use Illuminate\Support\Facades\Facade;

class ID extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'id';
    }
}
