<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $onePager->company->name }} · Press</title>
    <meta name="description" content="{{ $onePager->company->tagline ?? 'Press materials' }}">

    {{-- Open Graph for nice link previews when shared --}}
    <meta property="og:title" content="{{ $onePager->company->name }} · Press">
    <meta property="og:description" content="{{ $onePager->company->tagline ?? 'Press materials' }}">
    @if ($onePager->company->header_image_path)
        <meta property="og:image" content="{{ $onePager->company->headerImageUrl() }}">
    @endif

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=geist:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $accent = $onePager->company->accent_color ?: '#4339DC';
    @endphp
    <style>
        :root { --accent: {{ $accent }}; }
        .accent-text { color: var(--accent); }
        .accent-bg { background-color: var(--accent); }
        .accent-border { border-color: var(--accent); }
        .accent-fade-bg { background: linear-gradient(135deg, color-mix(in srgb, var(--accent) 8%, white), color-mix(in srgb, var(--accent) 3%, white)); }
        .accent-divider { background: linear-gradient(90deg, transparent, color-mix(in srgb, var(--accent) 50%, transparent), transparent); }
    </style>
</head>
<body class="font-sans antialiased text-slate-900 bg-white">

    {{-- ── Header band ── --}}
    <header class="relative overflow-hidden bg-slate-900 text-white">
        @if ($onePager->company->header_image_path)
            <div class="absolute inset-0">
                <img src="{{ $onePager->company->headerImageUrl() }}" alt="" class="w-full h-full object-cover opacity-40" />
                <div class="absolute inset-0 bg-gradient-to-b from-slate-900/40 via-slate-900/60 to-slate-900/90"></div>
            </div>
        @else
            <div class="absolute inset-0 accent-fade-bg opacity-50"></div>
        @endif

        <div class="relative max-w-4xl mx-auto px-6 lg:px-10 py-16 lg:py-24">
            @if ($onePager->company->logo_path)
                <img src="{{ $onePager->company->logoUrl() }}" alt="{{ $onePager->company->name }}" class="h-12 lg:h-16 mb-6 object-contain drop-shadow-lg" />
            @endif
            <h1 class="text-4xl lg:text-5xl font-semibold tracking-tight">{{ $onePager->company->name }}</h1>
            @if ($onePager->company->tagline)
                <p class="text-lg lg:text-xl text-white/85 mt-3 max-w-2xl">{{ $onePager->company->tagline }}</p>
            @endif
            <div class="mt-6 flex items-center flex-wrap gap-3 text-sm">
                @if ($onePager->company->ticker)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md bg-white/15 backdrop-blur-sm font-mono text-xs">
                        {{ $onePager->company->ticker }}{{ $onePager->company->exchange ? '.'.$onePager->company->exchange : '' }}
                    </span>
                @endif
                @if ($onePager->company->website)
                    <a href="{{ $onePager->company->website }}" target="_blank" rel="noopener" class="text-white/90 hover:text-white underline-offset-2 hover:underline">{{ parse_url($onePager->company->website, PHP_URL_HOST) }}</a>
                @endif
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-6 lg:px-10 py-12 lg:py-16 space-y-12 lg:space-y-16">

        {{-- ── Personal note ── --}}
        @if ($onePager->note_md)
            <section class="accent-fade-bg rounded-2xl p-6 lg:p-8 border border-slate-200">
                <div class="text-xs font-semibold uppercase tracking-wider accent-text mb-3">A note from us</div>
                <div class="prose prose-slate max-w-none text-slate-800 leading-relaxed whitespace-pre-wrap">{{ $onePager->note_md }}</div>
            </section>
        @endif

        {{-- ── Trigger press release ── --}}
        <section class="bg-white border border-slate-200 rounded-2xl p-6 lg:p-8">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">The story</div>
            <a href="{{ $onePager->match->pressRelease->source_url }}" target="_blank" rel="noopener" class="block group">
                <h2 class="text-2xl lg:text-3xl font-semibold text-slate-900 group-hover:accent-text transition-colors leading-tight">
                    {{ $onePager->match->pressRelease->title }}
                </h2>
                <div class="mt-3 text-sm text-slate-500 inline-flex items-center gap-2">
                    <span>Published {{ optional($onePager->match->pressRelease->published_at)->toFormattedDateString() }}</span>
                    <span class="accent-text">→ Read the full release</span>
                </div>
            </a>
        </section>

        {{-- ── About the company ── --}}
        @if ($onePager->company->description_md)
            <section>
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">About {{ $onePager->company->name }}</div>
                <div class="prose prose-slate max-w-none text-slate-800 leading-relaxed whitespace-pre-wrap">{{ $onePager->company->description_md }}</div>
            </section>
        @endif

        {{-- ── Pull quotes ── --}}
        @php
            $quotes = $onePager->assets->where('type', 'quote');
            $images = $onePager->assets->whereIn('type', ['image', 'logo', 'header']);
            $pdfs = $onePager->assets->where('type', 'pdf');
            $links = $onePager->assets->where('type', 'link');
        @endphp

        @if ($quotes->isNotEmpty())
            <section>
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-6">Quotes</div>
                <div class="space-y-6">
                    @foreach ($quotes as $quote)
                        <blockquote class="relative pl-6 lg:pl-8 py-2 border-l-2 accent-border">
                            <p class="text-xl lg:text-2xl text-slate-900 leading-relaxed font-medium">
                                &ldquo;{{ $quote->quote_text }}&rdquo;
                            </p>
                            @if ($quote->quote_attribution)
                                <footer class="mt-3 text-sm text-slate-500">— {{ $quote->quote_attribution }}</footer>
                            @endif
                        </blockquote>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ── Images ── --}}
        @if ($images->isNotEmpty())
            <section>
                <div class="flex items-center justify-between mb-6">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Images</div>
                    <span class="text-xs text-slate-400">Right-click any image to download</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach ($images as $image)
                        <figure class="rounded-xl overflow-hidden border border-slate-200 bg-slate-50">
                            <img src="{{ $image->publicUrl() }}" alt="{{ $image->name }}" class="w-full h-auto" loading="lazy" />
                            @if ($image->description || $image->name)
                                <figcaption class="px-3 py-2 text-xs text-slate-600 bg-white border-t border-slate-200">
                                    {{ $image->description ?: $image->name }}
                                </figcaption>
                            @endif
                        </figure>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ── PDFs ── --}}
        @if ($pdfs->isNotEmpty())
            <section>
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-4">Documents</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ($pdfs as $pdf)
                        <a href="{{ $pdf->publicUrl() }}" target="_blank" rel="noopener"
                           class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 bg-white hover:border-slate-400 hover:shadow-sm transition-all group">
                            <div class="grid place-items-center h-10 w-10 rounded-lg bg-rose-50 text-rose-600 shrink-0">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-medium text-slate-900 truncate group-hover:accent-text transition-colors">{{ $pdf->name }}</div>
                                @if ($pdf->size_bytes)
                                    <div class="text-xs text-slate-500">{{ number_format($pdf->size_bytes / 1024 / 1024, 1) }} MB · PDF</div>
                                @endif
                            </div>
                            <svg class="h-4 w-4 text-slate-400 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 4H4v16h16v-6M14 4h6v6M10 14L20 4" /></svg>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ── Links ── --}}
        @if ($links->isNotEmpty())
            <section>
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-4">Further reading</div>
                <ul class="space-y-2">
                    @foreach ($links as $link)
                        <li>
                            <a href="{{ $link->url }}" target="_blank" rel="noopener" class="flex items-center justify-between gap-3 px-4 py-3 rounded-lg border border-slate-200 bg-white hover:border-slate-400 transition-colors group">
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-slate-900 group-hover:accent-text transition-colors">{{ $link->name }}</div>
                                    @if ($link->description)
                                        <div class="text-xs text-slate-500 mt-0.5">{{ $link->description }}</div>
                                    @endif
                                </div>
                                <svg class="h-4 w-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 4H4v16h16v-6M14 4h6v6M10 14L20 4" /></svg>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- ── Press contact ── --}}
        @if ($onePager->company->press_contact_email || $onePager->company->ir_contact_email || $onePager->company->social_links)
            <section class="border-t border-slate-200 pt-8">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-4">Press contact</div>
                <div class="flex flex-col md:flex-row md:items-center gap-6 text-sm">
                    @php $email = $onePager->company->press_contact_email ?: $onePager->company->ir_contact_email; @endphp
                    @if ($email)
                        <a href="mailto:{{ $email }}" class="inline-flex items-center gap-2 accent-text font-medium hover:underline">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            {{ $email }}
                        </a>
                    @endif
                    @if (! empty($onePager->company->social_links['twitter']))
                        <a href="{{ $onePager->company->social_links['twitter'] }}" target="_blank" rel="noopener" class="text-slate-600 hover:text-slate-900">X / Twitter</a>
                    @endif
                    @if (! empty($onePager->company->social_links['linkedin']))
                        <a href="{{ $onePager->company->social_links['linkedin'] }}" target="_blank" rel="noopener" class="text-slate-600 hover:text-slate-900">LinkedIn</a>
                    @endif
                </div>
            </section>
        @endif
    </main>

    {{-- ── Footer ── --}}
    <footer class="border-t border-slate-200 mt-12 py-6">
        <div class="max-w-4xl mx-auto px-6 lg:px-10 flex items-center justify-between text-xs text-slate-400">
            <span>© {{ date('Y') }} {{ $onePager->company->name }}</span>
            <a href="/" class="inline-flex items-center gap-1.5 hover:text-slate-600 transition-colors">
                Page made with
                <span class="inline-flex items-center gap-1">
                    <span class="h-2.5 w-2.5 rounded-full bg-slate-900"></span>
                    <span class="font-medium text-slate-500">PrComet</span>
                </span>
            </a>
        </div>
    </footer>
</body>
</html>
