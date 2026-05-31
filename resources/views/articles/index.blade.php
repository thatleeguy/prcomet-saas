<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<x-articles.head
    title="The Angle — PrComet"
    description="The Angle — guides, playbooks, and field notes on PR discovery from the PrComet team."
    :canonical="route('articles.index')"
/>
<body class="font-sans antialiased text-slate-900 bg-white">

    <x-site-nav />

    {{-- ── Hero ──────────────────────────────────────────────────────────
         Leans into "The Angle": a geometric angle mark, brand gradient on
         the wordmark, and the same glow vocabulary as the marketing site. --}}
    <section class="relative overflow-hidden bg-slate-900 text-white">
        {{-- Soft gradient glow --}}
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute -top-24 right-0 h-[420px] w-[420px] rounded-full bg-gradient-to-br from-indigo-500/30 to-fuchsia-500/20 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/4 h-[360px] w-[360px] rounded-full bg-gradient-to-tr from-violet-500/20 to-blue-500/20 blur-3xl"></div>
        </div>

        {{-- The angle motif: two rays meeting at a vertex, with a sweep arc --}}
        <svg class="absolute -right-8 top-1/2 hidden -translate-y-1/2 lg:block" width="360" height="360"
             viewBox="0 0 200 200" fill="none" aria-hidden="true">
            <defs>
                <linearGradient id="angleGrad" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="#818cf8" stop-opacity="0.55" />
                    <stop offset="100%" stop-color="#e879f9" stop-opacity="0.12" />
                </linearGradient>
            </defs>
            <path d="M190 18 L20 100 L190 182" stroke="url(#angleGrad)" stroke-width="3"
                  stroke-linecap="round" stroke-linejoin="round" />
            <path d="M80 62 A46 46 0 0 1 80 138" stroke="url(#angleGrad)" stroke-width="3" stroke-linecap="round" />
            <circle cx="20" cy="100" r="4.5" fill="#a78bfa" />
        </svg>

        <div class="relative max-w-5xl mx-auto px-6 py-20 lg:py-28">
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-indigo-300">The PrComet field guide</p>

            <h1 class="mt-5 text-6xl lg:text-8xl font-semibold tracking-tight leading-none">
                The <span class="bg-gradient-to-r from-indigo-400 via-violet-400 to-fuchsia-400 bg-clip-text text-transparent">Angle</span>
            </h1>

            <p class="mt-6 max-w-xl text-lg text-slate-300 leading-relaxed">
                Guides, playbooks, and field notes on finding the journalists who'll actually publish your story.
            </p>

            @if($categories->isNotEmpty())
                <div class="mt-8 flex flex-wrap gap-2">
                    @foreach($categories as $category)
                        <a href="{{ route('articles.topic', $category) }}"
                           class="rounded-full border border-white/15 bg-white/5 px-3 py-1 text-sm text-slate-200 hover:border-white/30 hover:bg-white/10 transition-colors">
                            {{ $category }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Gradient hairline closing the band --}}
        <div class="relative h-px bg-gradient-to-r from-transparent via-violet-500/50 to-transparent"></div>
    </section>

    {{-- ── Article grid ── --}}
    <main class="max-w-5xl mx-auto px-6 py-14">
        @if($articles->isEmpty())
            <p class="text-slate-500">No articles published yet. Check back soon.</p>
        @else
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($articles as $article)
                    <article class="group flex flex-col">
                        <a href="{{ route('articles.show', $article) }}" class="flex flex-col h-full">
                            @if($article->og_image_path)
                                <img src="{{ Storage::url($article->og_image_path) }}" alt=""
                                     class="aspect-[16/9] w-full rounded-xl object-cover bg-slate-100">
                            @else
                                <div class="aspect-[16/9] w-full rounded-xl bg-gradient-to-br from-slate-100 to-slate-200"></div>
                            @endif

                            <div class="mt-4 flex-1">
                                @if($article->category)
                                    <span class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ $article->category }}</span>
                                @endif
                                <h2 class="mt-1 text-lg font-semibold leading-snug group-hover:text-indigo-700">
                                    {{ $article->title }}
                                </h2>
                                @if($article->excerpt)
                                    <p class="mt-2 text-sm text-slate-600 line-clamp-3">{{ $article->excerpt }}</p>
                                @endif
                            </div>

                            <div class="mt-4 flex items-center gap-2 text-xs text-slate-400">
                                @if($article->published_at)
                                    <time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('M j, Y') }}</time>
                                    <span>·</span>
                                @endif
                                <span>{{ $article->readingTime() }} min read</span>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        @endif
    </main>

    <x-articles.footer />
</body>
</html>
