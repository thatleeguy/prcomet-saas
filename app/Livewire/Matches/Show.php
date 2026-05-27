<?php

namespace App\Livewire\Matches;

use App\Jobs\FetchPlacementMetadataJob;
use App\Models\MatchEvent;
use App\Models\MatchRecord;
use App\Models\PublicationItem;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Brief')]
class Show extends Component
{
    public MatchRecord $match;

    #[Validate('nullable|string|max:2000')]
    public string $note = '';

    #[Validate('nullable|url|max:1000')]
    public string $placementUrl = '';

    public function mount(MatchRecord $match): void
    {
        $match->load(['company', 'pressRelease', 'publicationItem.source', 'author', 'events.user']);
        abort_unless($match->company->team_id === auth()->user()->currentTeam?->id, 403);
        $this->match = $match;
    }

    /**
     * Resolve cited publication items by ID so the citations panel can show
     * the real titles + link directly to each source item.
     */
    #[Computed]
    public function citedItems(): Collection
    {
        $ids = collect($this->match->citations ?? [])
            ->pluck('publication_item_id')
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return new Collection;
        }

        return PublicationItem::with('source')->whereIn('id', $ids)->get()->keyBy('id');
    }

    public function updateStatus(string $status): void
    {
        if (! in_array($status, [
            MatchRecord::STATUS_NEW,
            MatchRecord::STATUS_SAVED,
            MatchRecord::STATUS_CONTACTED,
            MatchRecord::STATUS_DISMISSED,
            MatchRecord::STATUS_PLACED,
        ], true)) {
            return;
        }

        $this->match->update(['status' => $status]);

        MatchEvent::create([
            'match_id' => $this->match->id,
            'user_id' => auth()->id(),
            'event_type' => $status,
        ]);

        $this->match->refresh();
        $this->match->load('events.user');
        session()->flash('status', "Marked as {$status}.");
    }

    public function attachPlacement(): void
    {
        $this->validate(['placementUrl' => 'required|url|max:1000']);

        $this->match->forceFill([
            'placement_url' => $this->placementUrl,
            'status' => MatchRecord::STATUS_PLACED,
        ])->save();

        MatchEvent::create([
            'match_id' => $this->match->id,
            'user_id' => auth()->id(),
            'event_type' => MatchRecord::STATUS_PLACED,
            'notes_md' => "Placement: {$this->placementUrl}",
        ]);

        FetchPlacementMetadataJob::dispatch($this->match->id);

        $this->placementUrl = '';
        $this->match->refresh();
        $this->match->load('events.user');
        session()->flash('status', "Placement attached. We'll fetch the title shortly.");
    }

    public function addNote(): void
    {
        $this->validate();

        if (trim($this->note) === '') {
            return;
        }

        MatchEvent::create([
            'match_id' => $this->match->id,
            'user_id' => auth()->id(),
            'event_type' => MatchEvent::TYPE_NOTE,
            'notes_md' => $this->note,
        ]);

        $this->note = '';
        $this->match->load('events.user');
    }

    public function render()
    {
        return view('livewire.matches.show');
    }
}
