<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<x-articles.head
    title="PrComet for Manufacturing Companies"
    description="New lines, reshoring, automation, and big contracts are real stories if the right trade and business reporters hear about them. PrComet finds the writers covering your sector and tells you why each one fits."
    :canonical="route('industries.manufacturing')"
/>
<body class="font-sans antialiased text-slate-900 bg-white">

    <x-site-nav />

    {{-- ── Hero with match mockup ── --}}
    <section class="relative overflow-hidden bg-slate-900 text-white">
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute -top-24 right-0 h-[520px] w-[520px] rounded-full bg-gradient-to-br from-sky-500/25 to-indigo-500/15 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/4 h-[420px] w-[420px] rounded-full bg-gradient-to-tr from-blue-500/20 to-violet-500/20 blur-3xl"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-6 lg:px-8 pt-16 lg:pt-24 pb-16 lg:pb-20 grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-xs font-medium text-sky-200">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-60 animate-ping"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-sky-400"></span>
                    </span>
                    PrComet for Manufacturing
                </div>

                <h1 class="mt-6 text-5xl lg:text-7xl font-semibold tracking-tight leading-[1.03]">
                    You build real things. <span class="text-sky-300">Get the credit.</span>
                </h1>
                <p class="mt-6 max-w-xl text-lg text-slate-300 leading-relaxed">
                    A new plant, a reshoring decision, an automation milestone, a landmark contract. These are exactly the stories trade and regional business reporters want. PrComet finds the ones already covering your sector and supply chain, and tells you why each is a fit.
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

            {{-- Announcement -> matched writer mockup --}}
            <div class="relative lg:pl-6">
                <div class="absolute -inset-6 bg-sky-500/20 rounded-[2.5rem] blur-3xl" aria-hidden="true"></div>
                <div class="relative space-y-3">
                    <div class="bg-white rounded-xl p-4 shadow-xl">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="grid place-items-center h-7 w-7 rounded-lg bg-sky-50 text-sky-600">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 12h6M9 16h6M9 8h6M5 4h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z" /></svg>
                            </div>
                            <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Your announcement</span>
                            <span class="ml-auto text-[10px] text-slate-400">3h ago</span>
                        </div>
                        <div class="text-sm font-medium text-slate-900 leading-snug">"Vanta Industries opens 120,000 sq ft automated plant, adds 90 jobs"</div>
                        <div class="text-xs text-slate-500 mt-1">Vanta Industries · Precision components</div>
                    </div>

                    <div class="flex justify-center -my-1.5 relative z-10">
                        <div class="grid place-items-center h-8 w-8 rounded-full bg-gradient-to-br from-sky-500 via-blue-500 to-indigo-500 shadow-lg ring-4 ring-slate-900">
                            <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 5v14M19 12l-7 7-7-7" /></svg>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                        <div class="px-5 py-2.5 bg-sky-50 border-b border-slate-100 flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-60 animate-ping"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span class="text-xs font-semibold text-slate-700">PrComet surfaces your top match</span>
                        </div>
                        <div class="p-5">
                            <div class="flex items-start gap-3 mb-4">
                                <div class="grid place-items-center h-11 w-11 rounded-full bg-gradient-to-br from-sky-500 via-blue-500 to-indigo-500 text-white font-bold text-sm ring-2 ring-white shadow shrink-0">DM</div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-semibold text-slate-900 leading-tight">Dana Mercer</div>
                                    <div class="text-xs text-slate-500">Industry editor · IndustryWeek</div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="text-2xl font-bold tabular leading-none">
                                        <span class="bg-gradient-to-br from-sky-600 via-blue-600 to-indigo-600 bg-clip-text text-transparent">84</span><span class="text-base text-slate-400 font-semibold">%</span>
                                    </div>
                                    <div class="text-[10px] uppercase tracking-wider text-emerald-700 font-semibold mt-1">High fit</div>
                                </div>
                            </div>
                            <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Why she's a fit</div>
                            <p class="text-sm text-slate-700 leading-relaxed">
                                Covers reshoring and factory automation weekly. Your new line is a concrete example of the trend she has been tracking all quarter.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="relative h-px bg-white/10"></div>
    </section>

    {{-- ── The cost of staying quiet ── --}}
    <section class="bg-slate-50 border-y border-slate-200/70 py-16 lg:py-24">
        <div class="max-w-5xl mx-auto px-6">
            <div class="max-w-2xl" data-reveal>
                <p class="text-xs font-semibold uppercase tracking-wider text-rose-600">The cost of staying quiet</p>
                <h2 class="mt-3 text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900 [text-wrap:balance]">
                    Best-kept secret is not a compliment.
                </h2>
                <p class="mt-4 text-lg text-slate-600 leading-relaxed">
                    Engineering-led companies tend to under-tell their story. Meanwhile the talent you want to hire, the customers evaluating you, and the officials deciding on incentives all form impressions from whatever coverage exists. When there is none, the gap gets filled by your louder competitors.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Without coverage --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" data-reveal>
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Without coverage</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">Flat inbound interest</div>
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
                        <path class="chart-draw" d="M0 80 L60 78 L90 64 L110 82 L150 79 L200 80 L260 78 L320 81"
                              stroke="#94a3b8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="90" cy="64" r="3" fill="#94a3b8" />
                    </svg>
                    <div class="mt-4 flex items-start gap-2 text-xs text-slate-500">
                        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" /></svg>
                        <span>The plant opens, a local notice runs, and nothing changes. Recruiters, buyers, and partners never hear about it.</span>
                    </div>
                </div>

                {{-- With coverage --}}
                <div class="rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm ring-1 ring-emerald-100" data-reveal>
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wider text-emerald-600">With coverage</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">Talent, customers, incentives</div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 8-8M21 7v6" /></svg>
                            Rising
                        </span>
                    </div>
                    <svg class="mt-5 w-full h-36" viewBox="0 0 320 120" fill="none" preserveAspectRatio="none" aria-hidden="true">
                        <defs>
                            <linearGradient id="mfgFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#10b981" stop-opacity="0.18" />
                                <stop offset="100%" stop-color="#10b981" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        <line x1="0" y1="30" x2="320" y2="30" stroke="#f1f5f9" stroke-width="1" />
                        <line x1="0" y1="60" x2="320" y2="60" stroke="#f1f5f9" stroke-width="1" />
                        <line x1="0" y1="90" x2="320" y2="90" stroke="#f1f5f9" stroke-width="1" />
                        <path d="M0 92 L70 88 L95 58 L150 50 L200 36 L260 24 L320 16 L320 120 L0 120 Z" fill="url(#mfgFill)" />
                        <path class="chart-draw" d="M0 92 L70 88 L95 58 L150 50 L200 36 L260 24 L320 16"
                              stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="95" cy="58" r="3.5" fill="#10b981" />
                    </svg>
                    <div class="mt-4 flex items-start gap-2 text-xs text-slate-600">
                        <svg class="h-4 w-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        <span>The same news, in the trade and regional press, brings in candidates, RFPs, and a stronger case for incentives.</span>
                    </div>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach ([
                    ['title' => 'Harder hiring', 'body' => 'Skilled candidates choose employers they have heard of. Silence cedes the talent pool to better-known rivals.'],
                    ['title' => 'Longer sales cycles', 'body' => 'Buyers vet suppliers by reputation. With no third-party proof, every deal starts from a colder place.'],
                    ['title' => 'Weaker leverage', 'body' => 'Grants and incentives favor visible employers. A documented track record strengthens every ask.'],
                ] as $cost)
                    <div class="rounded-xl border border-slate-200 bg-white p-5" data-reveal>
                        <h3 class="text-sm font-semibold text-slate-900">{{ $cost['title'] }}</h3>
                        <p class="mt-1.5 text-sm text-slate-600 leading-relaxed">{{ $cost['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── The quiet-leader trap (signature insight) ── --}}
    <section class="relative overflow-hidden bg-slate-900 text-white">
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute top-0 left-1/3 h-[360px] w-[360px] rounded-full bg-sky-500/15 blur-3xl"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-6 lg:px-8 py-16 lg:py-24">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <div data-reveal>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-300">The quiet-leader trap</p>
                    <h2 class="mt-4 text-3xl lg:text-4xl font-semibold tracking-tight leading-tight [text-wrap:balance]">
                        Reputation is decided whether you speak or not.
                    </h2>
                    <p class="mt-5 text-lg text-slate-300 leading-relaxed">
                        You can run the most advanced line in your region and still be invisible to the people who matter. Talent, customers, and officials build their picture of you from what they can find. If the record is thin, they fill in the blanks, usually in a competitor's favor.
                    </p>
                    <p class="mt-4 text-lg text-slate-300 leading-relaxed">
                        Trade and regional business journalists are how that record gets written. Reaching the right ones, with the milestones that matter, is how a quiet leader becomes the obvious choice.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-6 lg:p-8" data-reveal>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Who is forming an opinion of you</p>
                    <ul class="mt-5 space-y-4">
                        @foreach ([
                            ['Skilled talent', 'Engineers and operators weigh employer reputation before they ever apply. Coverage is recruiting.'],
                            ['Prospective customers', 'Buyers research suppliers long before the first call. Third-party stories do the convincing for you.'],
                            ['Local officials', 'The case for grants, zoning, and incentives is far easier when your impact is on the public record.'],
                        ] as $row)
                            <li class="flex items-start gap-3">
                                <span class="grid place-items-center h-6 w-6 shrink-0 rounded-md bg-gradient-to-br from-sky-500 to-indigo-500 text-white">
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
                <h2 class="mt-3 text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900">Reach the desks that cover industry.</h2>
            </div>
            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ([
                    ['Trade & vertical press', 'We track the specialist publications, newsletters, and analysts covering your exact sector, from industrial automation to food processing to aerospace.'],
                    ['Regional business desks', 'Plant expansions and hiring are local economic news. PrComet surfaces the regional reporters who cover jobs and investment in your area.'],
                    ['The reasoning, every time', 'Each match comes with why this reporter, this week, tied to something they actually wrote, so your outreach is relevant, not spray-and-pray.'],
                ] as $i => $c)
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all" data-reveal>
                        <div class="grid place-items-center h-9 w-9 rounded-lg bg-gradient-to-br from-sky-500 to-indigo-500 text-white font-semibold text-sm">{{ $i + 1 }}</div>
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
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Every milestone is a story</p>
            <h2 class="mt-3 text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900">Turn operations into coverage.</h2>
        </div>
        <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 gap-6">
            @foreach ([
                ['icon' => 'M3 21h18M5 21V7l8-4v18M19 21V11l-6-3', 'title' => 'Plant openings & expansions', 'body' => 'Match a new facility to the regional and trade reporters covering manufacturing investment and jobs.'],
                ['icon' => 'M4 7h16M4 12h16M4 17h10', 'title' => 'Reshoring & supply-chain wins', 'body' => 'Reach the writers tracking the reshoring story, one of the strongest narratives in industry right now.'],
                ['icon' => 'M12 8v8m0 0l-3-3m3 3l3-3M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'title' => 'Automation & technology', 'body' => 'Surface the reporters covering Industry 4.0, robotics, and the future of the factory floor.'],
                ['icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z', 'title' => 'Contracts & certifications', 'body' => 'Find the people who can frame a landmark order or new certification as proof of momentum.'],
            ] as $u)
                <div class="group flex gap-4 rounded-2xl border border-slate-200 bg-white p-6 hover:border-sky-300 hover:shadow-lg transition-all" data-reveal>
                    <div class="grid place-items-center h-11 w-11 shrink-0 rounded-xl bg-gradient-to-br from-sky-100 to-indigo-100 text-sky-700 group-hover:from-sky-500 group-hover:to-indigo-500 group-hover:text-white transition-colors">
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

    {{-- ── From announcement to advantage: walkthrough ── --}}
    <section class="bg-slate-50 border-y border-slate-200/70 py-16 lg:py-24">
        <div class="max-w-6xl mx-auto px-6">
            <div class="max-w-2xl" data-reveal>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">From announcement to advantage</p>
                <h2 class="mt-3 text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900">Watch a milestone become momentum.</h2>
                <p class="mt-4 text-lg text-slate-600 leading-relaxed">
                    The same plant opening from the top of the page, followed through to inbound talent and customers.
                </p>
            </div>

            <div class="mt-14 relative">
                <div class="hidden lg:block absolute left-1/2 top-2 bottom-2 w-px -translate-x-1/2 bg-slate-200"></div>
                <div class="space-y-12 lg:space-y-20">

                    {{-- Step 1 --}}
                    <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center" data-reveal>
                        <div class="lg:pr-12 lg:text-right">
                            <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-sky-600">
                                <span class="grid place-items-center h-6 w-6 rounded-full bg-gradient-to-br from-sky-500 to-indigo-500 text-white text-[11px]">1</span>
                                You announce
                            </div>
                            <h3 class="mt-3 text-xl font-semibold text-slate-900">Your news becomes a trigger.</h3>
                            <p class="mt-2 text-slate-600 leading-relaxed">Point us at your newsroom once. Every expansion, contract, or milestone kicks off discovery.</p>
                        </div>
                        <div class="mt-5 lg:mt-0">
                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-md max-w-sm lg:mr-auto">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="grid place-items-center h-7 w-7 rounded-lg bg-sky-50 text-sky-600">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 12h6M9 16h6M9 8h6M5 4h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z" /></svg>
                                    </div>
                                    <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Announcement</span>
                                    <span class="ml-auto inline-flex items-center gap-1 text-[10px] font-medium text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Ingested</span>
                                </div>
                                <div class="text-sm font-medium text-slate-900 leading-snug">"Vanta opens 120,000 sq ft automated plant, adds 90 jobs"</div>
                                <div class="text-xs text-slate-500 mt-1">Vanta Industries</div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 2 --}}
                    <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center" data-reveal>
                        <div class="lg:order-2 lg:pl-12">
                            <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-blue-600">
                                <span class="grid place-items-center h-6 w-6 rounded-full bg-gradient-to-br from-blue-500 to-indigo-500 text-white text-[11px]">2</span>
                                PrComet reads everything
                            </div>
                            <h3 class="mt-3 text-xl font-semibold text-slate-900">We do the reading for you.</h3>
                            <p class="mt-2 text-slate-600 leading-relaxed">Trade journals, regional business desks, and sector newsletters, analyzed against your news this week.</p>
                        </div>
                        <div class="mt-5 lg:mt-0 lg:order-1">
                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-md max-w-sm lg:ml-auto">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Live corpus</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium text-blue-600">
                                        <span class="relative flex h-1.5 w-1.5"><span class="absolute inline-flex h-full w-full rounded-full bg-blue-500 opacity-60 animate-ping"></span><span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-blue-500"></span></span>
                                        Analyzing
                                    </span>
                                </div>
                                <ul class="space-y-2">
                                    @foreach ([['IW','The reshoring boom, region by region'],['AM','Inside the lights-out factory'],['SB','Who is hiring in advanced manufacturing']] as $row)
                                        <li class="flex items-center gap-2.5">
                                            <div class="grid place-items-center h-6 w-6 rounded text-[9px] font-semibold text-white bg-gradient-to-br from-blue-500 to-indigo-600 shrink-0">{{ $row[0] }}</div>
                                            <span class="text-xs text-slate-700 truncate flex-1">{{ $row[1] }}</span>
                                            <svg class="h-3.5 w-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px]">
                                    <span class="text-slate-500">Items analyzed today</span>
                                    <span class="tabular font-semibold text-slate-900">118</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 3 --}}
                    <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center" data-reveal>
                        <div class="lg:pr-12 lg:text-right">
                            <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-indigo-600">
                                <span class="grid place-items-center h-6 w-6 rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-white text-[11px]">3</span>
                                You see ranked matches
                            </div>
                            <h3 class="mt-3 text-xl font-semibold text-slate-900">A short list, ranked by fit.</h3>
                            <p class="mt-2 text-slate-600 leading-relaxed">The reporters most likely to engage, each scored, with the reasoning and citations behind it.</p>
                        </div>
                        <div class="mt-5 lg:mt-0">
                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-md max-w-sm lg:mr-auto">
                                <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-3">Top matches</div>
                                <ul class="space-y-2.5">
                                    @foreach ([['DM','Dana Mercer','IndustryWeek','84'],['RP','Raj Patel','Regional Business','79'],['EL','Erin Lowe','Automation Today','74']] as $m)
                                        <li class="flex items-center gap-3">
                                            <div class="grid place-items-center h-7 w-7 rounded-full bg-gradient-to-br from-sky-500 to-indigo-500 text-white text-[10px] font-semibold shrink-0">{{ $m[0] }}</div>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-xs font-medium text-slate-900 truncate">{{ $m[1] }}</div>
                                                <div class="text-[10px] text-slate-500 truncate">{{ $m[2] }}</div>
                                            </div>
                                            <span class="text-xs font-bold tabular bg-gradient-to-br from-sky-600 to-indigo-600 bg-clip-text text-transparent">{{ $m[3] }}%</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Step 4 --}}
                    <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center" data-reveal>
                        <div class="lg:order-2 lg:pl-12">
                            <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-violet-600">
                                <span class="grid place-items-center h-6 w-6 rounded-full bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white text-[11px]">4</span>
                                You make the pitch
                            </div>
                            <h3 class="mt-3 text-xl font-semibold text-slate-900">Reach out from evidence.</h3>
                            <p class="mt-2 text-slate-600 leading-relaxed">You get the reasoning and the citation. The relationship and the words stay yours, always.</p>
                        </div>
                        <div class="mt-5 lg:mt-0 lg:order-1">
                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-md max-w-sm lg:ml-auto">
                                <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Why Dana is a fit</div>
                                <p class="text-sm text-slate-700 leading-relaxed">Wrote "The reshoring boom, region by region" last week. Your plant is a concrete local example.</p>
                                <div class="mt-3 pl-3 border-l-2 border-sky-300">
                                    <p class="text-[11px] text-slate-600 italic leading-relaxed">"the next wave of reshoring is being decided in mid-size towns"</p>
                                    <div class="text-[10px] text-sky-700 mt-1 font-medium">IndustryWeek, last week ↗</div>
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
                            <h3 class="mt-3 text-xl font-semibold text-slate-900">Coverage, then inbound.</h3>
                            <p class="mt-2 text-slate-600 leading-relaxed">The story reaches the right audience, and candidates, customers, and partners start coming to you.</p>
                        </div>
                        <div class="mt-5 lg:mt-0">
                            <div class="rounded-xl border border-emerald-200 bg-white p-4 shadow-md ring-1 ring-emerald-100 max-w-sm lg:mr-auto">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-semibold text-slate-900">Inbound this month</span>
                                    <span class="inline-flex items-center gap-1.5 rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 8-8M21 7v6" /></svg>
                                        Up
                                    </span>
                                </div>
                                <svg class="w-full h-28" viewBox="0 0 320 110" fill="none" preserveAspectRatio="none" aria-hidden="true">
                                    <defs>
                                        <linearGradient id="mfgOut" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="#10b981" stop-opacity="0.2" />
                                            <stop offset="100%" stop-color="#10b981" stop-opacity="0" />
                                        </linearGradient>
                                    </defs>
                                    <path d="M0 90 L80 84 L105 54 L160 48 L210 34 L270 22 L320 14 L320 110 L0 110 Z" fill="url(#mfgOut)" />
                                    <path class="chart-draw" d="M0 90 L80 84 L105 54 L160 48 L210 34 L270 22 L320 14" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                    <circle cx="105" cy="54" r="3.5" fill="#10b981" />
                                </svg>
                                <div class="mt-2 flex items-center justify-between text-[11px]">
                                    <span class="text-slate-500">Applications + RFPs</span>
                                    <span class="tabular font-semibold text-emerald-700">▲ 3.1x</span>
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
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Why PrComet for manufacturers</p>
                <h2 class="mt-3 text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900 [text-wrap:balance]">
                    Built for the desks that cover industry.
                </h2>
                <p class="mt-4 text-lg text-slate-600 leading-relaxed">
                    Generic PR tools treat a plant opening like a product launch. PrComet understands trade and regional coverage, and who actually writes it.
                </p>
                <a href="#request-demo" class="mt-7 inline-flex items-center gap-2 rounded-lg bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800 transition-colors">
                    Request a demo
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" /></svg>
                </a>
            </div>
            <ul class="space-y-3" data-reveal>
                @foreach ([
                    'Tracks trade journals, vertical newsletters, and analysts in your sector',
                    'Surfaces regional business desks that cover jobs and investment',
                    'Understands reshoring, automation, and supply-chain narratives',
                    'Tells you why each reporter fits, with citations to their work',
                    'Frames milestones for recruiting, sales, and incentives',
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
        source="Manufacturing"
        heading="Put your next milestone in front of the right reporter."
        sub="Tell us what you make and what's coming: an expansion, a contract, a new line. We'll show you the trade and business writers PrComet would surface."
    />

    <x-site-footer />
    <x-reveal-script />
</body>
</html>
