<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use JeffersonGoncalves\SessionManagement\Models\Session;
use JeffersonGoncalves\SessionManagement\Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

/**
 * Insert a session row the way the database session handler would store it.
 *
 * @param  array<string, mixed>  $attributes
 */
function makeSession(array $attributes = [], ?int $userId = null, int $minutesAgo = 0): Session
{
    return Session::query()->create([
        'id' => Str::random(40),
        'user_id' => $userId,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0',
        'payload' => base64_encode(serialize($attributes)),
        'last_activity' => Carbon::now()->subMinutes($minutesAgo)->getTimestamp(),
    ]);
}
