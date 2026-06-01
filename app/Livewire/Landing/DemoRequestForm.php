<?php

namespace App\Livewire\Landing;

use App\Models\DemoRequest;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Inline demo-request capture on the marketing landing page.
 *
 * Persists the inquiry to demo_requests and flips into a thank-you state
 * without a page reload. Email notification to admins can be wired later
 * via a model observer — kept off the critical path for now so this works
 * before mail is configured.
 */
class DemoRequestForm extends Component
{
    #[Validate('required|string|max:120')]
    public string $name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('required|string|max:160')]
    public string $company = '';

    #[Validate('nullable|string|max:120')]
    public string $role = '';

    #[Validate('nullable|url|max:255')]
    public string $website = '';

    #[Validate('nullable|string|max:1000')]
    public string $notes = '';

    public bool $submitted = false;

    /**
     * Optional offering/segment label (e.g. an industry name). When set, it's
     * tagged into the saved notes so these leads are distinguishable in the
     * admin without needing a dedicated column. Not part of validated input.
     */
    public ?string $source = null;

    public function mount(?string $source = null): void
    {
        $this->source = $source;
    }

    public function submit(): void
    {
        $data = $this->validate();

        if ($this->source) {
            $data['notes'] = trim('['.$this->source.'] '.$data['notes']);
        }

        DemoRequest::create($data + ['status' => DemoRequest::STATUS_NEW]);

        $this->submitted = true;
        $this->reset(['name', 'email', 'company', 'role', 'website', 'notes']);
    }

    public function render()
    {
        return view('livewire.landing.demo-request-form');
    }
}
