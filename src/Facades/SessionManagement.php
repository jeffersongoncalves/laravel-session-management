<?php

namespace JeffersonGoncalves\SessionManagement\Facades;

use Illuminate\Support\Facades\Facade;
use JeffersonGoncalves\SessionManagement\SessionService;

/**
 * @method static int revoke(string ...$ids)
 * @method static int revokeUser(mixed $user, ?string $exceptId = null)
 * @method static int prune()
 *
 * @see SessionService
 */
class SessionManagement extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SessionService::class;
    }
}
