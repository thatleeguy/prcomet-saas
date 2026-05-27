<?php

namespace App\Livewire\Companies;

use App\Models\Company;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Company')]
class Edit extends Component
{
    public ?Company $company = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:32')]
    public string $ticker = '';

    #[Validate('nullable|string|max:32')]
    public string $exchange = '';

    #[Validate('nullable|url|max:255')]
    public string $website = '';

    #[Validate('nullable|url|max:255')]
    public string $rss_feed_url = '';

    #[Validate('nullable|string|max:255')]
    public string $ir_contact_name = '';

    #[Validate('nullable|email|max:255')]
    public string $ir_contact_email = '';

    #[Validate('nullable|string|max:64')]
    public string $ir_contact_phone = '';

    /** Comma-separated tags input — converted to array on save. */
    #[Validate('nullable|string|max:255')]
    public string $sector_tags_csv = '';

    public bool $is_active = true;

    public function mount(?Company $company = null): void
    {
        if ($company && $company->exists) {
            $this->authorizeCompany($company);
            $this->company = $company;

            $this->name = $company->name;
            $this->ticker = $company->ticker ?? '';
            $this->exchange = $company->exchange ?? '';
            $this->website = $company->website ?? '';
            $this->rss_feed_url = $company->rss_feed_url ?? '';
            $this->ir_contact_name = $company->ir_contact_name ?? '';
            $this->ir_contact_email = $company->ir_contact_email ?? '';
            $this->ir_contact_phone = $company->ir_contact_phone ?? '';
            $this->sector_tags_csv = implode(', ', $company->sector_tags ?? []);
            $this->is_active = $company->is_active;
        }
    }

    public function save(): void
    {
        $this->validate();

        $team = auth()->user()->currentTeam;

        if (! $this->company && ! $team->canAddCompany()) {
            throw ValidationException::withMessages([
                'name' => "You've reached your seat limit of {$team->max_companies}. Contact us to add more.",
            ]);
        }

        $payload = [
            'name' => $this->name,
            'ticker' => $this->ticker ?: null,
            'exchange' => $this->exchange ?: null,
            'website' => $this->website ?: null,
            'rss_feed_url' => $this->rss_feed_url ?: null,
            'ir_contact_name' => $this->ir_contact_name ?: null,
            'ir_contact_email' => $this->ir_contact_email ?: null,
            'ir_contact_phone' => $this->ir_contact_phone ?: null,
            'sector_tags' => $this->parseTags(),
            'is_active' => $this->is_active,
        ];

        if ($this->company) {
            $this->company->update($payload);
        } else {
            $this->company = $team->companies()->create($payload);
        }

        session()->flash('status', $this->company->wasRecentlyCreated ? 'Company added.' : 'Company updated.');

        $this->redirectRoute('companies.show', $this->company, navigate: true);
    }

    /** @return array<int,string> */
    private function parseTags(): array
    {
        return collect(explode(',', $this->sector_tags_csv))
            ->map(fn ($t) => strtolower(trim($t)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function authorizeCompany(Company $company): void
    {
        abort_unless($company->team_id === auth()->user()->currentTeam?->id, 403);
    }

    public function render()
    {
        return view('livewire.companies.edit');
    }
}
