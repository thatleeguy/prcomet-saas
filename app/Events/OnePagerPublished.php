<?php

namespace App\Events;

use App\Models\OnePager;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a OnePager transitions to status=published. The
 * subscriber notification listener picks this up and dispatches
 * instant emails to cadence=instant subscribers; daily/weekly
 * subscribers are caught by the next digest sweep.
 */
class OnePagerPublished
{
    use Dispatchable, SerializesModels;

    public function __construct(public OnePager $onePager) {}
}
