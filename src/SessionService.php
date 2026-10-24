<?php

namespace JeffersonGoncalves\SessionManagement;

use JeffersonGoncalves\SessionManagement\Models\Session;

class SessionService
{
    /**
     * Delete the given sessions, logging their owners out on the next request.
     *
     * Returns the number of deleted sessions.
     */
    public function revoke(string ...$ids): int
    {
        if ($ids === []) {
            return 0;
        }

        return Session::query()->whereKey($ids)->delete();
    }

    /**
     * Delete every session of the given user (model or id), optionally keeping one
     * (e.g. the current session) alive.
     *
     * Note: a "remember me" cookie logs the user back in; cycle the user's
     * remember token too when that must not happen.
     */
    public function revokeUser(mixed $user, ?string $exceptId = null): int
    {
        return Session::query()
            ->forUser($user)
            ->when($exceptId !== null, fn ($query) => $query->whereKeyNot($exceptId))
            ->delete();
    }

    /**
     * Delete every expired session, like the session garbage collector does.
     */
    public function prune(): int
    {
        return Session::query()->expired()->delete();
    }
}
