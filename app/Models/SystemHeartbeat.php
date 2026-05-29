<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Liveness marker for a background subsystem.
 *
 *   SystemHeartbeat::beat('scheduler');           — minute cron
 *   SystemHeartbeat::beat('queue', ['worker' => gethostname()]);  — queue ping job
 *
 * Each `kind` is unique so beats overwrite (upsert) rather than
 * accumulating rows. SystemHealthService reads the most recent
 * timestamp and compares it against an expected cadence.
 */
class SystemHeartbeat extends Model
{
    public const KIND_SCHEDULER = 'scheduler';
    public const KIND_QUEUE = 'queue';

    protected $fillable = ['kind', 'last_at', 'payload'];

    protected function casts(): array
    {
        return [
            'last_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public static function beat(string $kind, ?array $payload = null): self
    {
        return static::updateOrCreate(
            ['kind' => $kind],
            ['last_at' => now(), 'payload' => $payload],
        );
    }

    public static function last(string $kind): ?CarbonInterface
    {
        return static::where('kind', $kind)->value('last_at');
    }
}
