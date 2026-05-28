<?php

namespace App\Livewire\Authors;

use App\Models\Author;
use App\Models\ExtractedClaim;
use App\Models\MatchRecord;
use App\Models\PublicationItem;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * An author detail page — full body-of-work view for journalists, podcast
 * hosts, and substack writers PrComet has profiled.
 *
 * Authors are global (not team-scoped) — the same Robert Sinclair shows up
 * for every team that has him in their corpus. Page content is therefore
 * largely public, but the "matches with this author" panel is scoped to
 * the current team so users see THEIR work history with the author.
 */
#[Layout('layouts.app')]
class Show extends Component
{
    use WithPagination;

    public Author $author;

    public function mount(Author $author): void
    {
        $this->author = $author->load('primarySource');
    }

    /** Most-covered topics across this author's items. */
    #[Computed]
    public function topicCounts(): array
    {
        $rows = PublicationItem::query()
            ->where('author_id', $this->author->id)
            ->whereNotNull('extracted_topics')
            ->pluck('extracted_topics');

        $counts = [];
        foreach ($rows as $topics) {
            if (! is_array($topics)) continue;
            foreach ($topics as $topic) {
                $counts[$topic] = ($counts[$topic] ?? 0) + 1;
            }
        }

        arsort($counts);
        return array_slice($counts, 0, 12, true);
    }

    /** Stance distribution across the author's analyzed items. */
    #[Computed]
    public function stanceCounts(): array
    {
        return PublicationItem::query()
            ->where('author_id', $this->author->id)
            ->whereNotNull('stance')
            ->selectRaw('stance, COUNT(*) as c')
            ->groupBy('stance')
            ->pluck('c', 'stance')
            ->toArray();
    }

    /** Verified predictions — the author's track record. */
    #[Computed]
    public function verifiedClaims()
    {
        return ExtractedClaim::query()
            ->with('publicationItem')
            ->where('author_id', $this->author->id)
            ->whereNotNull('verified_outcome')
            ->where('verified_outcome', '!=', ExtractedClaim::VERIFIED_UNVERIFIABLE)
            ->orderByDesc('verified_at')
            ->limit(5)
            ->get();
    }

    /**
     * Matches the user has on this author. Scopes to the focused company
     * when set so users on Company A don't accidentally see Company B's
     * private match history with this author. Falls back to team-wide
     * only when the team has no focused company (rare).
     */
    #[Computed]
    public function teamMatches()
    {
        return $this->matchesScopedQuery()
            ->with(['company', 'publicationItem'])
            ->where('author_id', $this->author->id)
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();
    }

    /**
     * Shared scope helper for the match-related panels.
     */
    protected function matchesScopedQuery()
    {
        $user = auth()->user();
        $current = $user->resolveCurrentCompany();

        return MatchRecord::query()->when($current,
            fn ($q) => $q->where('company_id', $current->id),
            fn ($q) => $q->whereHas('company', fn ($c) => $c->where('team_id', $user->currentTeam->id))
        );
    }

    #[Computed]
    public function counts(): array
    {
        $items = PublicationItem::where('author_id', $this->author->id);
        return [
            'total_items' => (clone $items)->count(),
            'last_30_days' => (clone $items)->where('published_at', '>=', now()->subDays(30))->count(),
            'topics' => count($this->topicCounts),
            'matches' => $this->matchesScopedQuery()
                ->where('author_id', $this->author->id)
                ->count(),
        ];
    }

    public function render()
    {
        $items = PublicationItem::query()
            ->with('source')
            ->where('author_id', $this->author->id)
            ->orderByDesc('published_at')
            ->paginate(10);

        return view('livewire.authors.show', ['items' => $items])
            ->title($this->author->name);
    }
}
