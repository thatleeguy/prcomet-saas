<?php

namespace App\Livewire\Subscriptions;

use App\Models\Company;
use App\Models\NewsroomSubscriber;
use App\Models\NewsroomSubscription;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Subscriber self-manage surface at /subscriptions/{token}.
 *
 * Token-based, no auth. Shows the subscriber's active subscriptions,
 * cadence preference, and the rest of the PrComet network they could
 * follow. Toggles + cadence changes commit instantly via wire:click /
 * wire:change so the page never reloads.
 */
#[Layout('layouts.public')]
#[Title('Manage subscriptions')]
class Manage extends Component
{
    public NewsroomSubscriber $subscriber;

    public string $token = '';

    public string $cadence = NewsroomSubscriber::CADENCE_WEEKLY;

    public string $search = '';

    public function mount(string $token): void
    {
        $this->subscriber = NewsroomSubscriber::where('token', $token)->firstOrFail();
        $this->token = $token;
        $this->cadence = $this->subscriber->cadence;
    }

    public function updatedCadence(string $value): void
    {
        if (! array_key_exists($value, NewsroomSubscriber::CADENCES)) {
            return;
        }
        $this->subscriber->forceFill(['cadence' => $value])->save();
        $this->dispatch('flash', message: 'Cadence updated.');
    }

    public function unsubscribeFrom(int $companyId): void
    {
        NewsroomSubscription::query()
            ->where('newsroom_subscriber_id', $this->subscriber->id)
            ->where('company_id', $companyId)
            ->update(['unsubscribed_at' => now()]);

        unset($this->activeSubscriptions, $this->discoverableCompanies);
        $this->dispatch('flash', message: 'Unsubscribed.');
    }

    public function subscribeTo(int $companyId): void
    {
        // Only attach to active, newsroom-published companies — stops
        // a tampered POST from attaching to a retired company.
        $company = Company::query()
            ->where('id', $companyId)
            ->where('is_active', true)
            ->where('newsroom_published', true)
            ->first();

        if (! $company) {
            return;
        }

        $pivot = NewsroomSubscription::firstOrCreate(
            ['newsroom_subscriber_id' => $this->subscriber->id, 'company_id' => $company->id],
            ['subscribed_at' => now()],
        );

        if ($pivot->unsubscribed_at !== null) {
            $pivot->forceFill(['unsubscribed_at' => null, 'subscribed_at' => now()])->save();
        }

        // Re-arm the identity if they were globally unsubscribed.
        if ($this->subscriber->isGloballyUnsubscribed()) {
            $this->subscriber->forceFill(['unsubscribed_at' => null])->save();
        }

        unset($this->activeSubscriptions, $this->discoverableCompanies);
        $this->dispatch('flash', message: "Subscribed to {$company->name}.");
    }

    public function unsubscribeAll(): void
    {
        $this->subscriber->forceFill(['unsubscribed_at' => now()])->save();
        // Detach every active pivot too so re-arming has clean state.
        NewsroomSubscription::query()
            ->where('newsroom_subscriber_id', $this->subscriber->id)
            ->whereNull('unsubscribed_at')
            ->update(['unsubscribed_at' => now()]);

        unset($this->activeSubscriptions, $this->discoverableCompanies);
        $this->dispatch('flash', message: 'Unsubscribed from everything.');
    }

    #[Computed]
    public function activeSubscriptions()
    {
        return NewsroomSubscription::query()
            ->with('company')
            ->where('newsroom_subscriber_id', $this->subscriber->id)
            ->whereNull('unsubscribed_at')
            ->get()
            ->sortBy(fn ($s) => $s->company?->name);
    }

    /** Other newsroom-published companies they're NOT yet following. */
    #[Computed]
    public function discoverableCompanies()
    {
        $alreadyIds = NewsroomSubscription::query()
            ->where('newsroom_subscriber_id', $this->subscriber->id)
            ->whereNull('unsubscribed_at')
            ->pluck('company_id');

        return Company::query()
            ->where('is_active', true)
            ->where('newsroom_published', true)
            ->whereNotIn('id', $alreadyIds)
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->limit(50)
            ->get();
    }

    public function render()
    {
        return view('livewire.subscriptions.manage');
    }
}
