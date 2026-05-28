<?php

namespace App\Livewire\OnePagers;

use App\Models\Company;
use App\Models\OnePager;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Per-company one-pager directory.
 *
 * One-pagers are tightly bound to MatchRecord (each match auto-creates a
 * draft as soon as the user touches its status), but the editor view nests
 * them under /matches/{match}/one-pager. Without this index, the only way
 * to find an existing one-pager was to remember which match created it.
 *
 * Filterable by status with a quick "open public URL" affordance on
 * published rows so the user can grab a link without an extra page load.
 */
#[Layout('layouts.app')]
#[Title('One-pagers')]
class Index extends Component
{
    public Company $company;

    /** all | draft | published | unpublished */
    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all';

    #[Url(as: 'q')]
    public string $search = '';

    public function mount(Company $company): void
    {
        abort_unless($company->team_id === auth()->user()->currentTeam?->id, 403);
        $this->company = $company;

        // Implicit focus switch — consistent with the other company-scoped pages.
        if (auth()->user()->current_company_id !== $company->id) {
            auth()->user()->switchCompany($company);
        }
    }

    public function setStatus(string $status): void
    {
        $this->statusFilter = $status;
    }

    #[Computed]
    public function onePagers()
    {
        return OnePager::query()
            ->with([
                'match.publicationItem.source',
                'match.author',
                'match.pressRelease',
            ])
            ->whereHas('match', fn ($q) => $q->where('company_id', $this->company->id))
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($inner) use ($term) {
                    $inner->whereHas('match.publicationItem', fn ($i) => $i->where('title', 'like', $term))
                          ->orWhereHas('match.author', fn ($a) => $a->where('name', 'like', $term))
                          ->orWhereHas('match.publicationItem.source', fn ($s) => $s->where('name', 'like', $term))
                          ->orWhereHas('match.pressRelease', fn ($p) => $p->where('title', 'like', $term));
                });
            })
            ->orderByRaw("CASE status
                          WHEN '".OnePager::STATUS_PUBLISHED."' THEN 1
                          WHEN '".OnePager::STATUS_DRAFT."' THEN 2
                          ELSE 3 END")
            ->orderByDesc('updated_at')
            ->get();
    }

    /**
     * Status counts for the filter chip row — same query base as the list
     * so the numbers stay honest with the search filter.
     */
    #[Computed]
    public function counts(): array
    {
        return OnePager::query()
            ->whereHas('match', fn ($q) => $q->where('company_id', $this->company->id))
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($inner) use ($term) {
                    $inner->whereHas('match.publicationItem', fn ($i) => $i->where('title', 'like', $term))
                          ->orWhereHas('match.author', fn ($a) => $a->where('name', 'like', $term));
                });
            })
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status')
            ->toArray();
    }

    public function render()
    {
        return view('livewire.one-pagers.index');
    }
}
