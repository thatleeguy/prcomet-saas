<div>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <a href="{{ route('companies.show', $company) }}" wire:navigate class="hover:text-slate-900">{{ $company->name }}</a>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-700">One-pagers</span>
                </div>
                <h1 class="text-base font-semibold text-slate-900 mt-0.5">One-pagers</h1>
            </div>
            <form method="POST" action="{{ route('companies.onepagers.store', $company) }}">
                @csrf
                <button type="submit" class="btn-primary inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    New one-pager
                </button>
            </form>
        </div>
    </x-slot>

    @php
        $counts = $this->counts;
        $total = array_sum($counts);
        $filters = [
            'all'         => ['label' => 'All',         'count' => $total],
            'published'   => ['label' => 'Published',   'count' => $counts['published'] ?? 0],
            'draft'       => ['label' => 'Draft',       'count' => $counts['draft'] ?? 0],
            'unpublished' => ['label' => 'Unpublished', 'count' => $counts['unpublished'] ?? 0],
        ];
    @endphp

    <div class="space-y-6 animate-fade-in">

        {{-- Filter strip --}}
        <section class="flex flex-col md:flex-row md:items-center gap-3">
            <div class="flex items-center gap-1 p-1 bg-white rounded-lg border border-slate-200">
                @foreach ($filters as $key => $f)
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
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by press release, journalist, or publication…"
                       class="w-full pl-9 pr-3 py-1.5 text-sm rounded-md border-slate-200 focus:border-brand-500 focus:ring-brand-500 bg-white" />
            </div>
        </section>

        {{-- List --}}
        @if ($this->onePagers->isEmpty())
            <section class="rounded-2xl border-2 border-dashed border-slate-200 bg-white p-12 text-center">
                <div class="mx-auto h-12 w-12 rounded-xl bg-slate-100 grid place-items-center mb-3">
                    <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                <div class="text-base font-semibold text-slate-900">
                    @if ($search !== '' || $statusFilter !== 'all')
                        Nothing matches those filters.
                    @else
                        No one-pagers yet.
                    @endif
                </div>
                <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">
                    @if ($search !== '' || $statusFilter !== 'all')
                        Try widening the search, or clear the filter.
                    @else
                        One-pagers are created automatically as soon as you act on a match, or you can start a blank one — useful for an evergreen company introduction or analyst deck.
                    @endif
                </p>
                @if ($search === '' && $statusFilter === 'all')
                    <div class="mt-5 flex items-center justify-center gap-2">
                        <a href="{{ route('matches.index') }}" wire:navigate class="btn-secondary text-xs">Open matches</a>
                        <form method="POST" action="{{ route('companies.onepagers.store', $company) }}">
                            @csrf
                            <button type="submit" class="btn-primary text-xs">Start a blank one</button>
                        </form>
                    </div>
                @endif
            </section>
        @else
            <section class="bg-white border border-slate-200 rounded-xl overflow-hidden divide-y divide-slate-100">
                @foreach ($this->onePagers as $op)
                    @php
                        $match = $op->match;
                        $headline = $op->displayTitle();
                        $target = $match
                            ? ($match->author?->name ?? $match->publicationItem?->source?->name ?? 'Unknown target')
                            : 'Standalone';
                    @endphp

                    <article wire:key="op-{{ $op->id }}"
                             class="group flex items-center gap-4 px-5 py-4 hover:bg-slate-50/60 transition-colors">

                        {{-- Status pill --}}
                        <div class="shrink-0 w-24">
                            @if ($op->isPublished())
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-medium">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Published
                                </span>
                            @elseif ($op->status === 'draft')
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-[11px] font-medium">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                    Draft
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[11px] font-medium">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                    Unpublished
                                </span>
                            @endif
                        </div>

                    @php
                        $editUrl = route('companies.onepagers.edit', ['company' => $company, 'onePager' => $op]);
                    @endphp
                        {{-- Subject lines --}}
                        <div class="min-w-0 flex-1">
                            <a href="{{ $editUrl }}" wire:navigate
                               class="block group-hover:text-brand-700 transition-colors">
                                <div class="text-sm font-medium text-slate-900 line-clamp-1">{{ $headline }}</div>
                                <div class="text-xs text-slate-500 mt-0.5 line-clamp-1">
                                    @if ($match)
                                        → {{ $target }}
                                        @if ($match?->publicationItem?->source?->name && $match?->author?->name)
                                            <span class="text-slate-400">·</span>
                                            <span>{{ $match->publicationItem->source->name }}</span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 text-[10px] font-medium uppercase tracking-wider">Standalone</span>
                                    @endif
                                </div>
                            </a>
                        </div>

                        {{-- Engagement stats (only meaningful when published) --}}
                        <div class="hidden md:flex items-center gap-6 shrink-0">
                            <div class="text-right">
                                <div class="text-sm font-semibold tabular text-slate-900">{{ $op->view_count }}</div>
                                <div class="text-[10px] text-slate-500 uppercase tracking-wider">{{ \Illuminate\Support\Str::plural('view', $op->view_count) }}</div>
                            </div>
                            <div class="text-right w-20">
                                @if ($op->last_viewed_at)
                                    <div class="text-xs text-slate-700">{{ $op->last_viewed_at->diffForHumans(syntax: \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW, short: true) }}</div>
                                    <div class="text-[10px] text-slate-500 uppercase tracking-wider">Last open</div>
                                @else
                                    <div class="text-xs text-slate-400">—</div>
                                @endif
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-1.5 shrink-0">
                            @if ($op->isPublished())
                                <a href="{{ $op->publicUrl() }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs text-slate-600 hover:bg-white hover:text-slate-900 transition-colors"
                                   title="Open public link">
                                    Open
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                </a>
                            @endif
                            <a href="{{ $editUrl }}" wire:navigate
                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium text-brand-700 hover:bg-brand-50 transition-colors">
                                Edit
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" /></svg>
                            </a>
                        </div>
                    </article>
                @endforeach
            </section>
        @endif
    </div>
</div>
