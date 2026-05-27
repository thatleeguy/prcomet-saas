<?php

namespace App\Livewire\Companies;

use App\Jobs\IngestCompanyRssJob;
use App\Models\Company;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Company')]
class Show extends Component
{
    public Company $company;

    public function mount(Company $company): void
    {
        abort_unless($company->team_id === auth()->user()->currentTeam?->id, 403);
        $this->company = $company;
    }

    /**
     * Manually trigger an RSS ingest. Runs synchronously so the user sees
     * fresh releases immediately, not "queued, check back later." Falls back
     * gracefully if the feed URL is missing.
     */
    public function ingestNow(): void
    {
        if (! $this->company->rss_feed_url) {
            session()->flash('status', 'Add an RSS feed URL first.');
            return;
        }

        try {
            dispatch_sync(new IngestCompanyRssJob($this->company->id));
            $this->company->refresh();
            session()->flash('status', 'Ingest complete. Any new releases appear below.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Ingest failed: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.companies.show');
    }
}
