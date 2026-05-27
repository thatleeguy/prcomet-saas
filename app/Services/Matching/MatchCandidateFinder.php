<?php

namespace App\Services\Matching;

use App\Models\PressRelease;
use App\Models\PublicationItem;
use App\Models\Source;
use Illuminate\Support\Collection;

/**
 * Stage 1 of matching: cheap SQL-based candidate filter.
 *
 * Given an analyzed press release, return up to N recent publication items
 * whose topics, commodity entities, or jurisdictions overlap. We keep the
 * filter loose — the LLM rank step has the final say. The goal here is to
 * cut a corpus of 10k+ items down to ~20 for the expensive model to read.
 */
class MatchCandidateFinder
{
    public function __construct(
        private readonly int $recencyDays = 90,
        private readonly int $candidateLimit = 20,
    ) {}

    /** @return Collection<int, PublicationItem> */
    public function findFor(PressRelease $release, \App\Models\Team $team): Collection
    {
        $signals = $this->signalsFromRelease($release);
        if ($signals === []) {
            return collect();
        }

        $visibleSourceIds = Source::visibleTo($team)
            ->where('is_active', true)
            ->pluck('id');

        if ($visibleSourceIds->isEmpty()) {
            return collect();
        }

        $cutoff = now()->subDays($this->recencyDays);

        // Score is a coarse PHP-side ranking after SQL filters. SQLite's lack
        // of JSON containment operators makes this acceptable for the v0
        // corpus size; we'll move topic indexing into a junction table once
        // the corpus is >10k items.
        $candidates = PublicationItem::query()
            ->whereIn('source_id', $visibleSourceIds)
            ->where('analysis_status', PublicationItem::ANALYSIS_DONE)
            ->where('published_at', '>=', $cutoff)
            ->whereNotNull('extracted_topics')
            ->orderByDesc('published_at')
            ->limit($this->candidateLimit * 4) // overscan, prune in PHP
            ->get();

        return $candidates
            ->map(function (PublicationItem $item) use ($signals) {
                $item->setAttribute('_overlap_score', $this->overlapScore($item, $signals));
                return $item;
            })
            ->filter(fn (PublicationItem $i) => $i->getAttribute('_overlap_score') > 0)
            ->sortByDesc(fn (PublicationItem $i) => $i->getAttribute('_overlap_score'))
            ->take($this->candidateLimit)
            ->values();
    }

    /** @return array<int, string> */
    private function signalsFromRelease(PressRelease $release): array
    {
        $entities = $release->extracted_entities ?? [];
        $commodities = $entities['commodities'] ?? [];
        $jurisdictions = $entities['jurisdictions'] ?? [];
        $topics = $release->extracted_topics ?? [];
        $companyTags = $release->company->sector_tags ?? [];

        return collect([$commodities, $jurisdictions, $topics, $companyTags])
            ->flatten()
            ->filter(fn ($s) => is_string($s) && $s !== '')
            ->map(fn ($s) => strtolower($s))
            ->unique()
            ->values()
            ->all();
    }

    private function overlapScore(PublicationItem $item, array $signals): int
    {
        $itemSignals = collect([
            $item->extracted_topics ?? [],
            $item->extracted_entities['commodities'] ?? [],
            $item->extracted_entities['jurisdictions'] ?? [],
        ])
            ->flatten()
            ->filter(fn ($s) => is_string($s) && $s !== '')
            ->map(fn ($s) => strtolower($s))
            ->unique();

        return $itemSignals->intersect($signals)->count();
    }
}
