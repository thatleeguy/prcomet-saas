<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A single PrComet-network subscriber identity, keyed by email.
 *
 * Subscribers attach to companies via the newsroom_subscriptions
 * pivot, so a single email can follow multiple companies and get
 * one digest covering all of them (instead of N independent
 * pings). PrComet owns the identity — even if a customer churns,
 * the subscriber stays in the network.
 *
 * Token is a 64-char URL-safe string used for the self-manage page
 * (/subscriptions/{token}) and unsubscribe links. Never expires;
 * regenerated only when the subscriber explicitly asks for it.
 */
class NewsroomSubscriber extends Model
{
    public const CADENCE_INSTANT = 'instant';
    public const CADENCE_DAILY = 'daily';
    public const CADENCE_WEEKLY = 'weekly';

    public const CADENCES = [
        self::CADENCE_INSTANT => 'Instant',
        self::CADENCE_DAILY => 'Daily digest',
        self::CADENCE_WEEKLY => 'Weekly digest',
    ];

    protected $fillable = [
        'email',
        'email_hashed',
        'token',
        'name',
        'cadence',
        'confirmed_at',
        'unsubscribed_at',
        'last_digest_sent_at',
        'source_ref',
        'ip',
    ];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
            'last_digest_sent_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $row) {
            if (! $row->email_hashed) {
                $row->email_hashed = self::hashEmail($row->email);
            }
            if (! $row->token) {
                $row->token = self::generateToken();
            }
        });
    }

    public static function hashEmail(string $email): string
    {
        return hash('sha256', mb_strtolower(trim($email)));
    }

    public static function generateToken(): string
    {
        return Str::random(64);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(NewsroomSubscription::class);
    }

    /**
     * The companies this subscriber actively follows. Excludes
     * soft-detached subscriptions (unsubscribed_at IS NOT NULL).
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'newsroom_subscriptions')
            ->wherePivotNull('unsubscribed_at')
            ->withPivot(['subscribed_at', 'unsubscribed_at'])
            ->withTimestamps();
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }

    public function isGloballyUnsubscribed(): bool
    {
        return $this->unsubscribed_at !== null;
    }

    public function manageUrl(): string
    {
        return route('subscriptions.manage', $this->token);
    }

    public function unsubscribeUrl(): string
    {
        return route('subscriptions.unsubscribe-all', $this->token);
    }

    public function confirmUrl(): string
    {
        return route('subscriptions.confirm', $this->token);
    }
}
