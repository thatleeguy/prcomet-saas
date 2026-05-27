<div>
    @if ($submitted)
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-8 text-center">
            <div class="mx-auto h-12 w-12 rounded-full bg-emerald-500 grid place-items-center mb-4">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-900">Thanks — we'll be in touch.</h3>
            <p class="text-sm text-slate-600 mt-2 max-w-md mx-auto">We review every request personally. Expect to hear from us within one business day with next steps and a calendar link.</p>
        </div>
    @else
        <form wire:submit="submit" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="d-name" class="block text-xs font-medium text-slate-700 mb-1.5">Name <span class="text-rose-500">*</span></label>
                    <input id="d-name" type="text" wire:model="name" autocomplete="name"
                           class="w-full rounded-lg border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500 bg-white" />
                    @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="d-email" class="block text-xs font-medium text-slate-700 mb-1.5">Work email <span class="text-rose-500">*</span></label>
                    <input id="d-email" type="email" wire:model="email" autocomplete="email"
                           class="w-full rounded-lg border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500 bg-white" />
                    @error('email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="d-company" class="block text-xs font-medium text-slate-700 mb-1.5">Company <span class="text-rose-500">*</span></label>
                    <input id="d-company" type="text" wire:model="company" autocomplete="organization"
                           class="w-full rounded-lg border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500 bg-white" />
                    @error('company') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="d-role" class="block text-xs font-medium text-slate-700 mb-1.5">Your role</label>
                    <input id="d-role" type="text" wire:model="role" placeholder="e.g. Head of Marketing"
                           class="w-full rounded-lg border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500 bg-white" />
                </div>
                <div class="md:col-span-2">
                    <label for="d-website" class="block text-xs font-medium text-slate-700 mb-1.5">Company website</label>
                    <input id="d-website" type="url" wire:model="website" placeholder="https://yourcompany.com"
                           class="w-full rounded-lg border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500 bg-white" />
                    @error('website') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label for="d-notes" class="block text-xs font-medium text-slate-700 mb-1.5">What are you hoping to grow?</label>
                    <textarea id="d-notes" wire:model="notes" rows="3" placeholder="A line or two about what kind of coverage or engagement you're chasing."
                              class="w-full rounded-lg border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500 bg-white resize-none"></textarea>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
                <p class="text-xs text-slate-500">We'll never share your details. One human reads each request.</p>
                <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-medium text-sm transition-colors disabled:opacity-60">
                    <span wire:loading.remove>Request a demo</span>
                    <span wire:loading>Sending…</span>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" /></svg>
                </button>
            </div>
        </form>
    @endif
</div>
