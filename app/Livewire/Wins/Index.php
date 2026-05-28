<?php

namespace App\Livewire\Wins;

use App\Models\Company;
use App\Models\MatchRecord;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Wins')]
class Index extends Component
{
    /**
     * Wins reflect the currently-focused company so you don't see another
     * company's placements when you're heads-down on this one. With no
     * company in focus (rare: empty team), falls back to team-wide.
     */
    #[Computed]
    public function currentCompany(): ?Company
    {
        return auth()->user()->resolveCurrentCompany();
    }

    protected function baseQuery()
    {
        $team = auth()->user()->currentTeam;
        $current = $this->currentCompany;

        return MatchRecord::query()
            ->when($current,
                fn ($q) => $q->where('company_id', $current->id),
                fn ($q) => $q->whereIn('company_id',
                    Company::where('team_id', $team->id)->pluck('id'))
            );
    }

    #[Computed]
    public function placedMatches()
    {
        return $this->baseQuery()
            ->with(['company', 'publicationItem.source', 'author'])
            ->where('status', MatchRecord::STATUS_PLACED)
            ->orderByDesc('placement_published_at')
            ->orderByDesc('updated_at')
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        $query = $this->baseQuery();

        return [
            'total' => (clone $query)->count(),
            'contacted' => (clone $query)->where('status', MatchRecord::STATUS_CONTACTED)->count(),
            'placed' => (clone $query)->where('status', MatchRecord::STATUS_PLACED)->count(),
        ];
    }

    public function render()
    {
        return view('livewire.wins.index');
    }
}
