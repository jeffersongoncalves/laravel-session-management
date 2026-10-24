<?php

namespace JeffersonGoncalves\SessionManagement\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property int|string|null $user_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string $payload
 * @property int $last_activity
 * @property-read Carbon $lastActivityAt
 * @property-read bool $isExpired
 * @property-read bool $isCurrent
 *
 * @method static Builder<static> active()
 * @method static Builder<static> expired()
 * @method static Builder<static> guests()
 * @method static Builder<static> authenticated()
 * @method static Builder<static> forUser(mixed $user)
 */
class Session extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected $hidden = ['payload'];

    protected $casts = [
        'last_activity' => 'integer',
    ];

    public function getTable(): string
    {
        return config('session-management.table') ?? config('session.table') ?? 'sessions';
    }

    public function getConnectionName(): ?string
    {
        return config('session-management.connection') ?? config('session.connection') ?? parent::getConnectionName();
    }

    /**
     * @return BelongsTo<Model, $this>
     */
    public function user(): BelongsTo
    {
        /** @var class-string<Model> $model */
        $model = config('session-management.user_model') ?? config('auth.providers.users.model');

        return $this->belongsTo($model, 'user_id');
    }

    /**
     * Sessions whose last activity is within "session.lifetime".
     *
     * @param  Builder<static>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('last_activity', '>=', static::expiresBefore());
    }

    /**
     * Sessions Laravel no longer accepts and will garbage collect.
     *
     * @param  Builder<static>  $query
     */
    public function scopeExpired(Builder $query): void
    {
        $query->where('last_activity', '<', static::expiresBefore());
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeGuests(Builder $query): void
    {
        $query->whereNull('user_id');
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeAuthenticated(Builder $query): void
    {
        $query->whereNotNull('user_id');
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeForUser(Builder $query, mixed $user): void
    {
        $query->where('user_id', $user instanceof Model ? $user->getKey() : $user);
    }

    /**
     * The unix timestamp a session must be active after to still be valid.
     */
    public static function expiresBefore(): int
    {
        return Carbon::now()->subMinutes((int) config('session.lifetime', 120))->getTimestamp();
    }

    public function getLastActivityAtAttribute(): Carbon
    {
        return Carbon::createFromTimestamp($this->last_activity);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->last_activity < static::expiresBefore();
    }

    public function getIsCurrentAttribute(): bool
    {
        return app()->bound('session') && $this->id === app('session')->getId();
    }

    /**
     * Decode the stored payload into the session attributes array.
     *
     * Mirrors the database handler + store: base64, then decryption when
     * "session.encrypt" is on, then PHP or JSON unserialization. Objects are
     * never instantiated (allowed_classes=false); they come back as arrays
     * with a "__class" key. Keys listed in "session-management.hidden_payload_keys"
     * are masked unless $masked is false. Undecodable payloads return [].
     *
     * @return array<array-key, mixed>
     */
    public function decodePayload(bool $masked = true): array
    {
        $data = base64_decode((string) $this->payload, true);

        if ($data === false) {
            return [];
        }

        if (config('session.encrypt')) {
            try {
                $data = Crypt::decryptString($data);
            } catch (DecryptException) {
                return [];
            }

            // The encrypter serializes the value before encrypting it.
            $data = @unserialize($data, ['allowed_classes' => false]);

            if (! is_string($data)) {
                return [];
            }
        }

        $attributes = config('session.serialization') === 'json'
            ? json_decode($data, true)
            : @unserialize($data, ['allowed_classes' => false]);

        if (! is_array($attributes)) {
            return [];
        }

        $attributes = static::normalize($attributes);

        return $masked ? static::mask($attributes) : $attributes;
    }

    /**
     * Turn (incomplete) objects into arrays so the payload is plain data.
     */
    protected static function normalize(mixed $value, int $depth = 0): mixed
    {
        if ($depth > 64) {
            return '…';
        }

        if (is_object($value)) {
            $properties = [];

            foreach ((array) $value as $key => $property) {
                // Strip the "\0*\0" / "\0Class\0" prefixes PHP adds to protected/private property names.
                $properties[(string) preg_replace('/^\0.+\0/', '', (string) $key)] = $property;
            }

            $class = $properties['__PHP_Incomplete_Class_Name'] ?? $value::class;
            unset($properties['__PHP_Incomplete_Class_Name']);

            $value = ['__class' => $class] + $properties;
        }

        return is_array($value)
            ? array_map(fn (mixed $item): mixed => static::normalize($item, $depth + 1), $value)
            : $value;
    }

    /**
     * @param  array<array-key, mixed>  $attributes
     * @return array<array-key, mixed>
     */
    protected static function mask(array $attributes): array
    {
        $hidden = (array) config('session-management.hidden_payload_keys', []);

        foreach ($attributes as $key => $value) {
            if (Str::is($hidden, (string) $key)) {
                $attributes[$key] = '********';
            }
        }

        return $attributes;
    }
}
