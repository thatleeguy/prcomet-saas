<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per page view on a one-pager. IPs are hashed (never stored raw) so
 * we can dedupe unique viewers + filter bots without keeping PII.
 */
class OnePagerView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'one_pager_id',
        'ip_hash',
        'user_agent',
        'referrer',
        'viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
        ];
    }

    public function onePager(): BelongsTo
    {
        return $this->belongsTo(OnePager::class);
    }
}
