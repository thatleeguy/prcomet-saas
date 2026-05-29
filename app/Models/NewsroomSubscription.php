<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Subscriber × Company pivot. Soft-detached via unsubscribed_at so
 * a subscriber can drop and re-attach without losing the audit
 * trail, and so the customer's "lifetime followers" metric stays
 * accurate across temporary opt-outs.
 */
class NewsroomSubscription extends Model
{
    protected $fillable = [
        'newsroom_subscriber_id',
        'company_id',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(NewsroomSubscriber::class, 'newsroom_subscriber_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isActive(): bool
    {
        return $this->unsubscribed_at === null;
    }
}
