<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<x-articles.head
    title="PrComet for Tourism Councils & DMOs"
    description="Festivals, new attractions, and seasonal campaigns only drive visits if travel writers and regional media spread the word. PrComet finds the journalists already covering destinations like yours and tells you why each fits."
    :canonical="route('industries.tourism-councils')"
/>
<body class="font-sans antialiased text-slate-900 bg-white">

    <x-site-nav />

    {{-- ── Hero with match mockup ── --}}
    <section class="relative overflow-hidden bg-slate-900 text-white">
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute -top-24 right-0 h-[520px] w-[520px] rounded-full bg-gradient-to-br from-emerald-500/25 to-cyan-500/15 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/4 h-[420px] w-[420px] rounded-full bg-gradient-to-tr from-teal-500/20 to-sky-500/20 blur-3xl"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-6 lg:px-8 pt-16 lg:pt-24 pb-16 lg:pb-20 grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-xs font-medium text-emerald-200">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60 animate-ping"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-400"></span>
                    </span>
                    PrComet for Tourism Councils &amp; DMOs
                </div>

                <h1 class="mt-6 text-5xl lg:text-7xl font-semibold tracking-tight leading-[1.03]">
                    Get your destination <span class="text-emerald-300">on the list.</span>
                </h1>
                <p class="mt-6 max-w-xl text-lg text-slate-300 leading-relaxed">
                    A new festival, a trail opening, a "best places to visit" season. They fill hotel rooms only if travel writers and regional outlets feature them. PrComet finds the journalists already covering destinations like yours and tells you why each one is a fit.
                </p>
                <div class="mt-8 flex flex-col sm:flex-row gap-3">
                    <a href="#request-demo" class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-5 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-100 transition-colors">
                        Request a demo
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" /></svg>
                    </a>
                    <a href="#how" class="inline-flex items-center justify-center gap-2 rounded-lg border border-white/15 bg-white/5 px-5 py-3 text-sm font-semibold text-slate-100 hover:bg-white/10 transition-colors">
                        See how it works
                    </a>
                </div>
            </div>

            {{-- Campaign -> matched writer mockup --}}
            <div class="relative lg:pl-6">
                <div class="absolute -inset-6 bg-emerald-500/20 rounded-[2.5rem] blur-3xl" aria-hidden="true"></div>
                <div class="relative space-y-3">
                    <div class="bg-white rounded-xl p-4 shadow-xl">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="grid place-items-center h-7 w-7 rounded-lg bg-emerald-50 text-emerald-600">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 12h6M9 16h6M9 8h6M5 4h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z" /></svg>
                            </div>
                            <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Your announcement</span>
                            <span class="ml-auto text-[10px] text-slate-400">1d ago</span>
                        </div>
                        <div class="text-sm font-medium text-slate-900 leading-snug">"Cedar Coast unveils 40km coastal trail and autumn food festival"</div>
                        <div class="text-xs text-slate-500 mt-1">Visit Cedar Coast · Regional DMO</div>
                    </div>

                    <div class="flex justify-center -my-1.5 relative z-10">
                        <div class="grid place-items-center h-8 w-8 rounded-full bg-gradient-to-br from-emerald-500 via-teal-500 to-cyan-500 shadow-lg ring-4 ring-slate-900">
                            <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 5v14M19 12l-7 7-7-7" /></svg>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                        <div class="px-5 py-2.5 bg-emerald-50 border-b border-slate-100 flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-60 animate-ping"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span class="text-xs font-semibold text-slate-700">PrComet surfaces your top match</span>
                        </div>
                        <div class="p-5">
                            <div class="flex items-start gap-3 mb-4">
                                <div class="grid place-items-center h-11 w-11 rounded-full bg-gradient-to-br from-emerald-500 via-teal-500 to-cyan-500 text-white font-bold text-sm ring-2 ring-white shadow shrink-0">PA</div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-semibold text-slate-900 leading-tight">Priya Anand</div>
                                    <div class="text-xs text-slate-500">Travel writer · Weekend Wanderer</div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="text-2xl font-bold tabular leading-none">
                                        <span class="bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-600 bg-clip-text text-transparent">82</span><span class="text-base text-slate-400 font-semibold">%</span>
                                    </div>
                                    <div class="text-[10px] uppercase tracking-wider text-emerald-700 font-semibold mt-1">High fit</div>
                                </div>
                            </div>
                            <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Why she's a fit</div>
                            <p class="text-sm text-slate-700 leading-relaxed">
                                Building an autumn coastal-getaways round-up right now. Your trail and festival are exactly the kind of fresh pick she is looking for.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="relative h-px bg-white/10"></div>
    </section>

    {{-- ── The cost of the empty calendar ── --}}
    <section class="bg-slate-50 border-y border-slate-200/70 py-16 lg:py-24">
        <div class="max-w-5xl mx-auto px-6">
            <div class="max-w-2xl" data-reveal>
                <p class="text-xs font-semibold uppercase tracking-wider text-rose-600">The cost of being overlooked</p>
                <h2 class="mt-3 text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900 [text-wrap:balance]">
                    A great season nobody wrote about.
                </h2>
                <p class="mt-4 text-lg text-slate-600 leading-relaxed">
                    Travel desks have shrunk, freelancers churn, and the writer who covered your region last year may be on a different beat now. Without earned coverage, even a standout season slips by unbooked, while destinations that got featured fill their rooms.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Without coverage --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" data-reveal>
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Without coverage</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">Soft season, empty rooms</div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-md bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                            Flat
                        </span>
                    </div>
                    <svg class="mt-5 w-full h-36" viewBox="0 0 320 120" fill="none" preserveAspectRatio="none" aria-hidden="true">
                        <line x1="0" y1="30" x2="320" y2="30" stroke="#f1f5f9" stroke-width="1" />
                        <line x1="0" y1="60" x2="320" y2="60" stroke="#f1f5f9" stroke-width="1" />
                        <line x1="0" y1="90" x2="320" y2="90" stroke="#f1f5f9" stroke-width="1" />
                        <path class="chart-draw" d="M0 80 L60 78 L90 66 L110 82 L150 79 L200 80 L260 78 L320 81"
                              stroke="#94a3b8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="90" cy="66" r="3" fill="#94a3b8" />
                    </svg>
                    <div class="mt-4 flex items-start gap-2 text-xs text-slate-500">
                        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" /></svg>
                        <span>The festival happens, a press release goes out, and the round-ups feature someone else. Bookings stay flat.</span>
                    </div>
                </div>

                {{-- With coverage --}}
                <div class="rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm ring-1 ring-emerald-100" data-reveal>
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wider text-emerald-600">With coverage</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">Featured, then booked</div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 8-8M21 7v6" /></svg>
                            Rising
                        </span>
                    </div>
                    <svg class="mt-5 w-full h-36" viewBox="0 0 320 120" fill="none" preserveAspectRatio="none" aria-hidden="true">
                        <defs>
                            <linearGradient id="tourFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#10b981" stop-opacity="0.18" />
                                <stop offset="100%" stop-color="#10b981" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        <line x1="0" y1="30" x2="320" y2="30" stroke="#f1f5f9" stroke-width="1" />
                        <line x1="0" y1="60" x2="320" y2="60" stroke="#f1f5f9" stroke-width="1" />
                        <line x1="0" y1="90" x2="320" y2="90" stroke="#f1f5f9" stroke-width="1" />
                        <path d="M0 92 L70 88 L95 58 L150 50 L200 36 L260 24 L320 16 L320 120 L0 120 Z" fill="url(#tourFill)" />
                        <path class="chart-draw" d="M0 92 L70 88 L95 58 L150 50 L200 36 L260 24 L320 16"
                              stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="95" cy="58" r="3.5" fill="#10b981" />
                    </svg>
                    <div class="mt-4 flex items-start gap-2 text-xs text-slate-600">
                        <svg class="h-4 w-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        <span>The same season, featured in the right round-ups, fills the booking window with new visitors.</span>
                    </div>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach ([
                    ['title' => 'Wasted ad spend', 'body' => 'Paid placements vanish when the budget runs out. Earned features keep working and travelers trust them more.'],
                    ['title' => 'Missed booking windows', 'body' => 'Travel coverage runs to a calendar. Miss the planning window and the season is gone for another year.'],
                    ['title' => 'Lost to louder regions', 'body' => 'Neighboring destinations capture the round-ups, the searches, and the visitors that could have been yours.'],
                ] as $cost)
                    <div class="rounded-xl border border-slate-200 bg-white p-5" data-reveal>
                        <h3 class="text-sm font-semibold text-slate-900">{{ $cost['title'] }}</h3>
                        <p class="mt-1.5 text-sm text-slate-600 leading-relaxed">{{ $cost['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── Earned beats paid (signature insight) ── --}}
    <section class="relative overflow-hidden bg-slate-900 text-white">
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute top-0 left-1/3 h-[360px] w-[360px] rounded-full bg-emerald-500/15 blur-3xl"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-6 lg:px-8 py-16 lg:py-24">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <div data-reveal>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-300">Earned beats paid</p>
                    <h2 class="mt-4 text-3xl lg:text-4xl font-semibold tracking-tight leading-tight [text-wrap:balance]">
                        Travelers trust a recommendation, not an ad.
                    </h2>
                    <p class="mt-5 text-lg text-slate-300 leading-relaxed">
                        A spot on a "where to go this year" list or a feature in the right travel section can do more than a paid campaign, and it keeps working long after a campaign ends. People plan trips around what writers and creators they trust recommend.
                    </p>
                    <p class="mt-4 text-lg text-slate-300 leading-relaxed">
                        The catch is reaching the right writer while the editorial window is still open. That is exactly what discovery is for.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-6 lg:p-8" data-reveal>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Where travelers decide</p>
                    <ul class="mt-5 space-y-4">
                        @foreach ([
                            ['Travel & lifestyle features', 'A destination feature reaches people already in planning mode, with the credibility a banner ad never has.'],
                            ['Seasonal round-ups', 'The "best of" and "where to go" lists shape itineraries. Being included is worth a season of ad spend.'],
                            ['Trusted creators', 'Newsletters and travel creators turn a mention into bookings from an audience that already listens to them.'],
                        ] as $row)
                            <li class="flex items-start gap-3">
                                <span class="grid place-items-center h-6 w-6 shrink-0 rounded-md bg-gradient-to-br from-emerald-500 to-cyan-500 text-white">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                </span>
                                <div>
                                    <div class="text-sm font-semibold text-white">{{ $row[0] }}</div>
                                    <p class="mt-1 text-sm text-slate-300 leading-relaxed">{{ $row[1] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="relative h-px bg-white/10"></div>
    </section>

    {{-- ── How PrComet helps ── --}}
    <section id="how" class="bg-slate-50 border-b border-slate-200/70 py-16 lg:py-24">
        <div class="max-w-5xl mx-auto px-6">
            <div class="max-w-2xl" data-reveal>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">How PrComet helps</p>
                <h2 class="mt-3 text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900">Reach the writers who send travelers your way.</h2>
            </div>
            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ([
                    ['Travel & lifestyle desks', 'We track the journalists, freelancers, and creators currently covering travel, the outdoors, food, and culture in your region and beyond.'],
                    ['Seasonal & timely', 'Tie your festival or campaign to the writers working on relevant round-ups right now, while the editorial window is still open.'],
                    ['Why they fit', 'Each match explains the connection (a recent piece, a beat, a past visit) so your pitch reads as a tip, not a blast.'],
                ] as $i => $c)
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all" data-reveal>
                        <div class="grid place-items-center h-9 w-9 rounded-lg bg-gradient-to-br from-emerald-500 to-cyan-500 text-white font-semibold text-sm">{{ $i + 1 }}</div>
                        <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ $c[0] }}</h3>
                        <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ $c[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── Catalysts -> coverage ── --}}
    <section class="max-w-5xl mx-auto px-6 py-16 lg:py-24">
        <div class="max-w-2xl" data-reveal>
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Every season is a story</p>
            <h2 class="mt-3 text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900">Turn the calendar into coverage.</h2>
        </div>
        <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 gap-6">
            @foreach ([
                ['icon' => 'M8 7V3m8 4V3M4 11h16M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z', 'title' => 'Festivals & events', 'body' => 'Match a marquee event to the writers building seasonal and weekend round-ups for your region.'],
                ['icon' => 'M3 12l4-4 4 4 4-6 6 6M3 20h18', 'title' => 'New attractions & trails', 'body' => 'Reach the travel and outdoors journalists looking for what is new to recommend.'],
                ['icon' => 'M12 8v8m0 0l-3-3m3 3l3-3M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'title' => 'Seasonal campaigns', 'body' => 'Surface the right desks ahead of peak booking windows, when their planning coverage runs.'],
                ['icon' => 'M12 2l2.4 7.4H22l-6 4.6 2.3 7.4-6.3-4.6L5.7 21l2.3-7.4-6-4.6h7.6z', 'title' => 'Awards & rankings', 'body' => 'Find the journalists who cover "best of" lists and destination accolades.'],
            ] as $u)
                <div class="group flex gap-4 rounded-2xl border border-slate-200 bg-white p-6 hover:border-emerald-300 hover:shadow-lg transition-all" data-reveal>
                    <div class="grid place-items-center h-11 w-11 shrink-0 rounded-xl bg-gradient-to-br from-emerald-100 to-cyan-100 text-emerald-700 group-hover:from-emerald-500 group-hover:to-cyan-500 group-hover:text-white transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="{{ $u['icon'] }}" /></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">{{ $u['title'] }}</h3>
                        <p class="mt-1.5 text-sm text-slate-600 leading-relaxed">{{ $u['body'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ── From campaign to bookings: walkthrough ── --}}
    <section class="bg-slate-50 border-y border-slate-200/70 py-16 lg:py-24">
        <div class="max-w-6xl mx-auto px-6">
            <div class="max-w-2xl" data-reveal>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">From campaign to bookings</p>
                <h2 class="mt-3 text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900">Watch a season fill up.</h2>
                <p class="mt-4 text-lg text-slate-600 leading-relaxed">
                    The same coastal trail and festival from the top of the page, followed through to a fuller booking window.
                </p>
            </div>

            <div class="mt-14 relative">
                <div class="hidden lg:block absolute left-1/2 top-2 bottom-2 w-px -translate-x-1/2 bg-slate-200"></div>
                <div class="space-y-12 lg:space-y-20">

                    {{-- Step 1 --}}
                    <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center" data-reveal>
                        <div class="lg:pr-12 lg:text-right">
                            <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-emerald-600">
                                <span class="grid place-items-center h-6 w-6 rounded-full bg-gradient-to-br from-emerald-500 to-cyan-500 text-white text-[11px]">1</span>
                                You announce
                            </div>
                            <h3 class="mt-3 text-xl font-semibold text-slate-900">Your season becomes a trigger.</h3>
                            <p class="mt-2 text-slate-600 leading-relaxed">Point us at your newsroom once. Every festival, opening, or campaign kicks off discovery.</p>
                        </div>
                        <div class="mt-5 lg:mt-0">
                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-md max-w-sm lg:mr-auto">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="grid place-items-center h-7 w-7 rounded-lg bg-emerald-50 text-emerald-600">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 12h6M9 16h6M9 8h6M5 4h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z" /></svg>
                                    </div>
                                    <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Announcement</span>
                                    <span class="ml-auto inline-flex items-center gap-1 text-[10px] font-medium text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Ingested</span>
                                </div>
                                <div class="text-sm font-medium text-slate-900 leading-snug">"Cedar Coast unveils 40km coastal trail and autumn food festival"</div>
                                <div class="text-xs text-slate-500 mt-1">Visit Cedar Coast</div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 2 --}}
                    <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center" data-reveal>
                        <div class="lg:order-2 lg:pl-12">
                            <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-teal-600">
                                <span class="grid place-items-center h-6 w-6 rounded-full bg-gradient-to-br from-teal-500 to-cyan-500 text-white text-[11px]">2</span>
                                PrComet reads everything
                            </div>
                            <h3 class="mt-3 text-xl font-semibold text-slate-900">We do the reading for you.</h3>
                            <p class="mt-2 text-slate-600 leading-relaxed">Travel sections, outdoors columns, and creator newsletters, analyzed against your season this week.</p>
                        </div>
                        <div class="mt-5 lg:mt-0 lg:order-1">
                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-md max-w-sm lg:ml-auto">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Live corpus</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium text-teal-600">
                                        <span class="relative flex h-1.5 w-1.5"><span class="absolute inline-flex h-full w-full rounded-full bg-teal-500 opacity-60 animate-ping"></span><span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-teal-500"></span></span>
                                        Analyzing
                                    </span>
                                </div>
                                <ul class="space-y-2">
                                    @foreach ([['WW','Best coastal escapes this autumn'],['TT','New trails worth the drive'],['FF','Where to eat on the coast']] as $row)
                                        <li class="flex items-center gap-2.5">
                                            <div class="grid place-items-center h-6 w-6 rounded text-[9px] font-semibold text-white bg-gradient-to-br from-teal-500 to-cyan-600 shrink-0">{{ $row[0] }}</div>
                                            <span class="text-xs text-slate-700 truncate flex-1">{{ $row[1] }}</span>
                                            <svg class="h-3.5 w-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px]">
                                    <span class="text-slate-500">Items analyzed today</span>
                                    <span class="tabular font-semibold text-slate-900">96</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 3 --}}
                    <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center" data-reveal>
                        <div class="lg:pr-12 lg:text-right">
                            <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-cyan-600">
                                <span class="grid place-items-center h-6 w-6 rounded-full bg-gradient-to-br from-cyan-500 to-sky-500 text-white text-[11px]">3</span>
                                You see ranked matches
                            </div>
                            <h3 class="mt-3 text-xl font-semibold text-slate-900">A short list, ranked by fit.</h3>
                            <p class="mt-2 text-slate-600 leading-relaxed">The writers most likely to feature you, each scored, with the reasoning and citations behind it.</p>
                        </div>
                        <div class="mt-5 lg:mt-0">
                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-md max-w-sm lg:mr-auto">
                                <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-3">Top matches</div>
                                <ul class="space-y-2.5">
                                    @foreach ([['PA','Priya Anand','Weekend Wanderer','82'],['TM','Tom Reyes','Outdoors Weekly','77'],['JC','Joan Clarke','Coast & Country','73']] as $m)
                                        <li class="flex items-center gap-3">
                                            <div class="grid place-items-center h-7 w-7 rounded-full bg-gradient-to-br from-emerald-500 to-cyan-500 text-white text-[10px] font-semibold shrink-0">{{ $m[0] }}</div>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-xs font-medium text-slate-900 truncate">{{ $m[1] }}</div>
                                                <div class="text-[10px] text-slate-500 truncate">{{ $m[2] }}</div>
                                            </div>
                                            <span class="text-xs font-bold tabular bg-gradient-to-br from-emerald-600 to-cyan-600 bg-clip-text text-transparent">{{ $m[3] }}%</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Step 4 --}}
                    <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center" data-reveal>
                        <div class="lg:order-2 lg:pl-12">
                            <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-sky-600">
                                <span class="grid place-items-center h-6 w-6 rounded-full bg-gradient-to-br from-sky-500 to-indigo-500 text-white text-[11px]">4</span>
                                You make the pitch
                            </div>
                            <h3 class="mt-3 text-xl font-semibold text-slate-900">Reach out from evidence.</h3>
                            <p class="mt-2 text-slate-600 leading-relaxed">You get the reasoning and the citation. The relationship and the words stay yours, always.</p>
                        </div>
                        <div class="mt-5 lg:mt-0 lg:order-1">
                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-md max-w-sm lg:ml-auto">
                                <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Why Priya is a fit</div>
                                <p class="text-sm text-slate-700 leading-relaxed">Building "Best coastal escapes this autumn" now. Your trail and festival fit the brief exactly.</p>
                                <div class="mt-3 pl-3 border-l-2 border-emerald-300">
                                    <p class="text-[11px] text-slate-600 italic leading-relaxed">"the best autumn trips pair a walk with somewhere great to eat"</p>
                                    <div class="text-[10px] text-emerald-700 mt-1 font-medium">Weekend Wanderer, 2 days ago ↗</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Outcome --}}
                    <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center" data-reveal>
                        <div class="lg:pr-12 lg:text-right">
                            <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-emerald-600">
                                <span class="grid place-items-center h-6 w-6 rounded-full bg-emerald-500 text-white">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                </span>
                                The result
                            </div>
                            <h3 class="mt-3 text-xl font-semibold text-slate-900">Coverage, then visits.</h3>
                            <p class="mt-2 text-slate-600 leading-relaxed">The feature reaches travelers in planning mode, and the booking window fills ahead of the season.</p>
                        </div>
                        <div class="mt-5 lg:mt-0">
                            <div class="rounded-xl border border-emerald-200 bg-white p-4 shadow-md ring-1 ring-emerald-100 max-w-sm lg:mr-auto">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-semibold text-slate-900">Bookings this season</span>
                                    <span class="inline-flex items-center gap-1.5 rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 8-8M21 7v6" /></svg>
                                        Up
                                    </span>
                                </div>
                                <svg class="w-full h-28" viewBox="0 0 320 110" fill="none" preserveAspectRatio="none" aria-hidden="true">
                                    <defs>
                                        <linearGradient id="tourOut" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="#10b981" stop-opacity="0.2" />
                                            <stop offset="100%" stop-color="#10b981" stop-opacity="0" />
                                        </linearGradient>
                                    </defs>
                                    <path d="M0 90 L80 84 L105 54 L160 48 L210 34 L270 22 L320 14 L320 110 L0 110 Z" fill="url(#tourOut)" />
                                    <path class="chart-draw" d="M0 90 L80 84 L105 54 L160 48 L210 34 L270 22 L320 14" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                    <circle cx="105" cy="54" r="3.5" fill="#10b981" />
                                </svg>
                                <div class="mt-2 flex items-center justify-between text-[11px]">
                                    <span class="text-slate-500">Visitor enquiries</span>
                                    <span class="tabular font-semibold text-emerald-700">▲ 2.8x</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    {{-- ── Why PrComet ── --}}
    <section class="max-w-5xl mx-auto px-6 py-16 lg:py-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-start">
            <div data-reveal>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Why PrComet for DMOs</p>
                <h2 class="mt-3 text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900 [text-wrap:balance]">
                    Built for the way destinations get discovered.
                </h2>
                <p class="mt-4 text-lg text-slate-600 leading-relaxed">
                    Generic PR tools blast a static media list. PrComet keeps up with a churning travel-media landscape and reaches writers while their round-ups are still open.
                </p>
                <a href="#request-demo" class="mt-7 inline-flex items-center gap-2 rounded-lg bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800 transition-colors">
                    Request a demo
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" /></svg>
                </a>
            </div>
            <ul class="space-y-3" data-reveal>
                @foreach ([
                    'Tracks travel desks, freelancers, and creators, not a stale list',
                    'Times outreach to seasonal round-ups and planning windows',
                    'Covers travel, outdoors, food, and culture beats in your region',
                    'Tells you why each writer fits, with citations to their work',
                    'Earned coverage that outlasts any paid campaign',
                    'Discovery, not automation. You own the relationship',
                ] as $point)
                    <li class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-4">
                        <svg class="h-5 w-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        <span class="text-sm text-slate-700 leading-relaxed">{{ $point }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    <x-industry.cta
        source="Tourism Councils"
        heading="Get your destination in front of the right travel writer."
        sub="Tell us what's happening this season and who you want to reach. We'll show you the travel and regional journalists PrComet would surface."
    />

    <x-site-footer />
    <x-reveal-script />
</body>
</html>
