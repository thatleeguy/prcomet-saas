<?php

namespace App\Livewire\OnePagers;

use App\Models\Company;
use App\Models\MatchRecord;
use App\Models\MediaAsset;
use App\Models\OnePager;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * One-pager editor.
 *
 * Handles two flows:
 *
 *  - Match-bound (legacy URL: /matches/{match}/one-pager) — auto-creates
 *    a draft OnePager via {@see MatchRecord::ensureOnePager()}, pre-fills
 *    the asset selection from tag-overlap curation, and surfaces match
 *    context in the right rail.
 *
 *  - Standalone (URL: /companies/{c}/onepagers/{onePager:uuid}) — works on
 *    an existing OnePager that has no `match_id`. The user picks a title
 *    explicitly and curates the assets themselves; no match context.
 *
 * The save / publish / unpublish methods are identical in both flows —
 * only mount() and the rendered view differ.
 */
#[Layout('layouts.app')]
#[Title('One-pager')]
class Edit extends Component
{
    public ?MatchRecord $match = null;

    public Company $company;

    public OnePager $onePager;

    #[Validate('nullable|string|max:200')]
    public string $title = '';

    #[Validate('nullable|string|max:5000')]
    public string $note = '';

    public array $selectedAssetIds = [];

    public function mount(
        ?MatchRecord $match = null,
        ?Company $company = null,
        ?OnePager $onePager = null,
    ): void {
        $user = auth()->user();

        if ($match && $match->exists) {
            // ── Match-bound flow ────────────────────────────────────
            abort_unless($match->company->team_id === $user->currentTeam?->id, 403);
            $this->match = $match;
            $this->company = $match->company;
            $this->onePager = $match->ensureOnePager($user->id);
        } else {
            // ── Standalone flow ─────────────────────────────────────
            abort_unless($company && $company->exists, 404);
            abort_unless($company->team_id === $user->currentTeam?->id, 403);
            abort_unless($onePager && $onePager->exists, 404);
            // Scope the OnePager to the requested company so a tampered
            // uuid for another company 404s instead of opening their page.
            abort_unless($onePager->company_id === $company->id, 404);

            $this->company = $company;
            $this->onePager = $onePager;
            $this->match = $onePager->match; // null for true standalones
        }

        // Implicit focus-switch — consistent with the other company-scoped pages.
        if ($user->current_company_id !== $this->company->id) {
            $user->switchCompany($this->company);
        }

        $this->title = $this->onePager->title ?? '';
        $this->note = $this->onePager->note_md ?? '';

        $existing = $this->onePager->assets()->pluck('media_assets.id')->toArray();
        $this->selectedAssetIds = empty($existing)
            ? $this->curatedDefaults()
            : $existing;
    }

    #[Computed]
    public function libraryAssets()
    {
        return MediaAsset::query()
            ->where('company_id', $this->company->id)
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Curate a default asset set on first edit.
     *
     * Match-bound: tag-overlap between the press release / publication item
     * and each asset. Standalone: every active asset, with the user free to
     * pare it down — we have no signals to rank by.
     */
    private function curatedDefaults(): array
    {
        if (! $this->match) {
            return $this->libraryAssets->pluck('id')->all();
        }

        $signals = collect([
            $this->match->publicationItem?->extracted_topics ?? [],
            $this->match->pressRelease?->extracted_topics ?? [],
            $this->match->pressRelease?->extracted_entities['commodities'] ?? [],
            $this->match->pressRelease?->extracted_entities['jurisdictions'] ?? [],
            $this->company->sector_tags ?? [],
        ])->flatten()->filter()->map(fn ($s) => strtolower($s))->unique()->values()->all();

        $assets = $this->libraryAssets;

        $matched = $assets->filter(function (MediaAsset $a) use ($signals) {
            $tags = collect($a->tags ?? [])->map(fn ($t) => strtolower($t))->all();
            return ! empty(array_intersect($tags, $signals));
        });

        return $matched->count() >= 3
            ? $matched->pluck('id')->all()
            : $assets->pluck('id')->all();
    }

    public function toggleAsset(int $assetId): void
    {
        // selectedAssetIds is wire:click-controllable — reject any id that
        // doesn't belong to this company before it lands in state.
        // (save() also re-filters, but rejecting here keeps the UI honest:
        //  a tampered id never appears as "selected".)
        if (! $this->ownsAsset($assetId)) {
            return;
        }

        if (in_array($assetId, $this->selectedAssetIds, true)) {
            $this->selectedAssetIds = array_values(array_diff($this->selectedAssetIds, [$assetId]));
        } else {
            $this->selectedAssetIds[] = $assetId;
        }
    }

    public function save(): void
    {
        $this->validate();

        $this->onePager->update([
            'title' => $this->title ?: null,
            'note_md' => $this->note ?: null,
        ]);

        // Defence-in-depth filter: even if selectedAssetIds was poisoned via
        // a crafted Livewire payload, only assets owned by this company
        // make it into the sync set.
        $allowedIds = MediaAsset::query()
            ->where('company_id', $this->company->id)
            ->whereIn('id', $this->selectedAssetIds)
            ->pluck('id')
            ->all();

        // Preserve the user's chosen ordering, dropping anything filtered out.
        $sync = [];
        $order = 0;
        foreach ($this->selectedAssetIds as $id) {
            if (in_array($id, $allowedIds, true)) {
                $sync[$id] = ['sort_order' => $order++];
            }
        }
        $this->onePager->assets()->sync($sync);

        // Reflect the filtered set back onto state in case anything was dropped.
        $this->selectedAssetIds = array_keys($sync);

        session()->flash('status', 'One-pager saved.');
    }

    /** Cheap ownership check used by toggleAsset. */
    private function ownsAsset(int $assetId): bool
    {
        return MediaAsset::where('company_id', $this->company->id)
            ->whereKey($assetId)
            ->exists();
    }

    public function publish(): void
    {
        $this->save();

        $wasAlreadyPublished = $this->onePager->status === OnePager::STATUS_PUBLISHED;

        $this->onePager->update([
            'status' => OnePager::STATUS_PUBLISHED,
            'published_at' => $this->onePager->published_at ?? now(),
        ]);

        // Only fire the subscriber-fanout event on the publish
        // transition (not re-saves of an already-published page).
        if (! $wasAlreadyPublished) {
            \App\Events\OnePagerPublished::dispatch($this->onePager->fresh());
        }

        session()->flash('status', 'Published. Share the URL with journalists.');
    }

    public function unpublish(): void
    {
        $this->onePager->update(['status' => OnePager::STATUS_UNPUBLISHED]);
        session()->flash('status', 'Unpublished. The URL is now inactive.');
    }

    public function render()
    {
        return view('livewire.one-pagers.edit');
    }
}
