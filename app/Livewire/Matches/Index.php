<?php

namespace App\Livewire\Matches;

use App\Models\Company;
use App\Models\MatchRecord;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Matches')]
class Index extends Component
{
    public ?Company $company = null;

    #[Url(as: 'status', except: 'new')]
    public string $statusFilter = 'new';

    public function mount(?Company $company = null): void
    {
        $user = auth()->user();

        if ($company && $company->exists) {
            abort_unless($company->team_id === $user->currentTeam?->id, 403);
            $this->company = $company;

            // Visiting /companies/{c}/matches pins focus — mirrors the
            // implicit switch on Show/Library/Branding so the chip stays
            // in sync with what you're looking at.
            if ($user->current_company_id !== $company->id) {
                $user->switchCompany($company);
            }

            return;
        }

        // No explicit company in the URL — fall back to the user's pinned
        // focus so /dashboard/matches always shows one company's matches.
        $this->company = $user->resolveCurrentCompany();
    }

    #[Computed]
    public function matches()
    {
        $team = auth()->user()->currentTeam;

        $query = MatchRecord::query()
            ->with(['company', 'publicationItem.source', 'author', 'pressRelease'])
            ->whereHas('company', fn ($q) => $q->where('team_id', $team->id))
            ->when($this->company, fn ($q) => $q->where('company_id', $this->company->id))
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('score')
            ->orderByDesc('created_at');

        return $query->limit(100)->get();
    }

    public function setStatus(string $status): void
    {
        $this->statusFilter = $status;
    }

    public function render()
    {
        return view('livewire.matches.index');
    }
}
