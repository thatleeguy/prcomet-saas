<?php

namespace App\Livewire\Observatory;

use App\Models\Company;
use App\Models\Watch;
use App\Models\WatchHit;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Observatory inbox.
 *
 * The landing surface is a Gmail-style feed of every hit across every
 * watch on the focused company, newest first, with unread items styled
 * for emphasis. Watches live in the left rail as "labels" with unread
 * counters — click one to filter the feed to its hits.
 *
 * Filtering across two dimensions:
 *   - Watch (the left rail "label" selector)
 *   - Status: All | Unread | (Confirmed/Pending/Rejected when LLM enabled)
 *
 * "Mark all as read" walks the currently-filtered set so the operator
 * can clear an in-focus subset (a single noisy watch, the unread queue,
 * etc.) without nuking history.
 */
#[Layout('layouts.app')]
#[Title('Observatory')]
class Index extends Component
{
    use WithPagination;

    public Company $company;

    #[Url(as: 'watch', except: 0)]
    public int $watchFilter = 0; // 0 = all watches

    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all'; // all|unread|confirmed|pending|rejected

    #[Url(as: 'q')]
    public string $search = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingWatchFilter(): void { $this->resetPage(); }

    public function mount(Company $company): void
    {
        abort_unless($company->team_id === auth()->user()->currentTeam?->id, 403);
        $this->company = $company;

        if (auth()->user()->current_company_id !== $company->id) {
            auth()->user()->switchCompany($company);
        }
    }

    public function setStatus(string $status): void
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function setWatch(int $watchId): void
    {
        $this->watchFilter = $watchId;
        $this->resetPage();
    }

    public function markRead(int $hitId): void
    {
        $hit = WatchHit::query()
            ->whereHas('watch', fn ($q) => $q->where('company_id', $this->company->id))
            ->findOrFail($hitId);

        $hit->markSeen();
    }

    public function markAllRead(): void
    {
        // Walk only the rows the user can currently see — same filter
        // pipeline as the feed query.
        $this->scopedHitsQuery()
            ->whereNull('seen_at')
            ->update(['seen_at' => now()]);

        session()->flash('status', 'Marked all visible hits as read.');
    }

    #[Computed]
    public function watches()
    {
        return Watch::query()
            ->where('company_id', $this->company->id)
            ->withCount(['hits as unread_count' => fn ($q) => $q->whereNull('seen_at')])
            ->orderByDesc('hit_count')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function llmEnabled(): bool
    {
        return $this->company->team?->llmObservatoryEnabled() ?? false;
    }

    /**
     * Aggregate counters for the filter chips at the top of the feed.
     * Shares the watch-filter clause with the feed so the badges always
     * reflect the currently-narrowed slice.
     */
    #[Computed]
    public function statusCounts(): array
    {
        $base = WatchHit::query()
            ->whereHas('watch', fn ($q) => $q->where('company_id', $this->company->id))
            ->when($this->watchFilter > 0, fn ($q) => $q->where('watch_id', $this->watchFilter));

        return [
            'all' => (clone $base)->count(),
            'unread' => (clone $base)->whereNull('seen_at')->count(),
            'confirmed' => (clone $base)->where('confirmed_by_llm', true)->count(),
            'pending' => (clone $base)->whereNull('confirmed_by_llm')->count(),
            'rejected' => (clone $base)->where('confirmed_by_llm', false)->count(),
        ];
    }

    /**
     * Shared query builder applied to the feed and to markAllRead so the
     * "what's visible" definition stays in one place.
     */
    protected function scopedHitsQuery()
    {
        return WatchHit::query()
            ->whereHas('watch', fn ($q) => $q->where('company_id', $this->company->id))
            ->when($this->watchFilter > 0, fn ($q) => $q->where('watch_id', $this->watchFilter))
            ->when($this->statusFilter === 'unread', fn ($q) => $q->whereNull('seen_at'))
            ->when($this->statusFilter === 'confirmed', fn ($q) => $q->where('confirmed_by_llm', true))
            ->when($this->statusFilter === 'pending', fn ($q) => $q->whereNull('confirmed_by_llm'))
            ->when($this->statusFilter === 'rejected', fn ($q) => $q->where('confirmed_by_llm', false))
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->where('context_snippet', 'like', $term);
            });
    }

    public function render()
    {
        $hits = $this->scopedHitsQuery()
            ->with('watch')
            ->orderByDesc('matched_at')
            ->paginate(25);

        return view('livewire.observatory.index', ['hits' => $hits]);
    }
}
