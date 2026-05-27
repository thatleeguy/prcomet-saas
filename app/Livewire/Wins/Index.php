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
    #[Computed]
    public function placedMatches()
    {
        $team = auth()->user()->currentTeam;
        $teamCompanyIds = Company::where('team_id', $team->id)->pluck('id');

        return MatchRecord::query()
            ->with(['company', 'publicationItem.source', 'author'])
            ->whereIn('company_id', $teamCompanyIds)
            ->where('status', MatchRecord::STATUS_PLACED)
            ->orderByDesc('placement_published_at')
            ->orderByDesc('updated_at')
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        $team = auth()->user()->currentTeam;
        $teamCompanyIds = Company::where('team_id', $team->id)->pluck('id');
        $query = MatchRecord::whereIn('company_id', $teamCompanyIds);

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
