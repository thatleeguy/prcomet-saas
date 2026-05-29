<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmed · PrComet</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=geist:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-900 bg-slate-50 min-h-screen">

    <header class="border-b border-slate-200 bg-white">
        <div class="max-w-3xl mx-auto px-6 lg:px-10 py-4 flex items-center gap-2.5">
            <span class="h-7 w-7 rounded-full bg-slate-900"></span>
            <span class="font-semibold text-slate-900 tracking-tight">PrComet</span>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-6 lg:px-10 py-12 lg:py-16">
        <div class="bg-white border border-emerald-200 rounded-2xl p-8 text-center">
            <div class="mx-auto h-12 w-12 rounded-full bg-emerald-100 grid place-items-center mb-4">
                <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            </div>
            <h1 class="text-2xl font-semibold text-slate-900 tracking-tight">You're confirmed.</h1>
            <p class="text-sm text-slate-600 mt-2 max-w-md mx-auto">You'll start receiving your PrComet network digest at your chosen cadence. Manage your subscriptions any time below.</p>
            <a href="{{ $manageUrl }}" class="inline-block mt-6 px-5 py-2.5 rounded-md bg-slate-900 hover:bg-slate-800 text-white text-sm font-medium transition-colors">Manage subscriptions</a>
        </div>
    </main>
</body>
</html>
