<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'PrComet' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=geist:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <x-tracking />
</head>
<body class="font-sans antialiased text-slate-900 bg-slate-50">

    <header class="border-b border-slate-200 bg-white">
        <div class="max-w-3xl mx-auto px-6 lg:px-10 py-4 flex items-center gap-2.5">
            <span class="h-7 w-7 rounded-full bg-slate-900"></span>
            <span class="font-semibold text-slate-900 tracking-tight">PrComet</span>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-6 lg:px-10 py-12 lg:py-16">
        {{ $slot }}
    </main>

    <footer class="max-w-3xl mx-auto px-6 lg:px-10 py-8 text-xs text-slate-400">
        © {{ date('Y') }} PrComet · Discovery, not automation.
    </footer>

    @livewireScripts
</body>
</html>
