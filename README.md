<div class="filament-hidden">

![Laravel Session Management](https://raw.githubusercontent.com/jeffersongoncalves/laravel-session-management/main/art/jeffersongoncalves-laravel-session-management.png)

</div>

# Laravel Session Management

[![Buy Me A Coffee](https://img.shields.io/badge/Buy%20Me%20A%20Coffee-support-FFDD00?style=flat-square&logo=buy-me-a-coffee&logoColor=black)](https://buymeacoffee.com/jeffersongoncalves)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/laravel-session-management.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-session-management)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-session-management/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/jeffersongoncalves/laravel-session-management/actions?query=workflow%3Atests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/laravel-session-management.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-session-management)
[![License](https://img.shields.io/packagist/l/jeffersongoncalves/laravel-session-management.svg?style=flat-square)](LICENSE.md)

Read and manage Laravel's database session table (`sessions`) through an Eloquent model and a small service layer: list who is logged in, decode session payloads safely, revoke sessions and prune expired ones. It is the framework-agnostic core of [`jeffersongoncalves/filament-session-management`](https://github.com/jeffersongoncalves/filament-session-management).

## Features

- `Session` model on top of the `sessions` table (follows `session.table` / `session.connection`)
- `user()` relation plus `active()`, `expired()`, `guests()`, `authenticated()` and `forUser()` scopes
- `decodePayload()` that handles plain, encrypted (`session.encrypt`) and JSON (`session.serialization`) payloads
- Payloads are unserialized with `allowed_classes => false`: stored objects are never instantiated
- CSRF token and `password_hash_*` keys masked by default
- `SessionManagement::revoke()`, `revokeUser()` and `prune()`, plus a `session-management:prune` command

## Installation

```bash
composer require jeffersongoncalves/laravel-session-management
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag="session-management-config"
```

> The package reads the table written by Laravel's `database` session driver. Set `SESSION_DRIVER=database` and create the table (`php artisan make:session-table` on older apps; new Laravel apps ship it in the default migration).

## Usage

### The model

```php
use JeffersonGoncalves\SessionManagement\Models\Session;

$sessions = Session::query()
    ->active()
    ->forUser($user)
    ->orderByDesc('last_activity')
    ->get();

$session->user;            // owner, or null for guests
$session->lastActivityAt;  // Carbon
$session->isExpired;       // older than session.lifetime
$session->isCurrent;       // the session of the current request
$session->decodePayload(); // ['_token' => '********', 'url' => [...], ...]
$session->decodePayload(masked: false);
```

### The service

```php
use JeffersonGoncalves\SessionManagement\Facades\SessionManagement;

SessionManagement::revoke($id, $otherId);                 // delete sessions by id
SessionManagement::revokeUser($user, session()->getId()); // log a user out everywhere else
SessionManagement::prune();                               // delete expired sessions
```

Revoking deletes the session row, so its owner is logged out on the next request. A "remember me" cookie logs them back in: cycle the user's `remember_token` too when that must not happen (or use `Auth::logoutOtherDevices()` for the current user).

### Pruning

Laravel's session garbage collector only runs on a lottery. To prune on a schedule:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('session-management:prune')->daily();
```

### Configuration

```php
return [
    'table' => null,      // null => session.table
    'connection' => null, // null => session.connection
    'user_model' => null, // null => auth.providers.users.model
    'hidden_payload_keys' => [
        '_token',
        'password_hash_*',
    ],
];
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jefferson Gonçalves](https://github.com/jeffersongoncalves)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
