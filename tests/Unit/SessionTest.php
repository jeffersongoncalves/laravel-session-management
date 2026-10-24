<?php

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use JeffersonGoncalves\SessionManagement\Models\Session;

it('follows the session table and connection config', function () {
    config()->set('session.table', 'custom_sessions');
    config()->set('session.connection', 'other');

    expect((new Session)->getTable())->toBe('custom_sessions')
        ->and((new Session)->getConnectionName())->toBe('other');

    config()->set('session-management.table', 'package_sessions');

    expect((new Session)->getTable())->toBe('package_sessions');
});

it('uses a string primary key without timestamps', function () {
    $session = new Session;

    expect($session->getKeyType())->toBe('string')
        ->and($session->getIncrementing())->toBeFalse()
        ->and($session->usesTimestamps())->toBeFalse();
});

it('decodes a php serialized payload and masks sensitive keys', function () {
    $session = makeSession([
        '_token' => 'csrf-secret',
        'password_hash_web' => 'hash',
        'url' => ['intended' => 'https://example.com/dashboard'],
    ]);

    expect($session->decodePayload())->toBe([
        '_token' => '********',
        'password_hash_web' => '********',
        'url' => ['intended' => 'https://example.com/dashboard'],
    ])->and($session->decodePayload(masked: false)['_token'])->toBe('csrf-secret');
});

it('never instantiates objects stored in the payload', function () {
    $session = makeSession(['errors' => (new ViewErrorBag)->put('default', new MessageBag(['email' => 'Required']))]);

    $errors = $session->decodePayload()['errors'];

    expect($errors['__class'])->toBe(ViewErrorBag::class)
        ->and($errors['bags']['default']['__class'])->toBe(MessageBag::class);
});

it('decodes encrypted payloads', function () {
    config()->set('session.encrypt', true);

    $session = makeSession();
    $session->payload = base64_encode(Crypt::encrypt(serialize(['foo' => 'bar'])));

    expect($session->decodePayload())->toBe(['foo' => 'bar']);
});

it('decodes json serialized payloads', function () {
    config()->set('session.serialization', 'json');

    $session = makeSession();
    $session->payload = base64_encode(json_encode(['foo' => 'bar']));

    expect($session->decodePayload())->toBe(['foo' => 'bar']);
});

it('returns an empty array for undecodable payloads', function () {
    $session = makeSession();
    $session->payload = 'not base64 !!';

    expect($session->decodePayload())->toBe([]);

    config()->set('session.encrypt', true);
    $session->payload = base64_encode('tampered');

    expect($session->decodePayload())->toBe([]);
});

it('scopes active, expired, guest and authenticated sessions', function () {
    $active = makeSession(userId: 1, minutesAgo: 5);
    $expired = makeSession(minutesAgo: 121);

    expect(Session::active()->pluck('id')->all())->toBe([$active->id])
        ->and(Session::expired()->pluck('id')->all())->toBe([$expired->id])
        ->and(Session::guests()->pluck('id')->all())->toBe([$expired->id])
        ->and(Session::authenticated()->pluck('id')->all())->toBe([$active->id])
        ->and(Session::forUser(1)->pluck('id')->all())->toBe([$active->id])
        ->and($active->isExpired)->toBeFalse()
        ->and($expired->isExpired)->toBeTrue()
        ->and($active->lastActivityAt->getTimestamp())->toBe($active->last_activity);
});

it('detects the current session', function () {
    $session = makeSession();

    app('session')->setId($session->id);

    expect($session->isCurrent)->toBeTrue()
        ->and(makeSession()->isCurrent)->toBeFalse();
});

it('belongs to the configured user model', function () {
    config()->set('auth.providers.users.model', TestSessionUser::class);

    $user = TestSessionUser::query()->create(['name' => 'Jane']);

    expect(makeSession(userId: $user->id)->user->name)->toBe('Jane');
});

class TestSessionUser extends User
{
    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = false;
}
