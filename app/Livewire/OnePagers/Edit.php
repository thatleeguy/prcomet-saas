<?php

namespace App\Livewire\OnePagers;

use App\Models\MatchRecord;
use App\Models\MediaAsset;
use App\Models\OnePager;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('One-pager')]
class Edit extends Component
{
    public MatchRecord $match;

    public OnePager $onePager;

    #[Validate('nullable|string|max:5000')]
    public string $note = '';

    public array $selectedAssetIds = [];

    public function mount(MatchRecord $match): void
    {
        abort_unless($match->company->team_id === auth()->user()->currentTeam?->id, 403);
        $this->match = $match;
        $this->onePager = $match->ensureOnePager(auth()->id());
        $this->note = $this->onePager->note_md ?? '';

        $existing = $this->onePager->assets()->pluck('media_assets.id')->toArray();
        $this->selectedAssetIds = empty($existing) ? $this->curatedDefaults() : $existing;
    }

    #[Computed]
    public function libraryAssets()
    {
        return MediaAsset::query()
            ->where('company_id', $this->match->company_id)
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->get();
    }

    private function curatedDefaults(): array
    {
        $signals = collect([
            $this->match->publicationItem->extracted_topics ?? [],
            $this->match->pressRelease->extracted_topics ?? [],
            $this->match->pressRelease->extracted_entities['commodities'] ?? [],
            $this->match->pressRelease->extracted_entities['jurisdictions'] ?? [],
            $this->match->company->sector_tags ?? [],
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
        if (in_array($assetId, $this->selectedAssetIds, true)) {
            $this->selectedAssetIds = array_values(array_diff($this->selectedAssetIds, [$assetId]));
        } else {
            $this->selectedAssetIds[] = $assetId;
        }
    }

    public function save(): void
    {
        $this->validate();

        $this->onePager->update(['note_md' => $this->note ?: null]);

        $sync = [];
        foreach ($this->selectedAssetIds as $i => $id) {
            $sync[$id] = ['sort_order' => $i];
        }
        $this->onePager->assets()->sync($sync);

        session()->flash('status', 'One-pager saved.');
    }

    public function publish(): void
    {
        $this->save();
        $this->onePager->update([
            'status' => OnePager::STATUS_PUBLISHED,
            'published_at' => $this->onePager->published_at ?? now(),
        ]);
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
