<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'PrComet') }}</title>

        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        <link rel="alternate icon" href="/favicon.ico">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=geist:400,500,600,700|geist-mono:400,500&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased text-slate-900 bg-white min-h-screen flex flex-col">

        {{-- ─────────────────────────────────────────────────────────────
              HEADER (matches the landing page)
          ───────────────────────────────────────────────────────────── --}}
        <header class="bg-white/85 backdrop-blur-md border-b border-slate-200/60">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="/" class="flex items-center gap-2.5">
                    <span class="h-7 w-7 rounded-full bg-slate-900"></span>
                    <span class="font-semibold text-slate-900 tracking-tight">PrComet</span>
                </a>
                <a href="/" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5M11 18l-6-6 6-6" /></svg>
                    Back to home
                </a>
            </div>
        </header>

        {{-- ─────────────────────────────────────────────────────────────
              MAIN: form left, marketing right (hidden under lg)
          ───────────────────────────────────────────────────────────── --}}
        <main class="flex-1 grid grid-cols-1 lg:grid-cols-2">
            <div class="flex items-center justify-center px-6 py-12 lg:py-16">
                <div class="w-full max-w-sm">
                    {{ $slot }}
                </div>
            </div>

            <aside class="hidden lg:flex items-center justify-center bg-slate-50 border-l border-slate-200/70 px-12 py-16 relative overflow-hidden">
                <div class="absolute -top-32 -right-24 h-96 w-96 rounded-full bg-gradient-to-br from-indigo-200/40 to-fuchsia-200/40 blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-32 -left-24 h-80 w-80 rounded-full bg-gradient-to-tr from-violet-200/30 to-indigo-200/30 blur-3xl pointer-events-none"></div>

                <div class="relative w-full max-w-md space-y-8">
                    <div>
                        <div class="text-xs font-semibold text-indigo-600 uppercase tracking-wider mb-4">Inside PrComet</div>
                        <p class="text-xl text-slate-800 leading-relaxed font-medium">
                            "Most of the writers who'd publish your story aren't on a list. They're buried in this week's coverage. We read it so you don't have to."
                        </p>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-xl shadow-md p-5">
                        <div class="flex items-center gap-1.5 mb-4">
                            <span class="relative flex h-1.5 w-1.5">
                                <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-60 animate-ping"></span>
                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                            </span>
                            <span class="text-[10px] font-semibold text-slate-700 uppercase tracking-wider">Top opportunity</span>
                            <span class="ml-auto text-[10px] text-slate-400 font-medium">2h ago</span>
                        </div>

                        <div class="flex items-start gap-2.5 mb-4">
                            <div class="grid place-items-center h-8 w-8 rounded-full bg-gradient-to-br from-indigo-500 via-violet-500 to-fuchsia-500 text-white font-semibold text-[10px] shrink-0">RS</div>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-semibold text-slate-900 leading-tight">Robert Sinclair</div>
                                <div class="text-[10px] text-slate-500 mt-0.5">Mining.com</div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-base font-bold tabular leading-none">
                                    <span class="bg-gradient-to-br from-indigo-600 via-violet-600 to-fuchsia-600 bg-clip-text text-transparent">87</span><span class="text-sm text-slate-400">%</span>
                                </div>
                            </div>
                        </div>

                        <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Why he's a fit</div>
                        <p class="text-xs text-slate-700 leading-relaxed">
                            Wrote about Walker Lane gold 4 days ago. Your drill result extends his thesis. He has a track record of follow-up coverage.
                        </p>
                    </div>

                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <span class="h-px w-6 bg-slate-300"></span>
                        Discovery, not automation. We surface. You write.
                    </div>
                </div>
            </aside>
        </main>

        {{-- ─────────────────────────────────────────────────────────────
              FOOTER (matches the landing page)
          ───────────────────────────────────────────────────────────── --}}
        <footer class="border-t border-slate-200/70 py-6 bg-white">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="h-5 w-5 rounded-full bg-slate-900"></span>
                    <span class="text-sm font-medium text-slate-700">PrComet</span>
                </div>
                <div class="text-xs text-slate-500">
                    © {{ date('Y') }} PrComet. Discovery, not automation.
                </div>
            </div>
        </footer>

        @livewireScripts
    </body>
</html>
