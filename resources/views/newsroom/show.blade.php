<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $company->name }} · Newsroom</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit($company->description_md ?: 'Newsroom for '.$company->name, 160) }}">

    {{-- Open Graph for nice link previews when shared --}}
    <meta property="og:title" content="{{ $company->name }} · Newsroom">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit($company->description_md ?: 'News and materials from '.$company->name, 200) }}">
    @if ($company->newsroomHeaderImageUrl())
        <meta property="og:image" content="{{ $company->newsroomHeaderImageUrl() }}">
    @endif
    <meta property="og:type" content="website">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=geist:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Same accent system as the one-pager renderer. A journalist who
         lands on Aurelian's story page then bookmarks their newsroom
         sees the same brand both times — that's the point. --}}
    @php $accent = $company->accent_color ?: '#4339DC'; @endphp
    <style>
        :root { --accent: {{ $accent }}; }
        .accent-text { color: var(--accent); }
        .accent-bg { background-color: var(--accent); }
        .accent-border { border-color: var(--accent); }
        .accent-fade-bg { background: linear-gradient(135deg, color-mix(in srgb, var(--accent) 8%, white), color-mix(in srgb, var(--accent) 3%, white)); }
        .accent-divider { background: linear-gradient(90deg, transparent, color-mix(in srgb, var(--accent) 50%, transparent), transparent); }
    </style>
    <x-tracking />
</head>
<body class="font-sans antialiased text-slate-900 bg-white">

    {{-- ── Header band ──
         Mirrors the one-pager header exactly: bg-slate-900 base, header
         image at opacity-40 + slate gradient overlay, unboxed logo with
         drop-shadow. Falls back to accent-fade-bg when no header image
         is set. --}}
    <header class="relative overflow-hidden bg-slate-900 text-white">
        @if ($company->newsroomHeaderImageUrl())
            <div class="absolute inset-0">
                <img src="{{ $company->newsroomHeaderImageUrl() }}" alt="" class="w-full h-full object-cover opacity-40" />
                <div class="absolute inset-0 bg-gradient-to-b from-slate-900/40 via-slate-900/60 to-slate-900/90"></div>
            </div>
        @else
            <div class="absolute inset-0 accent-fade-bg opacity-50"></div>
        @endif

        <div class="relative max-w-4xl mx-auto px-6 lg:px-10 py-16 lg:py-24">
            @if ($company->logo_path)
                <img src="{{ $company->logoUrl() }}" alt="{{ $company->name }}" class="h-12 lg:h-16 mb-6 object-contain drop-shadow-lg" />
            @endif
            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-white/70 mb-2">Newsroom</div>
            <h1 class="text-4xl lg:text-5xl font-semibold tracking-tight">{{ $company->name }}</h1>
            @if ($company->tagline)
                <p class="text-lg lg:text-xl text-white/85 mt-3 max-w-2xl">{{ $company->tagline }}</p>
            @endif
            <div class="mt-6 flex items-center flex-wrap gap-3 text-sm">
                @if ($company->ticker)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md bg-white/15 backdrop-blur-sm font-mono text-xs">
                        {{ $company->ticker }}{{ $company->exchange ? '.'.$company->exchange : '' }}
                    </span>
                @endif
                @if ($company->website)
                    <a href="{{ $company->website }}" target="_blank" rel="noopener" class="text-white/90 hover:text-white underline-offset-2 hover:underline">{{ parse_url($company->website, PHP_URL_HOST) }}</a>
                @endif
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-6 lg:px-10 py-12 lg:py-16 space-y-12 lg:space-y-16">

        {{-- ── About ── --}}
        @if ($company->description_md)
            <section>
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">About {{ $company->name }}</div>
                <div class="prose prose-slate max-w-none text-slate-800 leading-relaxed whitespace-pre-wrap">{{ $company->description_md }}</div>
            </section>
        @endif

        {{-- ── Stories (published one-pagers) ── --}}
        <section>
            <div class="flex items-end justify-between mb-6">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Published materials</div>
                    <h2 class="text-2xl font-semibold text-slate-900 tracking-tight">Stories</h2>
                </div>
                <div class="text-xs text-slate-400 tabular">
                    {{ $onePagers->count() }} {{ \Illuminate\Support\Str::plural('story', $onePagers->count()) }}
                </div>
            </div>

            @if ($onePagers->isEmpty())
                <div class="rounded-2xl border-2 border-dashed border-slate-200 p-12 text-center">
                    <div class="text-base font-semibold text-slate-900">Nothing published yet.</div>
                    <p class="text-sm text-slate-500 mt-1">Check back soon — or subscribe below to be notified.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($onePagers as $op)
                        @php
                            $title = $op->displayTitle();
                            $excerpt = $op->note_md ? \Illuminate\Support\Str::limit(strip_tags($op->note_md), 220) : null;
                        @endphp
                        <a href="{{ $op->publicUrl() }}"
                           class="block group rounded-xl border border-slate-200 bg-white hover:accent-border hover:shadow-sm transition-all p-5">
                            <div class="flex items-start gap-4">
                                <div class="grid place-items-center h-10 w-10 rounded-lg accent-bg text-white shrink-0">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1 text-[11px] text-slate-500">
                                        @if ($op->published_at)
                                            <time>{{ $op->published_at->format('M j, Y') }}</time>
                                        @endif
                                        @if ($op->match?->publicationItem?->source?->name)
                                            <span class="text-slate-300">·</span>
                                            <span>Briefed for {{ $op->match->publicationItem->source->name }}</span>
                                        @endif
                                    </div>
                                    <h3 class="text-base font-semibold text-slate-900 leading-snug group-hover:accent-text transition-colors">{{ $title }}</h3>
                                    @if ($excerpt)
                                        <p class="text-sm text-slate-600 leading-relaxed mt-1.5 line-clamp-2">{{ $excerpt }}</p>
                                    @endif
                                </div>
                                <svg class="h-4 w-4 text-slate-300 group-hover:translate-x-0.5 group-hover:text-slate-500 mt-1 shrink-0 transition-all" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ── Subscribe ──
             Uses the accent-fade-bg pattern so it reads as the same
             system as the "A note from us" card on the one-pager. --}}
        <section class="accent-fade-bg rounded-2xl p-6 lg:p-8 border border-slate-200">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 lg:items-center">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider accent-text mb-2">Get updates</div>
                    <h2 class="text-xl lg:text-2xl font-semibold text-slate-900 tracking-tight">
                        Be first to know when {{ $company->name }} publishes.
                    </h2>
                    <p class="text-sm text-slate-600 mt-2 leading-relaxed">
                        We'll email you when there's something new here — drill results, financings, partnerships. No spam, never sold.
                    </p>
                </div>
                <div>
                    @livewire('newsroom.subscribe-form', ['company' => $company])
                </div>
            </div>
        </section>

        {{-- ── Press contact ──
             Same shape as the one-pager's press contact section so a
             journalist sees identical interaction patterns. --}}
        @if ($company->press_contact_email || $company->ir_contact_email || $company->social_links)
            <section class="border-t border-slate-200 pt-8">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-4">Press contact</div>
                <div class="flex flex-col md:flex-row md:items-center gap-6 text-sm">
                    @php $email = $company->press_contact_email ?: $company->ir_contact_email; @endphp
                    @if ($email)
                        <a href="mailto:{{ $email }}" class="inline-flex items-center gap-2 accent-text font-medium hover:underline">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            {{ $email }}
                        </a>
                    @endif
                    @if (! empty($company->social_links['twitter']))
                        <a href="{{ $company->social_links['twitter'] }}" target="_blank" rel="noopener" class="text-slate-600 hover:text-slate-900">X / Twitter</a>
                    @endif
                    @if (! empty($company->social_links['linkedin']))
                        <a href="{{ $company->social_links['linkedin'] }}" target="_blank" rel="noopener" class="text-slate-600 hover:text-slate-900">LinkedIn</a>
                    @endif
                </div>
            </section>
        @endif
    </main>

    {{-- ── Footer ── --}}
    <footer class="border-t border-slate-200 mt-12 py-6">
        <div class="max-w-4xl mx-auto px-6 lg:px-10 flex items-center justify-between text-xs text-slate-400">
            <span>© {{ date('Y') }} {{ $company->name }}</span>
            <a href="/" class="inline-flex items-center gap-1.5 hover:text-slate-600 transition-colors">
                Page made with
                <span class="inline-flex items-center gap-1">
                    <span class="h-2.5 w-2.5 rounded-full bg-slate-900"></span>
                    <span class="font-medium text-slate-500">PrComet</span>
                </span>
            </a>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
