<?php

namespace JeffersonGoncalves\SessionManagement\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JeffersonGoncalves\SessionManagement\SessionManagement
 */
class SessionManagement extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-session-management';
    }
}
