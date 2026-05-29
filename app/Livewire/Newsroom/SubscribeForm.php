<?php

namespace App\Livewire\Newsroom;

use App\Models\Company;
use App\Models\NewsroomSubscriber;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Capture form embedded in the public newsroom page. Stays a Livewire
 * island so the rest of the page renders server-side at zero JS cost,
 * and the success state animates inline without a page reload.
 */
class SubscribeForm extends Component
{
    public Company $company;

    #[Validate('required|email|max:255')]
    public string $email = '';

    public bool $submitted = false;

    public function mount(Company $company): void
    {
        $this->company = $company;
    }

    public function subscribe(): void
    {
        $this->validate();

        $hash = NewsroomSubscriber::hashEmail($this->email);

        NewsroomSubscriber::firstOrCreate(
            ['company_id' => $this->company->id, 'email_hashed' => $hash],
            [
                'email' => $this->email,
                'source_ref' => 'newsroom',
                'ip' => request()->ip(),
            ],
        );

        $this->submitted = true;
        $this->email = '';
    }

    public function render()
    {
        return view('livewire.newsroom.subscribe-form');
    }
}
