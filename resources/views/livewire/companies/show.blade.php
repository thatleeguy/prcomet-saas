<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $company->name }}</h2>
            <a href="{{ route('companies.edit', $company) }}" wire:navigate
                class="text-sm text-amber-700 hover:text-amber-900">Edit</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-md px-4 py-3 text-sm">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-md px-4 py-3 text-sm">{{ session('error') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <dl class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div><dt class="text-gray-500">Ticker</dt><dd class="text-gray-900">{{ $company->ticker ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Exchange</dt><dd class="text-gray-900">{{ $company->exchange ?? '—' }}</dd></div>
                    <div>
                        <dt class="text-gray-500">Status</dt>
                        <dd>
                            @if ($company->is_active)
                                <span class="inline-flex px-2 text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="inline-flex px-2 text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-700">Paused</span>
                            @endif
                        </dd>
                    </div>
                    <div class="md:col-span-3"><dt class="text-gray-500">Website</dt><dd><a href="{{ $company->website }}" class="text-amber-700 hover:text-amber-900" target="_blank">{{ $company->website ?? '—' }}</a></dd></div>
                    <div class="md:col-span-3"><dt class="text-gray-500">RSS feed</dt><dd class="text-gray-900 break-all">{{ $company->rss_feed_url ?? '—' }}</dd></div>
                    <div class="md:col-span-3"><dt class="text-gray-500">Sector tags</dt><dd class="text-gray-900">{{ implode(', ', $company->sector_tags ?? []) ?: '—' }}</dd></div>
                </dl>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-gray-900">Press releases</h3>
                    @if ($company->rss_feed_url)
                        <button type="button" wire:click="ingestNow" wire:loading.attr="disabled"
                            class="text-xs px-3 py-1.5 border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50">
                            <span wire:loading.remove wire:target="ingestNow">Ingest now</span>
                            <span wire:loading wire:target="ingestNow">Ingesting…</span>
                        </button>
                    @endif
                </div>
                @if ($company->pressReleases()->exists())
                    <ul class="divide-y divide-gray-100">
                        @foreach ($company->pressReleases()->latest('published_at')->limit(10)->get() as $pr)
                            <li class="py-3">
                                <a href="{{ $pr->source_url }}" target="_blank" class="text-amber-700 hover:text-amber-900 font-medium">{{ $pr->title }}</a>
                                <div class="text-xs text-gray-500">{{ optional($pr->published_at)->toDayDateTimeString() }}</div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-500">No press releases ingested yet. Once you've added the RSS URL, new releases will appear here within the hour.</p>
                @endif
            </div>
        </div>
    </div>
</div>
