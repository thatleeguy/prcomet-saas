<?php

namespace App\Models;

use Database\Factories\DemoRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Inbound demo / early-access request from the marketing landing page.
 *
 * Pre-PMF we capture these into the DB rather than hand them straight to an
 * email — gives the admin a queryable list and avoids depending on a mail
 * provider being wired up before launch.
 */
class DemoRequest extends Model
{
    /** @use HasFactory<DemoRequestFactory> */
    use HasFactory;

    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'name',
        'email',
        'company',
        'role',
        'website',
        'notes',
        'status',
        'contacted_at',
    ];

    protected function casts(): array
    {
        return [
            'contacted_at' => 'datetime',
        ];
    }
}
