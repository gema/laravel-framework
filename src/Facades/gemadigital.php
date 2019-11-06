<?php

namespace gemadigital\gemadigital\Facades;

use Illuminate\Support\Facades\Facade;

class gemadigital extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'gemadigital';
    }
}
