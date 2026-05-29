<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'PrComet') }}</title>

        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        <link rel="alternate icon" href="/favicon.ico">

        {{-- Geist Sans + Geist Mono. Contemporary geometric grotesk used by
             modern SaaS surfaces; pairs cleanly with slate neutrals. --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=geist:400,500,600,700|geist-mono:400,500&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        <x-tracking />
    </head>
    <body class="font-sans antialiased text-slate-900">
        <x-banner />

        @if (session('impersonator_id'))
            <div class="sticky top-0 z-40 bg-amber-500 text-white px-4 py-2 text-sm flex items-center justify-center gap-3 shadow">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <span>
                    You're impersonating
                    <strong>{{ Auth::user()->name }}</strong>.
                </span>
                <form method="POST" action="{{ route('impersonate.stop') }}" class="inline">
                    @csrf
                    <button type="submit" class="ml-1 underline underline-offset-2 hover:no-underline font-medium">
                        Return to admin
                    </button>
                </form>
            </div>
        @endif

        <div class="min-h-screen flex">
            {{-- Sidebar nav — fixed on desktop, slide-over on mobile. --}}
            @include('partials.sidebar')

            {{-- Main column --}}
            <div class="flex-1 min-w-0 lg:pl-64">
                {{-- Top bar inside main; carries page title + per-page actions. --}}
                @if (isset($header))
                    <header class="sticky top-0 z-20 h-16 bg-white/80 backdrop-blur border-b border-slate-200">
                        <div class="h-full px-6 lg:px-8 flex items-center">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <main class="px-6 lg:px-8 py-8">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @stack('modals')
        @livewireScripts
    </body>
</html>
