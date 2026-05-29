<x-mail::message>
# {{ $company->name }} added to your subscriptions

You'll see new stories from {{ $company->name }} in your next PrComet digest.

<x-mail::button :url="$manageUrl">
Manage your subscriptions
</x-mail::button>

You're currently set to **{{ \App\Models\NewsroomSubscriber::CADENCES[$subscriber->cadence] ?? 'Weekly digest' }}**. Change it any time from the manage page.

<x-mail::subcopy>
Subscriptions are managed by PrComet — one inbox-friendly digest covers every company you follow.
</x-mail::subcopy>
</x-mail::message>
