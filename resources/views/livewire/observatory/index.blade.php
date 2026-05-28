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
        $counts = $this->statusCounts;
        $statuses = [
            'all' => ['label' => 'All', 'count' => $counts['all']],
            'unread' => ['label' => 'Unread', 'count' => $counts['unread']],
        ];
        if ($this->llmEnabled) {
            $statuses['confirmed'] = ['label' => 'Confirmed', 'count' => $counts['confirmed']];
            $statuses['pending'] = ['label' => 'Pending', 'count' => $counts['pending']];
            $statuses['rejected'] = ['label' => 'Rejected', 'count' => $counts['rejected']];
        }
        $totalUnread = collect($this->watches)->sum('unread_count');
    @endphp

    <div class="animate-fade-in">
        @if (session('status'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800 mb-4">{{ session('status') }}</div>
        @endif

        @if (! $this->llmEnabled)
            <section class="rounded-xl border border-indigo-200 bg-gradient-to-br from-indigo-50 via-violet-50 to-fuchsia-50 p-4 mb-4 flex items-start gap-3">
                <div class="grid place-items-center h-8 w-8 rounded-lg bg-gradient-to-br from-indigo-500 via-violet-500 to-fuchsia-500 text-white shrink-0">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-semibold text-slate-900">Add LLM confirmation</div>
                    <p class="text-xs text-slate-600 leading-relaxed mt-0.5">A string match for "Newmont" picks up Newmont <em>Court</em>, Newmont <em>Street</em>, etc. With the upgrade, Claude reads the snippet and confirms each hit is actually about the watched entity.</p>
                </div>
                <a href="mailto:hello@prcomet.com?subject=Observatory%20LLM%20upgrade" class="btn-secondary text-xs whitespace-nowrap">Contact us →</a>
            </section>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

            {{-- Left rail: watches as "labels". --}}
            <aside class="lg:col-span-1 space-y-2">
                <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Watches</div>
                        <span class="text-[11px] text-slate-400 tabular">{{ count($this->watches) }}</span>
                    </div>

                    <button wire:click="setWatch(0)"
                            class="w-full flex items-center justify-between px-4 py-2.5 text-sm transition-colors
                                   {{ $watchFilter === 0 ? 'bg-brand-50 text-brand-700 font-medium' : 'text-slate-700 hover:bg-slate-50' }}">
                        <span class="flex items-center gap-2">
                            <svg class="h-4 w-4 {{ $watchFilter === 0 ? 'text-brand-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            Inbox
                        </span>
                        @if ($totalUnread > 0)
                            <span class="inline-flex items-center px-1.5 rounded-full text-[10px] font-semibold {{ $watchFilter === 0 ? 'bg-brand-600 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $totalUnread }}</span>
                        @endif
                    </button>

                    @if ($this->watches->isNotEmpty())
                        <div class="border-t border-slate-100 py-1">
                            @foreach ($this->watches as $watch)
                                @php
                                    $kindDot = [
                                        'company' => 'bg-indigo-500',
                                        'location' => 'bg-amber-500',
                                        'product' => 'bg-emerald-500',
                                        'term' => 'bg-slate-400',
                                    ][$watch->kind] ?? 'bg-slate-400';
                                @endphp
                                <button wire:click="setWatch({{ $watch->id }})"
                                        class="w-full flex items-center justify-between px-4 py-2 text-sm transition-colors text-left
                                               {{ $watchFilter === $watch->id ? 'bg-brand-50 text-brand-700 font-medium' : 'text-slate-700 hover:bg-slate-50' }}">
                                    <span class="flex items-center gap-2 min-w-0">
                                        <span class="h-2 w-2 rounded-full {{ $kindDot }} shrink-0"></span>
                                        <span class="truncate">{{ $watch->name }}</span>
                                        @if ($watch->mode === 'literal_llm')
                                            <svg class="h-2.5 w-2.5 text-indigo-500 shrink-0" fill="currentColor" viewBox="0 0 24 24" title="LLM mode"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                                        @endif
                                    </span>
                                    @if ($watch->unread_count > 0)
                                        <span class="inline-flex items-center px-1.5 rounded-full text-[10px] font-semibold {{ $watchFilter === $watch->id ? 'bg-brand-600 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $watch->unread_count }}</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <a href="{{ route('companies.observatory.create', $company) }}" wire:navigate
                       class="block px-4 py-2.5 text-xs text-brand-700 hover:bg-slate-50 border-t border-slate-100">
                        + New watch
                    </a>
                </div>
            </aside>

            {{-- Main inbox column --}}
            <main class="lg:col-span-3 space-y-3">
                <div class="flex flex-col md:flex-row md:items-center gap-2">
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

                    <div class="relative flex-1 md:max-w-sm md:ml-auto">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19a8 8 0 100-16 8 8 0 000 16zM21 21l-4.35-4.35" /></svg>
                        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search snippets…"
                               class="w-full pl-9 pr-3 py-1.5 text-sm rounded-md border-slate-200 focus:border-brand-500 focus:ring-brand-500 bg-white" />
                    </div>

                    @if ($counts['unread'] > 0)
                        <button wire:click="markAllRead" class="btn-secondary text-xs whitespace-nowrap">Mark all read</button>
                    @endif
                </div>

                @if ($hits->isEmpty())
                    <section class="rounded-2xl border-2 border-dashed border-slate-200 bg-white p-12 text-center">
                        @if ($this->watches->isEmpty())
                            <div class="text-base font-semibold text-slate-900">No watches yet.</div>
                            <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">Set up a watch for a competitor, a location, or any term that matters to your story. We'll flag it whenever it appears in something we ingest.</p>
                            <a href="{{ route('companies.observatory.create', $company) }}" wire:navigate class="btn-primary text-xs mt-5 inline-flex">Create your first watch</a>
                        @else
                            <div class="text-base font-semibold text-slate-900">
                                @if ($search !== '' || $statusFilter !== 'all' || $watchFilter !== 0)
                                    Nothing matches those filters.
                                @else
                                    Inbox zero.
                                @endif
                            </div>
                            <p class="text-sm text-slate-500 mt-1">New hits will appear here as content gets ingested.</p>
                        @endif
                    </section>
                @else
                    <section class="bg-white border border-slate-200 rounded-xl overflow-hidden divide-y divide-slate-100">
                        @foreach ($hits as $hit)
                            @php
                                $watch = $hit->watch;
                                $unread = $hit->seen_at === null;
                                $snippet = $hit->context_snippet ?? '';
                                $term = $hit->matched_term;
                            @endphp
                            <article wire:key="hit-{{ $hit->id }}"
                                     class="relative pl-5 pr-4 py-3.5 hover:bg-slate-50/60 transition-colors
                                            {{ $unread ? 'bg-white' : 'bg-slate-50/30' }}">
                                {{-- Unread accent stripe --}}
                                @if ($unread)
                                    <span class="absolute left-0 inset-y-0 w-1 bg-brand-500"></span>
                                @endif

                                <div class="flex items-start gap-3">
                                    {{-- Unread dot — also serves as a click target to mark read --}}
                                    <button wire:click="markRead({{ $hit->id }})"
                                            class="mt-1.5 shrink-0 grid place-items-center h-4 w-4 rounded-full transition-colors {{ $unread ? 'bg-brand-500 hover:bg-brand-600' : 'bg-slate-200' }}"
                                            title="{{ $unread ? 'Mark as read' : 'Read' }}">
                                        @if (! $unread)
                                            <svg class="h-2.5 w-2.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        @endif
                                    </button>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 text-[11px] mb-1">
                                            <button wire:click="setWatch({{ $watch->id }})" class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded font-medium hover:underline
                                                @php
                                                    $kindClass = [
                                                        'company' => 'bg-indigo-50 text-indigo-700',
                                                        'location' => 'bg-amber-50 text-amber-700',
                                                        'product' => 'bg-emerald-50 text-emerald-700',
                                                        'term' => 'bg-slate-100 text-slate-600',
                                                    ][$watch->kind] ?? 'bg-slate-100 text-slate-600';
                                                @endphp
                                                {{ $kindClass }}">
                                                {{ $watch->name }}
                                            </button>

                                            @if ($hit->confirmed_by_llm === true)
                                                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 font-medium">
                                                    <svg class="h-2.5 w-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                                                    Confirmed
                                                </span>
                                            @elseif ($hit->confirmed_by_llm === false)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 font-medium">Rejected</span>
                                            @elseif ($watch->mode === 'literal_llm' && $this->llmEnabled)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-medium">Pending</span>
                                            @endif

                                            <span class="text-slate-400 ml-auto whitespace-nowrap">{{ $hit->matched_at->diffForHumans() }}</span>
                                        </div>

                                        <div class="text-sm {{ $unread ? 'text-slate-900 font-semibold' : 'text-slate-700' }} line-clamp-1 mb-0.5">
                                            {{ $hit->contentTitle() }}
                                        </div>

                                        @if ($snippet !== '')
                                            <p class="text-xs text-slate-600 leading-relaxed line-clamp-1">
                                                {!! preg_replace('/('.preg_quote($term, '/').')/i', '<mark class="bg-amber-100 text-slate-900 px-0.5 rounded">$1</mark>', e($snippet)) !!}
                                            </p>
                                        @endif

                                        @if ($hit->llm_reasoning)
                                            <p class="text-[11px] text-slate-500 mt-1 italic">Claude: {{ $hit->llm_reasoning }}</p>
                                        @endif
                                    </div>

                                    <div class="shrink-0 flex flex-col items-end gap-1.5">
                                        @if ($url = $hit->contentUrl())
                                            <a href="{{ $url }}" target="_blank" rel="noopener"
                                               wire:click="markRead({{ $hit->id }})"
                                               class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs text-slate-600 hover:bg-white hover:text-slate-900 border border-transparent hover:border-slate-200 transition-colors">
                                                Open
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </section>

                    <div>{{ $hits->links() }}</div>
                @endif
            </main>
        </div>
    </div>
</div>
