<div>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full gap-4">
            <div class="min-w-0">
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <a href="{{ route('companies.observatory', $company) }}" wire:navigate class="hover:text-slate-900">Observatory</a>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-700 truncate">{{ $watch->name }}</span>
                </div>
                <h1 class="text-base font-semibold text-slate-900 mt-0.5 truncate">{{ $watch->name }}</h1>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button wire:click="rescan" wire:loading.attr="disabled" class="btn-secondary text-xs">
                    <span wire:loading.remove wire:target="rescan">Scan now</span>
                    <span wire:loading wire:target="rescan">Scanning…</span>
                </button>
                <a href="{{ route('companies.observatory.edit', ['company' => $company, 'watch' => $watch]) }}" wire:navigate class="btn-primary text-xs">Edit watch</a>
            </div>
        </div>
    </x-slot>

    @php
        $counts = $this->counts;
        $statuses = $this->llmEnabled
            ? [
                'all' => ['label' => 'All', 'count' => $counts['total']],
                'confirmed' => ['label' => 'Confirmed', 'count' => $counts['confirmed']],
                'pending' => ['label' => 'Pending', 'count' => $counts['pending']],
                'rejected' => ['label' => 'Rejected', 'count' => $counts['rejected']],
            ]
            : ['all' => ['label' => 'All', 'count' => $counts['total']]];
    @endphp

    <div class="space-y-6 animate-fade-in">
        @if (session('status'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif

        {{-- Watch summary --}}
        <section class="bg-white border border-slate-200 rounded-xl p-5 flex items-start gap-4 flex-wrap">
            <div class="flex-1 min-w-[260px]">
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium uppercase tracking-wider bg-slate-100 text-slate-600">{{ $watch->kind }}</span>
                    @if ($this->llmEnabled)
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium uppercase tracking-wider bg-gradient-to-r from-indigo-100 via-violet-100 to-fuchsia-100 text-indigo-700">
                            <svg class="h-2.5 w-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                            LLM confirmed
                        </span>
                    @endif
                    @if ($watch->mode === 'literal_llm' && ! $this->llmEnabled)
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium uppercase tracking-wider bg-amber-50 text-amber-700">Falling back to literal</span>
                    @endif
                    @if (! $watch->is_active)
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium uppercase tracking-wider bg-slate-100 text-slate-500">Paused</span>
                    @endif
                </div>
                <div class="text-xs text-slate-500">
                    Aliases:
                    @foreach ($watch->terms as $term)
                        <span class="font-mono text-slate-700">{{ $term }}</span>@if (! $loop->last)<span class="text-slate-300 mx-1">·</span>@endif
                    @endforeach
                </div>
            </div>
            <div class="grid {{ $this->llmEnabled ? 'grid-cols-3' : 'grid-cols-1' }} gap-6 shrink-0">
                <div>
                    <div class="text-2xl font-semibold tabular text-slate-900">{{ $counts['total'] }}</div>
                    <div class="text-[10px] text-slate-500 uppercase tracking-wider">{{ \Illuminate\Support\Str::plural('hit', $counts['total']) }}</div>
                </div>
                @if ($this->llmEnabled)
                    <div>
                        <div class="text-2xl font-semibold tabular text-emerald-700">{{ $counts['confirmed'] }}</div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-wider">Confirmed</div>
                    </div>
                    <div>
                        <div class="text-2xl font-semibold tabular text-slate-400">{{ $counts['pending'] }}</div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-wider">Pending</div>
                    </div>
                @endif
            </div>
        </section>

        {{-- Filters --}}
        <section class="flex flex-col md:flex-row md:items-center gap-3">
            <div class="flex items-center gap-1 p-1 bg-white rounded-lg border border-slate-200">
                @foreach ($statuses as $key => $f)
                    <button wire:click="setStatus('{{ $key }}')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition-colors
                                   {{ $statusFilter === $key ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50' }}">
                        {{ $f['label'] }}
                        <span class="text-[10px] tabular {{ $statusFilter === $key ? 'text-brand-500' : 'text-slate-400' }}">{{ $f['count'] }}</span>
                    </button>
                @endforeach
            </div>
            <div class="relative flex-1 md:max-w-md md:ml-auto">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19a8 8 0 100-16 8 8 0 000 16zM21 21l-4.35-4.35" /></svg>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search snippets…"
                       class="w-full pl-9 pr-3 py-1.5 text-sm rounded-md border-slate-200 focus:border-brand-500 focus:ring-brand-500 bg-white" />
            </div>
        </section>

        {{-- Hits --}}
        @if ($hits->isEmpty())
            <section class="rounded-2xl border-2 border-dashed border-slate-200 bg-white p-12 text-center">
                <div class="text-base font-semibold text-slate-900">
                    @if ($search !== '' || $statusFilter !== 'all')
                        Nothing matches those filters.
                    @else
                        No hits recorded yet.
                    @endif
                </div>
                <p class="text-sm text-slate-500 mt-1">Hit "Scan now" to re-check the corpus, or wait for the next ingestion sweep.</p>
            </section>
        @else
            <section class="bg-white border border-slate-200 rounded-xl divide-y divide-slate-100 overflow-hidden">
                @foreach ($hits as $hit)
                    @php
                        $snippet = $hit->context_snippet ?? '';
                        $term = $hit->matched_term;
                    @endphp
                    <article wire:key="hit-{{ $hit->id }}" class="px-5 py-4 hover:bg-slate-50/60 transition-colors">
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <div class="flex items-center gap-1.5 text-[11px]">
                                @if ($hit->confirmed_by_llm === true)
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 font-medium">
                                        <svg class="h-2.5 w-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                                        Confirmed
                                    </span>
                                @elseif ($hit->confirmed_by_llm === false)
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 font-medium">
                                        Rejected
                                    </span>
                                @elseif ($this->llmEnabled)
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-medium">Pending</span>
                                @endif
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-medium uppercase tracking-wider text-[10px]">{{ $hit->contentTypeLabel() }}</span>
                                <span class="text-slate-400">·</span>
                                <span class="text-slate-500">{{ $hit->matched_at->diffForHumans() }}</span>
                                <span class="text-slate-400">·</span>
                                <span class="text-slate-500">matched <span class="font-mono text-slate-700">{{ $term }}</span></span>
                            </div>
                            @if ($url = $hit->contentUrl())
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="text-xs text-brand-700 hover:underline shrink-0">Open ↗</a>
                            @endif
                        </div>

                        <div class="text-sm text-slate-900 font-medium line-clamp-1 mb-1">{{ $hit->contentTitle() }}</div>

                        @if ($snippet !== '')
                            <p class="text-sm text-slate-700 leading-relaxed">
                                {!! preg_replace('/('.preg_quote($term, '/').')/i', '<mark class="bg-amber-100 text-slate-900 px-0.5 rounded">$1</mark>', e($snippet)) !!}
                            </p>
                        @endif

                        @if ($hit->llm_reasoning)
                            <p class="text-xs text-slate-500 mt-2 italic">Claude: {{ $hit->llm_reasoning }}</p>
                        @endif
                    </article>
                @endforeach
            </section>

            <div>{{ $hits->links() }}</div>
        @endif
    </div>
</div>
