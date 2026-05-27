<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Wins</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @php $stats = $this->stats; @endphp

            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <div class="text-xs text-gray-500 uppercase">Total matches</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['total'] }}</div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <div class="text-xs text-gray-500 uppercase">Contacted</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['contacted'] }}</div>
                </div>
                <div class="bg-emerald-50 shadow-sm sm:rounded-lg p-4 border border-emerald-200">
                    <div class="text-xs text-emerald-700 uppercase">Placed</div>
                    <div class="mt-1 text-2xl font-semibold text-emerald-900">{{ $stats['placed'] }}</div>
                </div>
            </div>

            @if ($this->placedMatches->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-12 text-center text-gray-500">
                    <p class="text-lg font-medium text-gray-700">No placements yet</p>
                    <p class="text-sm mt-1">When outreach turns into a real interview, article, or mention, attach the URL on a match's page to track the win here.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($this->placedMatches as $match)
                        <div class="bg-white shadow-sm sm:rounded-lg p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs text-gray-500 mb-1">
                                        {{ $match->company->name }} · {{ $match->publicationItem->source->name }}
                                        @if ($match->author) · {{ $match->author->name }} @endif
                                        @if ($match->placement_published_at) · {{ $match->placement_published_at->toFormattedDateString() }} @endif
                                    </div>
                                    <a href="{{ $match->placement_url }}" target="_blank" class="font-medium text-amber-700 hover:text-amber-900">
                                        {{ $match->placement_title ?? $match->placement_url }}
                                    </a>
                                    @if ($match->placement_description)
                                        <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $match->placement_description }}</p>
                                    @endif
                                    <div class="mt-2 text-xs">
                                        <a href="{{ route('matches.show', $match) }}" wire:navigate class="text-gray-500 hover:text-gray-900">View match brief →</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
