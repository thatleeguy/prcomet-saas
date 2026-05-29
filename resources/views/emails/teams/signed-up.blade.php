<x-mail::message>
# New team signup

**{{ $owner->name }}** ({{ $owner->email }}) just signed up.

- Team: **{{ $team->name }}**
- Signed up: {{ $team->created_at->toDayDateTimeString() }}

Head to /manage to activate them and set their seat limit.

<x-mail::button :url="url('/manage/teams/' . $team->id . '/edit')">
Open team in /manage
</x-mail::button>

Thanks,<br>
PrComet
</x-mail::message>
