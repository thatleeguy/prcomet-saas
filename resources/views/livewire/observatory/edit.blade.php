<div>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('companies.observatory', $company) }}" wire:navigate class="hover:text-slate-900">Observatory</a>
            <span class="text-slate-300">/</span>
            <span class="text-slate-700">{{ $this->isEditing ? $watch->name : 'New watch' }}</span>
        </div>
        <h1 class="text-base font-semibold text-slate-900 mt-0.5">{{ $this->isEditing ? 'Edit watch' : 'New watch' }}</h1>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6 animate-fade-in">
        @if (session('status'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif

        <form wire:submit="save" class="space-y-6">

            <section class="bg-white border border-slate-200 rounded-xl p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-900 mb-1.5">Name</label>
                    <p class="text-xs text-slate-500 mb-2">The primary label for this watch. Also used as the first search term.</p>
                    <input type="text" wire:model.blur="name" class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500" placeholder="Newmont Mining" />
                    @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-900 mb-1.5">Kind</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @foreach ([
                            'company' => 'Company',
                            'location' => 'Location',
                            'product' => 'Product',
                            'term' => 'Term',
                        ] as $k => $label)
                            <button type="button" wire:click="$set('kind', '{{ $k }}')"
                                    class="text-left rounded-lg border p-2 transition-all
                                           {{ $kind === $k ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-100' : 'border-slate-200 hover:border-slate-300' }}">
                                <div class="text-xs font-medium text-slate-900">{{ $label }}</div>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-900 mb-1.5">Aliases</label>
                    <p class="text-xs text-slate-500 mb-2">Comma-separated. The primary name is included automatically — add only the extras.</p>
                    <input type="text" wire:model.blur="termsCsv"
                           class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500 font-mono"
                           placeholder="Newmont, Newmont Goldcorp, NEM" />
                    @error('termsCsv') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </section>

            {{-- Mode selector --}}
            <section class="bg-white border border-slate-200 rounded-xl p-6 space-y-4">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Matching mode</h3>
                    <p class="text-xs text-slate-500 mt-0.5">How a hit gets recorded when one of the aliases appears in ingested content.</p>
                </div>

                <label class="flex items-start gap-2.5 cursor-pointer p-3 rounded-lg border {{ $mode === 'literal' ? 'border-brand-500 bg-brand-50' : 'border-slate-200 hover:border-slate-300' }}">
                    <input type="radio" wire:model.live="mode" value="literal" class="mt-1 border-slate-300 text-brand-600 focus:ring-brand-500" />
                    <div class="flex-1">
                        <div class="text-sm font-medium text-slate-900">Literal</div>
                        <div class="text-xs text-slate-500 mt-0.5">Case-insensitive whole-word match against the title and body. Fast, deterministic, free.</div>
                    </div>
                </label>

                <label class="flex items-start gap-2.5 cursor-pointer p-3 rounded-lg border
                              {{ $mode === 'literal_llm' ? 'border-brand-500 bg-brand-50' : 'border-slate-200 hover:border-slate-300' }}
                              {{ ! $this->llmEnabled ? 'opacity-60' : '' }}">
                    <input type="radio" wire:model.live="mode" value="literal_llm"
                           class="mt-1 border-slate-300 text-brand-600 focus:ring-brand-500"
                           @if (! $this->llmEnabled) disabled @endif />
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-slate-900">Literal + LLM confirmation</span>
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium uppercase tracking-wider bg-gradient-to-r from-indigo-100 via-violet-100 to-fuchsia-100 text-indigo-700">
                                <svg class="h-2.5 w-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                                Upgrade
                            </span>
                        </div>
                        <div class="text-xs text-slate-500 mt-0.5">Literal match finds candidates, then Claude reads the snippet and confirms each one. Catches false positives like "Newmont Court".</div>
                        @if (! $this->llmEnabled)
                            <a href="mailto:hello@prcomet.com?subject=Observatory%20LLM%20upgrade" class="text-xs text-brand-700 hover:underline mt-2 inline-block">Contact us to enable →</a>
                        @endif
                    </div>
                </label>
            </section>

            <section class="bg-white border border-slate-200 rounded-xl p-4 flex items-center justify-between">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                    <input type="checkbox" wire:model="isActive" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
                    Active — scan new content as it arrives
                </label>
                <div class="flex items-center gap-2">
                    <a href="{{ route('companies.observatory', $company) }}" wire:navigate class="btn-secondary text-xs">Cancel</a>
                    <button type="submit" wire:loading.attr="disabled" class="btn-primary">
                        <span wire:loading.remove>{{ $this->isEditing ? 'Save & re-scan' : 'Create & scan' }}</span>
                        <span wire:loading>Working…</span>
                    </button>
                </div>
            </section>
        </form>

        @if ($this->isEditing)
            <section class="bg-white border border-rose-100 rounded-xl p-5">
                <h3 class="text-xs font-semibold text-rose-600 uppercase tracking-wider mb-2">Danger zone</h3>
                <p class="text-xs text-slate-600 mb-3">Deletes the watch and every hit recorded against it.</p>
                <button wire:click="delete" wire:confirm="Delete this watch and its hit history?" class="text-xs font-medium text-rose-600 hover:text-rose-700">Delete watch</button>
            </section>
        @endif
    </div>
</div>
