<?php

namespace App\Livewire\Stream;

use App\Models\Author;
use App\Models\PublicationItem;
use App\Models\Source;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Public-facing "stream" of everything PrComet is ingesting and analyzing
 * for the team's corpus. Acts as both transparency (here's the library we're
 * matching you against) and discovery (browse / search the actual material).
 *
 * Scope: union of global sources + this team's private sources, only items
 * whose analysis has completed.
 */
#[Layout('layouts.app')]
#[Title('Stream')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'type', except: 'all')]
    public string $typeFilter = 'all';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingTypeFilter(): void { $this->resetPage(); }

    public function setType(string $type): void
    {
        $this->typeFilter = $type;
        $this->resetPage();
    }

    /**
     * Source IDs visible to this team (global + team-scoped).
     */
    private function visibleSourceIds()
    {
        return Source::visibleTo(auth()->user()->currentTeam)->pluck('id');
    }

    #[Computed]
    public function stats(): array
    {
        $ids = $this->visibleSourceIds();
        $base = PublicationItem::whereIn('source_id', $ids);

        return [
            'today' => (clone $base)->whereDate('published_at', today())->count(),
            'week' => (clone $base)->where('published_at', '>=', now()->subDays(7))->count(),
            'sources' => $ids->count(),
            'authors' => Author::whereHas('items', fn ($q) => $q->whereIn('source_id', $ids))->count(),
        ];
    }

    #[Computed]
    public function sourceTypeCounts(): array
    {
        $ids = $this->visibleSourceIds();

        return PublicationItem::query()
            ->join('sources', 'sources.id', '=', 'publication_items.source_id')
            ->whereIn('publication_items.source_id', $ids)
            ->selectRaw('sources.type, COUNT(*) as c')
            ->groupBy('sources.type')
            ->pluck('c', 'type')
            ->toArray();
    }

    public function render()
    {
        $ids = $this->visibleSourceIds();

        $items = PublicationItem::query()
            ->with(['source', 'author'])
            ->whereIn('source_id', $ids)
            ->where('analysis_status', PublicationItem::ANALYSIS_DONE)
            ->when($this->typeFilter !== 'all', fn ($q) =>
                $q->whereHas('source', fn ($s) => $s->where('type', $this->typeFilter)))
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($inner) use ($term) {
                    $inner->where('title', 'like', $term)
                          ->orWhere('body_text', 'like', $term)
                          ->orWhereHas('author', fn ($a) => $a->where('name', 'like', $term));
                });
            })
            ->orderByDesc('published_at')
            ->paginate(20);

        return view('livewire.stream.index', ['items' => $items]);
    }
}
