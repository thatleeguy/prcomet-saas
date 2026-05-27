<x-mail::message>
# New team signup

**{{ $owner->name }}** ({{ $owner->email }}) just signed up.

- Team: **{{ $team->name }}**
- Signed up: {{ $team->created_at->toDayDateTimeString() }}

Head to the admin panel to activate them and set their seat limit.

<x-mail::button :url="url('/admin/teams/' . $team->id . '/edit')">
Open team in admin
</x-mail::button>

Thanks,<br>
PrComet
</x-mail::message>
