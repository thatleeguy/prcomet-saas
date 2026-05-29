<div>
    @if ($submitted)
        <div class="rounded-xl px-5 py-4 text-sm" style="background: color-mix(in srgb, var(--newsroom-accent, #4339DC) 8%, white); color: color-mix(in srgb, var(--newsroom-accent, #4339DC) 70%, #0f172a);">
            <div class="font-semibold mb-0.5">You're in.</div>
            <div class="text-xs opacity-80">We'll email you when {{ $company->name }} publishes something new here.</div>
        </div>
    @else
        <form wire:submit="subscribe" class="flex flex-col sm:flex-row gap-2">
            <input type="email" wire:model="email" required placeholder="you@example.com"
                   class="flex-1 rounded-md border-slate-200 text-sm focus:ring-0"
                   style="--tw-ring-color: var(--newsroom-accent, #4339DC); border-color: rgb(226 232 240);"
                   onfocus="this.style.borderColor='var(--newsroom-accent, #4339DC)'"
                   onblur="this.style.borderColor='rgb(226 232 240)'" />
            <button type="submit" wire:loading.attr="disabled"
                    class="px-4 py-2 rounded-md text-white text-sm font-medium whitespace-nowrap transition-opacity hover:opacity-90"
                    style="background: var(--newsroom-accent, #4339DC);">
                <span wire:loading.remove wire:target="subscribe">Subscribe</span>
                <span wire:loading wire:target="subscribe">Saving…</span>
            </button>
        </form>
        @error('email')
            <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
        @enderror
    @endif
</div>
