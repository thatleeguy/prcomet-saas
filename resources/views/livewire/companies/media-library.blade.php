<div>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <a href="{{ route('companies.show', $company) }}" wire:navigate class="hover:text-slate-900">{{ $company->name }}</a>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-700">Media library</span>
                </div>
                <h1 class="text-base font-semibold text-slate-900 mt-0.5">Media library</h1>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="openEditor('image')" class="btn-secondary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    Image
                </button>
                <button wire:click="openEditor('pdf')" class="btn-secondary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    PDF
                </button>
                <button wire:click="openEditor('quote')" class="btn-secondary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h6m-6 4h10M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    Quote
                </button>
                <button wire:click="openEditor('link')" class="btn-primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                    Link
                </button>
            </div>
        </div>
    </x-slot>

    @php
        $counts = $this->counts;
        $types = [
            'all'   => ['label' => 'All',     'icon' => 'M4 6h16M4 12h16M4 18h16'],
            'image' => ['label' => 'Images',  'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
            'pdf'   => ['label' => 'PDFs',    'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            'quote' => ['label' => 'Quotes',  'icon' => 'M7 8h10M7 12h6m-6 4h10M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
            'link'  => ['label' => 'Links',   'icon' => 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1'],
        ];
    @endphp

    <div class="space-y-6 animate-fade-in">
        @if (session('status'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif

        {{-- Filter strip --}}
        <section class="flex flex-col md:flex-row md:items-center gap-3">
            <div class="flex items-center gap-1 p-1 bg-white rounded-lg border border-slate-200">
                @foreach ($types as $key => $t)
                    @php
                        $count = $key === 'all'
                            ? array_sum($counts)
                            : ($counts[$key] ?? 0) + ($key === 'image' ? ($counts['logo'] ?? 0) + ($counts['header'] ?? 0) : 0);
                    @endphp
                    <button wire:click="setType('{{ $key }}')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition-colors
                                   {{ $typeFilter === $key ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50' }}">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="{{ $t['icon'] }}" /></svg>
                        {{ $t['label'] }}
                        <span class="text-[10px] tabular {{ $typeFilter === $key ? 'text-brand-500' : 'text-slate-400' }}">{{ $count }}</span>
                    </button>
                @endforeach
            </div>

            <div class="relative flex-1 md:max-w-md md:ml-auto">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19a8 8 0 100-16 8 8 0 000 16zM21 21l-4.35-4.35" /></svg>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by name, caption, or quote..."
                       class="w-full pl-9 pr-3 py-1.5 text-sm rounded-md border-slate-200 focus:border-brand-500 focus:ring-brand-500 bg-white" />
            </div>
        </section>

        {{-- Grid --}}
        @if ($this->assets->isEmpty())
            <section class="rounded-2xl border-2 border-dashed border-slate-200 bg-white p-12 text-center">
                <div class="mx-auto h-12 w-12 rounded-xl bg-slate-100 grid place-items-center mb-3">
                    <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <div class="text-base font-semibold text-slate-900">Nothing here yet</div>
                <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">Upload logos, project images, PDFs, or curate a few pull quotes. One-pagers will draw from this library.</p>
            </section>
        @else
            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach ($this->assets as $asset)
                    <article wire:key="asset-{{ $asset->id }}"
                             class="group rounded-xl border border-slate-200 bg-white overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                        {{-- Preview --}}
                        <div class="aspect-[4/3] bg-slate-100 overflow-hidden relative">
                            @if (in_array($asset->type, ['image', 'logo', 'header']) && $asset->file_path)
                                <img src="{{ $asset->publicUrl() }}" alt="{{ $asset->name }}" class="w-full h-full object-cover" />
                            @elseif ($asset->type === 'pdf')
                                <div class="absolute inset-0 grid place-items-center text-rose-500">
                                    <svg class="h-12 w-12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                </div>
                            @elseif ($asset->type === 'quote')
                                <div class="absolute inset-0 p-4 flex items-center bg-gradient-to-br from-indigo-50 via-violet-50 to-fuchsia-50">
                                    <div>
                                        <svg class="h-5 w-5 text-indigo-400 mb-2" fill="currentColor" viewBox="0 0 24 24"><path d="M3 17l3-3v-4H3V7h4v3l-3 3v4zm10 0l3-3v-4h-3V7h4v3l-3 3v4z" /></svg>
                                        <p class="text-xs text-slate-700 leading-relaxed italic line-clamp-3">&ldquo;{{ $asset->quote_text }}&rdquo;</p>
                                    </div>
                                </div>
                            @elseif ($asset->type === 'link')
                                <div class="absolute inset-0 grid place-items-center text-brand-500">
                                    <svg class="h-12 w-12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                                </div>
                            @endif

                            @if (! $asset->is_active)
                                <div class="absolute inset-0 bg-slate-900/40 grid place-items-center">
                                    <span class="px-2 py-0.5 rounded-md bg-white text-slate-700 text-[10px] font-semibold uppercase tracking-wider">Hidden</span>
                                </div>
                            @endif

                            @if ($asset->source === 'press_release')
                                <span class="absolute top-2 left-2 inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-white/90 backdrop-blur text-[10px] font-medium text-slate-700">
                                    <svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" /></svg>
                                    From PR
                                </span>
                            @endif
                        </div>

                        {{-- Meta --}}
                        <div class="p-3 flex-1 flex flex-col">
                            <div class="text-xs font-medium text-slate-900 line-clamp-1">{{ $asset->name }}</div>
                            @if ($asset->type === 'quote' && $asset->quote_attribution)
                                <div class="text-[11px] text-slate-500 mt-0.5">— {{ $asset->quote_attribution }}</div>
                            @elseif ($asset->description)
                                <div class="text-[11px] text-slate-500 mt-0.5 line-clamp-1">{{ $asset->description }}</div>
                            @endif

                            @if (! empty($asset->tags))
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @foreach (array_slice($asset->tags, 0, 3) as $tag)
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">#{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="flex items-center gap-1 mt-3 pt-3 border-t border-slate-100">
                                <button wire:click="openEditor('image', {{ $asset->id }})" type="button" class="flex-1 text-[11px] text-slate-600 hover:text-brand-700 py-1 transition-colors">Edit</button>
                                <button wire:click="toggleActive({{ $asset->id }})" type="button" class="flex-1 text-[11px] text-slate-600 hover:text-brand-700 py-1 transition-colors border-l border-slate-100">{{ $asset->is_active ? 'Hide' : 'Show' }}</button>
                                <button wire:click="delete({{ $asset->id }})" wire:confirm="Delete this asset? It will be removed from any one-pagers using it." type="button" class="flex-1 text-[11px] text-rose-600 hover:text-rose-700 py-1 transition-colors border-l border-slate-100">Delete</button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>
        @endif
    </div>

    {{-- Editor modal --}}
    @if ($editorOpen)
        <div class="fixed inset-0 z-50 grid place-items-center bg-slate-900/40 backdrop-blur-sm" wire:click.self="closeEditor">
            <div class="bg-white rounded-xl shadow-2xl border border-slate-200 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-slate-900">
                        {{ $editingId ? 'Edit asset' : ('Add ' . $editingMode) }}
                    </h2>
                    <button wire:click="closeEditor" class="text-slate-400 hover:text-slate-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form wire:submit="save" class="p-6 space-y-4">
                    @if (in_array($editingMode, ['image', 'pdf']))
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1.5">Name <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="name" class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500" />
                            @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1.5">Description</label>
                            <textarea wire:model="description" rows="2" class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1.5">
                                File @if (! $editingId) <span class="text-rose-500">*</span> @endif
                            </label>
                            <input type="file" wire:model="file" accept="{{ $editingMode === 'pdf' ? 'application/pdf' : 'image/*' }}"
                                   class="block w-full text-sm text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200" />
                            @if ($editingId)
                                <p class="text-[11px] text-slate-500 mt-1">Leave empty to keep the existing file.</p>
                            @endif
                            <div wire:loading wire:target="file" class="text-xs text-slate-500 mt-1">Uploading...</div>
                            @error('file') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @if ($editingMode === 'quote')
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1.5">Quote <span class="text-rose-500">*</span></label>
                            <textarea wire:model="quoteText" rows="3" class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500" placeholder="The quote, no quotation marks needed."></textarea>
                            @error('quoteText') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1.5">Attribution <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="quoteAttribution" placeholder="Sarah Chen, CEO" class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500" />
                            @error('quoteAttribution') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @if ($editingMode === 'link')
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1.5">Title <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="name" placeholder="Q3 analyst report" class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500" />
                            @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1.5">URL <span class="text-rose-500">*</span></label>
                            <input type="url" wire:model="url" placeholder="https://..." class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500" />
                            @error('url') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1.5">Description</label>
                            <textarea wire:model="description" rows="2" class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Tags</label>
                        <input type="text" wire:model="tagsCsv" placeholder="gold, nevada, drill-results" class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500" />
                        <p class="text-[11px] text-slate-500 mt-1">Comma-separated. Used to match assets to one-pagers automatically.</p>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" wire:click="closeEditor" class="btn-secondary">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" class="btn-primary">
                            <span wire:loading.remove>{{ $editingId ? 'Save' : 'Add to library' }}</span>
                            <span wire:loading>Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
