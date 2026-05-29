<x-mail::message>
# Confirm your subscription

You're one click away from following **{{ $company->name }}**'s newsroom.

This is your one-time confirmation. Once you confirm, you'll start receiving {{ $company->name }}'s updates as part of your PrComet network digest.

<x-mail::button :url="$confirmUrl">
Confirm subscription
</x-mail::button>

If you didn't ask for this, you can safely ignore the email — nothing will be sent until you confirm.

<x-mail::subcopy>
Subscriptions are managed by PrComet. You can change your cadence, add other companies, or unsubscribe any time from the manage link in every email we send.
</x-mail::subcopy>
</x-mail::message>
