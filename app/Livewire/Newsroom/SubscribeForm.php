<?php

namespace App\Livewire\Newsroom;

use App\Mail\SubscriptionAdded;
use App\Mail\SubscriptionConfirmRequest;
use App\Models\Company;
use App\Models\NewsroomSubscriber;
use App\Models\NewsroomSubscription;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * The capture form on the public newsroom page.
 *
 * Multi-company aware: if the email is already a PrComet-network
 * subscriber, attach the new company without a second double-opt-in
 * round-trip. Brand-new subscribers get a confirm email; their
 * subscription stays inert until they click through.
 */
class SubscribeForm extends Component
{
    public Company $company;

    #[Validate('required|email|max:255')]
    public string $email = '';

    public bool $submitted = false;

    /** Result state for the success card. */
    public string $resultMessage = '';

    public string $resultBody = '';

    public function mount(Company $company): void
    {
        $this->company = $company;
    }

    public function subscribe(): void
    {
        $this->validate();

        $hash = NewsroomSubscriber::hashEmail($this->email);

        // Identity-first upsert. The model's booted hook fills in the
        // hash + token on first create.
        $subscriber = NewsroomSubscriber::firstOrCreate(
            ['email_hashed' => $hash],
            [
                'email' => $this->email,
                'cadence' => NewsroomSubscriber::CADENCE_WEEKLY,
                'source_ref' => 'newsroom',
                'ip' => request()->ip(),
            ],
        );

        // Bring them back from a prior global unsubscribe — adding a
        // new company is an opt-back-in by intent.
        if ($subscriber->isGloballyUnsubscribed()) {
            $subscriber->forceFill(['unsubscribed_at' => null])->save();
        }

        // Attach (or re-attach) this company via the soft-detach
        // pivot. firstOrCreate keyed on the unique tuple stops
        // accidental dupes.
        $pivot = NewsroomSubscription::firstOrCreate(
            ['newsroom_subscriber_id' => $subscriber->id, 'company_id' => $this->company->id],
            ['subscribed_at' => now()],
        );

        // If they had previously detached from THIS company, bring
        // the row back.
        if ($pivot->unsubscribed_at !== null) {
            $pivot->forceFill(['unsubscribed_at' => null, 'subscribed_at' => now()])->save();
        }

        // Decide which email to send and what to surface in the UI:
        //   - Brand-new subscriber → confirm-request email, "check
        //     your inbox to confirm" UI.
        //   - Already confirmed → silent attachment + "added"
        //     confirmation email, "you're in" UI.
        if (! $subscriber->isConfirmed()) {
            Mail::to($subscriber->email)->queue(new SubscriptionConfirmRequest($subscriber, $this->company));
            $this->resultMessage = 'Check your inbox to confirm.';
            $this->resultBody = "We sent a one-click confirmation to {$subscriber->email}. Once you click, you'll start receiving {$this->company->name}'s updates as part of your PrComet network digest.";
        } else {
            Mail::to($subscriber->email)->queue(new SubscriptionAdded($subscriber, $this->company));
            $this->resultMessage = "You're in.";
            $this->resultBody = "{$this->company->name} is now part of your PrComet digest. Manage your subscriptions any time from the link in your inbox.";
        }

        $this->submitted = true;
        $this->email = '';
    }

    public function render()
    {
        return view('livewire.newsroom.subscribe-form');
    }
}
