<?php

namespace App\Livewire\Companies;

use App\Models\Company;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Companies')]
class Index extends Component
{
    public string $search = '';

    #[Computed]
    public function companies()
    {
        $team = auth()->user()->currentTeam;

        return Company::query()
            ->where('team_id', $team->id)
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function seatsRemaining(): int
    {
        return auth()->user()->currentTeam->seatsRemaining();
    }

    #[Computed]
    public function canAdd(): bool
    {
        return auth()->user()->currentTeam->canAddCompany();
    }

    public function render()
    {
        return view('livewire.companies.index');
    }
}
