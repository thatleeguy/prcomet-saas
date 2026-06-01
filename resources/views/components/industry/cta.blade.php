@props([
    'source',        // industry label, tags the lead in the admin
    'heading' => 'See PrComet running on your story.',
    'sub' => 'Tell us what you publish and who you want to reach. A real person reads every request and replies within one business day.',
])

{{-- Conversion band shared across industry pages. Reuses the demo-request
     Livewire form, tagging the lead with the industry via $source. --}}
<section id="request-demo" class="relative overflow-hidden bg-slate-50 border-t border-slate-200/70 py-20 lg:py-28">
    <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
        <div class="absolute -top-32 left-1/4 h-[460px] w-[460px] rounded-full bg-gradient-to-br from-indigo-300/40 to-fuchsia-300/30 blur-3xl"></div>
        <div class="absolute -bottom-32 right-1/4 h-[460px] w-[460px] rounded-full bg-gradient-to-tr from-violet-300/30 to-indigo-300/40 blur-3xl"></div>
    </div>

    <div class="relative max-w-3xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600 mb-4">Request a demo</p>
            <h2 class="text-4xl lg:text-5xl font-semibold tracking-tight text-slate-900 [text-wrap:balance]">{{ $heading }}</h2>
            <p class="mt-5 text-lg text-slate-600 leading-relaxed [text-wrap:balance]">{{ $sub }}</p>
        </div>

        <div class="relative rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-indigo-600 via-violet-600 to-fuchsia-600"></div>
            <div class="p-6 lg:p-8">
                @livewire('landing.demo-request-form', ['source' => $source])
            </div>
        </div>
    </div>
</section>
