<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $company->name }} — Newsroom</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit($company->description_md ?: 'Newsroom for '.$company->name, 160) }}">

    {{-- OG tags so it previews well in Slack / email --}}
    <meta property="og:title" content="{{ $company->name }} — Newsroom">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit($company->description_md ?: 'News and materials from '.$company->name, 200) }}">
    @if ($company->headerImageUrl())
        <meta property="og:image" content="{{ $company->headerImageUrl() }}">
    @endif
    <meta property="og:type" content="website">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=geist:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Inject the company's accent colour as a CSS variable so accent
         buttons, focus rings, and the subscribe form pick it up
         without per-company class soup. --}}
    <style>
        :root { --newsroom-accent: {{ $company->accent_color ?: '#4339DC' }}; }
    </style>
</head>

<body class="font-sans antialiased text-slate-900 bg-white">

    {{-- ─────────────────────────────────────────────────────────
          HEADER BAND
       ──────────────────────────────────────────────────────── --}}
    <header class="relative overflow-hidden">
        @if ($company->headerImageUrl())
            <div class="absolute inset-0">
                <img src="{{ $company->headerImageUrl() }}" alt="" class="w-full h-full object-cover" />
                <div class="absolute inset-0" style="background: linear-gradient(135deg, color-mix(in srgb, var(--newsroom-accent, #4339DC) 75%, #0f172a), color-mix(in srgb, var(--newsroom-accent, #4339DC) 40%, #0f172a) 100%); opacity: 0.85;"></div>
            </div>
        @else
            <div class="absolute inset-0" style="background: linear-gradient(135deg, var(--newsroom-accent, #4339DC), color-mix(in srgb, var(--newsroom-accent, #4339DC) 60%, #1e293b));"></div>
        @endif

        <div class="relative max-w-5xl mx-auto px-6 lg:px-10 py-16 lg:py-24 text-white">
            <div class="flex items-center gap-4 mb-6">
                @if ($company->logoUrl())
                    <div class="grid place-items-center h-14 w-14 rounded-xl bg-white shadow-lg p-2 shrink-0">
                        <img src="{{ $company->logoUrl() }}" alt="{{ $company->name }}" class="w-full h-full object-contain" />
                    </div>
                @endif
                <div>
                    <div class="text-[11px] uppercase tracking-[0.2em] font-semibold opacity-80">Newsroom</div>
                    <h1 class="text-3xl lg:text-4xl font-semibold tracking-tight">{{ $company->name }}</h1>
                </div>
            </div>

            @if ($company->tagline)
                <p class="text-lg lg:text-xl max-w-2xl leading-relaxed opacity-95">{{ $company->tagline }}</p>
            @endif

            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 mt-8 text-sm opacity-90">
                @if ($company->ticker)
                    <span class="inline-flex items-center gap-1.5">
                        <span class="font-mono font-semibold">{{ $company->ticker }}</span>
                        @if ($company->exchange) <span class="opacity-70">· {{ $company->exchange }}</span> @endif
                    </span>
                @endif
                @if ($company->website)
                    <a href="{{ $company->website }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 hover:underline">
                        Website ↗
                    </a>
                @endif
                @if ($company->press_contact_email)
                    <a href="mailto:{{ $company->press_contact_email }}" class="inline-flex items-center gap-1 hover:underline">
                        Press contact
                    </a>
                @endif
            </div>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-6 lg:px-10 py-12 lg:py-16 space-y-14">

        {{-- ── About ───────────────────────────────────────────── --}}
        @if ($company->description_md)
            <section>
                <div class="text-[11px] uppercase tracking-[0.2em] font-semibold text-slate-500 mb-3">About</div>
                <div class="prose prose-slate max-w-none text-base leading-relaxed text-slate-700">
                    {!! nl2br(e($company->description_md)) !!}
                </div>
            </section>
        @endif

        {{-- ── One-pagers ─────────────────────────────────────── --}}
        <section>
            <div class="flex items-end justify-between mb-6">
                <div>
                    <div class="text-[11px] uppercase tracking-[0.2em] font-semibold text-slate-500">Stories</div>
                    <h2 class="text-2xl font-semibold text-slate-900 tracking-tight mt-1">Published materials</h2>
                </div>
                <div class="text-xs text-slate-500 tabular">
                    {{ $onePagers->count() }} {{ \Illuminate\Support\Str::plural('story', $onePagers->count()) }}
                </div>
            </div>

            @if ($onePagers->isEmpty())
                <div class="rounded-2xl border-2 border-dashed border-slate-200 p-12 text-center">
                    <div class="text-base font-semibold text-slate-900">Nothing published yet.</div>
                    <p class="text-sm text-slate-500 mt-1">Check back soon, or subscribe below to be notified.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($onePagers as $op)
                        @php
                            $title = $op->displayTitle();
                            $excerpt = $op->note_md ? \Illuminate\Support\Str::limit(strip_tags($op->note_md), 220) : null;
                        @endphp
                        <a href="{{ $op->publicUrl() }}"
                           class="group flex items-start gap-4 rounded-xl border border-slate-200 bg-white p-5 hover:shadow-md transition-all"
                           style="border-color: rgb(226 232 240);"
                           onmouseover="this.style.borderColor='var(--newsroom-accent, #4339DC)'"
                           onmouseout="this.style.borderColor='rgb(226 232 240)'">
                            <div class="grid place-items-center h-10 w-10 rounded-lg text-white shrink-0" style="background: var(--newsroom-accent, #4339DC);">
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
                                <h3 class="text-base font-semibold text-slate-900 leading-snug group-hover:underline">{{ $title }}</h3>
                                @if ($excerpt)
                                    <p class="text-sm text-slate-600 leading-relaxed mt-1.5 line-clamp-2">{{ $excerpt }}</p>
                                @endif
                            </div>
                            <svg class="h-5 w-5 text-slate-300 group-hover:text-slate-500 mt-1 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ── Subscribe ──────────────────────────────────────── --}}
        <section class="rounded-2xl p-7 lg:p-10" style="background: color-mix(in srgb, var(--newsroom-accent, #4339DC) 5%, white); border: 1px solid color-mix(in srgb, var(--newsroom-accent, #4339DC) 15%, white);">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:items-center">
                <div>
                    <div class="text-[11px] uppercase tracking-[0.2em] font-semibold mb-2" style="color: var(--newsroom-accent, #4339DC);">Get updates</div>
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

        {{-- ── Press contact ──────────────────────────────────── --}}
        @if ($company->press_contact_email || $company->ir_contact_name)
            <section>
                <div class="text-[11px] uppercase tracking-[0.2em] font-semibold text-slate-500 mb-3">Press contact</div>
                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <div class="flex items-center gap-3">
                        @if ($company->ir_contact_name)
                            <div class="grid place-items-center h-10 w-10 rounded-full text-white font-semibold text-sm shrink-0" style="background: var(--newsroom-accent, #4339DC);">
                                {{ strtoupper(substr($company->ir_contact_name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-slate-900">{{ $company->ir_contact_name }}</div>
                                @if ($company->press_contact_email)
                                    <a href="mailto:{{ $company->press_contact_email }}" class="text-xs text-slate-500 hover:underline" style="color: var(--newsroom-accent, #4339DC);">{{ $company->press_contact_email }}</a>
                                @endif
                            </div>
                        @else
                            <a href="mailto:{{ $company->press_contact_email }}" class="text-sm font-medium" style="color: var(--newsroom-accent, #4339DC);">
                                {{ $company->press_contact_email }}
                            </a>
                        @endif
                    </div>
                </div>
            </section>
        @endif

    </main>

    <footer class="border-t border-slate-200 py-8 text-center">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-slate-600">
            <span class="h-3 w-3 rounded-full bg-slate-900"></span>
            <span>Newsroom by <span class="font-medium">PrComet</span></span>
        </a>
    </footer>

    @livewireScripts
</body>
</html>
