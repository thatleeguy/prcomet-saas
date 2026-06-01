<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>PrComet: Find the journalists who'll publish your story</title>
        <meta name="description" content="PrComet continuously analyzes thousands of publications, podcasts, and newsletters to surface the writers most likely to engage with your story, and tells you exactly why they're a fit.">

        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        <link rel="alternate icon" href="/favicon.ico">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=geist:400,500,600,700|geist-mono:400,500&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        <x-tracking />
    </head>
    <body class="font-sans antialiased text-slate-900 bg-white">

        {{-- ─────────────────────────────────────────────────────────────
              NAV
          ───────────────────────────────────────────────────────────── --}}
        <x-site-nav />

        {{-- ─────────────────────────────────────────────────────────────
              HERO
          ───────────────────────────────────────────────────────────── --}}
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
                <div class="absolute top-0 right-0 h-[600px] w-[600px] rounded-full bg-gradient-to-br from-indigo-200/40 to-fuchsia-200/40 blur-3xl translate-x-1/3 -translate-y-1/3"></div>
                <div class="absolute bottom-0 left-0 h-[500px] w-[500px] rounded-full bg-gradient-to-tr from-violet-200/30 to-indigo-200/30 blur-3xl -translate-x-1/3"></div>
            </div>

            <div class="relative max-w-7xl mx-auto px-6 lg:px-8 pt-16 lg:pt-24 pb-16 lg:pb-24 grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <div class="space-y-7">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-xs font-medium text-indigo-700">
                        <span class="relative flex h-1.5 w-1.5">
                            <span class="absolute inline-flex h-full w-full rounded-full bg-indigo-500 opacity-60 animate-ping"></span>
                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-indigo-500"></span>
                        </span>
                        Early access · invitation only
                    </div>

                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-semibold text-slate-900 tracking-tight leading-[1.05] [text-wrap:balance]">
                        Find the writers who'll publish your story.
                    </h1>

                    <p class="text-lg text-slate-600 leading-relaxed max-w-xl">
                        PrComet reads thousands of articles, podcasts, and newsletters every week so you don't have to. We surface the journalists, hosts, and analysts most likely to engage with your story, and we tell you exactly why each one is a fit.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="#request-demo" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-medium text-sm transition-colors shadow-sm">
                            Request a demo
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" /></svg>
                        </a>
                        <a href="#how" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-lg bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-medium text-sm transition-colors">
                            See how it works
                        </a>
                    </div>

                    <div class="flex items-center gap-6 pt-2 text-xs text-slate-500">
                        <span class="flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            Discovery, not automation
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            Citations on every claim
                        </span>
                    </div>
                </div>

                {{-- Hero mockup: two-step flow showing the actual product loop.
                     Press release goes in → PrComet surfaces the matching writer.
                     Content on white cards so it's actually readable; gradient
                     reserved for backdrop, avatar, score, and connector. --}}
                <div class="relative lg:pl-8">
                    {{-- Soft gradient backdrop --}}
                    <div class="absolute -inset-6 bg-gradient-to-br from-indigo-300/40 via-violet-300/40 to-fuchsia-300/40 rounded-[2.5rem] blur-3xl" aria-hidden="true"></div>

                    <div class="relative space-y-3">
                        {{-- STEP 1: Your press release --}}
                        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-md">
                            <div class="flex items-center gap-2 mb-2.5">
                                <div class="grid place-items-center h-7 w-7 rounded-lg bg-slate-100 text-slate-500">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 12h6M9 16h6M9 8h6M5 4h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z" /></svg>
                                </div>
                                <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">When you publish</div>
                                <span class="ml-auto text-[10px] text-slate-400">2h ago</span>
                            </div>
                            <div class="text-sm font-medium text-slate-900 leading-snug">
                                "Aurelian intersects 12.4 g/t Au over 28m at Big Sky"
                            </div>
                            <div class="text-xs text-slate-500 mt-1">Aurelian Gold · Press release</div>
                        </div>

                        {{-- Gradient connector --}}
                        <div class="flex justify-center -my-1.5 relative z-10">
                            <div class="grid place-items-center h-8 w-8 rounded-full bg-gradient-to-br from-indigo-500 via-violet-500 to-fuchsia-500 shadow-lg ring-4 ring-white">
                                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 5v14M19 12l-7 7-7-7" /></svg>
                            </div>
                        </div>

                        {{-- STEP 2: PrComet surfaces the match --}}
                        <div class="bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden">
                            {{-- Header strip --}}
                            <div class="px-5 py-2.5 bg-gradient-to-r from-indigo-50 via-violet-50 to-fuchsia-50 border-b border-slate-100 flex items-center gap-2">
                                <span class="relative flex h-2 w-2">
                                    <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-60 animate-ping"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                </span>
                                <span class="text-xs font-semibold text-slate-700">PrComet surfaces your top match</span>
                            </div>

                            <div class="p-5 lg:p-6">
                                {{-- Author identity + score --}}
                                <div class="flex items-start gap-3 mb-5">
                                    <div class="grid place-items-center h-12 w-12 rounded-full bg-gradient-to-br from-indigo-500 via-violet-500 to-fuchsia-500 text-white font-bold text-sm ring-2 ring-white shadow shrink-0">RS</div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold text-slate-900 leading-tight">Robert Sinclair</div>
                                        <div class="text-xs text-slate-500 mt-0.5">Senior reporter · Mining.com</div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="text-3xl font-bold tabular leading-none">
                                            <span class="bg-gradient-to-br from-indigo-600 via-violet-600 to-fuchsia-600 bg-clip-text text-transparent">87</span><span class="text-xl text-slate-400 font-semibold">%</span>
                                        </div>
                                        <div class="inline-flex items-center gap-1 mt-1.5 text-[10px] uppercase tracking-wider text-emerald-700 font-semibold">
                                            <span class="h-1 w-1 rounded-full bg-emerald-500"></span>
                                            High fit
                                        </div>
                                    </div>
                                </div>

                                {{-- The "why": short and punchy --}}
                                <div class="space-y-3">
                                    <div>
                                        <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Why he's a fit</div>
                                        <p class="text-sm text-slate-700 leading-relaxed">
                                            Wrote about Walker Lane gold 4 days ago. Your drill result extends his thesis. He has a track record of follow-up coverage on companies he's flagged.
                                        </p>
                                    </div>

                                    <div class="pt-3 border-t border-slate-100">
                                        <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Pitch this</div>
                                        <p class="text-sm text-slate-700 leading-relaxed">
                                            Send the intercept summary + offer your CEO for a 15-min call. Keep it factual.
                                        </p>
                                    </div>
                                </div>

                                <button class="mt-5 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-medium text-sm transition-colors shadow-sm">
                                    Open full brief
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ─────────────────────────────────────────────────────────────
              HOW IT WORKS · 3 steps with mini mockups
          ───────────────────────────────────────────────────────────── --}}
        <section id="how" class="bg-slate-50 border-y border-slate-200/70 py-20 lg:py-28">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="max-w-2xl">
                    <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider mb-3">How it works</p>
                    <h2 class="text-3xl lg:text-4xl font-semibold text-slate-900 tracking-tight [text-wrap:balance]">
                        You hit publish. The universe shrugs.
                    </h2>
                    <p class="text-lg text-slate-600 mt-4 leading-relaxed">
                        Somewhere in the thousands of articles, podcasts, and newsletters going out this week, there are three or four writers who'd be excited about your story. They're buried under everything else. PrComet reads on your behalf, finds them, and shows you exactly why each one is a fit.
                    </p>
                </div>

                <div class="mt-16 grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Step 1: feed-connection mock --}}
                    <div class="space-y-5">
                        <div class="flex items-center gap-3">
                            <span class="grid place-items-center h-8 w-8 rounded-lg bg-indigo-100 text-indigo-700 font-semibold text-sm">01</span>
                            <h3 class="text-lg font-semibold text-slate-900">Connect your content</h3>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Point us at your RSS feed: press releases, blog posts, product updates. We watch it for new publications and use them as the trigger for discovery.
                        </p>

                        <div class="rounded-xl bg-white border border-slate-200 shadow-sm p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="text-xs font-medium text-slate-500">Press feed</div>
                                <span class="inline-flex items-center gap-1 text-[10px] font-medium text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Connected
                                </span>
                            </div>
                            <div class="font-mono text-xs text-slate-700 bg-slate-50 rounded px-2.5 py-1.5 truncate border border-slate-100">
                                https://yourcompany.com/news/rss.xml
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500">Last polled</span>
                                <span class="tabular text-slate-700 font-medium">12 min ago</span>
                            </div>
                            <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100">
                                <span class="text-slate-500">Releases ingested</span>
                                <span class="tabular text-slate-900 font-semibold">47</span>
                            </div>
                        </div>
                    </div>

                    {{-- Step 2: live corpus --}}
                    <div class="space-y-5">
                        <div class="flex items-center gap-3">
                            <span class="grid place-items-center h-8 w-8 rounded-lg bg-violet-100 text-violet-700 font-semibold text-sm">02</span>
                            <h3 class="text-lg font-semibold text-slate-900">We do the reading</h3>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Every article, podcast episode, and newsletter post in your industry gets read and indexed. We build a rolling profile of each writer's stance, topics, and track record.
                        </p>

                        <div class="rounded-xl bg-white border border-slate-200 shadow-sm p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-xs font-medium text-slate-500">Live corpus</div>
                                <span class="inline-flex items-center gap-1 text-[10px] font-medium text-slate-500">
                                    <span class="relative flex h-1.5 w-1.5">
                                        <span class="absolute inline-flex h-full w-full rounded-full bg-violet-500 opacity-60 animate-ping"></span>
                                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-violet-500"></span>
                                    </span>
                                    Analyzing
                                </span>
                            </div>
                            <ul class="space-y-2">
                                @foreach ([
                                    ['MN', 'Nevada drill season heats up', 'from-indigo-500 to-violet-600'],
                                    ['CI', 'Macro setup for gold in H2 2026', 'from-violet-500 to-fuchsia-600'],
                                    ['MS', 'Why Nevada gold is underpriced', 'from-blue-500 to-indigo-600'],
                                ] as $item)
                                    <li class="flex items-center gap-2.5">
                                        <div class="grid place-items-center h-6 w-6 rounded text-[10px] font-semibold text-white bg-gradient-to-br {{ $item[2] }} shrink-0">{{ $item[0] }}</div>
                                        <span class="text-xs text-slate-700 truncate flex-1">{{ $item[1] }}</span>
                                        <svg class="h-3 w-3 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                                <span class="text-slate-500">Items analyzed today</span>
                                <span class="tabular text-slate-900 font-semibold">142</span>
                            </div>
                        </div>
                    </div>

                    {{-- Step 3: ranked matches --}}
                    <div class="space-y-5">
                        <div class="flex items-center gap-3">
                            <span class="grid place-items-center h-8 w-8 rounded-lg bg-fuchsia-100 text-fuchsia-700 font-semibold text-sm">03</span>
                            <h3 class="text-lg font-semibold text-slate-900">You see the matches</h3>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            For each new piece of your content, we surface the writers most likely to engage, ranked by fit, with the rationale, citations, and a suggested angle.
                        </p>

                        <div class="rounded-xl bg-white border border-slate-200 shadow-sm p-4">
                            <div class="text-xs font-medium text-slate-500 mb-3">Top matches</div>
                            <ul class="space-y-2.5">
                                @foreach ([
                                    ['Robert Sinclair', 'Mining.com', '87%', 'text-emerald-700'],
                                    ['Kerry Lutz', 'Mining Stock Education', '81%', 'text-emerald-700'],
                                    ['Brent Cook', 'Exploration Insights', '76%', 'text-indigo-700'],
                                ] as $m)
                                    <li class="flex items-center gap-3 py-1.5">
                                        <div class="grid place-items-center h-7 w-7 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-white text-[10px] font-semibold shrink-0">{{ strtoupper(substr($m[0], 0, 1).explode(' ', $m[0])[1][0]) }}</div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-medium text-slate-900 truncate">{{ $m[0] }}</div>
                                            <div class="text-[10px] text-slate-500 truncate">{{ $m[1] }}</div>
                                        </div>
                                        <span class="text-xs font-semibold tabular {{ $m[3] }}">{{ $m[2] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ─────────────────────────────────────────────────────────────
              THE BRIEF · main feature showcase
          ───────────────────────────────────────────────────────────── --}}
        <section id="brief" class="py-20 lg:py-28">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                <div class="space-y-6 order-2 lg:order-1">
                    <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">The brief</p>
                    <h2 class="text-3xl lg:text-4xl font-semibold text-slate-900 tracking-tight">
                        Every match comes with reasoning.
                    </h2>
                    <p class="text-lg text-slate-600 leading-relaxed">
                        Lists of journalists aren't useful. Anyone can sell you a database. PrComet shows you <em class="font-medium text-slate-900">why</em> a specific writer is the right person for a specific story, with citations linking back to their actual work.
                    </p>

                    <ul class="space-y-4 pt-2">
                        <li class="flex gap-3">
                            <span class="grid place-items-center h-6 w-6 rounded-md bg-indigo-100 text-indigo-700 shrink-0 mt-0.5">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </span>
                            <div>
                                <div class="text-sm font-semibold text-slate-900">A rationale, not a hunch</div>
                                <p class="text-sm text-slate-600 leading-relaxed mt-0.5">
                                    "Sinclair wrote a Nevada gold piece 4 days ago. Your drill result fits his thesis. He has a track record of follow-up coverage."
                                </p>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <span class="grid place-items-center h-6 w-6 rounded-md bg-violet-100 text-violet-700 shrink-0 mt-0.5">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </span>
                            <div>
                                <div class="text-sm font-semibold text-slate-900">A suggested angle, ready to send</div>
                                <p class="text-sm text-slate-600 leading-relaxed mt-0.5">
                                    A concrete pitch hook (counter-story, podcast slot, follow-up) so your team isn't starting from scratch.
                                </p>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <span class="grid place-items-center h-6 w-6 rounded-md bg-fuchsia-100 text-fuchsia-700 shrink-0 mt-0.5">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </span>
                            <div>
                                <div class="text-sm font-semibold text-slate-900">Citations on every claim</div>
                                <p class="text-sm text-slate-600 leading-relaxed mt-0.5">
                                    Every assertion links back to the article, episode, or post it came from. Nothing hallucinated.
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>

                {{-- Larger brief mockup --}}
                <div class="order-1 lg:order-2 space-y-3">
                    <div class="flex items-center gap-2 text-xs text-slate-500 px-2">
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7-7-7M12 21V3" /></svg>
                        Surfaced by your release: <span class="font-medium text-slate-700 truncate">12.4 g/t Au over 28m at Big Sky</span>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white shadow-xl overflow-hidden">
                        <div class="p-6 lg:p-7 space-y-6">
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="h-3.5 w-3.5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" /></svg>
                                    <span class="text-xs font-semibold text-slate-900 uppercase tracking-wide">Why they're a fit</span>
                                </div>
                                <p class="text-sm text-slate-700 leading-relaxed">
                                    Sinclair published "Nevada drill season heats up: five names to watch" four days ago, calling out Walker Lane high-grade plays as the most interesting setups of the quarter. Your fresh 12.4 g/t intercept at Big Sky lands squarely in that frame.
                                </p>
                            </div>

                            <div class="rounded-xl bg-gradient-to-br from-indigo-50 via-violet-50 to-fuchsia-50 border border-indigo-100 p-4">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <svg class="h-3.5 w-3.5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" /></svg>
                                    <span class="text-xs font-semibold text-indigo-900 uppercase tracking-wide">Suggested angle</span>
                                </div>
                                <p class="text-sm text-slate-800 leading-relaxed">
                                    Email Sinclair with the intercept summary. Offer Sarah for a 15-min call. Keep it factual. He dislikes promotional framing.
                                </p>
                            </div>

                            <div>
                                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Citations</div>
                                <div class="pl-3 border-l-2 border-indigo-300">
                                    <p class="text-xs text-slate-700 italic leading-relaxed">"Walker Lane high-grade plays are the most interesting setups of the quarter…"</p>
                                    <div class="text-[10px] text-indigo-700 mt-1 font-medium">
                                        Nevada drill season heats up · Mining.com ↗
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ─────────────────────────────────────────────────────────────
              THE SEND · media library + one-pager
          ───────────────────────────────────────────────────────────── --}}
        <section id="send" class="bg-slate-50 border-y border-slate-200/70 py-20 lg:py-28">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">

                {{-- Mockup column: a one-pager preview with branded header,
                     pull quote, asset thumbnails, and an engagement strip.
                     The whole thing leans on company-branding cues so the
                     reader instantly groks "this is what the journalist sees." --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-xs text-slate-500 px-2 font-mono">
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                        prcomet.com/onepagers/<span class="text-indigo-700">a48f…2c</span>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white shadow-xl overflow-hidden">
                        {{-- Branded header strip --}}
                        <div class="relative h-24 bg-gradient-to-br from-amber-500 via-orange-500 to-rose-500">
                            <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.4) 0%, transparent 50%), radial-gradient(circle at 80% 30%, rgba(255,255,255,0.3) 0%, transparent 50%);"></div>
                            <div class="absolute bottom-3 left-5 right-5 flex items-end justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="grid place-items-center h-9 w-9 rounded-lg bg-white text-amber-700 font-bold text-sm shadow">AU</div>
                                    <div class="text-white">
                                        <div class="font-semibold text-sm leading-tight">Aurelian Gold Resources</div>
                                        <div class="text-[10px] text-white/80">TSX-V: AUG · Nevada gold exploration</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-5 lg:p-6 space-y-5">
                            {{-- Personal note --}}
                            <div class="text-sm text-slate-700 leading-relaxed">
                                Hi Robert — given your Walker Lane coverage, thought you'd find our latest intercept at Big Sky worth a look. Full assay table below, plus a 60-second call with our VP Exploration if helpful.
                            </div>

                            {{-- Pull quote --}}
                            <div class="rounded-xl bg-gradient-to-br from-indigo-50 via-violet-50 to-fuchsia-50 border border-indigo-100 p-4">
                                <svg class="h-4 w-4 text-indigo-500 mb-1.5" fill="currentColor" viewBox="0 0 24 24"><path d="M9.983 3v7.391c0 5.704-3.731 9.57-8.983 10.609l-.995-2.151c2.432-.917 3.995-3.638 3.995-5.849h-4v-10h9.983zm14.017 0v7.391c0 5.704-3.748 9.571-9 10.609l-.996-2.151c2.433-.917 3.996-3.638 3.996-5.849h-3.983v-10h9.983z"/></svg>
                                <p class="text-sm text-slate-800 italic leading-relaxed">"This intercept extends the high-grade zone by 200 metres along strike and confirms the system is open at depth."</p>
                                <div class="text-[10px] text-indigo-700 mt-2 font-medium">— Sarah Chen, VP Exploration</div>
                            </div>

                            {{-- Asset thumbnails --}}
                            <div>
                                <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-2">Materials</div>
                                <div class="grid grid-cols-4 gap-2">
                                    {{-- Drill core image --}}
                                    <div class="aspect-square rounded-lg bg-gradient-to-br from-stone-300 via-amber-200 to-stone-400 border border-slate-200 overflow-hidden relative">
                                        <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-stone-700/60 to-transparent"></div>
                                        <div class="absolute bottom-1 left-1.5 text-[9px] text-white font-medium">Drill core</div>
                                    </div>
                                    {{-- Map --}}
                                    <div class="aspect-square rounded-lg bg-gradient-to-br from-emerald-100 via-teal-100 to-slate-100 border border-slate-200 overflow-hidden relative">
                                        <svg class="absolute inset-0 w-full h-full" viewBox="0 0 40 40" preserveAspectRatio="none"><path d="M0 25 Q10 15 20 22 T40 18" fill="none" stroke="rgb(13 148 136 / 0.5)" stroke-width="1"/><path d="M0 32 Q15 22 25 28 T40 25" fill="none" stroke="rgb(13 148 136 / 0.4)" stroke-width="1"/><circle cx="22" cy="20" r="1.5" fill="rgb(220 38 38)"/></svg>
                                        <div class="absolute bottom-1 left-1.5 text-[9px] text-slate-700 font-medium">Geology map</div>
                                    </div>
                                    {{-- PDF --}}
                                    <div class="aspect-square rounded-lg bg-rose-50 border border-rose-100 grid place-items-center relative">
                                        <svg class="h-7 w-7 text-rose-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        <div class="absolute bottom-1 left-1.5 text-[9px] text-rose-700 font-medium">NI 43-101</div>
                                    </div>
                                    {{-- Link --}}
                                    <div class="aspect-square rounded-lg bg-indigo-50 border border-indigo-100 grid place-items-center relative">
                                        <svg class="h-7 w-7 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                                        <div class="absolute bottom-1 left-1.5 text-[9px] text-indigo-700 font-medium">Corp deck</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Engagement strip (what the sender sees on their side) --}}
                        <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-1.5 text-slate-600">
                                <span class="relative flex h-1.5 w-1.5">
                                    <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-60 animate-ping"></span>
                                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                                </span>
                                <span class="font-medium text-emerald-700">Opened</span>
                                <span class="text-slate-400">·</span>
                                <span>3 views · 12 min ago</span>
                            </div>
                            <span class="text-slate-400 font-mono text-[10px]">Robert Sinclair</span>
                        </div>
                    </div>
                </div>

                {{-- Copy column --}}
                <div class="space-y-6">
                    <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">The send</p>
                    <h2 class="text-3xl lg:text-4xl font-semibold text-slate-900 tracking-tight [text-wrap:balance]">
                        Send a story, not a thread of attachments.
                    </h2>
                    <p class="text-lg text-slate-600 leading-relaxed">
                        Once you've decided which writer to reach out to, PrComet builds them a single branded page with everything they need to actually run the story. No 12 MB email. No "let me know if you want photos." Paste a link, get a response.
                    </p>

                    <ul class="space-y-5 pt-2">
                        <li class="flex gap-3">
                            <span class="grid place-items-center h-6 w-6 rounded-md bg-indigo-100 text-indigo-700 shrink-0 mt-0.5">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7zM4 15l4-4 4 4 4-4 4 4M9 9a1 1 0 100-2 1 1 0 000 2z" /></svg>
                            </span>
                            <div>
                                <div class="text-sm font-semibold text-slate-900">A media library, organized once</div>
                                <p class="text-sm text-slate-600 leading-relaxed mt-0.5">
                                    Drill core photos, NI 43-101s, executive bios, pull quotes from past coverage. Upload it once, tag it, and PrComet pulls the right pieces onto each one-pager automatically.
                                </p>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <span class="grid place-items-center h-6 w-6 rounded-md bg-violet-100 text-violet-700 shrink-0 mt-0.5">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </span>
                            <div>
                                <div class="text-sm font-semibold text-slate-900">Auto-curated, then tweakable</div>
                                <p class="text-sm text-slate-600 leading-relaxed mt-0.5">
                                    Tags on each asset get matched to the story's topics — drill results pull photos and the technical report, financings pull the deck and last earnings transcript. Override anything in one click.
                                </p>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <span class="grid place-items-center h-6 w-6 rounded-md bg-fuchsia-100 text-fuchsia-700 shrink-0 mt-0.5">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            </span>
                            <div>
                                <div class="text-sm font-semibold text-slate-900">Knowing when they opened it</div>
                                <p class="text-sm text-slate-600 leading-relaxed mt-0.5">
                                    Every view is logged with timestamps. "Sinclair opened it twice yesterday" is the cue to follow up. Not the cold pitch. Not the silent void.
                                </p>
                            </div>
                        </li>
                    </ul>

                    <div class="pt-4 flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-slate-500">
                        <span class="flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            Your branding, your accent color
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            One link, mobile-ready
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            Unpublish anytime
                        </span>
                    </div>
                </div>
            </div>
        </section>

        {{-- ─────────────────────────────────────────────────────────────
              WHY US
          ───────────────────────────────────────────────────────────── --}}
        <section class="bg-slate-900 text-white py-20 lg:py-28 relative overflow-hidden">
            <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
                <div class="absolute top-0 left-1/4 h-96 w-96 rounded-full bg-indigo-500/20 blur-3xl"></div>
                <div class="absolute bottom-0 right-1/4 h-96 w-96 rounded-full bg-fuchsia-500/20 blur-3xl"></div>
            </div>

            <div class="relative max-w-7xl mx-auto px-6 lg:px-8">
                <div class="max-w-2xl">
                    <p class="text-xs font-semibold text-indigo-300 uppercase tracking-wider mb-3">Different by design</p>
                    <h2 class="text-3xl lg:text-4xl font-semibold tracking-tight">
                        We surface. <span class="text-indigo-300">You write.</span>
                    </h2>
                    <p class="text-lg text-slate-300 mt-4 leading-relaxed">
                        PrComet is a discovery tool, not an outreach machine. Friction stays where it should: at the human. That's the whole point.
                    </p>
                </div>

                <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach ([
                        ['title' => 'Reasoning, not lists', 'body' => 'Databases sell you names. We tell you why each name is the right one for this story, this week.', 'icon' => 'M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z'],
                        ['title' => 'Track records, not opinion', 'body' => "When a writer has made a public prediction, we tell you whether they were right. Reach out from a position of evidence.", 'icon' => 'M3 17l6-6 4 4 8-8M14 7h7v7'],
                        ['title' => 'Citations on everything', 'body' => 'Every line in a brief links to the actual article, episode, or post it came from. Nothing hallucinated. Nothing implied.', 'icon' => 'M10 14a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71M14 10a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71'],
                    ] as $card)
                        <div class="rounded-2xl bg-white/5 backdrop-blur-sm border border-white/10 p-6">
                            <div class="grid place-items-center h-10 w-10 rounded-lg bg-gradient-to-br from-indigo-500 to-fuchsia-500 mb-4">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="{{ $card['icon'] }}" /></svg>
                            </div>
                            <h3 class="text-lg font-semibold mb-2">{{ $card['title'] }}</h3>
                            <p class="text-sm text-slate-300 leading-relaxed">{{ $card['body'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ─────────────────────────────────────────────────────────────
              FOR WHOM
          ───────────────────────────────────────────────────────────── --}}
        <section id="for-whom" class="py-20 lg:py-28">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="max-w-2xl">
                    <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider mb-3">Who it's for</p>
                    <h2 class="text-3xl lg:text-4xl font-semibold text-slate-900 tracking-tight">
                        Made for teams that need attention to grow.
                    </h2>
                </div>

                <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ([
                        ['title' => 'Growth-stage companies', 'body' => "You've hit your stride. Now you need analyst attention, podcast slots, and coverage in the publications your customers read."],
                        ['title' => 'PR & comms agencies', 'body' => "Manage outreach across multiple clients in one workspace. Stop pitching journalists who said no last quarter; lead with the ones who actually want the story."],
                        ['title' => 'IR teams at public companies', 'body' => 'Every press release is a chance to move the narrative, but only if the right voices pick it up. We surface them. You handle the relationship.'],
                    ] as $aud)
                        <div class="group rounded-2xl border border-slate-200 bg-white p-6 hover:border-indigo-300 hover:shadow-lg transition-all">
                            <h3 class="text-base font-semibold text-slate-900 mb-3">{{ $aud['title'] }}</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">{{ $aud['body'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ─────────────────────────────────────────────────────────────
              DEMO REQUEST
          ───────────────────────────────────────────────────────────── --}}
        <section id="request-demo" class="py-24 lg:py-32 bg-slate-50 border-t border-slate-200/70 relative overflow-hidden">
            {{-- Rich gradient backdrop (the conversion moment deserves the same care
                 as the hero). Multiple blobs at higher intensity than other sections. --}}
            <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
                <div class="absolute -top-32 left-1/4 h-[500px] w-[500px] rounded-full bg-gradient-to-br from-indigo-300/40 to-fuchsia-300/30 blur-3xl"></div>
                <div class="absolute -bottom-32 right-1/4 h-[500px] w-[500px] rounded-full bg-gradient-to-tr from-violet-300/30 to-indigo-300/40 blur-3xl"></div>
            </div>

            <div class="relative max-w-6xl mx-auto px-6 lg:px-8">
                {{-- Hero copy --}}
                <div class="max-w-3xl text-center mx-auto mb-14 lg:mb-16">
                    <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider mb-4">Request a demo</p>
                    <h2 class="text-4xl lg:text-5xl font-semibold text-slate-900 tracking-tight [text-wrap:balance]">
                        See PrComet running on your content.
                    </h2>
                    <p class="text-lg text-slate-600 mt-5 leading-relaxed [text-wrap:balance]">
                        In 15 minutes we'll ingest your last three releases, run them through the engine, and walk you through the briefs we'd surface for your team.
                    </p>
                </div>

                {{-- Two-column: promise + trust signal on the left, form on the right --}}
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-10 lg:gap-12 items-start">

                    {{-- LEFT: what to expect --}}
                    <div class="lg:col-span-2 space-y-10 lg:pt-2">
                        <div>
                            <h3 class="text-xs font-semibold text-slate-900 uppercase tracking-wider mb-6">What happens next</h3>
                            <ol class="space-y-6">
                                <li class="flex gap-4">
                                    <span class="grid place-items-center h-8 w-8 rounded-full bg-slate-900 text-white text-xs font-semibold shrink-0 ring-4 ring-white shadow-sm">1</span>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">You submit the form</div>
                                        <p class="text-sm text-slate-600 mt-1 leading-relaxed">A real person reads it. No drip campaigns, no "Hi {firstname}" sequence.</p>
                                    </div>
                                </li>
                                <li class="flex gap-4">
                                    <span class="grid place-items-center h-8 w-8 rounded-full bg-slate-900 text-white text-xs font-semibold shrink-0 ring-4 ring-white shadow-sm">2</span>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">We send you real briefs within 24 hours</div>
                                        <p class="text-sm text-slate-600 mt-1 leading-relaxed">Actual opportunities surfaced from your last three releases. Rationale, suggested angles, the works.</p>
                                    </div>
                                </li>
                                <li class="flex gap-4">
                                    <span class="grid place-items-center h-8 w-8 rounded-full bg-gradient-to-br from-indigo-500 via-violet-500 to-fuchsia-500 text-white text-xs font-semibold shrink-0 ring-4 ring-white shadow-md">3</span>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">15-minute walkthrough</div>
                                        <p class="text-sm text-slate-600 mt-1 leading-relaxed">If the briefs land, we'll set up your team and you're off.</p>
                                    </div>
                                </li>
                            </ol>
                        </div>

                        {{-- Trust signal --}}
                        <div class="pt-8 border-t border-slate-200/80">
                            <div class="flex items-start gap-3">
                                <div class="relative shrink-0">
                                    <div class="grid place-items-center h-11 w-11 rounded-full bg-gradient-to-br from-indigo-500 via-violet-500 to-fuchsia-500 text-white text-sm font-semibold ring-2 ring-white shadow">L</div>
                                    <span class="absolute -bottom-0.5 -right-0.5 grid place-items-center h-4 w-4 rounded-full bg-emerald-500 ring-2 ring-white">
                                        <svg class="h-2.5 w-2.5 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    </span>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">Personally reviewed</div>
                                    <p class="text-sm text-slate-600 mt-0.5 leading-relaxed">Every request gets a human reply within one business day. Promise.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT: the form, elevated and signaled with the brand gradient --}}
                    <div class="lg:col-span-3">
                        <div class="relative rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
                            {{-- Thin gradient stripe across the top, same vocabulary as the dashboard hero --}}
                            <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-indigo-600 via-violet-600 to-fuchsia-600"></div>
                            <div class="p-6 lg:p-8">
                                @livewire('landing.demo-request-form')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ─────────────────────────────────────────────────────────────
              FOOTER
          ───────────────────────────────────────────────────────────── --}}
        <x-site-footer />

        @livewireScripts
    </body>
</html>
