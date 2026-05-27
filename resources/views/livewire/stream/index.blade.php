<div>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h1 class="text-base font-semibold text-slate-900">Stream</h1>
                <p class="text-xs text-slate-500">Everything we're scanning for fit</p>
            </div>
            <div class="hidden md:flex items-center gap-1.5 text-xs text-slate-500">
                <span class="relative flex h-1.5 w-1.5">
                    <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-60 animate-ping"></span>
                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                </span>
                <span>Live · auto-refreshes every 30 min</span>
            </div>
        </div>
    </x-slot>

    @php
        $stats = $this->stats;
        $typeCounts = $this->sourceTypeCounts;

        $stanceMeta = [
            'bullish'       => ['cls' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'dot' => 'bg-emerald-500'],
            'bearish'       => ['cls' => 'bg-rose-50 text-rose-700 border-rose-200',         'dot' => 'bg-rose-500'],
            'contrarian'    => ['cls' => 'bg-amber-50 text-amber-700 border-amber-200',      'dot' => 'bg-amber-500'],
            'neutral'       => ['cls' => 'bg-slate-100 text-slate-600 border-slate-200',     'dot' => 'bg-slate-400'],
            'informational' => ['cls' => 'bg-slate-100 text-slate-600 border-slate-200',     'dot' => 'bg-slate-400'],
        ];

        // Source type icons + labels for the filter strip
        $types = [
            'all'         => ['label' => 'All',         'icon' => 'M4 6h16M4 12h16M4 18h16'],
            'publication' => ['label' => 'Publications','icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z'],
            'podcast'     => ['label' => 'Podcasts',    'icon' => 'M12 1v22M19 12a7 7 0 11-14 0M8 12a4 4 0 108 0'],
            'substack'    => ['label' => 'Newsletters', 'icon' => 'M4 4h16v16H4zM4 8h16M8 4v16'],
            'x'           => ['label' => 'X',           'icon' => 'M18 6L6 18M6 6l12 12'],
            'youtube'     => ['label' => 'YouTube',     'icon' => 'M23 7l-9 5-9-5v10l9 5 9-5V7z'],
        ];
    @endphp

    <div class="space-y-6 animate-fade-in">

        {{-- Stats strip --}}
        <section class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ([
                ['label' => 'Published today',  'value' => $stats['today'],   'tone' => 'bg-gradient-to-br from-indigo-500 to-violet-600'],
                ['label' => 'Past 7 days',      'value' => $stats['week'],    'tone' => 'bg-gradient-to-br from-violet-500 to-fuchsia-600'],
                ['label' => 'Sources tracked',  'value' => $stats['sources'], 'tone' => 'bg-gradient-to-br from-blue-500 to-indigo-600'],
                ['label' => 'Authors profiled', 'value' => $stats['authors'], 'tone' => 'bg-gradient-to-br from-fuchsia-500 to-rose-500'],
            ] as $s)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 relative overflow-hidden">
                    <div class="absolute -top-6 -right-6 h-20 w-20 rounded-full {{ $s['tone'] }} opacity-10 blur-2xl"></div>
                    <div class="relative">
                        <div class="text-3xl font-semibold text-slate-900 tabular leading-none">{{ $s['value'] }}</div>
                        <div class="text-xs text-slate-500 mt-2">{{ $s['label'] }}</div>
                    </div>
                </div>
            @endforeach
        </section>

        {{-- Filter strip --}}
        <section class="flex flex-col md:flex-row md:items-center gap-3">
            <div class="flex items-center gap-1 p-1 bg-white rounded-lg border border-slate-200 overflow-x-auto">
                @foreach ($types as $key => $t)
                    @php
                        $count = $key === 'all'
                            ? array_sum($typeCounts)
                            : ($typeCounts[$key] ?? 0);
                        // Hide types with zero items (X, YouTube — until those get implemented)
                        if ($count === 0 && $key !== 'all') continue;
                    @endphp
                    <button wire:click="setType('{{ $key }}')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition-colors whitespace-nowrap
                                   {{ $typeFilter === $key ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50' }}">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="{{ $t['icon'] }}" /></svg>
                        {{ $t['label'] }}
                        <span class="text-[10px] tabular {{ $typeFilter === $key ? 'text-brand-500' : 'text-slate-400' }}">{{ $count }}</span>
                    </button>
                @endforeach
            </div>

            <div class="relative flex-1 md:max-w-md md:ml-auto">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19a8 8 0 100-16 8 8 0 000 16zM21 21l-4.35-4.35" /></svg>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search title, author, body…"
                       class="w-full pl-9 pr-3 py-1.5 text-sm rounded-md border-slate-200 focus:border-brand-500 focus:ring-brand-500 bg-white" />
            </div>
        </section>

        {{-- Feed --}}
        @if ($items->isEmpty())
            <section class="rounded-2xl border-2 border-dashed border-slate-200 bg-white p-12 text-center">
                <div class="mx-auto h-12 w-12 rounded-xl bg-slate-100 grid place-items-center mb-3">
                    <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19a8 8 0 100-16 8 8 0 000 16zM21 21l-4.35-4.35" /></svg>
                </div>
                <h3 class="text-base font-semibold text-slate-900">Nothing matches</h3>
                <p class="text-sm text-slate-500 mt-1">Try a broader search term, or switch to "All" sources.</p>
            </section>
        @else
            <section class="space-y-3">
                @foreach ($items as $item)
                    @php
                        $stance = $stanceMeta[$item->stance] ?? null;
                        // Deterministic 4-color rotation for source marks so the page has visual rhythm.
                        $marks = ['from-indigo-500 to-violet-600', 'from-blue-500 to-indigo-600', 'from-violet-500 to-fuchsia-600', 'from-rose-500 to-fuchsia-500'];
                        $mark = $marks[$item->source_id % count($marks)];
                    @endphp
                    <article class="group rounded-xl border border-slate-200 bg-white p-5 hover:border-brand-300 hover:shadow-md transition-all">
                        <div class="flex items-start gap-4">
                            {{-- Source mark --}}
                            <div class="grid place-items-center h-10 w-10 rounded-lg bg-gradient-to-br {{ $mark }} text-white font-semibold text-xs shrink-0">
                                {{ strtoupper(substr($item->source->name, 0, 2)) }}
                            </div>

                            <div class="min-w-0 flex-1">
                                {{-- Meta line --}}
                                <div class="flex items-center flex-wrap gap-x-2 gap-y-1 text-xs text-slate-500 mb-1.5">
                                    <span class="font-medium text-slate-700">{{ $item->source->name }}</span>
                                    @if ($item->author)
                                        <span class="text-slate-300">·</span>
                                        <a href="{{ route('authors.show', $item->author) }}" wire:navigate
                                           class="hover:text-brand-700 transition-colors">{{ $item->author->name }}</a>
                                    @endif
                                    <span class="text-slate-300">·</span>
                                    <span class="tabular">{{ optional($item->published_at)->diffForHumans(short: true) }}</span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium uppercase tracking-wide text-slate-500 bg-slate-100">{{ $item->source->type }}</span>
                                </div>

                                {{-- Title --}}
                                <a href="{{ $item->url }}" target="_blank" rel="noopener"
                                   class="block text-base font-semibold text-slate-900 leading-snug hover:text-brand-700 transition-colors">
                                    {{ $item->title }}
                                    <svg class="inline-block h-3 w-3 text-slate-300 group-hover:text-brand-500 ml-1 -mt-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 4H4v16h16v-6M14 4h6v6M10 14L20 4" /></svg>
                                </a>

                                {{-- Snippet --}}
                                @if ($item->body_text)
                                    <p class="text-sm text-slate-600 leading-relaxed mt-2 line-clamp-2">{{ \Illuminate\Support\Str::limit($item->body_text, 280) }}</p>
                                @endif

                                {{-- Tags + stance --}}
                                <div class="flex items-center flex-wrap gap-1.5 mt-3">
                                    @foreach (array_slice($item->extracted_topics ?? [], 0, 5) as $topic)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium text-slate-600 bg-slate-100">#{{ $topic }}</span>
                                    @endforeach
                                    @if ($stance)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium border {{ $stance['cls'] }} ml-auto">
                                            <span class="h-1 w-1 rounded-full {{ $stance['dot'] }}"></span>
                                            {{ $item->stance }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            {{-- Pagination --}}
            @if ($items->hasPages())
                <div class="px-1">
                    {{ $items->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
