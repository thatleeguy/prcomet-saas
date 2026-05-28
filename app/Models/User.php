<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasProfilePhoto;
    use HasTeams {
        switchTeam as private switchTeamFromJetstream;
    }
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public const DIGEST_DAILY = 'daily';
    public const DIGEST_WEEKLY = 'weekly';
    public const DIGEST_OFF = 'off';

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'digest_frequency',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'digest_sent_at' => 'datetime',
        ];
    }

    /**
     * Super-admin gate for the Filament panel.
     *
     * Only users with is_admin=true can access /admin. This is the operator
     * surface — activating teams, setting seat limits, managing the global
     * source catalog. Customers never see it.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin === true;
    }

    /*
    |--------------------------------------------------------------------------
    | Current company
    |--------------------------------------------------------------------------
    | Mirrors Jetstream's currentTeam pattern but at the company level. A
    | team can monitor several companies; "current company" is the one the
    | user is actively working in. The workspace UI (matches list, dashboard,
    | Library + Branding nav items) scopes to it so you can't accidentally
    | edit assets for the wrong company.
    |
    | The accessor self-heals: if the stored id is missing or no longer
    | belongs to the current team (e.g. they switched teams), it picks the
    | first available company and persists that.
    */

    public function currentCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'current_company_id');
    }

    /**
     * Override Jetstream's switchTeam to also reset the focused company. The
     * stored current_company_id might belong to the old team, which would
     * cause confusing UI on the new team. Null it here and let the accessor
     * re-elect a default on the next read.
     */
    public function switchTeam($team)
    {
        $switched = $this->switchTeamFromJetstream($team);

        if ($switched) {
            $this->forceFill(['current_company_id' => null])->save();
            $this->unsetRelation('currentCompany');
        }

        return $switched;
    }

    /**
     * Resolve the company the user is focused on, defaulting to the first
     * company in their current team if nothing is set or the stored value
     * is stale. Returns null only when the current team has no companies.
     */
    public function resolveCurrentCompany(): ?Company
    {
        $team = $this->currentTeam;

        if (! $team) {
            return null;
        }

        $current = $this->current_company_id
            ? Company::where('team_id', $team->id)->find($this->current_company_id)
            : null;

        if ($current) {
            return $current;
        }

        $fallback = Company::where('team_id', $team->id)->orderBy('id')->first();

        if ($fallback && $this->current_company_id !== $fallback->id) {
            $this->forceFill(['current_company_id' => $fallback->id])->saveQuietly();
        }

        return $fallback;
    }

    /**
     * Pin a company as the current focus. Refuses companies that don't
     * belong to the user's current team — callers should switch teams
     * first if they need cross-team navigation.
     */
    public function switchCompany(Company $company): bool
    {
        if ($company->team_id !== $this->current_team_id) {
            return false;
        }

        $this->forceFill(['current_company_id' => $company->id])->save();
        $this->setRelation('currentCompany', $company);

        return true;
    }
}
