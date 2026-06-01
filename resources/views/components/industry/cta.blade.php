@props([
    'source',        // industry label, tags the lead in the admin
    'heading' => 'See PrComet running on your story.',
    'sub' => 'Tell us what you publish and who you want to reach. A real person reads every request and replies within one business day.',
    'points' => [
        'A real person reads every request',
        'Reply within one business day',
        'No spam, ever. We never share your details',
    ],
])

{{-- Conversion band shared across industry pages. Reuses the demo-request
     Livewire form, tagging the lead with the industry via $source.
     Dark, two-column: pitch + reassurances on the left, form card on the right. --}}
<section id="request-demo" class="relative overflow-hidden bg-slate-950 text-white py-20 lg:py-28">
    <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
        <div class="absolute -top-32 left-1/4 h-[460px] w-[460px] rounded-full bg-indigo-500/20 blur-3xl"></div>
        <div class="absolute -bottom-32 right-1/4 h-[460px] w-[460px] rounded-full bg-fuchsia-500/15 blur-3xl"></div>
    </div>

    <div class="relative max-w-6xl mx-auto px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
        {{-- Left: pitch + reassurances --}}
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-300 mb-4">Request a demo</p>
            <h2 class="text-4xl lg:text-5xl font-semibold tracking-tight [text-wrap:balance]">{{ $heading }}</h2>
            <p class="mt-5 text-lg text-slate-300 leading-relaxed [text-wrap:balance]">{{ $sub }}</p>

            @if(! empty($points))
                <ul class="mt-8 space-y-3">
                    @foreach($points as $point)
                        <li class="flex items-start gap-3">
                            <span class="grid place-items-center h-5 w-5 shrink-0 rounded-full bg-emerald-500/15 text-emerald-400 mt-0.5">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </span>
                            <span class="text-sm text-slate-300 leading-relaxed">{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Right: form card --}}
        <div class="relative rounded-2xl bg-white text-slate-900 shadow-2xl overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-indigo-600 via-violet-600 to-fuchsia-600"></div>
            <div class="p-6 lg:p-8">
                @livewire('landing.demo-request-form', ['source' => $source])
            </div>
        </div>
    </div>
</section>
