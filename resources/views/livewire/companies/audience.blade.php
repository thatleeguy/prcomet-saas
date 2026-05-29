<div>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <a href="{{ route('companies.show', $company) }}" wire:navigate class="hover:text-slate-900">{{ $company->name }}</a>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-700">Audience</span>
                </div>
                <h1 class="text-base font-semibold text-slate-900 mt-0.5">Newsroom audience</h1>
            </div>
            @if ($this->stats['total_active'] > 0)
                <button wire:click="exportCsv" class="btn-secondary inline-flex items-center gap-1.5 text-xs">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" /></svg>
                    Export CSV
                </button>
            @endif
        </div>
    </x-slot>

    @php $stats = $this->stats; @endphp

    <div class="space-y-6 animate-fade-in">

        {{-- Network framing ────────────────────────────────────────
             Pairs the customer's slice with the PrComet network
             total so they see the multiplier even when their own
             count is small. --}}
        <section class="rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-indigo-50/30 p-6">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <div class="inline-flex items-center gap-1.5 mb-1.5">
                        <svg class="h-3.5 w-3.5 text-indigo-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                        <span class="text-[10px] font-semibold text-indigo-600 uppercase tracking-wider">PrComet network</span>
                    </div>
                    <h2 class="text-2xl font-semibold text-slate-900 tracking-tight">
                        {{ $stats['total_active'] }} {{ \Illuminate\Support\Str::plural('person', $stats['total_active']) }} {{ $stats['total_active'] === 1 ? 'follows' : 'follow' }} {{ $company->name }}
                    </h2>
                    <p class="text-sm text-slate-600 mt-1.5 max-w-2xl leading-relaxed">
                        You're plugged into a network of <span class="font-semibold text-slate-900">{{ $stats['network_total'] }}</span> active subscribers. Subscribers manage one set of preferences across every company they follow, which is why churn is low and engagement is high.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="rounded-lg bg-white border border-slate-200 px-4 py-3">
                    <div class="text-2xl font-semibold tabular text-slate-900">{{ $stats['total_active'] }}</div>
                    <div class="text-[10px] text-slate-500 uppercase tracking-wider mt-0.5">Confirmed</div>
                </div>
                <div class="rounded-lg bg-white border border-slate-200 px-4 py-3">
                    <div class="text-2xl font-semibold tabular text-amber-700">{{ $stats['unconfirmed'] }}</div>
                    <div class="text-[10px] text-slate-500 uppercase tracking-wider mt-0.5">Pending confirm</div>
                </div>
                <div class="rounded-lg bg-white border border-slate-200 px-4 py-3">
                    <div class="text-2xl font-semibold tabular text-emerald-700">+{{ $stats['this_month'] }}</div>
                    <div class="text-[10px] text-slate-500 uppercase tracking-wider mt-0.5">This month</div>
                </div>
                <div class="rounded-lg bg-white border border-slate-200 px-4 py-3">
                    <div class="text-2xl font-semibold tabular text-slate-900">{{ $stats['network_total'] }}</div>
                    <div class="text-[10px] text-slate-500 uppercase tracking-wider mt-0.5">Network total</div>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 mt-3">
                @foreach ([
                    ['key' => 'instant', 'label' => 'Instant', 'count' => $stats['instant'], 'dot' => 'bg-rose-500'],
                    ['key' => 'daily',   'label' => 'Daily',   'count' => $stats['daily'],   'dot' => 'bg-amber-500'],
                    ['key' => 'weekly',  'label' => 'Weekly',  'count' => $stats['weekly'],  'dot' => 'bg-emerald-500'],
                ] as $row)
                    <div class="rounded-lg bg-white border border-slate-200 px-4 py-3 flex items-center gap-3">
                        <span class="h-2 w-2 rounded-full {{ $row['dot'] }}"></span>
                        <div class="flex-1">
                            <div class="text-sm font-semibold text-slate-900 tabular">{{ $row['count'] }}</div>
                            <div class="text-[10px] text-slate-500 uppercase tracking-wider">{{ $row['label'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        @if (! $company->newsroom_published)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 flex items-start gap-3">
                <svg class="h-5 w-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <div class="flex-1">
                    <div class="text-sm font-semibold text-amber-900">Newsroom is unpublished</div>
                    <p class="text-xs text-amber-800 mt-0.5">New subscribers can't reach you and digests skip your stories. Flip the toggle on <a href="{{ route('companies.branding', $company) }}" wire:navigate class="underline">Branding</a> to go live.</p>
                </div>
            </div>
        @endif

        {{-- Filter strip --}}
        <section class="flex flex-col md:flex-row md:items-center gap-3">
            <div class="flex items-center gap-1 p-1 bg-white rounded-lg border border-slate-200">
                @foreach ([
                    'all'         => ['label' => 'All',     'count' => $stats['total_active'] + $stats['unconfirmed']],
                    'instant'     => ['label' => 'Instant', 'count' => $stats['instant']],
                    'daily'       => ['label' => 'Daily',   'count' => $stats['daily']],
                    'weekly'      => ['label' => 'Weekly',  'count' => $stats['weekly']],
                    'unconfirmed' => ['label' => 'Pending', 'count' => $stats['unconfirmed']],
                ] as $key => $row)
                    <button wire:click="setFilter('{{ $key }}')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition-colors
                                   {{ $filter === $key ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50' }}">
                        {{ $row['label'] }}
                        <span class="text-[10px] tabular {{ $filter === $key ? 'text-brand-500' : 'text-slate-400' }}">{{ $row['count'] }}</span>
                    </button>
                @endforeach
            </div>

            <div class="relative flex-1 md:max-w-md md:ml-auto">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19a8 8 0 100-16 8 8 0 000 16zM21 21l-4.35-4.35" /></svg>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by email or name…"
                       class="w-full pl-9 pr-3 py-1.5 text-sm rounded-md border-slate-200 focus:border-brand-500 focus:ring-brand-500 bg-white" />
            </div>
        </section>

        {{-- Subscriber list --}}
        @if ($subscribers->isEmpty())
            <section class="rounded-2xl border-2 border-dashed border-slate-200 bg-white p-12 text-center">
                @if ($stats['total_active'] + $stats['unconfirmed'] === 0)
                    <div class="text-base font-semibold text-slate-900">No subscribers yet.</div>
                    <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">Share your newsroom URL — every subscriber from there joins the PrComet network and contributes to the digest reach.</p>
                    @if ($company->newsroom_published)
                        <a href="{{ url('/newsroom/'.$company->slug) }}" target="_blank" rel="noopener" class="btn-secondary text-xs mt-5 inline-block">
                            Open your newsroom ↗
                        </a>
                    @endif
                @else
                    <div class="text-base font-semibold text-slate-900">No matches.</div>
                    <p class="text-sm text-slate-500 mt-1">Try widening the filter or clearing the search.</p>
                @endif
            </section>
        @else
            <section class="bg-white border border-slate-200 rounded-xl overflow-hidden divide-y divide-slate-100">
                @foreach ($subscribers as $sub)
                    @php
                        $cadenceMeta = [
                            'instant' => ['dot' => 'bg-rose-500',    'label' => 'Instant'],
                            'daily'   => ['dot' => 'bg-amber-500',   'label' => 'Daily'],
                            'weekly'  => ['dot' => 'bg-emerald-500', 'label' => 'Weekly'],
                        ][$sub->cadence] ?? ['dot' => 'bg-slate-400', 'label' => $sub->cadence];
                        $pivotSubscribedAt = $sub->pivot_subscribed_at ? \Carbon\Carbon::parse($sub->pivot_subscribed_at) : null;
                    @endphp
                    <article wire:key="sub-{{ $sub->id }}" class="px-5 py-3.5 flex items-center gap-4">
                        <div class="grid place-items-center h-9 w-9 rounded-full bg-slate-100 text-slate-700 font-medium text-xs shrink-0">
                            {{ strtoupper(substr($sub->name ?? $sub->email, 0, 2)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <div class="text-sm font-medium text-slate-900 truncate">{{ $sub->name ?: $sub->email }}</div>
                                @if (! $sub->confirmed_at)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium uppercase tracking-wider bg-amber-50 text-amber-700">Pending</span>
                                @endif
                            </div>
                            @if ($sub->name)
                                <div class="text-xs text-slate-500 truncate font-mono">{{ $sub->email }}</div>
                            @endif
                        </div>
                        <div class="hidden md:flex items-center gap-2 text-xs shrink-0">
                            <span class="h-1.5 w-1.5 rounded-full {{ $cadenceMeta['dot'] }}"></span>
                            <span class="text-slate-700 font-medium">{{ $cadenceMeta['label'] }}</span>
                        </div>
                        <div class="text-right text-xs text-slate-500 shrink-0 w-24">
                            @if ($pivotSubscribedAt)
                                <div>{{ $pivotSubscribedAt->diffForHumans(syntax: \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW, short: true) }}</div>
                                <div class="text-[10px] text-slate-400 uppercase tracking-wider">Joined</div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </section>

            <div>{{ $subscribers->links() }}</div>
        @endif

        <p class="text-[11px] text-slate-400 leading-relaxed">
            Subscriber identities are managed by PrComet. People who subscribe through your newsroom may also follow other companies on the network — you see your slice here, but each subscriber controls their own preferences from their personal manage page.
        </p>
    </div>
</div>
