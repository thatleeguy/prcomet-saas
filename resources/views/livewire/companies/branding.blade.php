<div>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <a href="{{ route('companies.show', $company) }}" wire:navigate class="hover:text-slate-900">{{ $company->name }}</a>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-700">Branding</span>
                </div>
                <h1 class="text-base font-semibold text-slate-900 mt-0.5">Branding</h1>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6 animate-fade-in">
        @if (session('status'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif

        <form wire:submit="save" class="space-y-6">

            <section class="bg-white border border-slate-200 rounded-xl p-6">
                <h2 class="text-sm font-semibold text-slate-900 mb-4">Logo</h2>
                <div class="flex items-start gap-5">
                    <div class="grid place-items-center h-24 w-24 rounded-lg border border-slate-200 bg-slate-50 overflow-hidden shrink-0">
                        @if ($logo)
                            <img src="{{ $logo->temporaryUrl() }}" class="w-full h-full object-contain" alt="Logo preview" />
                        @elseif ($company->logo_path)
                            <img src="{{ $company->logoUrl() }}" class="w-full h-full object-contain" alt="Current logo" />
                        @else
                            <svg class="h-8 w-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        @endif
                    </div>
                    <div class="flex-1">
                        <input type="file" wire:model="logo" accept="image/*"
                               class="block w-full text-sm text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200" />
                        <p class="text-[11px] text-slate-500 mt-1.5">PNG with transparency works best. Up to 5MB.</p>
                        @error('logo') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        @if ($company->logo_path)
                            <button type="button" wire:click="removeLogo" class="text-xs text-rose-600 hover:text-rose-700 mt-2">Remove current logo</button>
                        @endif
                    </div>
                </div>
            </section>

            <section class="bg-white border border-slate-200 rounded-xl p-6">
                <h2 class="text-sm font-semibold text-slate-900 mb-1">Header image</h2>
                <p class="text-xs text-slate-500 mb-4">The hero band at the top of every one-pager. Wide aspect (3:1 or 2:1) works best.</p>
                <div class="aspect-[3/1] rounded-lg overflow-hidden border border-slate-200 bg-slate-50 mb-3">
                    @if ($headerImage)
                        <img src="{{ $headerImage->temporaryUrl() }}" class="w-full h-full object-cover" alt="Header preview" />
                    @elseif ($company->header_image_path)
                        <img src="{{ $company->headerImageUrl() }}" class="w-full h-full object-cover" alt="Current header" />
                    @else
                        <div class="w-full h-full grid place-items-center text-slate-300">
                            <svg class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                    @endif
                </div>
                <input type="file" wire:model="headerImage" accept="image/*"
                       class="block w-full text-sm text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200" />
                <p class="text-[11px] text-slate-500 mt-1.5">Up to 10MB. JPEG or PNG.</p>
                @error('headerImage') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                @if ($company->header_image_path)
                    <button type="button" wire:click="removeHeader" class="text-xs text-rose-600 hover:text-rose-700 mt-2">Remove current header</button>
                @endif
            </section>

            <section class="bg-white border border-slate-200 rounded-xl p-6 space-y-4">
                <h2 class="text-sm font-semibold text-slate-900">Identity</h2>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Accent color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" wire:model="accentColor" class="h-10 w-16 rounded border border-slate-200 p-0 cursor-pointer" />
                        <input type="text" wire:model="accentColor" placeholder="#4339DC" class="w-32 rounded-md border-slate-200 text-sm font-mono focus:border-brand-500 focus:ring-brand-500" />
                    </div>
                    @error('accentColor') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Tagline</label>
                    <input type="text" wire:model="tagline" placeholder="Nevada-focused gold explorer." class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500" />
                    @error('tagline') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">About the company</label>
                    <textarea wire:model="description" rows="6" placeholder="A paragraph or two of background. Markdown supported." class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                    <p class="text-[11px] text-slate-500 mt-1">This appears on every one-pager. Keep it tight, 2-3 short paragraphs.</p>
                </div>
            </section>

            <section class="bg-white border border-slate-200 rounded-xl p-6 space-y-4">
                <h2 class="text-sm font-semibold text-slate-900">Press contact</h2>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Email for journalists</label>
                    <input type="email" wire:model="pressContactEmail" placeholder="press@yourcompany.com" class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500" />
                    @error('pressContactEmail') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">X (Twitter) URL</label>
                        <input type="url" wire:model="twitter" placeholder="https://x.com/yourcompany" class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500" />
                        @error('twitter') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">LinkedIn URL</label>
                        <input type="url" wire:model="linkedin" placeholder="https://linkedin.com/company/yourcompany" class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500" />
                        @error('linkedin') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <div class="flex justify-end">
                <button type="submit" wire:loading.attr="disabled" class="btn-primary">
                    <span wire:loading.remove>Save branding</span>
                    <span wire:loading>Saving...</span>
                </button>
            </div>
        </form>
    </div>
</div>
