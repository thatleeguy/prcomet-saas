<?php

namespace App\Models;

use App\Mail\TeamActivated;
use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Mail;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;

class Team extends JetstreamTeam
{
    /** @use HasFactory<TeamFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'personal_team',
        'is_active',
        'max_companies',
        'activated_at',
        'activated_by_id',
        'billing_notes',
        'paid_through_at',
        'llm_observatory_enabled',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'personal_team' => 'boolean',
            'is_active' => 'boolean',
            'max_companies' => 'integer',
            'activated_at' => 'datetime',
            'paid_through_at' => 'datetime',
            'llm_observatory_enabled' => 'boolean',
        ];
    }

    /**
     * Is the team entitled to the LLM-confirmation upgrade on Observatory
     * watches? Drives both the UI surface (the upgrade CTA disappears when
     * true) and the scan engine (literal_llm watches downgrade to literal
     * when this returns false).
     */
    public function llmObservatoryEnabled(): bool
    {
        return (bool) $this->llm_observatory_enabled;
    }

    /**
     * The super-admin who activated this team, if any.
     */
    public function activatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'activated_by_id');
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    /**
     * Catalogues this team subscribes to. The pivot carries provenance
     * (when it was granted, by whom, complimentary vs paid, expiry).
     * Source::visibleTo joins through this so the team's corpus reflects
     * exactly which catalogues they pay for.
     */
    public function sourceGroups(): BelongsToMany
    {
        return $this->belongsToMany(SourceGroup::class, 'source_group_subscriptions')
            ->withPivot(['is_complimentary', 'subscribed_at', 'expires_at', 'granted_by_user_id', 'notes'])
            ->withTimestamps();
    }

    /**
     * Can this team add one more company under its current seat allowance?
     */
    public function canAddCompany(): bool
    {
        return $this->is_active
            && $this->companies()->count() < $this->max_companies;
    }

    /**
     * Remaining seats. Negative is treated as zero (admin can over-allocate
     * historically by reducing max_companies after companies were created).
     */
    public function seatsRemaining(): int
    {
        return max(0, $this->max_companies - $this->companies()->count());
    }

    /**
     * Activate the team and grant it `$seats` companies.
     *
     * Idempotent — re-activating just updates the seat count and audit fields.
     * Emails the team owner on the first activation only.
     */
    public function activate(int $seats, User $admin): void
    {
        $wasInactive = ! $this->is_active;

        $this->forceFill([
            'is_active' => true,
            'max_companies' => $seats,
            'activated_at' => $this->activated_at ?? now(),
            'activated_by_id' => $admin->id,
        ])->save();

        if ($wasInactive && $this->owner?->email) {
            Mail::to($this->owner->email)->send(new TeamActivated($this->fresh()));
        }
    }

    /**
     * Suspend the team — workspace becomes inaccessible, seat count zeroed.
     * Data is preserved.
     */
    public function suspend(): void
    {
        $this->forceFill([
            'is_active' => false,
            'max_companies' => 0,
        ])->save();
    }
}
