<div>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4 w-full min-w-0">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('stream.index') }}" wire:navigate
                   class="flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-900 transition-colors shrink-0">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5M11 18l-6-6 6-6" /></svg>
                    Stream
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-medium text-slate-700 truncate">{{ $author->name }}</span>
            </div>

            @if ($author->email)
                <a href="mailto:{{ $author->email }}" class="btn-secondary text-xs">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    Email {{ explode(' ', $author->name)[0] }}
                </a>
            @endif
        </div>
    </x-slot>

    @php
        $counts = $this->counts;
        $topics = $this->topicCounts;
        $stances = $this->stanceCounts;
        $claims = $this->verifiedClaims;
        $teamMatches = $this->teamMatches;

        // Pick a deterministic gradient for this author's mark — same hash trick
        // as the stream, but keyed on the author so they have a stable identity.
        $marks = ['from-indigo-500 to-violet-600', 'from-blue-500 to-indigo-600', 'from-violet-500 to-fuchsia-600', 'from-rose-500 to-fuchsia-500', 'from-amber-500 to-orange-600', 'from-emerald-500 to-teal-600'];
        $mark = $marks[$author->id % count($marks)];

        $stanceCls = [
            'bullish'       => ['bg' => 'bg-emerald-500', 'text' => 'text-emerald-700'],
            'bearish'       => ['bg' => 'bg-rose-500',    'text' => 'text-rose-700'],
            'contrarian'    => ['bg' => 'bg-amber-500',   'text' => 'text-amber-700'],
            'neutral'       => ['bg' => 'bg-slate-400',   'text' => 'text-slate-600'],
            'informational' => ['bg' => 'bg-slate-400',   'text' => 'text-slate-600'],
        ];

        $verifiedCls = [
            'correct'      => ['cls' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Correct',     'dot' => 'bg-emerald-500'],
            'incorrect'    => ['cls' => 'bg-rose-50 text-rose-700 border-rose-200',          'label' => 'Incorrect',   'dot' => 'bg-rose-500'],
            'partial'      => ['cls' => 'bg-amber-50 text-amber-700 border-amber-200',       'label' => 'Partial',     'dot' => 'bg-amber-500'],
        ];

        $totalStance = max(1, array_sum($stances));
    @endphp

    <div class="space-y-6 animate-fade-in">

        {{-- ─────────────────────────────────────────────────────────────
              HERO — author identity + key metrics
          ───────────────────────────────────────────────────────────── --}}
        <section class="relative overflow-hidden rounded-2xl shadow-xl text-white bg-gradient-to-br {{ $mark }}">
            <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 h-72 w-72 rounded-full bg-white/5 blur-3xl"></div>
            <div class="absolute inset-0 opacity-[0.04] mix-blend-overlay"
                 style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%270 0 200 200%27 xmlns=%27http://www.w3.org/2000/svg%27%3E%3Cfilter id=%27n%27%3E%3CfeTurbulence type=%27fractalNoise%27 baseFrequency=%270.9%27 numOctaves=%272%27 stitchTiles=%27stitch%27/%3E%3C/filter%3E%3Crect width=%27100%25%27 height=%27100%25%27 filter=%27url(%23n)%27/%3E%3C/svg%3E');"></div>

            <div class="relative p-8 lg:p-10">
                <div class="flex items-start gap-6">
                    {{-- Avatar --}}
                    <div class="grid place-items-center h-20 w-20 rounded-2xl bg-white/15 backdrop-blur-sm font-bold text-2xl shrink-0 ring-2 ring-white/20">
                        {{ strtoupper(substr($author->name, 0, 2)) }}
                    </div>

                    {{-- Identity --}}
                    <div class="min-w-0 flex-1">
                        <h1 class="text-2xl lg:text-3xl font-semibold tracking-tight">{{ $author->name }}</h1>
                        @if ($author->primarySource)
                            <div class="text-sm text-white/80 mt-1">
                                {{ $author->primarySource->name }}
                                <span class="text-white/50">· {{ $author->primarySource->type }}</span>
                            </div>
                        @endif
                        <div class="mt-3 flex items-center flex-wrap gap-x-4 gap-y-1.5 text-sm">
                            @if ($author->x_handle)
                                <span class="inline-flex items-center gap-1.5 text-white/85 font-mono text-xs">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zM17.083 19.77h1.833L7.084 4.126H5.117z"/></svg>
                                    {{ $author->x_handle }}
                                </span>
                            @endif
                            @if ($author->email)
                                <a href="mailto:{{ $author->email }}" class="inline-flex items-center gap-1.5 text-white/85 hover:text-white">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    {{ $author->email }}
                                </a>
                            @endif
                            @if ($author->website)
                                <a href="{{ $author->website }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-white/85 hover:text-white">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 4H4v16h16v-6M14 4h6v6M10 14L20 4" /></svg>
                                    Website
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Stats bar --}}
                <div class="mt-7 pt-6 border-t border-white/15 grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                    <div>
                        <div class="text-2xl font-semibold tabular leading-none">{{ $counts['total_items'] }}</div>
                        <div class="text-xs text-white/70 mt-1.5">Total publications</div>
                    </div>
                    <div>
                        <div class="text-2xl font-semibold tabular leading-none">{{ $counts['last_30_days'] }}</div>
                        <div class="text-xs text-white/70 mt-1.5">Last 30 days</div>
                    </div>
                    <div>
                        <div class="text-2xl font-semibold tabular leading-none">{{ $counts['topics'] }}</div>
                        <div class="text-xs text-white/70 mt-1.5">Topics covered</div>
                    </div>
                    <div>
                        <div class="text-2xl font-semibold tabular leading-none">{{ $counts['matches'] }}</div>
                        <div class="text-xs text-white/70 mt-1.5">Matches with your team</div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ─────────────────────────────────────────────────────────────
              MAIN GRID
          ───────────────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- LEFT: body of work --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- LLM-generated body-of-work summary --}}
                @if ($author->body_of_work_summary)
                    <section class="rounded-2xl border border-slate-200 bg-white p-6 lg:p-7">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="h-4 w-4 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" /></svg>
                            <h2 class="text-sm font-semibold text-slate-900 uppercase tracking-wide">Body of work</h2>
                            @if ($author->body_of_work_updated_at)
                                <span class="text-[10px] text-slate-400 ml-auto">Updated {{ $author->body_of_work_updated_at->diffForHumans(short: true) }}</span>
                            @endif
                        </div>
                        <p class="text-[15px] leading-relaxed text-slate-700 whitespace-pre-wrap">{{ $author->body_of_work_summary }}</p>
                    </section>
                @endif

                {{-- Recent publications --}}
                <section class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                        <h3 class="text-sm font-semibold text-slate-900">Recent publications</h3>
                        <span class="text-xs text-slate-500 tabular">{{ $items->total() }} total</span>
                    </div>

                    @if ($items->isEmpty())
                        <div class="p-8 text-center text-sm text-slate-500">No publications analyzed yet.</div>
                    @else
                        <ul class="divide-y divide-slate-100">
                            @foreach ($items as $item)
                                @php $stance = $stanceCls[$item->stance] ?? null; @endphp
                                <li>
                                    <a href="{{ $item->url }}" target="_blank" rel="noopener"
                                       class="group block px-6 py-4 hover:bg-slate-50 transition-colors">
                                        <div class="flex items-start gap-3">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center flex-wrap gap-x-2 gap-y-1 text-xs text-slate-500 mb-1.5">
                                                    <span class="font-medium text-slate-700">{{ $item->source->name }}</span>
                                                    <span class="text-slate-300">·</span>
                                                    <span class="tabular">{{ optional($item->published_at)->toFormattedDateString() ?? 'undated' }}</span>
                                                    @if ($stance)
                                                        <span class="text-slate-300">·</span>
                                                        <span class="inline-flex items-center gap-1 {{ $stance['text'] }}">
                                                            <span class="h-1 w-1 rounded-full {{ $stance['bg'] }}"></span>
                                                            {{ $item->stance }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-sm font-medium text-slate-900 leading-snug group-hover:text-brand-700 transition-colors">
                                                    {{ $item->title }}
                                                    <svg class="inline-block h-3 w-3 text-slate-300 group-hover:text-brand-500 ml-0.5 -mt-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 4H4v16h16v-6M14 4h6v6M10 14L20 4" /></svg>
                                                </div>
                                                @if (! empty($item->extracted_topics))
                                                    <div class="flex items-center flex-wrap gap-1 mt-2">
                                                        @foreach (array_slice($item->extracted_topics, 0, 5) as $topic)
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium text-slate-500 bg-slate-100">#{{ $topic }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        @if ($items->hasPages())
                            <div class="px-6 py-3 border-t border-slate-100">
                                {{ $items->links() }}
                            </div>
                        @endif
                    @endif
                </section>
            </div>

            {{-- RIGHT: coverage + track record + your matches --}}
            <div class="space-y-6">

                {{-- Top topics --}}
                @if (! empty($topics))
                    <section class="rounded-2xl border border-slate-200 bg-white p-6">
                        <h3 class="text-sm font-semibold text-slate-900 mb-3">Most-covered topics</h3>
                        <div class="space-y-2">
                            @php $maxTopic = max($topics); @endphp
                            @foreach ($topics as $topic => $c)
                                <div>
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="text-slate-700">#{{ $topic }}</span>
                                        <span class="text-slate-400 tabular">{{ $c }}</span>
                                    </div>
                                    <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-brand-500 to-violet-500 rounded-full" style="width: {{ ($c / $maxTopic) * 100 }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Stance distribution --}}
                @if (! empty($stances))
                    <section class="rounded-2xl border border-slate-200 bg-white p-6">
                        <h3 class="text-sm font-semibold text-slate-900 mb-4">Typical stance</h3>
                        <div class="flex h-2 overflow-hidden rounded-full bg-slate-100">
                            @foreach (['bullish', 'contrarian', 'bearish', 'neutral', 'informational'] as $key)
                                @if (! empty($stances[$key] ?? null))
                                    <div class="{{ $stanceCls[$key]['bg'] }}" style="width: {{ ($stances[$key] / $totalStance) * 100 }}%"></div>
                                @endif
                            @endforeach
                        </div>
                        <ul class="mt-4 space-y-2">
                            @foreach (['bullish', 'contrarian', 'bearish', 'neutral', 'informational'] as $key)
                                @if (! empty($stances[$key] ?? null))
                                    <li class="flex items-center justify-between text-xs">
                                        <span class="flex items-center gap-2">
                                            <span class="h-2 w-2 rounded-full {{ $stanceCls[$key]['bg'] }}"></span>
                                            <span class="text-slate-700">{{ ucfirst($key) }}</span>
                                        </span>
                                        <span class="font-medium text-slate-900 tabular">{{ $stances[$key] }}</span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </section>
                @endif

                {{-- Track record (verified predictions) --}}
                @if ($claims->isNotEmpty())
                    <section class="rounded-2xl border border-slate-200 bg-white p-6">
                        <h3 class="text-sm font-semibold text-slate-900 mb-1">Track record</h3>
                        <p class="text-xs text-slate-500 mb-4">Predictions PrComet has verified.</p>
                        <ul class="space-y-3">
                            @foreach ($claims as $claim)
                                @php $v = $verifiedCls[$claim->verified_outcome] ?? null; @endphp
                                <li class="pl-3 border-l-2 {{ $v ? str_replace(['bg-', '50'], ['border-', '500'], $v['cls']) : 'border-slate-200' }}">
                                    @if ($v)
                                        <div class="flex items-center gap-1.5 mb-1">
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium border {{ $v['cls'] }}">
                                                <span class="h-1 w-1 rounded-full {{ $v['dot'] }}"></span>{{ $v['label'] }}
                                            </span>
                                            @if ($claim->timeframe)
                                                <span class="text-[10px] text-slate-400">{{ $claim->timeframe }}</span>
                                            @endif
                                        </div>
                                    @endif
                                    <p class="text-xs text-slate-700 italic">&ldquo;{{ \Illuminate\Support\Str::limit($claim->claim_text, 140) }}&rdquo;</p>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                {{-- Your team's matches with this author --}}
                @if ($teamMatches->isNotEmpty())
                    <section class="rounded-2xl border border-slate-200 bg-white p-6">
                        <h3 class="text-sm font-semibold text-slate-900 mb-1">Your matches</h3>
                        <p class="text-xs text-slate-500 mb-4">{{ $teamMatches->count() }} {{ \Illuminate\Support\Str::plural('match', $teamMatches->count()) }} surfaced for your companies.</p>
                        <ul class="space-y-3">
                            @foreach ($teamMatches as $m)
                                <li>
                                    <a href="{{ route('matches.show', $m) }}" wire:navigate
                                       class="block hover:bg-slate-50 -mx-2 px-2 py-1.5 rounded transition-colors">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-xs text-slate-500 truncate">{{ $m->company->name }}</span>
                                            <span class="text-xs font-semibold tabular {{ $m->score >= 0.8 ? 'text-emerald-700' : 'text-brand-700' }}">{{ number_format($m->score * 100) }}%</span>
                                        </div>
                                        <div class="text-xs text-slate-700 line-clamp-1 mt-0.5">{{ $m->publicationItem->title }}</div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </div>
        </div>
    </div>
</div>
