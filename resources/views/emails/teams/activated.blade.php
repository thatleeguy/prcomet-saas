<x-mail::message>
# Your PrComet account is active

Hi {{ $team->owner->name }},

Your team **{{ $team->name }}** has been activated. You can now sign in and add up to **{{ $seats }}** {{ \Illuminate\Support\Str::plural('company', $seats) }} to monitor.

<x-mail::button :url="url('/dashboard')">
Open your dashboard
</x-mail::button>

If you have any questions, just reply to this email.

Thanks,<br>
PrComet
</x-mail::message>
