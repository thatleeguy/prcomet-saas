<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per (company, email) capture from the public newsroom
 * subscribe form. The hashed-email unique index stops duplicate
 * submissions silently.
 */
class NewsroomSubscriber extends Model
{
    protected $fillable = [
        'company_id',
        'email',
        'email_hashed',
        'source_ref',
        'ip',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public static function hashEmail(string $email): string
    {
        return hash('sha256', mb_strtolower(trim($email)));
    }
}
