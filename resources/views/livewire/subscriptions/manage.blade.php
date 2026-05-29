<div x-data="{ flash: '' }" x-on:flash.window="flash = $event.detail.message; setTimeout(() => flash = '', 1800)">

    <div x-show="flash" x-transition class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800" x-text="flash"></div>

    <div class="space-y-10">

        {{-- Header --}}
        <section>
            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 mb-2">PrComet network</div>
            <h1 class="text-3xl font-semibold text-slate-900 tracking-tight">Your subscriptions</h1>
            <p class="text-sm text-slate-600 mt-2">Signed in as <span class="font-mono text-slate-900">{{ $subscriber->email }}</span>. No password needed — this link is unique to you.</p>
        </section>

        {{-- Cadence --}}
        <section class="bg-white border border-slate-200 rounded-xl p-6">
            <h2 class="text-sm font-semibold text-slate-900 mb-1">Delivery cadence</h2>
            <p class="text-xs text-slate-500 mb-4">How often we send you a digest from across your subscriptions.</p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                @foreach (\App\Models\NewsroomSubscriber::CADENCES as $key => $label)
                    <label class="rounded-lg border p-3 cursor-pointer transition-all
                                  {{ $cadence === $key ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-100' : 'border-slate-200 hover:border-slate-300' }}">
                        <input type="radio" wire:model.live="cadence" value="{{ $key }}" class="sr-only" />
                        <div class="text-sm font-semibold text-slate-900">{{ $label }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            @switch($key)
                                @case('instant') A separate email for every publish. @break
                                @case('daily') One email a day, only when there's something new. @break
                                @case('weekly') One email a week, only when there's something new. @break
                            @endswitch
                        </div>
                    </label>
                @endforeach
            </div>
        </section>

        {{-- Active subscriptions --}}
        <section>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Subscribed</h2>
                    <p class="text-xs text-slate-500">{{ $this->activeSubscriptions->count() }} {{ \Illuminate\Support\Str::plural('company', $this->activeSubscriptions->count()) }}</p>
                </div>
            </div>

            @if ($this->activeSubscriptions->isEmpty())
                <div class="rounded-xl border-2 border-dashed border-slate-200 p-6 text-center">
                    <p class="text-sm text-slate-600">You're not following any companies right now.</p>
                    <p class="text-xs text-slate-500 mt-1">Browse the network below to add one.</p>
                </div>
            @else
                <div class="bg-white border border-slate-200 rounded-xl divide-y divide-slate-100 overflow-hidden">
                    @foreach ($this->activeSubscriptions as $sub)
                        @php $c = $sub->company; @endphp
                        <div class="flex items-center justify-between gap-3 px-5 py-4">
                            <div class="min-w-0">
                                <a href="{{ route('newsroom.show', $c) }}" class="text-sm font-semibold text-slate-900 hover:underline">{{ $c->name }}</a>
                                @if ($c->tagline)
                                    <div class="text-xs text-slate-500 mt-0.5 line-clamp-1">{{ $c->tagline }}</div>
                                @endif
                                <div class="text-[11px] text-slate-400 mt-1 font-mono">Following since {{ $sub->subscribed_at?->format('M j, Y') }}</div>
                            </div>
                            <button wire:click="unsubscribeFrom({{ $c->id }})"
                                    wire:confirm="Stop following {{ $c->name }}?"
                                    class="text-xs text-rose-600 hover:text-rose-700 font-medium whitespace-nowrap">
                                Unsubscribe
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Discover --}}
        <section>
            <div class="mb-4">
                <h2 class="text-sm font-semibold text-slate-900">Browse the network</h2>
                <p class="text-xs text-slate-500">Other companies on PrComet you can follow with one click.</p>
            </div>

            <div class="relative mb-3">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19a8 8 0 100-16 8 8 0 000 16zM21 21l-4.35-4.35" /></svg>
                <input type="search" wire:model.live.debounce.250ms="search" placeholder="Search by name…"
                       class="w-full pl-9 pr-3 py-2 text-sm rounded-md border-slate-200 focus:border-brand-500 focus:ring-brand-500 bg-white" />
            </div>

            @if ($this->discoverableCompanies->isEmpty())
                <div class="rounded-xl border border-dashed border-slate-200 bg-white p-6 text-center text-sm text-slate-500">
                    @if ($search === '') You're following every newsroom on the network. @else No matches for "{{ $search }}". @endif
                </div>
            @else
                <div class="bg-white border border-slate-200 rounded-xl divide-y divide-slate-100 overflow-hidden">
                    @foreach ($this->discoverableCompanies as $c)
                        <div class="flex items-center justify-between gap-3 px-5 py-3">
                            <div class="min-w-0">
                                <a href="{{ route('newsroom.show', $c) }}" target="_blank" rel="noopener" class="text-sm font-semibold text-slate-900 hover:underline">{{ $c->name }}</a>
                                @if ($c->tagline)
                                    <div class="text-xs text-slate-500 mt-0.5 line-clamp-1">{{ $c->tagline }}</div>
                                @endif
                            </div>
                            <button wire:click="subscribeTo({{ $c->id }})"
                                    class="text-xs font-medium text-brand-700 hover:bg-brand-50 px-2.5 py-1 rounded-md transition-colors whitespace-nowrap">
                                + Subscribe
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Danger zone --}}
        <section class="border-t border-slate-200 pt-8">
            <h2 class="text-sm font-semibold text-rose-600 mb-1">Unsubscribe from everything</h2>
            <p class="text-xs text-slate-500 mb-3">One click stops all sends. You can re-subscribe to any newsroom afterwards if you change your mind.</p>
            <button wire:click="unsubscribeAll"
                    wire:confirm="Stop receiving every PrComet network email?"
                    class="text-xs font-medium text-rose-600 hover:text-rose-700">
                Unsubscribe from everything
            </button>
        </section>
    </div>
</div>
