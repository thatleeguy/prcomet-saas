<?php

namespace App\Livewire\Observatory;

use App\Models\Company;
use App\Models\Watch;
use App\Services\Observatory\WatchScanner;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Create / edit a single watch.
 *
 * Terms are entered as a comma-separated list and normalised on save.
 * The LLM mode radio is disabled when the team doesn't have the
 * llm_observatory_enabled upgrade — the saved column accepts the value
 * either way, but the {@see Watch::effectiveMode()} accessor downgrades
 * silently at scan time when the entitlement isn't present.
 */
#[Layout('layouts.app')]
#[Title('Watch')]
class Edit extends Component
{
    public Company $company;

    public ?Watch $watch = null;

    #[Validate('required|string|max:120')]
    public string $name = '';

    /** company | location | product | term */
    #[Validate('required|string|in:company,location,product,term')]
    public string $kind = Watch::KIND_TERM;

    /** Comma-separated; the primary name is included automatically. */
    #[Validate('required|string|max:1000')]
    public string $termsCsv = '';

    /** literal | literal_llm */
    public string $mode = Watch::MODE_LITERAL;

    public bool $isActive = true;

    public function mount(Company $company, ?Watch $watch = null): void
    {
        abort_unless($company->team_id === auth()->user()->currentTeam?->id, 403);
        $this->company = $company;

        if (auth()->user()->current_company_id !== $company->id) {
            auth()->user()->switchCompany($company);
        }

        if ($watch && $watch->exists) {
            abort_unless($watch->company_id === $company->id, 404);
            $this->watch = $watch;
            $this->name = $watch->name;
            $this->kind = $watch->kind;
            $this->termsCsv = collect($watch->terms)->implode(', ');
            $this->mode = $watch->mode;
            $this->isActive = $watch->is_active;
        }
    }

    #[Computed]
    public function llmEnabled(): bool
    {
        return $this->company->team?->llmObservatoryEnabled() ?? false;
    }

    #[Computed]
    public function isEditing(): bool
    {
        return $this->watch !== null;
    }

    public function save(WatchScanner $scanner)
    {
        $this->validate();

        $terms = $this->parseTerms();
        if (empty($terms)) {
            $this->addError('termsCsv', 'Provide at least one term to match.');
            return null;
        }

        // Mode falls back to literal when the team isn't entitled — UX is
        // honest about which mode will actually run.
        $effectiveMode = ($this->mode === Watch::MODE_LITERAL_LLM && ! $this->llmEnabled)
            ? Watch::MODE_LITERAL
            : $this->mode;

        $payload = [
            'company_id' => $this->company->id,
            'name' => $this->name,
            'kind' => $this->kind,
            'terms' => $terms,
            'mode' => $effectiveMode,
            'is_active' => $this->isActive,
        ];

        if ($this->isEditing) {
            $this->watch->update($payload);
        } else {
            $payload['created_by_user_id'] = auth()->id();
            $this->watch = Watch::create($payload);
        }

        // Auto-scan on create / save so the user immediately sees results
        // from the existing corpus rather than waiting for ingestion.
        $scanner->scan($this->watch->fresh());

        session()->flash('status', $this->isEditing ? 'Watch saved.' : 'Watch created. Scanning corpus…');

        if (! $this->isEditing) {
            return $this->redirectRoute('companies.observatory.show', [
                'company' => $this->company,
                'watch' => $this->watch,
            ], navigate: true);
        }

        return null;
    }

    public function delete()
    {
        abort_unless($this->isEditing, 404);
        $this->watch->delete();
        session()->flash('status', 'Watch removed.');
        return $this->redirectRoute('companies.observatory', $this->company, navigate: true);
    }

    /**
     * Splits the CSV, normalises whitespace, includes the primary name as
     * the first term automatically so the user doesn't have to repeat it.
     *
     * @return array<int,string>
     */
    private function parseTerms(): array
    {
        $terms = collect(explode(',', $this->termsCsv))
            ->map(fn ($t) => trim((string) $t))
            ->filter()
            ->prepend(trim($this->name))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->unique(fn ($t) => mb_strtolower($t))
            ->values()
            ->all();

        return array_slice($terms, 0, 20); // hard cap to keep scans cheap
    }

    public function render()
    {
        return view('livewire.observatory.edit');
    }
}
