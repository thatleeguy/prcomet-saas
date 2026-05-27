<div>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h1 class="text-base font-semibold text-slate-900">Today's brief</h1>
                <p class="text-xs text-slate-500">{{ now()->format('l, F j') }}</p>
            </div>
            <a href="{{ route('matches.index') }}" wire:navigate class="btn-secondary">
                See all matches
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" /></svg>
            </a>
        </div>
    </x-slot>

    @php
        $stats = $this->stats;
        $tops = $this->topMatches;
        $queue = $this->queueMatches;
        $hero = $tops->first();
        $secondaries = $tops->skip(1);

        $statusMeta = [
            'new'       => ['label' => 'New',         'cls' => 'bg-amber-50 text-amber-700 border-amber-200'],
            'saved'     => ['label' => 'Saved',       'cls' => 'bg-blue-50 text-blue-700 border-blue-200'],
            'contacted' => ['label' => 'In outreach', 'cls' => 'bg-blue-50 text-blue-700 border-blue-200'],
            'placed'    => ['label' => 'Placed',      'cls' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            'dismissed' => ['label' => 'Dismissed',   'cls' => 'bg-slate-100 text-slate-600 border-slate-200'],
        ];

        // Score → gradient mapping. High scores get the warm "hot" gradient,
        // mid-scores get a cooler indigo-violet, low stays in white card territory.
        $gradientFor = function (float $score): string {
            if ($score >= 0.80) return 'from-indigo-600 via-violet-600 to-fuchsia-600';
            if ($score >= 0.65) return 'from-indigo-500 via-indigo-600 to-violet-700';
            return 'from-slate-700 to-slate-900';
        };
    @endphp

    <div class="space-y-8 animate-fade-in">

        @if ($hero)
            {{-- ─────────────────────────────────────────────────────────────
                  HERO OPPORTUNITY — the brief
              ───────────────────────────────────────────────────────────── --}}
            <section class="relative overflow-hidden rounded-2xl shadow-xl text-white
                            bg-gradient-to-br {{ $gradientFor((float) $hero->score) }}">

                {{-- Decorative glow blobs --}}
                <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute -bottom-20 -left-20 h-72 w-72 rounded-full bg-fuchsia-400/20 blur-3xl"></div>
                {{-- Subtle noise overlay for depth --}}
                <div class="absolute inset-0 opacity-[0.04] mix-blend-overlay"
                     style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%270 0 200 200%27 xmlns=%27http://www.w3.org/2000/svg%27%3E%3Cfilter id=%27n%27%3E%3CfeTurbulence type=%27fractalNoise%27 baseFrequency=%270.9%27 numOctaves=%272%27 stitchTiles=%27stitch%27/%3E%3C/filter%3E%3Crect width=%27100%25%27 height=%27100%25%27 filter=%27url(%23n)%27/%3E%3C/svg%3E');"></div>

                <div class="relative p-8 lg:p-10 grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {{-- LEFT 2/3: meta + title + reasoning --}}
                    <div class="lg:col-span-2 space-y-6">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-white/15 backdrop-blur-sm text-xs font-medium uppercase tracking-wider">
                                <span class="relative flex h-1.5 w-1.5">
                                    <span class="absolute inline-flex h-full w-full rounded-full bg-white opacity-75 animate-ping"></span>
                                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-white"></span>
                                </span>
                                Top opportunity · {{ $hero->created_at->diffForHumans(short: true) }}
                            </span>
                        </div>

                        <div>
                            <div class="flex items-center gap-3 mb-3">
                                <div class="grid place-items-center h-9 w-9 rounded-lg bg-white/15 backdrop-blur-sm font-semibold text-sm">
                                    {{ strtoupper(substr($hero->publicationItem->source->name, 0, 2)) }}
                                </div>
                                <div class="text-sm">
                                    <div class="font-medium text-white">{{ $hero->author?->name ?? $hero->publicationItem->source->name }}</div>
                                    <div class="text-white/70 text-xs">{{ $hero->publicationItem->source->name }}{{ $hero->author ? ' · '.optional($hero->publicationItem->published_at)->diffForHumans(short: true) : '' }}</div>
                                </div>
                            </div>
                            <h2 class="text-2xl lg:text-3xl font-semibold leading-tight tracking-tight">
                                {{ $hero->publicationItem->title }}
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/10">
                                <div class="text-[10px] font-semibold uppercase tracking-wider text-white/60 mb-2">Why they're a fit</div>
                                <p class="text-sm leading-relaxed text-white/95 line-clamp-5">{{ \Illuminate\Support\Str::limit(strip_tags($hero->rationale_md), 280) }}</p>
                            </div>
                            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/10">
                                <div class="text-[10px] font-semibold uppercase tracking-wider text-white/60 mb-2">Suggested angle</div>
                                <p class="text-sm leading-relaxed text-white/95 line-clamp-5">{{ \Illuminate\Support\Str::limit(strip_tags($hero->suggested_angle_md), 220) }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 pt-1">
                            <a href="{{ route('matches.show', $hero) }}" wire:navigate
                               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-white text-slate-900 font-medium text-sm hover:bg-slate-100 transition-colors shadow-lg">
                                Open full brief
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" /></svg>
                            </a>
                            <span class="text-white/70 text-xs">
                                For <span class="text-white font-medium">{{ $hero->company->name }}</span>
                            </span>
                        </div>
                    </div>

                    {{-- RIGHT 1/3: score gauge --}}
                    <div class="flex lg:justify-end">
                        <div class="relative h-44 w-44">
                            @php
                                $score = (float) $hero->score;
                                $circumference = 2 * pi() * 70;
                                $offset = $circumference * (1 - $score);
                            @endphp
                            <svg viewBox="0 0 160 160" class="h-full w-full -rotate-90">
                                <circle cx="80" cy="80" r="70" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="10" />
                                <circle cx="80" cy="80" r="70" fill="none" stroke="white" stroke-width="10" stroke-linecap="round"
                                        stroke-dasharray="{{ $circumference }}"
                                        stroke-dashoffset="{{ $offset }}"
                                        style="transition: stroke-dashoffset 800ms cubic-bezier(0.22, 1, 0.36, 1);" />
                            </svg>
                            <div class="absolute inset-0 grid place-items-center text-center">
                                <div>
                                    <div class="text-5xl font-bold tabular leading-none">{{ number_format($score * 100) }}<span class="text-2xl font-medium text-white/70">%</span></div>
                                    <div class="mt-1.5 text-[10px] uppercase tracking-wider text-white/70 font-medium">Match confidence</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ─────────────────────────────────────────────────────────────
              SECONDARY OPPORTUNITIES — same density, half the size
          ───────────────────────────────────────────────────────────── --}}
        @if ($secondaries->isNotEmpty())
            <section class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($secondaries as $m)
                    @php $score = (float) $m->score; @endphp
                    <a href="{{ route('matches.show', $m) }}" wire:navigate
                       class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6
                              hover:border-brand-300 hover:shadow-lg transition-all">

                        {{-- Tinted gradient corner --}}
                        <div class="absolute -top-12 -right-12 h-40 w-40 rounded-full
                                    bg-gradient-to-br {{ $gradientFor($score) }} opacity-10 group-hover:opacity-20 blur-2xl transition-opacity"></div>

                        <div class="relative">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="grid place-items-center h-8 w-8 rounded-md
                                                bg-gradient-to-br {{ $gradientFor($score) }} text-white font-semibold text-xs">
                                        {{ strtoupper(substr($m->publicationItem->source->name, 0, 2)) }}
                                    </div>
                                    <div class="text-xs">
                                        <div class="font-medium text-slate-900">{{ $m->author?->name ?? $m->publicationItem->source->name }}</div>
                                        <div class="text-slate-500">{{ $m->publicationItem->source->name }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xl font-semibold tabular bg-gradient-to-br {{ $gradientFor($score) }} bg-clip-text text-transparent">
                                        {{ number_format($score * 100) }}%
                                    </div>
                                </div>
                            </div>

                            <h3 class="font-semibold text-slate-900 leading-snug mb-3 line-clamp-2 group-hover:text-brand-700 transition-colors">
                                {{ $m->publicationItem->title }}
                            </h3>

                            <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Why they're a fit</div>
                            <p class="text-sm text-slate-600 leading-relaxed line-clamp-3">
                                {{ \Illuminate\Support\Str::limit(strip_tags($m->rationale_md), 220) }}
                            </p>

                            <div class="mt-4 flex items-center justify-between">
                                <span class="text-xs text-slate-500">For <span class="font-medium text-slate-700">{{ $m->company->name }}</span></span>
                                <span class="text-xs font-medium text-brand-700 group-hover:translate-x-0.5 transition-transform">
                                    Open brief →
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </section>
        @endif

        {{-- ─────────────────────────────────────────────────────────────
              INLINE STATUS STRIP
          ───────────────────────────────────────────────────────────── --}}
        <section class="rounded-2xl border border-slate-200 bg-white px-6 py-5">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 md:gap-6 items-center">

                <div>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-2xl font-semibold tabular text-slate-900">{{ $stats['new_this_week'] }}</span>
                        @if ($stats['trend_pct'] !== null)
                            <span class="text-xs font-medium {{ $stats['trend_pct'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $stats['trend_pct'] >= 0 ? '↗' : '↘' }} {{ abs($stats['trend_pct']) }}%
                            </span>
                        @endif
                    </div>
                    <div class="text-xs text-slate-500 mt-0.5">New this week</div>
                </div>

                <div>
                    <div class="text-2xl font-semibold tabular text-slate-900">{{ $stats['in_outreach'] }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">In outreach</div>
                </div>

                <div>
                    <div class="text-2xl font-semibold tabular text-emerald-700">{{ $stats['placed'] }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">Placements</div>
                </div>

                <div>
                    <div class="text-2xl font-semibold tabular text-slate-900">{{ $stats['sources_scanned'] }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">Sources scanned</div>
                </div>

                {{-- Sparkline --}}
                <div class="md:text-right">
                    <svg viewBox="0 0 100 24" preserveAspectRatio="none" class="w-full h-12 md:w-32 md:h-10 md:ml-auto overflow-visible">
                        <defs>
                            <linearGradient id="sparkFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#4339DC" stop-opacity="0.25" />
                                <stop offset="100%" stop-color="#4339DC" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        @php
                            $sparkPath = $this->sparklinePath;
                            $areaPath = $sparkPath.' L 100 24 L 0 24 Z';
                        @endphp
                        <path d="{{ $areaPath }}" fill="url(#sparkFill)" />
                        <path d="{{ $sparkPath }}" fill="none" stroke="#4339DC" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="text-[10px] text-slate-400 md:text-right">14-day activity</div>
                </div>
            </div>
        </section>

        {{-- ─────────────────────────────────────────────────────────────
              REST OF THE QUEUE
          ───────────────────────────────────────────────────────────── --}}
        @if ($queue->isNotEmpty())
            <section class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <h3 class="text-sm font-semibold text-slate-900">More in your queue</h3>
                    <a href="{{ route('matches.index') }}" wire:navigate class="text-xs font-medium text-brand-700 hover:text-brand-800">
                        View all matches →
                    </a>
                </div>
                <ul class="divide-y divide-slate-100">
                    @foreach ($queue as $m)
                        @php $meta = $statusMeta[$m->status] ?? $statusMeta['new']; @endphp
                        <li>
                            <a href="{{ route('matches.show', $m) }}" wire:navigate class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50 transition-colors">
                                <div class="grid place-items-center h-8 w-8 rounded-md bg-slate-100 text-slate-600 font-medium text-[11px] shrink-0">
                                    {{ strtoupper(substr($m->publicationItem->source->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-medium text-slate-900 truncate">{{ $m->publicationItem->title }}</div>
                                    <div class="text-xs text-slate-500 truncate mt-0.5">
                                        {{ $m->company->name }} · {{ $m->publicationItem->source->name }}@if ($m->author) · {{ $m->author->name }} @endif
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium border {{ $meta['cls'] }}">{{ $meta['label'] }}</span>
                                <div class="text-sm font-semibold tabular w-12 text-right {{ $m->score >= 0.8 ? 'text-emerald-700' : ($m->score >= 0.65 ? 'text-brand-700' : 'text-slate-500') }}">
                                    {{ number_format($m->score * 100) }}%
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- Empty state: no opportunities at all --}}
        @if (! $hero && $queue->isEmpty())
            <section class="rounded-2xl border-2 border-dashed border-slate-200 bg-white p-12 text-center">
                <div class="mx-auto h-14 w-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-fuchsia-500 grid place-items-center mb-4 shadow-lg">
                    <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" /></svg>
                </div>
                <h3 class="text-base font-semibold text-slate-900">All caught up</h3>
                <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">No opportunities in your queue right now. We're scanning {{ $stats['sources_scanned'] }} sources for new fits.</p>
                <a href="{{ route('companies.index') }}" wire:navigate class="btn-primary mt-5">Manage companies</a>
            </section>
        @endif
    </div>
</div>
