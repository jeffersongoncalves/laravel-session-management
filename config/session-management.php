<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sessions Table
    |--------------------------------------------------------------------------
    |
    | The table and connection used by Laravel's "database" session driver.
    | Leave them null to follow "session.table" and "session.connection".
    |
    */
    'table' => null,

    'connection' => null,

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The model the "user_id" column points to. Null falls back to the model
    | of the "users" auth provider ("auth.providers.users.model").
    |
    */
    'user_model' => null,

    /*
    |--------------------------------------------------------------------------
    | Hidden Payload Keys
    |--------------------------------------------------------------------------
    |
    | Payload keys masked when a session payload is decoded for display. The
    | CSRF token and the password hashes stored by AuthenticateSession can be
    | used to hijack a session, so they are hidden by default. Wildcards ("*")
    | are supported.
    |
    */
    'hidden_payload_keys' => [
        '_token',
        'password_hash_*',
    ],
];
