<div>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4 w-full min-w-0">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('matches.index') }}" wire:navigate class="flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-900 transition-colors shrink-0">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5M11 18l-6-6 6-6" /></svg>
                    Matches
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-medium text-slate-700 truncate">Brief</span>
            </div>

            <a href="{{ $match->publicationItem->url }}" target="_blank" rel="noopener"
               class="btn-secondary text-xs">
                Open original article
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 4H4v16h16v-6M14 4h6v6M10 14L20 4" /></svg>
            </a>
        </div>
    </x-slot>

    @php
        $score = (float) $match->score;

        // Same gradient scale as the dashboard hero — keeps the visual logic
        // consistent across the app: high scores feel hot, mid feels cool.
        $gradient = $score >= 0.80
            ? 'from-indigo-600 via-violet-600 to-fuchsia-600'
            : ($score >= 0.65 ? 'from-indigo-500 via-indigo-600 to-violet-700' : 'from-slate-700 to-slate-900');

        $statusMeta = [
            'new'       => ['label' => 'New',         'dot' => 'bg-amber-400',    'fg' => 'text-amber-700',    'bg' => 'bg-amber-50',    'border' => 'border-amber-200'],
            'saved'     => ['label' => 'Saved',       'dot' => 'bg-blue-500',     'fg' => 'text-blue-700',     'bg' => 'bg-blue-50',     'border' => 'border-blue-200'],
            'contacted' => ['label' => 'In outreach', 'dot' => 'bg-indigo-500',   'fg' => 'text-indigo-700',   'bg' => 'bg-indigo-50',   'border' => 'border-indigo-200'],
            'placed'    => ['label' => 'Placed',      'dot' => 'bg-emerald-500',  'fg' => 'text-emerald-700',  'bg' => 'bg-emerald-50',  'border' => 'border-emerald-200'],
            'dismissed' => ['label' => 'Dismissed',   'dot' => 'bg-slate-400',    'fg' => 'text-slate-600',    'bg' => 'bg-slate-100',   'border' => 'border-slate-200'],
        ];
        $current = $statusMeta[$match->status] ?? $statusMeta['new'];

        // Suggested next action contextual to current status.
        $nextStep = match ($match->status) {
            'new'       => ['label' => 'Save for outreach', 'target' => 'saved',     'icon' => 'M5 5h14v14l-7-4-7 4V5z'],
            'saved'     => ['label' => 'Mark as contacted', 'target' => 'contacted', 'icon' => 'M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z'],
            'contacted' => ['label' => 'Attach placement',  'target' => null,        'icon' => 'M8 21h8M12 17v4M7 4h10v5a5 5 0 01-10 0V4z'], // scrolls to form
            'placed'    => null,
            'dismissed' => ['label' => 'Restore',           'target' => 'new',       'icon' => 'M3 12a9 9 0 0115.7-6.3L21 8M21 3v5h-5M21 12a9 9 0 01-15.7 6.3L3 16M3 21v-5h5'],
            default     => null,
        };

        $eventIcons = [
            'saved'     => 'M5 5h14v14l-7-4-7 4V5z',
            'contacted' => 'M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z',
            'placed'    => 'M8 21h8M12 17v4M7 4h10v5a5 5 0 01-10 0V4z',
            'dismissed' => 'M6 6l12 12M18 6L6 18',
            'note'      => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2 2 0 113 3L12 15l-4 1 1-4 9.5-9.5z',
        ];
        $cited = $this->citedItems;
    @endphp

    <div class="space-y-6 animate-fade-in">

        {{-- Flash messages --}}
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 flex items-center gap-2">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
        @endif

        {{-- ─────────────────────────────────────────────────────────────
              PLACEMENT WIN BANNER (only when placed)
          ───────────────────────────────────────────────────────────── --}}
        @if ($match->status === 'placed' && $match->placement_url)
            <section class="relative overflow-hidden rounded-2xl text-white bg-gradient-to-br from-emerald-600 via-emerald-500 to-teal-500 shadow-lg">
                <div class="absolute -top-16 -right-16 h-48 w-48 rounded-full bg-white/10 blur-3xl"></div>
                <div class="relative p-6 flex items-start gap-4">
                    <div class="grid place-items-center h-10 w-10 rounded-xl bg-white/15 backdrop-blur-sm shrink-0">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 01-10 0V4z" /></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-medium uppercase tracking-wider text-white/80">Placement attributed</div>
                        @if ($match->placement_title)
                            <div class="text-base font-semibold leading-snug mt-0.5">{{ $match->placement_title }}</div>
                        @endif
                        <a href="{{ $match->placement_url }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 mt-2 text-sm text-white/90 hover:text-white underline underline-offset-2 break-all">
                            {{ \Illuminate\Support\Str::limit($match->placement_url, 70) }}
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 4H4v16h16v-6M14 4h6v6M10 14L20 4" /></svg>
                        </a>
                        @if ($match->placement_published_at)
                            <div class="text-xs text-white/70 mt-1">Published {{ $match->placement_published_at->toFormattedDateString() }}</div>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        {{-- ─────────────────────────────────────────────────────────────
              HERO BAND — score-driven gradient + reasoning preview
          ───────────────────────────────────────────────────────────── --}}
        <section class="relative overflow-hidden rounded-2xl shadow-xl text-white bg-gradient-to-br {{ $gradient }}">
            <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 h-72 w-72 rounded-full bg-fuchsia-400/20 blur-3xl"></div>
            <div class="absolute inset-0 opacity-[0.04] mix-blend-overlay"
                 style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%270 0 200 200%27 xmlns=%27http://www.w3.org/2000/svg%27%3E%3Cfilter id=%27n%27%3E%3CfeTurbulence type=%27fractalNoise%27 baseFrequency=%270.9%27 numOctaves=%272%27 stitchTiles=%27stitch%27/%3E%3C/filter%3E%3Crect width=%27100%25%27 height=%27100%25%27 filter=%27url(%23n)%27/%3E%3C/svg%3E');"></div>

            <div class="relative p-8 lg:p-10 grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <div class="flex items-center flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/15 backdrop-blur-sm text-xs font-medium">
                            <span class="h-1.5 w-1.5 rounded-full {{ $current['dot'] }}"></span>
                            {{ $current['label'] }}
                        </span>
                        <span class="text-white/60 text-xs">·</span>
                        <a href="{{ route('companies.show', $match->company) }}" wire:navigate class="text-xs text-white/80 hover:text-white">
                            For {{ $match->company->name }}
                        </a>
                        <span class="text-white/60 text-xs">·</span>
                        <span class="text-xs text-white/70">Surfaced {{ $match->created_at->diffForHumans() }}</span>
                    </div>

                    <div>
                        <div class="flex items-center gap-3 mb-3">
                            <div class="grid place-items-center h-10 w-10 rounded-lg bg-white/15 backdrop-blur-sm font-semibold text-sm">
                                {{ strtoupper(substr($match->publicationItem->source->name, 0, 2)) }}
                            </div>
                            <div class="text-sm">
                                <div class="font-medium">{{ $match->author?->name ?? $match->publicationItem->source->name }}</div>
                                <div class="text-white/70 text-xs">
                                    {{ $match->publicationItem->source->name }}
                                    @if ($match->publicationItem->published_at) · published {{ $match->publicationItem->published_at->diffForHumans(short: true) }} @endif
                                </div>
                            </div>
                        </div>
                        <h1 class="text-2xl lg:text-3xl font-semibold leading-tight tracking-tight">{{ $match->publicationItem->title }}</h1>
                    </div>

                    {{-- Suggested next-step CTA --}}
                    @if ($nextStep)
                        <div class="flex items-center gap-3 flex-wrap pt-1">
                            @if ($nextStep['target'])
                                <button wire:click="updateStatus('{{ $nextStep['target'] }}')"
                                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-white text-slate-900 font-medium text-sm hover:bg-slate-100 transition-colors shadow-lg">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="{{ $nextStep['icon'] }}" /></svg>
                                    {{ $nextStep['label'] }}
                                </button>
                            @else
                                <a href="#placement" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-white text-slate-900 font-medium text-sm hover:bg-slate-100 transition-colors shadow-lg">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="{{ $nextStep['icon'] }}" /></svg>
                                    {{ $nextStep['label'] }}
                                </a>
                            @endif
                            @if ($match->status !== 'dismissed')
                                <button wire:click="updateStatus('dismissed')" class="text-sm text-white/70 hover:text-white transition-colors px-3 py-2">
                                    Not a fit
                                </button>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Score gauge --}}
                <div class="flex lg:justify-end">
                    <div class="relative h-40 w-40">
                        @php
                            $circumference = 2 * pi() * 70;
                            $offset = $circumference * (1 - $score);
                        @endphp
                        <svg viewBox="0 0 160 160" class="h-full w-full -rotate-90">
                            <circle cx="80" cy="80" r="70" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="10" />
                            <circle cx="80" cy="80" r="70" fill="none" stroke="white" stroke-width="10" stroke-linecap="round"
                                    stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}"
                                    style="transition: stroke-dashoffset 800ms cubic-bezier(0.22, 1, 0.36, 1);" />
                        </svg>
                        <div class="absolute inset-0 grid place-items-center text-center">
                            <div>
                                <div class="text-4xl font-bold tabular leading-none">{{ number_format($score * 100) }}<span class="text-xl font-medium text-white/70">%</span></div>
                                <div class="mt-1.5 text-[10px] uppercase tracking-wider text-white/70 font-medium">Match confidence</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ─────────────────────────────────────────────────────────────
              TRIGGER REFERENCE (what press release surfaced this match)
          ───────────────────────────────────────────────────────────── --}}
        <div class="flex items-center gap-3 text-xs text-slate-500 px-1">
            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7-7-7M12 21V3" /></svg>
            <span>Surfaced by your release:</span>
            <a href="{{ route('companies.show', $match->company) }}" wire:navigate class="font-medium text-slate-700 hover:text-brand-700 truncate max-w-md">
                {{ $match->pressRelease->title }}
            </a>
            <span class="text-slate-400">· {{ optional($match->pressRelease->published_at)->diffForHumans(short: true) }}</span>
        </div>

        {{-- ─────────────────────────────────────────────────────────────
              MAIN GRID — brief content (2/3) + sidebar (1/3)
          ───────────────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- LEFT: the brief --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Rationale + suggested angle in one card with clear sections --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-6 lg:p-8 space-y-7">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="h-4 w-4 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" /></svg>
                            <h2 class="text-sm font-semibold text-slate-900 uppercase tracking-wide">Why they're a fit</h2>
                        </div>
                        <div class="text-[15px] leading-relaxed text-slate-700 whitespace-pre-wrap">{{ $match->rationale_md }}</div>
                    </div>

                    <div class="relative rounded-xl bg-gradient-to-br from-indigo-50 via-violet-50 to-fuchsia-50 border border-indigo-100 p-5 overflow-hidden">
                        <div class="absolute -top-8 -right-8 h-32 w-32 rounded-full bg-gradient-to-br from-indigo-200 to-fuchsia-200 opacity-50 blur-2xl"></div>
                        <div class="relative">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" /></svg>
                                <h3 class="text-sm font-semibold text-indigo-900 uppercase tracking-wide">Suggested angle</h3>
                            </div>
                            <p class="text-[15px] leading-relaxed text-slate-800 whitespace-pre-wrap">{{ $match->suggested_angle_md }}</p>
                        </div>
                    </div>
                </section>

                {{-- Citations --}}
                @if (! empty($match->citations))
                    <section class="rounded-2xl border border-slate-200 bg-white p-6">
                        <h3 class="text-sm font-semibold text-slate-900 mb-4">Citations</h3>
                        <ul class="space-y-3">
                            @foreach ($match->citations as $cite)
                                @php
                                    $citedItem = $cited[$cite['publication_item_id'] ?? 0] ?? null;
                                @endphp
                                <li class="flex gap-3 pl-3 border-l-2 border-brand-300">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-slate-700 italic leading-relaxed">&ldquo;{{ $cite['quote'] ?? '' }}&rdquo;</p>
                                        @if ($citedItem)
                                            <a href="{{ $citedItem->url }}" target="_blank" rel="noopener"
                                               class="inline-flex items-center gap-1.5 mt-1.5 text-xs text-brand-700 hover:text-brand-800">
                                                <span class="font-medium">{{ $citedItem->title }}</span>
                                                <span class="text-slate-400">· {{ $citedItem->source->name }}</span>
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 4H4v16h16v-6M14 4h6v6M10 14L20 4" /></svg>
                                            </a>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                {{-- Source publication preview --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-slate-900">Source publication</h3>
                        <a href="{{ $match->publicationItem->url }}" target="_blank" rel="noopener" class="text-xs font-medium text-brand-700 hover:text-brand-800 inline-flex items-center gap-1">
                            Read article
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 4H4v16h16v-6M14 4h6v6M10 14L20 4" /></svg>
                        </a>
                    </div>
                    <div class="space-y-3">
                        <div class="text-sm font-medium text-slate-900 leading-snug">{{ $match->publicationItem->title }}</div>
                        @if ($match->publicationItem->body_text)
                            <p class="text-sm text-slate-600 leading-relaxed line-clamp-3">{{ \Illuminate\Support\Str::limit($match->publicationItem->body_text, 320) }}</p>
                        @endif
                        <div class="flex items-center flex-wrap gap-2 pt-1">
                            @foreach (($match->publicationItem->extracted_topics ?? []) as $topic)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-600">{{ $topic }}</span>
                            @endforeach
                            @if ($match->publicationItem->stance)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-900 text-white">{{ $match->publicationItem->stance }}</span>
                            @endif
                        </div>
                    </div>
                </section>
            </div>

            {{-- RIGHT: author + workflow + activity --}}
            <div class="space-y-6">

                {{-- Author profile --}}
                @if ($match->author)
                    <section class="rounded-2xl border border-slate-200 bg-white p-6">
                        <a href="{{ route('authors.show', $match->author) }}" wire:navigate class="flex items-start gap-3 mb-4 -m-2 p-2 rounded-lg hover:bg-slate-50 transition-colors group">
                            <div class="grid place-items-center h-11 w-11 rounded-full bg-gradient-to-br {{ $gradient }} text-white font-semibold text-sm shrink-0">
                                {{ strtoupper(substr($match->author->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-semibold text-slate-900 group-hover:text-brand-700 transition-colors flex items-center gap-1">
                                    {{ $match->author->name }}
                                    <svg class="h-3 w-3 text-slate-300 group-hover:text-brand-500 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                </div>
                                <div class="text-xs text-slate-500 truncate">{{ $match->publicationItem->source->name }}</div>
                                @if ($match->author->x_handle || $match->author->email)
                                    <div class="flex items-center gap-2 mt-1.5 text-xs">
                                        @if ($match->author->x_handle)
                                            <span class="text-slate-500 font-mono">{{ $match->author->x_handle }}</span>
                                        @endif
                                        @if ($match->author->email)
                                            <span class="text-brand-700">{{ $match->author->email }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </a>
                        @if ($match->author->body_of_work_summary)
                            <div class="pt-4 border-t border-slate-100">
                                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Body of work</div>
                                <p class="text-xs text-slate-600 leading-relaxed">{{ $match->author->body_of_work_summary }}</p>
                            </div>
                        @endif
                    </section>
                @endif

                {{-- Status workflow (full pipeline) --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Status</h3>
                    <div class="space-y-1.5">
                        @foreach ([
                            'new'       => 'New',
                            'saved'     => 'Saved',
                            'contacted' => 'In outreach',
                            'placed'    => 'Placed',
                            'dismissed' => 'Dismissed',
                        ] as $key => $label)
                            @php $m = $statusMeta[$key]; $isActive = $match->status === $key; @endphp
                            <button wire:click="updateStatus('{{ $key }}')"
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm transition-colors
                                           {{ $isActive ? $m['bg'].' '.$m['border'].' border '.$m['fg'].' font-medium' : 'text-slate-600 hover:bg-slate-50' }}">
                                <span class="flex items-center gap-2.5">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $m['dot'] }}"></span>
                                    {{ $label }}
                                </span>
                                @if ($isActive)
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </section>

                {{-- Placement attribution (only if not yet placed) --}}
                @if ($match->status !== 'placed')
                    <section id="placement" class="rounded-2xl border border-slate-200 bg-white p-6">
                        <h3 class="text-sm font-semibold text-slate-900 mb-1.5">Did you get a placement?</h3>
                        <p class="text-xs text-slate-500 mb-3 leading-relaxed">Paste the URL of the interview, article, or mention and we'll fetch the metadata + add it to your wins.</p>
                        <form wire:submit="attachPlacement" class="space-y-2">
                            <input type="url" wire:model="placementUrl" placeholder="https://…"
                                   class="block w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500" />
                            @error('placementUrl') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                            <button type="submit" class="btn-primary w-full justify-center">
                                Attach placement
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" /></svg>
                            </button>
                        </form>
                    </section>
                @endif

                {{-- Activity timeline --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Activity</h3>

                    <form wire:submit="addNote" class="mb-5 space-y-2">
                        <textarea wire:model="note" rows="3" placeholder="Add a note…"
                                  class="block w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500 resize-none"></textarea>
                        @error('note') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                        <div class="flex justify-end">
                            <button type="submit" class="btn-primary text-xs">Add note</button>
                        </div>
                    </form>

                    @if ($match->events->isEmpty())
                        <p class="text-xs text-slate-500 text-center py-3">No activity yet.</p>
                    @else
                        <ol class="relative space-y-5">
                            {{-- Vertical timeline rail --}}
                            <span class="absolute left-3 top-2 bottom-2 w-px bg-slate-200" aria-hidden="true"></span>

                            @foreach ($match->events->sortByDesc('created_at') as $event)
                                @php
                                    $em = $statusMeta[$event->event_type] ?? ['dot' => 'bg-slate-400', 'fg' => 'text-slate-600', 'bg' => 'bg-slate-100'];
                                    $isNote = $event->event_type === 'note';
                                    $icon = $eventIcons[$event->event_type] ?? null;
                                @endphp
                                <li class="relative flex gap-3 pl-0">
                                    <span class="relative z-10 grid place-items-center h-6 w-6 rounded-full {{ $isNote ? 'bg-slate-100 text-slate-500' : $em['bg'].' '.$em['fg'] }} border-2 border-white shadow-sm shrink-0">
                                        @if ($icon)
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="{{ $icon }}" /></svg>
                                        @endif
                                    </span>
                                    <div class="flex-1 min-w-0 pt-0.5">
                                        <div class="flex items-baseline justify-between gap-2">
                                            <div class="text-xs">
                                                <span class="font-medium text-slate-900">{{ $event->user?->name ?? 'System' }}</span>
                                                <span class="text-slate-500">
                                                    @if ($isNote)
                                                        added a note
                                                    @else
                                                        marked as {{ $statusMeta[$event->event_type]['label'] ?? $event->event_type }}
                                                    @endif
                                                </span>
                                            </div>
                                            <span class="text-[10px] text-slate-400 shrink-0">{{ $event->created_at->diffForHumans(short: true) }}</span>
                                        </div>
                                        @if ($event->notes_md)
                                            <div class="mt-1 text-xs text-slate-700 whitespace-pre-wrap bg-slate-50 rounded-md p-2.5 border border-slate-100">{{ $event->notes_md }}</div>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </section>
            </div>
        </div>
    </div>
</div>
