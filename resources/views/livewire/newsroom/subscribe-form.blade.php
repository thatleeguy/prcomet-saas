<div>
    @if ($submitted)
        <div class="rounded-xl px-5 py-4 text-sm accent-fade-bg border border-slate-200">
            <div class="font-semibold text-slate-900 mb-0.5">{{ $resultMessage }}</div>
            <div class="text-xs text-slate-600 leading-relaxed">{{ $resultBody }}</div>
        </div>
    @else
        <form wire:submit="subscribe" class="flex flex-col sm:flex-row gap-2">
            <input type="email" wire:model="email" required placeholder="you@example.com"
                   class="flex-1 rounded-md border-slate-200 text-sm focus:border-slate-400 focus:ring-0" />
            <button type="submit" wire:loading.attr="disabled"
                    class="accent-bg px-4 py-2 rounded-md text-white text-sm font-medium whitespace-nowrap transition-opacity hover:opacity-90 disabled:opacity-60">
                <span wire:loading.remove wire:target="subscribe">Subscribe</span>
                <span wire:loading wire:target="subscribe">Saving…</span>
            </button>
        </form>
        @error('email')
            <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
        @enderror
        <p class="text-[11px] text-slate-500 mt-2 leading-relaxed">
            Subscriptions are managed by <span class="font-medium text-slate-700">PrComet</span>. One inbox-friendly digest covers every company you follow. Unsubscribe anytime.
        </p>
    @endif
</div>
