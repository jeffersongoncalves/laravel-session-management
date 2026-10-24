<?php

use JeffersonGoncalves\SessionManagement\Facades\SessionManagement;
use JeffersonGoncalves\SessionManagement\Models\Session;
use JeffersonGoncalves\SessionManagement\SessionService;

it('resolves the service as a singleton', function () {
    expect(app(SessionService::class))->toBe(app(SessionService::class));
});

it('revokes the given sessions', function () {
    [$a, $b, $c] = [makeSession(), makeSession(), makeSession()];

    expect(SessionManagement::revoke($a->id, $b->id))->toBe(2)
        ->and(Session::query()->pluck('id')->all())->toBe([$c->id])
        ->and(SessionManagement::revoke())->toBe(0);
});

it('revokes every session of a user except one', function () {
    $keep = makeSession(userId: 1);
    makeSession(userId: 1);
    $other = makeSession(userId: 2);

    expect(SessionManagement::revokeUser(1, $keep->id))->toBe(1)
        ->and(Session::query()->pluck('id')->sort()->values()->all())
        ->toBe(collect([$keep->id, $other->id])->sort()->values()->all());
});

it('prunes expired sessions', function () {
    $active = makeSession(minutesAgo: 10);
    makeSession(minutesAgo: 500);

    expect(SessionManagement::prune())->toBe(1)
        ->and(Session::query()->pluck('id')->all())->toBe([$active->id]);
});

it('prunes expired sessions from the command', function () {
    makeSession(minutesAgo: 500);

    $this->artisan('session-management:prune')
        ->expectsOutputToContain('1 expired session(s) pruned.')
        ->assertSuccessful();

    expect(Session::query()->count())->toBe(0);
});
