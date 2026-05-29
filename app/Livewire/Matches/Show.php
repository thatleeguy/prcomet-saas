<?php

namespace App\Livewire\Matches;

use App\Jobs\FetchPlacementMetadataJob;
use App\Models\MatchEvent;
use App\Models\MatchRecord;
use App\Models\PitchDraft;
use App\Models\PublicationItem;
use App\Services\Llm\BudgetExceededException;
use App\Services\Llm\LlmClient;
use App\Services\Llm\Prompts\PitchDraftPrompt;
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

    /** Tone selector for the AI pitch draft section. */
    public string $pitchTone = PitchDraft::TONE_DIRECT;

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

    /*
    |--------------------------------------------------------------------------
    | AI pitch draft
    |--------------------------------------------------------------------------
    | Generates a cold-outreach email tailored to the journalist + release.
    | Gated behind the team's paid LLM features flag (reuses
    | llm_observatory_enabled — same conceptual upgrade).
    */

    public function setPitchTone(string $tone): void
    {
        if (! array_key_exists($tone, PitchDraft::TONES)) {
            return;
        }
        $this->pitchTone = $tone;
    }

    #[Computed]
    public function aiEnabled(): bool
    {
        return $this->match->company?->team?->llmObservatoryEnabled() ?? false;
    }

    /**
     * The current draft for the active tone, if one's already been
     * generated. The view reads this to decide between "generate" and
     * "show existing".
     */
    #[Computed]
    public function currentDraft(): ?PitchDraft
    {
        return PitchDraft::where('match_id', $this->match->id)
            ->where('tone', $this->pitchTone)
            ->latest('updated_at')
            ->first();
    }

    /**
     * The journalist's mailto recipient — when we have an email on
     * file we prefill it; otherwise the mailto opens with an empty To.
     */
    #[Computed]
    public function recipientEmail(): ?string
    {
        return $this->match->author?->email;
    }

    public function generatePitchDraft(LlmClient $llm): void
    {
        if (! $this->aiEnabled) {
            session()->flash('error', 'AI pitch drafts require the paid LLM upgrade. Contact us to enable.');
            return;
        }

        // Look up the published one-pager URL when there is one — that's
        // the natural CTA in the email body.
        $onePagerUrl = null;
        $page = $this->match->onePager()->first();
        if ($page && $page->isPublished()) {
            $onePagerUrl = $page->publicUrl();
        }

        try {
            $result = PitchDraftPrompt::run($llm, $this->match, $this->pitchTone, $onePagerUrl);
        } catch (BudgetExceededException) {
            session()->flash('error', 'LLM budget reached. Drafts pause until the window rolls.');
            return;
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Draft failed: '.$e->getMessage());
            return;
        }

        if ($result === null) {
            session()->flash('error', 'Claude returned an unparseable response. Try again or pick a different tone.');
            return;
        }

        // Upsert by (match, tone) so re-running overwrites in place.
        PitchDraft::updateOrCreate(
            ['match_id' => $this->match->id, 'tone' => $this->pitchTone],
            [
                'subject' => $result['subject'],
                'body' => $result['body'],
                'generated_by_user_id' => auth()->id(),
            ],
        );

        // Bust the computed cache so the view picks up the new row.
        unset($this->currentDraft);

        session()->flash('status', 'Draft generated.');
    }

    public function render()
    {
        return view('livewire.matches.show');
    }
}
