@php
    $metaTitle = $article->meta_title ?: $article->title;
    $metaDescription = $article->metaDescription();
    $canonical = route('articles.show', $article);
    $ogImage = $article->og_image_path ? Storage::url($article->og_image_path) : null;
    $published = $article->published_at;

    $jsonLd = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $article->title,
        'description' => $metaDescription,
        'author' => [
            '@type' => 'Organization',
            'name' => $article->author_name ?: 'PrComet',
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'PrComet',
            'url' => route('home'),
        ],
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => $canonical,
        ],
        'datePublished' => $published?->toAtomString(),
        'dateModified' => $article->updated_at?->toAtomString(),
        'image' => $ogImage,
    ], fn ($v) => ! is_null($v));

    $breadcrumbLd = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => array_values(array_filter([
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'The Angle', 'item' => route('articles.index')],
            $article->category
                ? ['@type' => 'ListItem', 'position' => 2, 'name' => $article->category, 'item' => route('articles.topic', $article->category)]
                : null,
            ['@type' => 'ListItem', 'position' => $article->category ? 3 : 2, 'name' => $article->title, 'item' => $canonical],
        ])),
    ];

    $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<x-articles.head
    :title="$metaTitle . ' — PrComet'"
    :description="$metaDescription"
    :canonical="$canonical"
    :image="$ogImage"
    type="article"
>
    @if($published)
        <meta property="article:published_time" content="{{ $published->toAtomString() }}">
    @endif
    <meta property="article:modified_time" content="{{ $article->updated_at?->toAtomString() }}">
    @if($article->category)
        <meta property="article:section" content="{{ $article->category }}">
    @endif

    <script type="application/ld+json">{!! json_encode($jsonLd, $jsonFlags) !!}</script>
    <script type="application/ld+json">{!! json_encode($breadcrumbLd, $jsonFlags) !!}</script>
</x-articles.head>
<body class="font-sans antialiased text-slate-900 bg-white">

    <x-site-nav />

    <div class="max-w-5xl mx-auto px-6 py-10">
        {{-- Breadcrumb --}}
        <nav class="text-sm text-slate-400" aria-label="Breadcrumb">
            <a href="{{ route('articles.index') }}" class="hover:text-slate-700">The Angle</a>
            @if($article->category)
                <span class="mx-1">/</span>
                <a href="{{ route('articles.topic', $article->category) }}" class="hover:text-slate-700">{{ $article->category }}</a>
            @endif
        </nav>

        {{-- Title block --}}
        <header class="mt-4 max-w-3xl">
            @if($article->category)
                <span class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ $article->category }}</span>
            @endif
            <h1 class="mt-2 text-4xl font-bold tracking-tight leading-tight">{{ $article->title }}</h1>
            <div class="mt-4 flex flex-wrap items-center gap-2 text-sm text-slate-500">
                <span>{{ $article->author_name }}</span>
                @if($published)
                    <span>·</span>
                    <time datetime="{{ $published->toDateString() }}">{{ $published->format('F j, Y') }}</time>
                @endif
                <span>·</span>
                <span>{{ $article->readingTime() }} min read</span>
            </div>
        </header>

        @if($ogImage)
            <img src="{{ $ogImage }}" alt="" class="mt-8 aspect-[16/9] w-full rounded-2xl object-cover bg-slate-100">
        @endif

        <div class="mt-10 lg:grid lg:grid-cols-[16rem_1fr] lg:gap-12">
            {{-- On-page index, built from the H2/H3 tags in the body --}}
            @if(count($toc))
                <aside class="hidden lg:block">
                    <div class="sticky top-24">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">On this page</p>
                        <nav class="mt-3 border-l border-slate-200" data-toc>
                            <ul class="space-y-1 text-sm">
                                @foreach($toc as $item)
                                    <li>
                                        <a href="#{{ $item['slug'] }}"
                                           class="toc-link -ml-px block border-l-2 border-transparent py-1 pl-4 text-slate-600 transition-colors hover:border-indigo-500 hover:text-indigo-700">
                                            {{ $item['text'] }}
                                        </a>
                                        @if(! empty($item['children']))
                                            <ul class="space-y-1">
                                                @foreach($item['children'] as $child)
                                                    <li>
                                                        <a href="#{{ $child['slug'] }}"
                                                           class="toc-link -ml-px block border-l-2 border-transparent py-1 pl-8 text-slate-500 transition-colors hover:border-indigo-500 hover:text-indigo-700">
                                                            {{ $child['text'] }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </nav>
                    </div>
                </aside>
            @endif

            {{-- Article body --}}
            <article class="prose prose-slate max-w-none prose-headings:scroll-mt-24 prose-a:text-indigo-600">
                {!! $html !!}
            </article>
        </div>

        {{-- Related --}}
        @if($related->isNotEmpty())
            <div class="mt-16 border-t border-slate-100 pt-10">
                <h2 class="text-xl font-bold">Keep reading</h2>
                <div class="mt-6 grid gap-8 sm:grid-cols-3">
                    @foreach($related as $item)
                        <a href="{{ route('articles.show', $item) }}" class="group">
                            <h3 class="font-semibold leading-snug group-hover:text-indigo-700">{{ $item->title }}</h3>
                            @if($item->excerpt)
                                <p class="mt-2 text-sm text-slate-600 line-clamp-2">{{ $item->excerpt }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <x-articles.footer />

    {{-- Scrollspy: highlight the on-page index item for the section in view. --}}
    <script>
        (function () {
            const nav = document.querySelector('[data-toc]');
            if (! nav) return;

            const links = Array.from(nav.querySelectorAll('a[href^="#"]'));
            const map = new Map(); // heading element -> link, in document order
            links.forEach(function (link) {
                const id = decodeURIComponent(link.hash.slice(1));
                const target = id && document.getElementById(id);
                if (target) map.set(target, link);
            });
            if (! map.size) return;

            function setActive(active) {
                links.forEach(function (link) {
                    link.classList.toggle('toc-active', link === active);
                });
            }

            const visible = new Set();
            const observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) visible.add(entry.target);
                    else visible.delete(entry.target);
                });

                // The topmost heading currently in the trigger band wins.
                for (const [target, link] of map) {
                    if (visible.has(target)) { setActive(link); return; }
                }
            }, { rootMargin: '0px 0px -70% 0px', threshold: 0 });

            map.forEach(function (link, target) { observer.observe(target); });

            // Default to the first section until the observer reports a position.
            setActive(map.values().next().value);
        })();
    </script>
</body>
</html>
