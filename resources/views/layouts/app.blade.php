<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'PrComet') }}</title>

        {{-- Geist Sans + Geist Mono. Contemporary geometric grotesk used by
             modern SaaS surfaces; pairs cleanly with slate neutrals. --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=geist:400,500,600,700|geist-mono:400,500&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased text-slate-900">
        <x-banner />

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
