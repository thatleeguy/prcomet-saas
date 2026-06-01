@php
    // Self-contained so the same nav works on the marketing homepage and the
    // standalone article pages.
    $showAngle = true;

    $navArticles = $showAngle
        ? \App\Models\Article::published()->latest('published_at')->take(4)->get(['slug', 'title', 'category', 'published_at'])
        : collect();

    $navCategories = $showAngle
        ? \App\Models\Article::published()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category')
        : collect();

    // Industry landing pages surfaced under "Who it's for".
    $navIndustries = [
        ['route' => 'industries.junior-mining', 'label' => 'Junior Mining', 'blurb' => 'Drill results & financings'],
        ['route' => 'industries.manufacturing', 'label' => 'Manufacturing', 'blurb' => 'Expansions & contracts'],
        ['route' => 'industries.tourism-councils', 'label' => 'Tourism Councils', 'blurb' => 'Festivals & destinations'],
        ['route' => 'industries.municipalities', 'label' => 'Municipalities', 'blurb' => 'Civic news & projects'],
    ];
@endphp

<header class="sticky top-0 z-30 bg-white/85 backdrop-blur-md border-b border-slate-200/60">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 h-16 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            <span class="h-7 w-7 rounded-full bg-slate-900"></span>
            <span class="font-semibold text-slate-900 tracking-tight">PrComet</span>
        </a>

        {{-- ── Desktop nav ── --}}
        <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-600">
            <a href="{{ route('home') }}#how" class="hover:text-slate-900 transition-colors">How it works</a>
            <a href="{{ route('home') }}#brief" class="hover:text-slate-900 transition-colors">The brief</a>
            <a href="{{ route('home') }}#send" class="hover:text-slate-900 transition-colors">The send</a>

            {{-- Who it's for — industry dropdown. CSS-only (hover + focus-within). --}}
            <div class="group relative">
                <a href="{{ route('home') }}#for-whom"
                   class="inline-flex items-center gap-1 hover:text-slate-900 group-hover:text-slate-900 transition-colors">
                    Who it's for
                    <svg class="h-3.5 w-3.5 text-slate-400 transition-transform duration-150 group-hover:rotate-180"
                         fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </a>

                <div class="absolute left-1/2 top-full -translate-x-1/2 pt-3 w-[26rem] max-w-[calc(100vw-2rem)]
                            invisible translate-y-1 opacity-0
                            group-hover:visible group-hover:translate-y-0 group-hover:opacity-100
                            focus-within:visible focus-within:translate-y-0 focus-within:opacity-100
                            transition duration-150 ease-out">
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-xl overflow-hidden">
                        <div class="h-1 bg-gradient-to-r from-indigo-600 via-violet-600 to-fuchsia-600"></div>
                        <div class="p-3">
                            <p class="px-3 pt-1 pb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">PrComet for your industry</p>
                            <div class="grid grid-cols-2 gap-1">
                                @foreach($navIndustries as $ind)
                                    <a href="{{ route($ind['route']) }}" class="block rounded-lg px-3 py-2 hover:bg-slate-50">
                                        <div class="text-sm font-medium text-slate-900">{{ $ind['label'] }}</div>
                                        <div class="text-xs text-slate-500">{{ $ind['blurb'] }}</div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($showAngle)
                {{-- The Angle — multi-column mega-menu. CSS-only (hover + focus-within). --}}
                <div class="group relative">
                    <a href="{{ route('articles.index') }}"
                       class="inline-flex items-center gap-1 hover:text-slate-900 group-hover:text-slate-900 transition-colors">
                        The Angle
                        <svg class="h-3.5 w-3.5 text-slate-400 transition-transform duration-150 group-hover:rotate-180"
                             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </a>

                    <div class="absolute left-1/2 top-full -translate-x-1/2 pt-3 w-[42rem] max-w-[calc(100vw-2rem)]
                                invisible translate-y-1 opacity-0
                                group-hover:visible group-hover:translate-y-0 group-hover:opacity-100
                                focus-within:visible focus-within:translate-y-0 focus-within:opacity-100
                                transition duration-150 ease-out">
                        <div class="rounded-2xl border border-slate-200 bg-white shadow-xl overflow-hidden">
                            <div class="h-1 bg-gradient-to-r from-indigo-600 via-violet-600 to-fuchsia-600"></div>

                            <div class="grid grid-cols-3 gap-x-8 p-6">
                                <div class="col-span-2">
                                    <div class="flex items-center justify-between mb-3">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Latest from The Angle</p>
                                        <a href="{{ route('articles.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">Browse all →</a>
                                    </div>

                                    @if($navArticles->isNotEmpty())
                                        <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                                            @foreach($navArticles as $a)
                                                <a href="{{ route('articles.show', $a) }}" class="group/item block">
                                                    @if($a->category)
                                                        <span class="text-[11px] font-semibold uppercase tracking-wide text-indigo-600">{{ $a->category }}</span>
                                                    @endif
                                                    <div class="mt-0.5 text-sm font-medium text-slate-900 leading-snug line-clamp-2 group-hover/item:text-indigo-700">
                                                        {{ $a->title }}
                                                    </div>
                                                    @if($a->published_at)
                                                        <div class="mt-1 text-xs text-slate-400">{{ $a->published_at->format('M j, Y') }}</div>
                                                    @endif
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-slate-500">No articles published yet.</p>
                                    @endif
                                </div>

                                <div class="border-l border-slate-100 pl-8">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Topics</p>
                                    <ul class="space-y-2.5">
                                        @forelse($navCategories as $cat)
                                            <li>
                                                <a href="{{ route('articles.topic', $cat) }}" class="text-sm text-slate-600 hover:text-indigo-700 transition-colors">{{ $cat }}</a>
                                            </li>
                                        @empty
                                            <li class="text-sm text-slate-400">Coming soon</li>
                                        @endforelse
                                    </ul>
                                    <a href="{{ route('articles.index') }}" class="mt-5 inline-flex items-center gap-1 text-sm font-medium text-slate-900 hover:text-indigo-700 transition-colors">
                                        Visit The Angle →
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </nav>

        {{-- ── Right cluster ── --}}
        <div class="flex items-center gap-2">
            @auth
                <a href="{{ url('/dashboard') }}" class="hidden sm:inline-flex items-center px-3.5 py-1.5 text-sm font-medium text-slate-700 hover:text-slate-900 transition-colors">
                    Open dashboard →
                </a>
            @else
                <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center px-3.5 py-1.5 text-sm font-medium text-slate-700 hover:text-slate-900 transition-colors">
                    Sign in
                </a>
            @endauth
            <a href="{{ route('home') }}#request-demo" class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-md bg-slate-900 hover:bg-slate-800 text-white text-sm font-medium transition-colors">
                Request a demo
            </a>

            {{-- Mobile toggle --}}
            <button type="button" data-nav-toggle aria-controls="mobile-nav" aria-expanded="false" aria-label="Toggle menu"
                    class="md:hidden inline-flex items-center justify-center h-9 w-9 rounded-md text-slate-700 hover:bg-slate-100 transition-colors">
                <svg data-icon-open class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg data-icon-close class="hidden h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M6 18L18 6" />
                </svg>
            </button>
        </div>
    </div>

    {{-- ── Mobile drawer ── --}}
    <div id="mobile-nav" data-nav-panel class="hidden md:hidden border-t border-slate-200 bg-white">
        <div class="max-w-7xl mx-auto px-6 py-4 space-y-1">
            <a href="{{ route('home') }}#how" class="block rounded-lg px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50">How it works</a>
            <a href="{{ route('home') }}#brief" class="block rounded-lg px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50">The brief</a>
            <a href="{{ route('home') }}#send" class="block rounded-lg px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50">The send</a>

            <details class="nav-accordion pt-1 mt-1">
                <summary class="flex items-center justify-between rounded-lg px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50 cursor-pointer">
                    Who it's for
                    <svg class="nav-accordion-chevron h-4 w-4 text-slate-400 transition-transform duration-150" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="mt-1 pl-3 border-l border-slate-100 ml-3">
                    @foreach($navIndustries as $ind)
                        <a href="{{ route($ind['route']) }}" class="block rounded-lg px-3 py-2 hover:bg-slate-50">
                            <span class="text-sm font-medium text-slate-900">{{ $ind['label'] }}</span>
                            <span class="block text-xs text-slate-500">{{ $ind['blurb'] }}</span>
                        </a>
                    @endforeach
                </div>
            </details>

            @if($showAngle)
                <div class="pt-3 mt-2 border-t border-slate-100">
                    <div class="flex items-center justify-between px-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">The Angle</p>
                        <a href="{{ route('articles.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">Browse all →</a>
                    </div>

                    @foreach($navArticles->take(3) as $a)
                        <a href="{{ route('articles.show', $a) }}" class="block rounded-lg px-3 py-2 hover:bg-slate-50">
                            @if($a->category)
                                <span class="text-[11px] font-semibold uppercase tracking-wide text-indigo-600">{{ $a->category }}</span>
                            @endif
                            <div class="text-sm font-medium text-slate-900 leading-snug line-clamp-2">{{ $a->title }}</div>
                        </a>
                    @endforeach

                    @if($navCategories->isNotEmpty())
                        <div class="flex flex-wrap gap-2 px-3 pt-2">
                            @foreach($navCategories as $cat)
                                <a href="{{ route('articles.topic', $cat) }}"
                                   class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-600 hover:border-slate-400 hover:text-slate-900">{{ $cat }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <div class="pt-3 mt-2 border-t border-slate-100 space-y-2">
                @auth
                    <a href="{{ url('/dashboard') }}" class="block rounded-lg px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50">Open dashboard →</a>
                @else
                    <a href="{{ route('login') }}" class="block rounded-lg px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50">Sign in</a>
                @endauth
                <a href="{{ route('home') }}#request-demo" class="block rounded-lg bg-slate-900 px-3 py-2.5 text-center text-base font-medium text-white hover:bg-slate-800">
                    Request a demo
                </a>
            </div>
        </div>
    </div>

    {{-- Mobile drawer behaviour: no Alpine/Livewire needed on article pages. --}}
    <script>
        (function () {
            const header = document.currentScript.closest('header');
            if (! header) return;
            const btn = header.querySelector('[data-nav-toggle]');
            const panel = header.querySelector('[data-nav-panel]');
            const iconOpen = header.querySelector('[data-icon-open]');
            const iconClose = header.querySelector('[data-icon-close]');
            if (! btn || ! panel) return;

            function setOpen(open) {
                panel.classList.toggle('hidden', ! open);
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (iconOpen) iconOpen.classList.toggle('hidden', open);
                if (iconClose) iconClose.classList.toggle('hidden', ! open);
            }

            btn.addEventListener('click', function () {
                setOpen(panel.classList.contains('hidden'));
            });
            panel.querySelectorAll('a').forEach(function (a) {
                a.addEventListener('click', function () { setOpen(false); });
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') setOpen(false);
            });
        })();
    </script>
</header>
