<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<x-articles.head
    :title="$topic . ' · The Angle'"
    :description="$topic . ' articles from The Angle, PrComet\'s field guide to PR discovery.'"
    :canonical="route('articles.topic', $topic)"
/>
<body class="font-sans antialiased text-slate-900 bg-white">

    <x-site-nav />

    {{-- ── Topic hero (compact match of the main hero) ── --}}
    <section class="relative overflow-hidden bg-slate-900 text-white">
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute -top-20 right-1/4 h-[320px] w-[320px] rounded-full bg-gradient-to-br from-indigo-500/25 to-fuchsia-500/15 blur-3xl"></div>
        </div>

        <div class="relative max-w-5xl mx-auto px-6 py-14 lg:py-16">
            <nav class="text-sm text-slate-400" aria-label="Breadcrumb">
                <a href="{{ route('articles.index') }}" class="hover:text-white transition-colors">The Angle</a>
                <span class="mx-1">/</span>
                <span class="text-slate-200">{{ $topic }}</span>
            </nav>
            <h1 class="mt-3 text-4xl lg:text-5xl font-semibold tracking-tight">{{ $topic }}</h1>
        </div>

        <div class="relative h-px bg-gradient-to-r from-transparent via-violet-500/50 to-transparent"></div>
    </section>

    <main class="max-w-5xl mx-auto px-6 py-14">
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
                            <h2 class="text-lg font-semibold leading-snug group-hover:text-indigo-700">{{ $article->title }}</h2>
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
    </main>

    <x-site-footer />
</body>
</html>
