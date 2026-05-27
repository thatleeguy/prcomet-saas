<x-mail::message>
# {{ count($matches) }} new outreach {{ \Illuminate\Support\Str::plural('opportunity', count($matches)) }}

Hi {{ $user->name }},

Here's your {{ $frequency }} digest of PrComet matches.

@foreach ($matches as $match)
---

**{{ $match->company->name }}** · *{{ $match->publicationItem->source->name }}*{{ $match->author ? ' · '.$match->author->name : '' }}

**{{ $match->publicationItem->title }}** ({{ number_format($match->score * 100) }}% confidence)

{{ \Illuminate\Support\Str::limit(strip_tags($match->rationale_md), 300) }}

[Open in PrComet]({{ route('matches.show', $match) }})

@endforeach

---

[Update digest preferences]({{ route('settings.digest') }})

Thanks,<br>
PrComet
</x-mail::message>
