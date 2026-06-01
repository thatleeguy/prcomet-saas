<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<x-articles.head
    title="Contact · PrComet"
    description="Get in touch with the PrComet team. Request a demo, ask a question, or talk through whether PrComet is a fit for your organization."
    :canonical="route('contact')"
/>
<body class="font-sans antialiased text-slate-900 bg-white">

    <x-site-nav />

    {{-- ── Hero ── --}}
    <section class="relative overflow-hidden bg-slate-900 text-white">
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute -top-24 right-0 h-[420px] w-[420px] rounded-full bg-gradient-to-br from-indigo-500/30 to-fuchsia-500/20 blur-3xl"></div>
        </div>
        <div class="relative max-w-5xl mx-auto px-6 py-20 lg:py-24">
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-indigo-300">Contact</p>
            <h1 class="mt-5 max-w-3xl text-5xl lg:text-6xl font-semibold tracking-tight leading-[1.05]">
                Let's talk.
            </h1>
            <p class="mt-6 max-w-xl text-lg text-slate-300 leading-relaxed">
                Questions, demos, partnerships, press. A real person reads every message and replies within one business day.
            </p>
        </div>
        <div class="relative h-px bg-gradient-to-r from-transparent via-violet-500/50 to-transparent"></div>
    </section>

    {{-- ── Contact options ── --}}
    <main class="max-w-5xl mx-auto px-6 py-16 lg:py-20">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="rounded-2xl border border-slate-200 p-6">
                <h2 class="text-base font-semibold text-slate-900">See a demo</h2>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    The fastest way to understand PrComet is to see it run on your own content.
                </p>
                <a href="{{ route('home') }}#request-demo" class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-700">
                    Request a demo
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" /></svg>
                </a>
            </div>

            <div class="rounded-2xl border border-slate-200 p-6">
                <h2 class="text-base font-semibold text-slate-900">Email us</h2>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Prefer email? Reach the team directly and we'll point you to the right person.
                </p>
                <a href="mailto:hello@prcomet.com" class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-700">
                    hello@prcomet.com
                </a>
            </div>

            <div class="rounded-2xl border border-slate-200 p-6">
                <h2 class="text-base font-semibold text-slate-900">Press &amp; partnerships</h2>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Writing about PrComet, or exploring a partnership? We'd love to hear from you.
                </p>
                <a href="mailto:hello@prcomet.com?subject=Press%20%2F%20partnership" class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-700">
                    hello@prcomet.com
                </a>
            </div>
        </div>
    </main>

    {{-- ── Demo form ── --}}
    <x-industry.cta
        source="Contact"
        heading="Tell us what you're working on."
        sub="Share a little about your organization and what you're hoping to grow. We'll get back to you within one business day."
    />

    <x-site-footer />
</body>
</html>
