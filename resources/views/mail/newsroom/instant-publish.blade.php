<x-mail::message>
# {{ $onePager->displayTitle() }}

{{ $company->name }} just published.

@if ($onePager->note_md)
> {{ \Illuminate\Support\Str::limit(strip_tags($onePager->note_md), 280) }}
@endif

<x-mail::button :url="$onePager->publicUrl()">
Read the one-pager
</x-mail::button>

<x-mail::subcopy>
You're receiving this because you subscribed to {{ $company->name }} through PrComet at instant cadence. [Switch to a digest]({{ $manageUrl }}) · [Unsubscribe from everything]({{ $unsubscribeUrl }})
</x-mail::subcopy>
</x-mail::message>
