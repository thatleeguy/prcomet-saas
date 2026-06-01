<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<x-articles.head
    title="PrComet for Municipalities & Local Government"
    description="Infrastructure projects, public programs, and economic-development wins need to reach residents and regional media. PrComet finds the local reporters covering your community and tells you why each one fits."
    :canonical="route('industries.municipalities')"
/>
<body class="font-sans antialiased text-slate-900 bg-white">

    <x-site-nav />

    {{-- ── Hero with match mockup ── --}}
    <section class="relative overflow-hidden bg-slate-900 text-white">
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute -top-24 right-0 h-[520px] w-[520px] rounded-full bg-gradient-to-br from-blue-500/25 to-indigo-500/15 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/4 h-[420px] w-[420px] rounded-full bg-gradient-to-tr from-sky-500/20 to-violet-500/20 blur-3xl"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-6 lg:px-8 pt-16 lg:pt-24 pb-16 lg:pb-20 grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-xs font-medium text-blue-200">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-60 animate-ping"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-blue-400"></span>
                    </span>
                    PrComet for Municipalities
                </div>

                <h1 class="mt-6 text-5xl lg:text-7xl font-semibold tracking-tight leading-[1.03]">
                    Help residents <span class="text-blue-300">actually hear the news.</span>
                </h1>
                <p class="mt-6 max-w-xl text-lg text-slate-300 leading-relaxed">
                    A new transit line, a flood-mitigation project, a downtown revitalization, a major employer choosing your community. These matter to residents and to the businesses deciding where to invest. PrComet finds the local and regional reporters who cover your community and tells you why each one is a fit.
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

            {{-- Announcement -> matched reporter mockup --}}
            <div class="relative lg:pl-6">
                <div class="absolute -inset-6 bg-blue-500/20 rounded-[2.5rem] blur-3xl" aria-hidden="true"></div>
                <div class="relative space-y-3">
                    <div class="bg-white rounded-xl p-4 shadow-xl">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="grid place-items-center h-7 w-7 rounded-lg bg-blue-50 text-blue-600">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 12h6M9 16h6M9 8h6M5 4h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z" /></svg>
                            </div>
                            <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Your announcement</span>
                            <span class="ml-auto text-[10px] text-slate-400">5h ago</span>
                        </div>
                        <div class="text-sm font-medium text-slate-900 leading-snug">"City breaks ground on $40M flood-mitigation and riverfront project"</div>
                        <div class="text-xs text-slate-500 mt-1">City of Riverton · Public Works</div>
                    </div>

                    <div class="flex justify-center -my-1.5 relative z-10">
                        <div class="grid place-items-center h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 via-indigo-500 to-violet-500 shadow-lg ring-4 ring-slate-900">
                            <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 5v14M19 12l-7 7-7-7" /></svg>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                        <div class="px-5 py-2.5 bg-blue-50 border-b border-slate-100 flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-60 animate-ping"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span class="text-xs font-semibold text-slate-700">PrComet surfaces your top match</span>
                        </div>
                        <div class="p-5">
                            <div class="flex items-start gap-3 mb-4">
                                <div class="grid place-items-center h-11 w-11 rounded-full bg-gradient-to-br from-blue-500 via-indigo-500 to-violet-500 text-white font-bold text-sm ring-2 ring-white shadow shrink-0">MT</div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-semibold text-slate-900 leading-tight">Marcus Tran</div>
                                    <div class="text-xs text-slate-500">City hall reporter · Riverton Daily</div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="text-2xl font-bold tabular leading-none">
                                        <span class="bg-gradient-to-br from-blue-600 via-indigo-600 to-violet-600 bg-clip-text text-transparent">88</span><span class="text-base text-slate-400 font-semibold">%</span>
                                    </div>
                                    <div class="text-[10px] uppercase tracking-wider text-emerald-700 font-semibold mt-1">High fit</div>
                                </div>
                            </div>
                            <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Why he's a fit</div>
                            <p class="text-sm text-slate-700 leading-relaxed">
                                Covers city infrastructure and flooding closely. He has followed the riverfront debate for months and will want the groundbreaking details.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="relative h-px bg-white/10"></div>
    </section>

    {{-- ── The cost of the unread notice ── --}}
    <section class="bg-slate-50 border-y border-slate-200/70 py-16 lg:py-24">
        <div class="max-w-5xl mx-auto px-6">
            <div class="max-w-2xl" data-reveal>
                <p class="text-xs font-semibold uppercase tracking-wider text-rose-600">The cost of the unread notice</p>
                <h2 class="mt-3 text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900 [text-wrap:balance]">
                    A notice on the website is not communication.
                </h2>
                <p class="mt-4 text-lg text-slate-600 leading-relaxed">
                    As local newsrooms shrink and beats consolidate, important civic news struggles to reach the people it affects. When a project breaks ground or a program launches, posting a PDF is not enough. It needs to reach the reporters who can explain it to residents in context.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Without coverage --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" data-reveal>
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Without coverage</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">Confusion and rumor</div>
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
                        <span>The notice goes up, few residents see it, and the gap fills with speculation and social-media rumor.</span>
                    </div>
                </div>

                {{-- With coverage --}}
                <div class="rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm ring-1 ring-emerald-100" data-reveal>
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wider text-emerald-600">With coverage</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">Informed, engaged residents</div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 8-8M21 7v6" /></svg>
                            Rising
                        </span>
                    </div>
                    <svg class="mt-5 w-full h-36" viewBox="0 0 320 120" fill="none" preserveAspectRatio="none" aria-hidden="true">
                        <defs>
                            <linearGradient id="muniFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#10b981" stop-opacity="0.18" />
                                <stop offset="100%" stop-color="#10b981" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        <line x1="0" y1="30" x2="320" y2="30" stroke="#f1f5f9" stroke-width="1" />
                        <line x1="0" y1="60" x2="320" y2="60" stroke="#f1f5f9" stroke-width="1" />
                        <line x1="0" y1="90" x2="320" y2="90" stroke="#f1f5f9" stroke-width="1" />
                        <path d="M0 92 L70 88 L95 58 L150 50 L200 36 L260 24 L320 16 L320 120 L0 120 Z" fill="url(#muniFill)" />
                        <path class="chart-draw" d="M0 92 L70 88 L95 58 L150 50 L200 36 L260 24 L320 16"
                              stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="95" cy="58" r="3.5" fill="#10b981" />
                    </svg>
                    <div class="mt-4 flex items-start gap-2 text-xs text-slate-600">
                        <svg class="h-4 w-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        <span>The same project, explained by a trusted local reporter, reaches residents and builds support.</span>
                    </div>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach ([
                    ['title' => 'Rumor fills the vacuum', 'body' => 'When residents do not hear it from a credible source, social media supplies a version that is harder to correct.'],
                    ['title' => 'Lower program uptake', 'body' => 'Grants, rebates, and services go unused when the people they are meant to help never learn they exist.'],
                    ['title' => 'Eroded trust', 'body' => 'Communities that feel uninformed assume the worst. Clear, covered news is the foundation of public confidence.'],
                ] as $cost)
                    <div class="rounded-xl border border-slate-200 bg-white p-5" data-reveal>
                        <h3 class="text-sm font-semibold text-slate-900">{{ $cost['title'] }}</h3>
                        <p class="mt-1.5 text-sm text-slate-600 leading-relaxed">{{ $cost['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── The local-news gap (signature insight) ── --}}
    <section class="relative overflow-hidden bg-slate-900 text-white">
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute top-0 left-1/3 h-[360px] w-[360px] rounded-full bg-blue-500/15 blur-3xl"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-6 lg:px-8 py-16 lg:py-24">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <div data-reveal>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-300">The local-news gap</p>
                    <h2 class="mt-4 text-3xl lg:text-4xl font-semibold tracking-tight leading-tight [text-wrap:balance]">
                        Fewer reporters, but the right ones still matter most.
                    </h2>
                    <p class="mt-5 text-lg text-slate-300 leading-relaxed">
                        Local newsrooms have thinned, but the reporters who remain carry more weight than ever. The few who still cover your council, your schools, and your public works are the difference between residents understanding a project and only hearing the complaints about it.
                    </p>
                    <p class="mt-4 text-lg text-slate-300 leading-relaxed">
                        Finding those specific people, and reaching them with the right context, is how civic news still travels in a thinned-out media landscape.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-6 lg:p-8" data-reveal>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Who still carries civic news</p>
                    <ul class="mt-5 space-y-4">
                        @foreach ([
                            ['Local & regional reporters', 'The remaining city-hall and regional desks reach residents directly and set the tone for how a project is understood.'],
                            ['Community outlets', 'Neighborhood papers, public radio, and community newsletters reach audiences the big outlets miss.'],
                            ['Beat specialists', 'Reporters who cover transit, housing, or public safety can explain a complex project clearly and credibly.'],
                        ] as $row)
                            <li class="flex items-start gap-3">
                                <span class="grid place-items-center h-6 w-6 shrink-0 rounded-md bg-gradient-to-br from-blue-500 to-indigo-500 text-white">
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
                <h2 class="mt-3 text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900">Reach the reporters who still cover your community.</h2>
            </div>
            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ([
                    ['Local & regional media', 'We track the reporters, community outlets, and beat writers currently covering your region: civic affairs, transit, development, and more.'],
                    ['Issue-aware matching', 'PrComet reads your announcement and matches it to the writers covering that exact issue, from public safety to economic development.'],
                    ['Transparent and on the record', 'Every match comes with the reason it fits, tied to real reporting, so your communications are relevant and accountable, never spam.'],
                ] as $i => $c)
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all" data-reveal>
                        <div class="grid place-items-center h-9 w-9 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-500 text-white font-semibold text-sm">{{ $i + 1 }}</div>
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
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Every announcement matters to someone</p>
            <h2 class="mt-3 text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900">Turn civic news into understanding.</h2>
        </div>
        <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 gap-6">
            @foreach ([
                ['icon' => 'M4 19V9l8-5 8 5v10M9 19v-6h6v6M4 19h16', 'title' => 'Infrastructure & transit', 'body' => 'Match a groundbreaking or service change to the reporters covering transportation and public works.'],
                ['icon' => 'M3 21h18M6 21V8l6-4 6 4v13M10 12h4M10 16h4', 'title' => 'Economic development', 'body' => 'Reach the writers tracking jobs, investment, and new employers choosing your community.'],
                ['icon' => 'M12 6v6l4 2M12 22a10 10 0 110-20 10 10 0 010 20z', 'title' => 'Public programs & services', 'body' => 'Surface the journalists who can help residents understand and access a new program.'],
                ['icon' => 'M12 3l9 4-9 4-9-4 9-4zM3 12l9 4 9-4M3 17l9 4 9-4', 'title' => 'Emergency & resilience', 'body' => 'Identify the right outlets in advance, so critical information reaches residents quickly when it counts.'],
            ] as $u)
                <div class="group flex gap-4 rounded-2xl border border-slate-200 bg-white p-6 hover:border-blue-300 hover:shadow-lg transition-all" data-reveal>
                    <div class="grid place-items-center h-11 w-11 shrink-0 rounded-xl bg-gradient-to-br from-blue-100 to-indigo-100 text-blue-700 group-hover:from-blue-500 group-hover:to-indigo-500 group-hover:text-white transition-colors">
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

    {{-- ── From announcement to understanding: walkthrough ── --}}
    <section class="bg-slate-50 border-y border-slate-200/70 py-16 lg:py-24">
        <div class="max-w-6xl mx-auto px-6">
            <div class="max-w-2xl" data-reveal>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">From announcement to understanding</p>
                <h2 class="mt-3 text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900">Watch a project reach residents.</h2>
                <p class="mt-4 text-lg text-slate-600 leading-relaxed">
                    The same flood-mitigation project from the top of the page, followed through to an informed community.
                </p>
            </div>

            <div class="mt-14 relative">
                <div class="hidden lg:block absolute left-1/2 top-2 bottom-2 w-px -translate-x-1/2 bg-slate-200"></div>
                <div class="space-y-12 lg:space-y-20">

                    {{-- Step 1 --}}
                    <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center" data-reveal>
                        <div class="lg:pr-12 lg:text-right">
                            <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-blue-600">
                                <span class="grid place-items-center h-6 w-6 rounded-full bg-gradient-to-br from-blue-500 to-indigo-500 text-white text-[11px]">1</span>
                                You announce
                            </div>
                            <h3 class="mt-3 text-xl font-semibold text-slate-900">Your announcement becomes a trigger.</h3>
                            <p class="mt-2 text-slate-600 leading-relaxed">Point us at your newsroom once. Every project, program, or notice kicks off discovery.</p>
                        </div>
                        <div class="mt-5 lg:mt-0">
                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-md max-w-sm lg:mr-auto">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="grid place-items-center h-7 w-7 rounded-lg bg-blue-50 text-blue-600">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 12h6M9 16h6M9 8h6M5 4h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z" /></svg>
                                    </div>
                                    <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Announcement</span>
                                    <span class="ml-auto inline-flex items-center gap-1 text-[10px] font-medium text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Ingested</span>
                                </div>
                                <div class="text-sm font-medium text-slate-900 leading-snug">"City breaks ground on $40M flood-mitigation and riverfront project"</div>
                                <div class="text-xs text-slate-500 mt-1">City of Riverton</div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 2 --}}
                    <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center" data-reveal>
                        <div class="lg:order-2 lg:pl-12">
                            <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-indigo-600">
                                <span class="grid place-items-center h-6 w-6 rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-white text-[11px]">2</span>
                                PrComet reads everything
                            </div>
                            <h3 class="mt-3 text-xl font-semibold text-slate-900">We do the reading for you.</h3>
                            <p class="mt-2 text-slate-600 leading-relaxed">Local papers, regional desks, public radio, and community outlets, analyzed against your news this week.</p>
                        </div>
                        <div class="mt-5 lg:mt-0 lg:order-1">
                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-md max-w-sm lg:ml-auto">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Live corpus</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium text-indigo-600">
                                        <span class="relative flex h-1.5 w-1.5"><span class="absolute inline-flex h-full w-full rounded-full bg-indigo-500 opacity-60 animate-ping"></span><span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-indigo-500"></span></span>
                                        Analyzing
                                    </span>
                                </div>
                                <ul class="space-y-2">
                                    @foreach ([['RD','Riverfront plan clears council'],['PR','Flood risk along the river, explained'],['CW','What the new project means for traffic']] as $row)
                                        <li class="flex items-center gap-2.5">
                                            <div class="grid place-items-center h-6 w-6 rounded text-[9px] font-semibold text-white bg-gradient-to-br from-indigo-500 to-violet-600 shrink-0">{{ $row[0] }}</div>
                                            <span class="text-xs text-slate-700 truncate flex-1">{{ $row[1] }}</span>
                                            <svg class="h-3.5 w-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px]">
                                    <span class="text-slate-500">Items analyzed today</span>
                                    <span class="tabular font-semibold text-slate-900">73</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 3 --}}
                    <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center" data-reveal>
                        <div class="lg:pr-12 lg:text-right">
                            <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-violet-600">
                                <span class="grid place-items-center h-6 w-6 rounded-full bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white text-[11px]">3</span>
                                You see ranked matches
                            </div>
                            <h3 class="mt-3 text-xl font-semibold text-slate-900">A short list, ranked by fit.</h3>
                            <p class="mt-2 text-slate-600 leading-relaxed">The reporters most likely to cover it, each scored, with the reasoning and citations behind it.</p>
                        </div>
                        <div class="mt-5 lg:mt-0">
                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-md max-w-sm lg:mr-auto">
                                <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-3">Top matches</div>
                                <ul class="space-y-2.5">
                                    @foreach ([['MT','Marcus Tran','Riverton Daily','88'],['SO','Sofia Ortiz','Public Radio West','80'],['NB','Neil Boyd','Community Voice','75']] as $m)
                                        <li class="flex items-center gap-3">
                                            <div class="grid place-items-center h-7 w-7 rounded-full bg-gradient-to-br from-blue-500 to-indigo-500 text-white text-[10px] font-semibold shrink-0">{{ $m[0] }}</div>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-xs font-medium text-slate-900 truncate">{{ $m[1] }}</div>
                                                <div class="text-[10px] text-slate-500 truncate">{{ $m[2] }}</div>
                                            </div>
                                            <span class="text-xs font-bold tabular bg-gradient-to-br from-blue-600 to-indigo-600 bg-clip-text text-transparent">{{ $m[3] }}%</span>
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
                                <span class="grid place-items-center h-6 w-6 rounded-full bg-gradient-to-br from-sky-500 to-blue-500 text-white text-[11px]">4</span>
                                You reach out
                            </div>
                            <h3 class="mt-3 text-xl font-semibold text-slate-900">Brief them from evidence.</h3>
                            <p class="mt-2 text-slate-600 leading-relaxed">You get the reasoning and the citation. Your office keeps control of the message, always.</p>
                        </div>
                        <div class="mt-5 lg:mt-0 lg:order-1">
                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-md max-w-sm lg:ml-auto">
                                <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Why Marcus is a fit</div>
                                <p class="text-sm text-slate-700 leading-relaxed">Has followed the riverfront debate for months. The groundbreaking is the next chapter of his coverage.</p>
                                <div class="mt-3 pl-3 border-l-2 border-blue-300">
                                    <p class="text-[11px] text-slate-600 italic leading-relaxed">"residents have waited years for a real answer on the river"</p>
                                    <div class="text-[10px] text-blue-700 mt-1 font-medium">Riverton Daily, last month ↗</div>
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
                            <h3 class="mt-3 text-xl font-semibold text-slate-900">Coverage, then trust.</h3>
                            <p class="mt-2 text-slate-600 leading-relaxed">The story reaches residents in context, questions get answered, and confidence in the project grows.</p>
                        </div>
                        <div class="mt-5 lg:mt-0">
                            <div class="rounded-xl border border-emerald-200 bg-white p-4 shadow-md ring-1 ring-emerald-100 max-w-sm lg:mr-auto">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-semibold text-slate-900">Resident reach</span>
                                    <span class="inline-flex items-center gap-1.5 rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 8-8M21 7v6" /></svg>
                                        Up
                                    </span>
                                </div>
                                <svg class="w-full h-28" viewBox="0 0 320 110" fill="none" preserveAspectRatio="none" aria-hidden="true">
                                    <defs>
                                        <linearGradient id="muniOut" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="#10b981" stop-opacity="0.2" />
                                            <stop offset="100%" stop-color="#10b981" stop-opacity="0" />
                                        </linearGradient>
                                    </defs>
                                    <path d="M0 90 L80 84 L105 54 L160 48 L210 34 L270 22 L320 14 L320 110 L0 110 Z" fill="url(#muniOut)" />
                                    <path class="chart-draw" d="M0 90 L80 84 L105 54 L160 48 L210 34 L270 22 L320 14" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                    <circle cx="105" cy="54" r="3.5" fill="#10b981" />
                                </svg>
                                <div class="mt-2 flex items-center justify-between text-[11px]">
                                    <span class="text-slate-500">Residents reached</span>
                                    <span class="tabular font-semibold text-emerald-700">▲ 5.0x</span>
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
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Why PrComet for local government</p>
                <h2 class="mt-3 text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900 [text-wrap:balance]">
                    Built for keeping a community informed.
                </h2>
                <p class="mt-4 text-lg text-slate-600 leading-relaxed">
                    Generic PR tools were built to sell products. PrComet is tuned for civic communication: reaching the right local reporters, on the record, with the context residents need.
                </p>
                <a href="#request-demo" class="mt-7 inline-flex items-center gap-2 rounded-lg bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800 transition-colors">
                    Request a demo
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" /></svg>
                </a>
            </div>
            <ul class="space-y-3" data-reveal>
                @foreach ([
                    'Tracks local papers, regional desks, public radio, and community outlets',
                    'Matches each announcement to the beat that covers the issue',
                    'Reaches the specific reporters who still cover your community',
                    'Tells you why each reporter fits, with citations to their work',
                    'Helps residents understand projects, not just hear about them',
                    'Discovery, not automation. Your office owns the message',
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
        source="Municipalities"
        heading="Get civic news to the residents it affects."
        sub="Tell us about your community and what's coming. We'll show you the local and regional reporters PrComet would surface for it."
    />

    <x-site-footer />
    <x-reveal-script />
</body>
</html>
