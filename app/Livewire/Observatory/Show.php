<?php

namespace App\Livewire\Observatory;

use App\Models\Company;
use App\Models\Watch;
use App\Models\WatchHit;
use App\Services\Observatory\WatchScanner;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Watch detail page — the hits feed for a single watch.
 *
 * Two filters:
 *  - status: all (default) | confirmed | rejected | pending — LLM verdict
 *  - search: substring match on the snippet
 *
 * The "Scan now" button re-runs WatchScanner synchronously against the
 * current corpus. Cheap because the unique constraint on (watch, content)
 * means re-runs over already-seen content are no-ops.
 */
#[Layout('layouts.app')]
#[Title('Watch')]
class Show extends Component
{
    use WithPagination;

    public Company $company;

    public Watch $watch;

    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all'; // all|unread|confirmed|rejected|pending

    #[Url(as: 'q')]
    public string $search = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function markRead(int $hitId): void
    {
        $hit = WatchHit::where('watch_id', $this->watch->id)->findOrFail($hitId);
        $hit->markSeen();
    }

    public function markAllRead(): void
    {
        WatchHit::where('watch_id', $this->watch->id)
            ->whereNull('seen_at')
            ->update(['seen_at' => now()]);

        session()->flash('status', 'Marked all hits as read.');
    }

    public function mount(Company $company, Watch $watch): void
    {
        abort_unless($company->team_id === auth()->user()->currentTeam?->id, 403);
        abort_unless($watch->company_id === $company->id, 404);

        $this->company = $company;
        $this->watch = $watch;

        if (auth()->user()->current_company_id !== $company->id) {
            auth()->user()->switchCompany($company);
        }
    }

    public function setStatus(string $status): void
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    /**
     * Manually re-run the scanner. Synchronous so the page render after
     * the call shows the fresh results.
     */
    public function rescan(WatchScanner $scanner): void
    {
        $created = $scanner->scan($this->watch);
        $this->watch->refresh();

        session()->flash('status', $created === 0
            ? 'Scan complete — no new hits.'
            : "Scan complete — {$created} new ".\Illuminate\Support\Str::plural('hit', $created).' found.'
        );
    }

    #[Computed]
    public function llmEnabled(): bool
    {
        return $this->watch->llmEnabled();
    }

    #[Computed]
    public function counts(): array
    {
        $base = WatchHit::query()->where('watch_id', $this->watch->id);

        return [
            'total' => (clone $base)->count(),
            'unread' => (clone $base)->whereNull('seen_at')->count(),
            'confirmed' => (clone $base)->where('confirmed_by_llm', true)->count(),
            'rejected' => (clone $base)->where('confirmed_by_llm', false)->count(),
            'pending' => (clone $base)->whereNull('confirmed_by_llm')->count(),
        ];
    }

    public function render()
    {
        $hits = WatchHit::query()
            ->where('watch_id', $this->watch->id)
            ->when($this->statusFilter === 'unread', fn ($q) => $q->whereNull('seen_at'))
            ->when($this->statusFilter === 'confirmed', fn ($q) => $q->where('confirmed_by_llm', true))
            ->when($this->statusFilter === 'rejected', fn ($q) => $q->where('confirmed_by_llm', false))
            ->when($this->statusFilter === 'pending', fn ($q) => $q->whereNull('confirmed_by_llm'))
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->where('context_snippet', 'like', $term);
            })
            ->orderByDesc('matched_at')
            ->paginate(20);

        return view('livewire.observatory.show', ['hits' => $hits]);
    }
}
