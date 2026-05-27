<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $company ? "Matches · {$company->name}" : 'All matches' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @php
                $statuses = [
                    'new' => 'New',
                    'saved' => 'Saved',
                    'contacted' => 'Contacted',
                    'placed' => 'Placed',
                    'dismissed' => 'Dismissed',
                    'all' => 'All',
                ];
            @endphp

            <div class="flex flex-wrap items-center gap-2 text-sm">
                @foreach ($statuses as $key => $label)
                    <button wire:click="setStatus('{{ $key }}')"
                        class="px-3 py-1.5 rounded-full border {{ $statusFilter === $key ? 'bg-amber-600 text-white border-amber-600' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            @if ($this->matches->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-12 text-center text-gray-500">
                    <p class="text-lg font-medium text-gray-700">Nothing here yet</p>
                    <p class="text-sm mt-1">
                        Matches appear once we've analyzed a new press release and found receptive publications.
                    </p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($this->matches as $match)
                        <a href="{{ route('matches.show', $match) }}" wire:navigate
                            class="block bg-white shadow-sm sm:rounded-lg p-5 hover:shadow-md transition">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                                        <span class="font-medium text-gray-700">{{ $match->company->name }}</span>
                                        <span>·</span>
                                        <span>{{ $match->publicationItem->source->name }}</span>
                                        @if ($match->author)
                                            <span>·</span>
                                            <span>{{ $match->author->name }}</span>
                                        @endif
                                        <span>·</span>
                                        <span>{{ $match->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="font-medium text-gray-900 line-clamp-1">{{ $match->publicationItem->title }}</div>
                                    <div class="text-sm text-gray-600 mt-1 line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags($match->rationale_md), 220) }}</div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="inline-flex items-center gap-1 text-xs font-semibold {{ $match->score >= 0.8 ? 'text-emerald-700' : ($match->score >= 0.65 ? 'text-amber-700' : 'text-gray-600') }}">
                                        {{ number_format($match->score * 100) }}%
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">{{ ucfirst($match->status) }}</div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
