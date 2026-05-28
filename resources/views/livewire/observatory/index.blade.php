<div>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <a href="{{ route('companies.show', $company) }}" wire:navigate class="hover:text-slate-900">{{ $company->name }}</a>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-700">Observatory</span>
                </div>
                <h1 class="text-base font-semibold text-slate-900 mt-0.5">Observatory</h1>
            </div>
            <a href="{{ route('companies.observatory.create', $company) }}" wire:navigate class="btn-primary inline-flex items-center gap-1.5">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                New watch
            </a>
        </div>
    </x-slot>

    @php
        $kinds = [
            'all'      => 'All',
            'company'  => 'Companies',
            'location' => 'Locations',
            'product'  => 'Products',
            'term'     => 'Terms',
        ];
    @endphp

    <div class="space-y-6 animate-fade-in">
        @if (session('status'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif

        {{-- LLM upgrade pitch — visible only when the team isn't entitled. --}}
        @if (! $this->llmEnabled)
            <section class="rounded-xl border border-indigo-200 bg-gradient-to-br from-indigo-50 via-violet-50 to-fuchsia-50 p-5 flex items-start gap-4">
                <div class="grid place-items-center h-10 w-10 rounded-lg bg-gradient-to-br from-indigo-500 via-violet-500 to-fuchsia-500 text-white shrink-0">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-semibold text-slate-900">Add LLM confirmation</div>
                    <p class="text-xs text-slate-600 leading-relaxed mt-1 max-w-2xl">
                        Literal matching is fast and free — but a string match for "Newmont" picks up Newmont <em>Court</em>, Newmont <em>Street</em>, etc. With the upgrade, Claude reads the surrounding paragraph and confirms each hit is actually about the watched entity. A small per-hit cost.
                    </p>
                </div>
                <a href="mailto:hello@prcomet.com?subject=Observatory%20LLM%20upgrade" class="btn-secondary text-xs whitespace-nowrap">Contact us →</a>
            </section>
        @endif

        {{-- Filter strip --}}
        <section class="flex flex-col md:flex-row md:items-center gap-3">
            <div class="flex items-center gap-1 p-1 bg-white rounded-lg border border-slate-200">
                @foreach ($kinds as $key => $label)
                    @php $count = $key === 'all' ? array_sum($this->kindCounts) : ($this->kindCounts[$key] ?? 0); @endphp
                    <button wire:click="setKind('{{ $key }}')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition-colors
                                   {{ $kindFilter === $key ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50' }}">
                        {{ $label }}
                        <span class="text-[10px] tabular {{ $kindFilter === $key ? 'text-brand-500' : 'text-slate-400' }}">{{ $count }}</span>
                    </button>
                @endforeach
            </div>

            <div class="relative flex-1 md:max-w-md md:ml-auto">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19a8 8 0 100-16 8 8 0 000 16zM21 21l-4.35-4.35" /></svg>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by watch name…"
                       class="w-full pl-9 pr-3 py-1.5 text-sm rounded-md border-slate-200 focus:border-brand-500 focus:ring-brand-500 bg-white" />
            </div>
        </section>

        @if ($this->watches->isEmpty())
            <section class="rounded-2xl border-2 border-dashed border-slate-200 bg-white p-12 text-center">
                <div class="mx-auto h-12 w-12 rounded-xl bg-slate-100 grid place-items-center mb-3">
                    <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                </div>
                <div class="text-base font-semibold text-slate-900">
                    @if ($search !== '' || $kindFilter !== 'all')
                        Nothing matches those filters.
                    @else
                        No watches yet.
                    @endif
                </div>
                <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">
                    @if ($search !== '' || $kindFilter !== 'all')
                        Try clearing the kind filter, or widening the search.
                    @else
                        Set up a watch for a competitor, a location, or any term that matters to your story. We'll flag it whenever it appears in something we ingest.
                    @endif
                </p>
                @if ($search === '' && $kindFilter === 'all')
                    <a href="{{ route('companies.observatory.create', $company) }}" wire:navigate class="btn-primary text-xs mt-5 inline-flex">Create your first watch</a>
                @endif
            </section>
        @else
            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($this->watches as $watch)
                    @php
                        $kindStyle = [
                            'company'  => ['bg' => 'bg-indigo-50',  'fg' => 'text-indigo-700'],
                            'location' => ['bg' => 'bg-amber-50',   'fg' => 'text-amber-700'],
                            'product'  => ['bg' => 'bg-emerald-50', 'fg' => 'text-emerald-700'],
                            'term'     => ['bg' => 'bg-slate-100',  'fg' => 'text-slate-600'],
                        ][$watch->kind] ?? ['bg' => 'bg-slate-100', 'fg' => 'text-slate-600'];
                    @endphp

                    <a href="{{ route('companies.observatory.show', ['company' => $company, 'watch' => $watch]) }}" wire:navigate
                       wire:key="watch-{{ $watch->id }}"
                       class="group block rounded-xl border border-slate-200 bg-white p-5 hover:shadow-md hover:border-slate-300 transition-all">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-slate-900 group-hover:text-brand-700 transition-colors truncate">{{ $watch->name }}</div>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium uppercase tracking-wider {{ $kindStyle['bg'] }} {{ $kindStyle['fg'] }}">{{ $watch->kind }}</span>
                                    @if ($watch->mode === 'literal_llm')
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium uppercase tracking-wider bg-gradient-to-r from-indigo-100 via-violet-100 to-fuchsia-100 text-indigo-700">
                                            <svg class="h-2.5 w-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                                            LLM
                                        </span>
                                    @endif
                                    @if (! $watch->is_active)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium uppercase tracking-wider bg-slate-100 text-slate-500">Paused</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-2xl font-semibold tabular text-slate-900">{{ $watch->hit_count }}</div>
                                <div class="text-[10px] text-slate-500 uppercase tracking-wider">{{ \Illuminate\Support\Str::plural('hit', $watch->hit_count) }}</div>
                            </div>
                        </div>

                        <div class="text-xs text-slate-500 line-clamp-1 mb-3">
                            @foreach (array_slice($watch->terms ?? [], 0, 4) as $term)
                                <span class="font-mono">{{ $term }}</span>@if (! $loop->last)<span class="text-slate-300 mx-1">·</span>@endif
                            @endforeach
                            @if (count($watch->terms ?? []) > 4)
                                <span class="text-slate-400">+{{ count($watch->terms) - 4 }} more</span>
                            @endif
                        </div>

                        <div class="text-[11px] text-slate-500 pt-3 border-t border-slate-100">
                            @if ($watch->last_matched_at)
                                Last hit {{ $watch->last_matched_at->diffForHumans() }}
                            @else
                                <span class="text-slate-400">No hits yet — corpus will be re-scanned on ingest.</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </section>
        @endif
    </div>
</div>
