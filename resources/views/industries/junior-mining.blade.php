<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<x-articles.head
    title="PrComet for Junior Mining Companies"
    description="Drill results and financings only move the market if the right analysts and mining journalists pick them up. PrComet finds the writers already covering your commodity and jurisdiction, and tells you why each one fits."
    :canonical="route('industries.junior-mining')"
/>
<body class="font-sans antialiased text-slate-900 bg-white">

    <x-site-nav />

    {{-- ── Hero with stat counters + match mockup ── --}}
    <section class="relative overflow-hidden bg-slate-900 text-white">
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute -top-24 right-0 h-[520px] w-[520px] rounded-full bg-gradient-to-br from-amber-500/25 to-fuchsia-500/15 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/4 h-[420px] w-[420px] rounded-full bg-gradient-to-tr from-violet-500/20 to-indigo-500/20 blur-3xl"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-6 lg:px-8 pt-16 lg:pt-24 pb-16 lg:pb-20 grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-xs font-medium text-amber-200">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-60 animate-ping"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-amber-400"></span>
                    </span>
                    PrComet for Junior Mining
                </div>

                <h1 class="mt-6 text-5xl lg:text-7xl font-semibold tracking-tight leading-[1.03]">
                    Your drill result deserves <span class="text-amber-300">more than a wire release.</span>
                </h1>
                <p class="mt-6 max-w-xl text-lg text-slate-300 leading-relaxed">
                    A high-grade intercept means nothing if the analysts and reporters who move your commodity never see it. PrComet finds the writers already covering your metal, your jurisdiction, and your peers, and tells you exactly why each one is a fit.
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

            {{-- Press release -> matched writer device mockup --}}
            <div class="relative lg:pl-6">
                <div class="absolute -inset-6 bg-amber-500/20 rounded-[2.5rem] blur-3xl" aria-hidden="true"></div>
                <div class="relative space-y-3">
                    <div class="bg-white rounded-xl p-4 shadow-xl">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="grid place-items-center h-7 w-7 rounded-lg bg-amber-50 text-amber-600">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 12h6M9 16h6M9 8h6M5 4h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z" /></svg>
                            </div>
                            <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Your news release</span>
                            <span class="ml-auto text-[10px] text-slate-400">2h ago</span>
                        </div>
                        <div class="text-sm font-medium text-slate-900 leading-snug">"Aurelian intersects 12.4 g/t Au over 28m at Big Sky"</div>
                        <div class="text-xs text-slate-500 mt-1">Aurelian Gold · TSX-V: AUG</div>
                    </div>

                    <div class="flex justify-center -my-1.5 relative z-10">
                        <div class="grid place-items-center h-8 w-8 rounded-full bg-gradient-to-br from-amber-500 via-violet-500 to-fuchsia-500 shadow-lg ring-4 ring-slate-900">
                            <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 5v14M19 12l-7 7-7-7" /></svg>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                        <div class="px-5 py-2.5 bg-amber-50 border-b border-slate-100 flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-60 animate-ping"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span class="text-xs font-semibold text-slate-700">PrComet surfaces your top match</span>
                        </div>
                        <div class="p-5">
                            <div class="flex items-start gap-3 mb-4">
                                <div class="grid place-items-center h-11 w-11 rounded-full bg-gradient-to-br from-amber-500 via-violet-500 to-fuchsia-500 text-white font-bold text-sm ring-2 ring-white shadow shrink-0">RS</div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-semibold text-slate-900 leading-tight">Robert Sinclair</div>
                                    <div class="text-xs text-slate-500">Senior reporter · Mining.com</div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="text-2xl font-bold tabular leading-none">
                                        <span class="bg-gradient-to-br from-amber-600 via-violet-600 to-fuchsia-600 bg-clip-text text-transparent">87</span><span class="text-base text-slate-400 font-semibold">%</span>
                                    </div>
                                    <div class="text-[10px] uppercase tracking-wider text-emerald-700 font-semibold mt-1">High fit</div>
                                </div>
                            </div>
                            <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Why he's a fit</div>
                            <p class="text-sm text-slate-700 leading-relaxed">
                                Wrote about Walker Lane high-grade gold 4 days ago. Your intercept extends his thesis, and he has a track record of follow-up coverage on names he flags.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="relative h-px bg-white/10"></div>
    </section>

    {{-- ── The cost of being ignored: stagnant price / illiquidity ── --}}
    <section class="bg-slate-50 border-y border-slate-200/70 py-16 lg:py-24">
        <div class="max-w-5xl mx-auto px-6">
            <div class="max-w-2xl" data-reveal>
                <p class="text-xs font-semibold uppercase tracking-wider text-rose-600">The cost of silence</p>
                <h2 class="mt-3 text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900 [text-wrap:balance]">
                    A great hole and a dead chart.
                </h2>
                <p class="mt-4 text-lg text-slate-600 leading-relaxed">
                    Without coverage, even strong results sink into a thin, sideways tape. No new eyes, no volume, no re-rate. The story that should have moved the stock never reaches the people who would have bought it.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Without coverage: flat, illiquid --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" data-reveal>
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Without coverage</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">Thin float, no re-rate</div>
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
                        {{-- a drill-result blip that fades back to flat --}}
                        <path class="chart-draw" d="M0 78 L60 76 L90 60 L110 80 L150 77 L200 79 L260 76 L320 80"
                              stroke="#94a3b8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="90" cy="60" r="3" fill="#94a3b8" />
                    </svg>
                    <div class="mt-4 flex items-start gap-2 text-xs text-slate-500">
                        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" /></svg>
                        <span>The intercept barely registers. Volume dries up within days and the price drifts back to where it started.</span>
                    </div>
                </div>

                {{-- With coverage: stepped re-rate --}}
                <div class="rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm ring-1 ring-emerald-100" data-reveal>
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wider text-emerald-600">With coverage</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">New eyes, real volume</div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 8-8M21 7v6" /></svg>
                            Re-rate
                        </span>
                    </div>
                    <svg class="mt-5 w-full h-36" viewBox="0 0 320 120" fill="none" preserveAspectRatio="none" aria-hidden="true">
                        <defs>
                            <linearGradient id="rerateFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#10b981" stop-opacity="0.18" />
                                <stop offset="100%" stop-color="#10b981" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        <line x1="0" y1="30" x2="320" y2="30" stroke="#f1f5f9" stroke-width="1" />
                        <line x1="0" y1="60" x2="320" y2="60" stroke="#f1f5f9" stroke-width="1" />
                        <line x1="0" y1="90" x2="320" y2="90" stroke="#f1f5f9" stroke-width="1" />
                        <path d="M0 92 L70 88 L95 58 L150 50 L200 36 L260 24 L320 16 L320 120 L0 120 Z" fill="url(#rerateFill)" />
                        <path class="chart-draw" d="M0 92 L70 88 L95 58 L150 50 L200 36 L260 24 L320 16"
                              stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="95" cy="58" r="3.5" fill="#10b981" />
                    </svg>
                    <div class="mt-4 flex items-start gap-2 text-xs text-slate-600">
                        <svg class="h-4 w-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        <span>The same intercept, explained to the right audience, pulls in fresh buyers and sustained volume.</span>
                    </div>
                </div>
            </div>

            {{-- What illiquidity actually costs --}}
            <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach ([
                    ['title' => 'Dilutive raises', 'body' => 'A thin, depressed stock means every financing prices low and hands away more of the company.'],
                    ['title' => 'No institutional bid', 'body' => 'Funds need liquidity to enter and exit. Without volume, you are off the buy list before the meeting.'],
                    ['title' => 'A discount that sticks', 'body' => 'Quiet names trade below peers on the same ounces. The market cannot reward a story it never hears.'],
                ] as $cost)
                    <div class="rounded-xl border border-slate-200 bg-white p-5" data-reveal>
                        <h3 class="text-sm font-semibold text-slate-900">{{ $cost['title'] }}</h3>
                        <p class="mt-1.5 text-sm text-slate-600 leading-relaxed">{{ $cost['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── Barbershop liquidity ── --}}
    <section class="relative overflow-hidden bg-slate-900 text-white">
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute top-0 left-1/3 h-[360px] w-[360px] rounded-full bg-amber-500/15 blur-3xl"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-6 lg:px-8 py-16 lg:py-24">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <div data-reveal>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-300">Barbershop liquidity</p>
                    <h2 class="mt-4 text-3xl lg:text-4xl font-semibold tracking-tight leading-tight [text-wrap:balance]">
                        Liquidity is a conversation. You have to be in it.
                    </h2>
                    <p class="mt-5 text-lg text-slate-300 leading-relaxed">
                        The retail investor funding the next junior isn't pulling your SEDAR filings. They heard a name at the barbershop, saw it in the Financial Times, caught it on a podcast on the drive home. Liquidity comes from being part of the story retail is already telling each other, not from the technical disclosure almost no one reads.
                    </p>
                    <p class="mt-4 text-lg text-slate-300 leading-relaxed">
                        That conversation happens in the mainstream and trade financial press. If your news never reaches the writers who shape it, your float stays thin and your raise gets harder, no matter how good the hole looked.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-6 lg:p-8" data-reveal>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Where retail actually hears about you</p>
                    <ul class="mt-5 space-y-4">
                        @foreach ([
                            ['The financial press', 'A mention in the FT, a wire pickup, or a sector column puts your name in front of investors who would never open a filing.'],
                            ['Newsletters & podcasts', 'Resource newsletters and mining podcasts are where engaged retail forms a watchlist. One feature can move a float.'],
                            ['The room next door', 'Coverage compounds. The barbershop tip, the group chat, the conference hallway all start with something someone read.'],
                        ] as $row)
                            <li class="flex items-start gap-3">
                                <span class="grid place-items-center h-6 w-6 shrink-0 rounded-md bg-gradient-to-br from-amber-500 to-fuchsia-500 text-white">
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

    {{-- ── How PrComet helps (numbered feature cards) ── --}}
    <section id="how" class="bg-slate-50 border-b border-slate-200/70 py-16 lg:py-24">
        <div class="max-w-5xl mx-auto px-6">
            <div class="max-w-2xl" data-reveal>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">How PrComet helps</p>
                <h2 class="mt-3 text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900">Built for the way resource news actually travels.</h2>
            </div>
            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ([
                    ['Commodity & jurisdiction aware', 'We track who is writing about gold in Nevada, lithium in Argentina, or copper in the DRC right now, not who covered "mining" five years ago.'],
                    ['Reads the technical story', 'PrComet ingests your release, understands the grade, width, and significance, and matches it to writers whose recent coverage your result extends.'],
                    ['Track records, not hype', 'See which analysts have followed up on companies they flagged, so you reach out from a position of evidence, not a cold pitch.'],
                ] as $i => $c)
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all" data-reveal>
                        <div class="grid place-items-center h-9 w-9 rounded-lg bg-gradient-to-br from-amber-500 to-fuchsia-500 text-white font-semibold text-sm">{{ $i + 1 }}</div>
                        <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ $c[0] }}</h3>
                        <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ $c[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── Catalysts -> coverage (visual card grid) ── --}}
    <section class="max-w-5xl mx-auto px-6 py-16 lg:py-24">
        <div class="max-w-2xl" data-reveal>
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Every catalyst is a chance to be covered</p>
            <h2 class="mt-3 text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900">From the drill core to the front page.</h2>
        </div>
        <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 gap-6">
            @foreach ([
                ['icon' => 'M12 3v18M5 10l7-7 7 7', 'title' => 'Drill results', 'body' => 'Match a high-grade intercept to the analysts already writing about the trend or district it belongs to.'],
                ['icon' => 'M3 12h18M12 3v18', 'title' => 'Financings & raises', 'body' => 'Reach the writers covering capital flows into your commodity, so the raise lands as a signal of momentum.'],
                ['icon' => 'M4 6h16M4 12h16M4 18h10', 'title' => 'Resource estimates & PEAs', 'body' => 'Surface the technical reporters and newsletter writers who can translate an NI 43-101 for investors.'],
                ['icon' => 'M8 7h12M8 12h12M8 17h12M3 7h.01M3 12h.01M3 17h.01', 'title' => 'M&A and JV news', 'body' => 'Find the people tracking consolidation in your space before your competitors brief them first.'],
            ] as $u)
                <div class="group flex gap-4 rounded-2xl border border-slate-200 bg-white p-6 hover:border-amber-300 hover:shadow-lg transition-all" data-reveal>
                    <div class="grid place-items-center h-11 w-11 shrink-0 rounded-xl bg-gradient-to-br from-amber-100 to-fuchsia-100 text-amber-700 group-hover:from-amber-500 group-hover:to-fuchsia-500 group-hover:text-white transition-colors">
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

    {{-- ── From release to re-rate: visual walkthrough ── --}}
    <section class="bg-slate-50 border-y border-slate-200/70 py-16 lg:py-24">
        <div class="max-w-6xl mx-auto px-6">
            <div class="max-w-2xl" data-reveal>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">From release to re-rate</p>
                <h2 class="mt-3 text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900">Watch a result become liquidity.</h2>
                <p class="mt-4 text-lg text-slate-600 leading-relaxed">
                    The same drill hole from the top of the page, followed all the way through to fresh volume on the tape.
                </p>
            </div>

            {{-- Vertical rail with alternating copy / mockup rows --}}
            <div class="mt-14 relative">
                <div class="hidden lg:block absolute left-1/2 top-2 bottom-2 w-px -translate-x-1/2 bg-slate-200"></div>

                <div class="space-y-12 lg:space-y-20">

                    {{-- Step 1: You publish --}}
                    <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center" data-reveal>
                        <div class="lg:pr-12 lg:text-right">
                            <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-amber-600">
                                <span class="grid place-items-center h-6 w-6 rounded-full bg-gradient-to-br from-amber-500 to-fuchsia-500 text-white text-[11px]">1</span>
                                You publish
                            </div>
                            <h3 class="mt-3 text-xl font-semibold text-slate-900">Your release becomes a trigger.</h3>
                            <p class="mt-2 text-slate-600 leading-relaxed">Point us at your news feed once. Every release you put out kicks off discovery, automatically.</p>
                        </div>
                        <div class="mt-5 lg:mt-0">
                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-md max-w-sm lg:mr-auto">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="grid place-items-center h-7 w-7 rounded-lg bg-amber-50 text-amber-600">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 12h6M9 16h6M9 8h6M5 4h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z" /></svg>
                                    </div>
                                    <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">News release</span>
                                    <span class="ml-auto inline-flex items-center gap-1 text-[10px] font-medium text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Ingested</span>
                                </div>
                                <div class="text-sm font-medium text-slate-900 leading-snug">"Aurelian intersects 12.4 g/t Au over 28m at Big Sky"</div>
                                <div class="text-xs text-slate-500 mt-1">Aurelian Gold · TSX-V: AUG</div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 2: PrComet reads everything --}}
                    <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center" data-reveal>
                        <div class="lg:order-2 lg:pl-12">
                            <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-violet-600">
                                <span class="grid place-items-center h-6 w-6 rounded-full bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white text-[11px]">2</span>
                                PrComet reads everything
                            </div>
                            <h3 class="mt-3 text-xl font-semibold text-slate-900">We do the reading for you.</h3>
                            <p class="mt-2 text-slate-600 leading-relaxed">Thousands of articles, podcasts, and newsletters in your space, analyzed against your result this week.</p>
                        </div>
                        <div class="mt-5 lg:mt-0 lg:order-1">
                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-md max-w-sm lg:ml-auto">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Live corpus</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium text-violet-600">
                                        <span class="relative flex h-1.5 w-1.5"><span class="absolute inline-flex h-full w-full rounded-full bg-violet-500 opacity-60 animate-ping"></span><span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-violet-500"></span></span>
                                        Analyzing
                                    </span>
                                </div>
                                <ul class="space-y-2">
                                    @foreach ([['MN','Nevada drill season heats up'],['EI','Walker Lane: the names to watch'],['MS','Why high-grade gold is back']] as $row)
                                        <li class="flex items-center gap-2.5">
                                            <div class="grid place-items-center h-6 w-6 rounded text-[9px] font-semibold text-white bg-gradient-to-br from-violet-500 to-fuchsia-600 shrink-0">{{ $row[0] }}</div>
                                            <span class="text-xs text-slate-700 truncate flex-1">{{ $row[1] }}</span>
                                            <svg class="h-3.5 w-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px]">
                                    <span class="text-slate-500">Items analyzed today</span>
                                    <span class="tabular font-semibold text-slate-900">142</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 3: Ranked matches --}}
                    <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center" data-reveal>
                        <div class="lg:pr-12 lg:text-right">
                            <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-fuchsia-600">
                                <span class="grid place-items-center h-6 w-6 rounded-full bg-gradient-to-br from-fuchsia-500 to-pink-500 text-white text-[11px]">3</span>
                                You see ranked matches
                            </div>
                            <h3 class="mt-3 text-xl font-semibold text-slate-900">A short list, ranked by fit.</h3>
                            <p class="mt-2 text-slate-600 leading-relaxed">The writers most likely to engage, each scored, with the reasoning and citations behind the score.</p>
                        </div>
                        <div class="mt-5 lg:mt-0">
                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-md max-w-sm lg:mr-auto">
                                <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-3">Top matches</div>
                                <ul class="space-y-2.5">
                                    @foreach ([['RS','Robert Sinclair','Mining.com','87'],['KL','Kerry Lutz','Stock Education','81'],['BC','Brent Cook','Exploration Insights','76']] as $m)
                                        <li class="flex items-center gap-3">
                                            <div class="grid place-items-center h-7 w-7 rounded-full bg-gradient-to-br from-amber-500 to-fuchsia-500 text-white text-[10px] font-semibold shrink-0">{{ $m[0] }}</div>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-xs font-medium text-slate-900 truncate">{{ $m[1] }}</div>
                                                <div class="text-[10px] text-slate-500 truncate">{{ $m[2] }}</div>
                                            </div>
                                            <span class="text-xs font-bold tabular bg-gradient-to-br from-amber-600 to-fuchsia-600 bg-clip-text text-transparent">{{ $m[3] }}%</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Step 4: The pitch --}}
                    <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center" data-reveal>
                        <div class="lg:order-2 lg:pl-12">
                            <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-indigo-600">
                                <span class="grid place-items-center h-6 w-6 rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-white text-[11px]">4</span>
                                You make the pitch
                            </div>
                            <h3 class="mt-3 text-xl font-semibold text-slate-900">Reach out from evidence.</h3>
                            <p class="mt-2 text-slate-600 leading-relaxed">You get the reasoning and the citation. The relationship and the words stay yours, always.</p>
                        </div>
                        <div class="mt-5 lg:mt-0 lg:order-1">
                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-md max-w-sm lg:ml-auto">
                                <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Why Robert is a fit</div>
                                <p class="text-sm text-slate-700 leading-relaxed">Wrote "Walker Lane: the names to watch" 4 days ago. Your intercept extends his thesis directly.</p>
                                <div class="mt-3 pl-3 border-l-2 border-indigo-300">
                                    <p class="text-[11px] text-slate-600 italic leading-relaxed">"high-grade gold along the Walker Lane is the most interesting setup of the quarter"</p>
                                    <div class="text-[10px] text-indigo-700 mt-1 font-medium">Mining.com, 4 days ago ↗</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Outcome: re-rate / liquidity --}}
                    <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center" data-reveal>
                        <div class="lg:pr-12 lg:text-right">
                            <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-emerald-600">
                                <span class="grid place-items-center h-6 w-6 rounded-full bg-emerald-500 text-white">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                </span>
                                The result
                            </div>
                            <h3 class="mt-3 text-xl font-semibold text-slate-900">Coverage, then liquidity.</h3>
                            <p class="mt-2 text-slate-600 leading-relaxed">The story reaches the right audience, new buyers show up, and the chart finally does something.</p>
                        </div>
                        <div class="mt-5 lg:mt-0">
                            <div class="rounded-xl border border-emerald-200 bg-white p-4 shadow-md ring-1 ring-emerald-100 max-w-sm lg:mr-auto">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-semibold text-slate-900">AUG.V</span>
                                    <span class="inline-flex items-center gap-1.5 rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 8-8M21 7v6" /></svg>
                                        Re-rate
                                    </span>
                                </div>
                                <svg class="w-full h-28" viewBox="0 0 320 110" fill="none" preserveAspectRatio="none" aria-hidden="true">
                                    <defs>
                                        <linearGradient id="walkFill" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="#10b981" stop-opacity="0.2" />
                                            <stop offset="100%" stop-color="#10b981" stop-opacity="0" />
                                        </linearGradient>
                                    </defs>
                                    <path d="M0 86 L80 82 L105 52 L160 46 L210 32 L270 20 L320 12 L320 110 L0 110 Z" fill="url(#walkFill)" />
                                    <path class="chart-draw" d="M0 86 L80 82 L105 52 L160 46 L210 32 L270 20 L320 12" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                    <circle cx="105" cy="52" r="3.5" fill="#10b981" />
                                </svg>
                                <div class="mt-2 flex items-center justify-between text-[11px]">
                                    <span class="text-slate-500">Volume</span>
                                    <span class="tabular font-semibold text-emerald-700">▲ 4.2x avg</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    {{-- ── Why PrComet (value-prop list) ── --}}
    <section class="max-w-5xl mx-auto px-6 py-16 lg:py-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-start">
            <div data-reveal>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Why PrComet for juniors</p>
                <h2 class="mt-3 text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900 [text-wrap:balance]">
                    Built by people who understand resource markets.
                </h2>
                <p class="mt-4 text-lg text-slate-600 leading-relaxed">
                    Generic PR tools don't know a porphyry from a placer. PrComet is tuned for the way mining stories are told, scrutinized, and funded.
                </p>
                <a href="#request-demo" class="mt-7 inline-flex items-center gap-2 rounded-lg bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800 transition-colors">
                    Request a demo
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" /></svg>
                </a>
            </div>
            <ul class="space-y-3" data-reveal>
                @foreach ([
                    'Commodity- and jurisdiction-aware matching, not keyword spam',
                    'Understands grades, widths, and what makes a result material',
                    'Surfaces analysts, newsletters, and podcasters, not just newspapers',
                    'Tells you why each writer fits, with citations to their work',
                    'Flags who has a track record of following the names they cover',
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
        source="Junior Mining"
        heading="Get your next drill result in front of the right desk."
        sub="Tell us your commodity, your jurisdiction, and what you've got coming. We'll show you the analysts and writers PrComet would surface for it."
    />

    <x-site-footer />
    <x-reveal-script />
</body>
</html>
