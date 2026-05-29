<x-mail::message>
# Your PrComet digest

@php
    $totalStories = $groups->sum(fn ($g) => $g['onePagers']->count());
    $companies = $groups->count();
@endphp

{{ $totalStories }} new {{ \Illuminate\Support\Str::plural('story', $totalStories) }} from {{ $companies }} {{ \Illuminate\Support\Str::plural('company', $companies) }} you follow.

---

@foreach ($groups as $group)
@php $c = $group['company']; @endphp

## {{ $c->name }}

@foreach ($group['onePagers'] as $op)
**[{{ $op->displayTitle() }}]({{ $op->publicUrl() }})**

@if ($op->published_at){{ $op->published_at->format('M j, Y') }} · @endif{{ $op->note_md ? \Illuminate\Support\Str::limit(strip_tags($op->note_md), 180) : 'New on '.$c->name."'s newsroom." }}

@endforeach

@endforeach

<x-mail::button :url="$manageUrl">
Manage your subscriptions
</x-mail::button>

<x-mail::subcopy>
You're receiving this digest because you subscribed via PrComet. [Manage]({{ $manageUrl }}) · [Unsubscribe from everything]({{ $unsubscribeUrl }})
</x-mail::subcopy>
</x-mail::message>
