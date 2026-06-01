<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<x-articles.head
    title="About · PrComet"
    description="PrComet is a PR discovery tool. We read thousands of publications, podcasts, and newsletters so you can find the journalists most likely to publish your story, and see exactly why each one fits."
    :canonical="route('about')"
/>
<body class="font-sans antialiased text-slate-900 bg-white">

    <x-site-nav />

    {{-- ── Hero ── --}}
    <section class="relative overflow-hidden bg-slate-900 text-white">
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute -top-24 right-0 h-[420px] w-[420px] rounded-full bg-gradient-to-br from-indigo-500/30 to-fuchsia-500/20 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/4 h-[340px] w-[340px] rounded-full bg-gradient-to-tr from-violet-500/20 to-blue-500/20 blur-3xl"></div>
        </div>
        <div class="relative max-w-5xl mx-auto px-6 py-20 lg:py-24">
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-indigo-300">About PrComet</p>
            <h1 class="mt-5 max-w-3xl text-5xl lg:text-6xl font-semibold tracking-tight leading-[1.05]">
                We surface. <span class="bg-gradient-to-r from-indigo-400 via-violet-400 to-fuchsia-400 bg-clip-text text-transparent">You write.</span>
            </h1>
            <p class="mt-6 max-w-xl text-lg text-slate-300 leading-relaxed">
                PrComet helps you find the journalists, hosts, and analysts most likely to engage with your story, and tells you exactly why each one is a fit.
            </p>
        </div>
        <div class="relative h-px bg-gradient-to-r from-transparent via-violet-500/50 to-transparent"></div>
    </section>

    {{-- ── Body ── --}}
    <main class="max-w-3xl mx-auto px-6 py-16 lg:py-20 prose prose-slate">
        <h2>Why we built it</h2>
        <p>
            Most PR tooling sells you a bigger list and a faster way to blast it. That approach burns
            relationships and lands in spam folders. We think the hard part was never sending more
            email; it was knowing who genuinely cares about your story this week, and why.
        </p>
        <p>
            PrComet reads thousands of articles, podcast episodes, and newsletters so you don't have
            to. When you publish something, we surface the writers whose recent work your story
            extends, with the reasoning and citations to back it up. You stay in control of the
            relationship and the words. We just take the searching off your plate.
        </p>

        <h2>What we believe</h2>
        <ul>
            <li><strong>Reasoning over lists.</strong> A name is worthless without a reason. Every match explains why this person, this week.</li>
            <li><strong>Discovery, not automation.</strong> The human belongs at the pitch. We find the opportunity; you make the connection.</li>
            <li><strong>Citations on everything.</strong> Every claim links back to the work it came from. Nothing hallucinated, nothing implied.</li>
        </ul>

        <h2>Who it's for</h2>
        <p>
            Growth-stage companies, PR and comms agencies, IR teams, and organizations across
            industries from junior mining to manufacturing to tourism and local government. If
            attention helps you grow, PrComet helps you earn it.
        </p>

        <p>
            Want to see it on your own content?
            <a href="{{ route('home') }}#request-demo">Request a demo</a> and a real person will get
            back to you within one business day.
        </p>
    </main>

    <x-site-footer />
</body>
</html>
