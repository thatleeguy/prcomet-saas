<?php

namespace App\Livewire\Observatory;

use App\Models\Company;
use App\Models\Watch;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Per-company watch directory.
 *
 * Each card shows the watch's name, kind, mode (literal vs literal+LLM),
 * recent-hits counts, and last-matched timestamp. The kind chip groups
 * companies / locations / products / generic terms visually without
 * forcing the user to filter explicitly.
 *
 * Surfaces an "Upgrade" CTA when the team doesn't yet have
 * llm_observatory_enabled, since several screens in this module reference it.
 */
#[Layout('layouts.app')]
#[Title('Observatory')]
class Index extends Component
{
    public Company $company;

    #[Url(as: 'kind', except: 'all')]
    public string $kindFilter = 'all';

    #[Url(as: 'q')]
    public string $search = '';

    public function mount(Company $company): void
    {
        abort_unless($company->team_id === auth()->user()->currentTeam?->id, 403);
        $this->company = $company;

        if (auth()->user()->current_company_id !== $company->id) {
            auth()->user()->switchCompany($company);
        }
    }

    public function setKind(string $kind): void
    {
        $this->kindFilter = $kind;
    }

    #[Computed]
    public function watches()
    {
        return Watch::query()
            ->where('company_id', $this->company->id)
            ->when($this->kindFilter !== 'all', fn ($q) => $q->where('kind', $this->kindFilter))
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->where('name', 'like', $term);
            })
            ->orderByDesc('last_matched_at')
            ->orderByDesc('hit_count')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function kindCounts(): array
    {
        return Watch::query()
            ->where('company_id', $this->company->id)
            ->selectRaw('kind, COUNT(*) as c')
            ->groupBy('kind')
            ->pluck('c', 'kind')
            ->toArray();
    }

    #[Computed]
    public function llmEnabled(): bool
    {
        return $this->company->team?->llmObservatoryEnabled() ?? false;
    }

    public function render()
    {
        return view('livewire.observatory.index');
    }
}
