<div>
    {{-- Header slot is static after initial render (Jetstream renders it into
         the layout once). So it only holds the breadcrumb / page title; the
         reactive status indicator and action buttons live in the body. --}}
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div class="min-w-0">
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <a href="{{ route('matches.show', $match) }}" wire:navigate class="hover:text-slate-900">Match</a>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-700">One-pager</span>
                </div>
                <h1 class="text-base font-semibold text-slate-900 mt-0.5">One-pager · {{ $match->publicationItem->source->name }}</h1>
            </div>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto space-y-6 animate-fade-in">
        {{-- Reactive control bar — lives in the body so it updates after
             wire:click actions. --}}
        <div class="flex items-center justify-between gap-3 bg-white border border-slate-200 rounded-xl p-3 pl-4">
            <div class="text-sm">
                @if ($onePager->isPublished())
                    <span class="inline-flex items-center gap-1.5 text-emerald-700 font-medium">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Published
                    </span>
                    <span class="text-slate-400 mx-2">·</span>
                    <span class="text-slate-600">{{ $onePager->view_count }} {{ \Illuminate\Support\Str::plural('view', $onePager->view_count) }}</span>
                @else
                    <span class="inline-flex items-center gap-1.5 text-amber-700 font-medium">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        Draft
                    </span>
                    <span class="text-slate-400 mx-2">·</span>
                    <span class="text-slate-500">Not yet shareable</span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if ($onePager->isPublished())
                    <button wire:click="unpublish" wire:loading.attr="disabled" class="btn-secondary text-xs">Unpublish</button>
                @endif
                <button wire:click="save" wire:loading.attr="disabled" class="btn-secondary">Save draft</button>
                @if (! $onePager->isPublished())
                    <button wire:click="publish" wire:loading.attr="disabled" class="btn-primary">
                        <span wire:loading.remove wire:target="publish">Publish</span>
                        <span wire:loading wire:target="publish">Publishing…</span>
                    </button>
                @endif
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif

        @if ($onePager->isPublished())
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <div class="text-xs font-semibold text-emerald-700 uppercase tracking-wider mb-1">Share this link</div>
                    <div class="font-mono text-sm text-emerald-900 truncate">{{ $onePager->publicUrl() }}</div>
                </div>
                <a href="{{ $onePager->publicUrl() }}" target="_blank" rel="noopener" class="btn-secondary text-xs whitespace-nowrap">Open ↗</a>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <section class="bg-white border border-slate-200 rounded-xl p-6">
                    <label class="block text-sm font-semibold text-slate-900 mb-2">Personal note</label>
                    <p class="text-xs text-slate-500 mb-3">Appears at the top of the page, above the assets. Markdown supported.</p>
                    <textarea wire:model="note" rows="5" placeholder="Hey Robert, thought you'd find our latest drill results interesting given your Walker Lane coverage..."
                              class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                </section>

                <section class="bg-white border border-slate-200 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900">Included assets</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Click to toggle. Curated automatically by tag overlap; tweak as needed.</p>
                        </div>
                        <a href="{{ route('companies.library', $match->company) }}" wire:navigate class="text-xs text-brand-700 hover:text-brand-800">Manage library →</a>
                    </div>

                    @if ($this->libraryAssets->isEmpty())
                        <div class="rounded-lg border-2 border-dashed border-slate-200 p-8 text-center">
                            <p class="text-sm text-slate-500">The media library is empty. <a href="{{ route('companies.library', $match->company) }}" wire:navigate class="text-brand-700 hover:underline">Add some assets</a> to include them here.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach ($this->libraryAssets as $asset)
                                @php $selected = in_array($asset->id, $selectedAssetIds, true); @endphp
                                <button type="button" wire:key="asset-{{ $asset->id }}" wire:click="toggleAsset({{ $asset->id }})"
                                        class="text-left rounded-lg border-2 overflow-hidden transition-all
                                               {{ $selected ? 'border-brand-500 ring-2 ring-brand-100' : 'border-slate-200 hover:border-slate-300' }}">
                                    <div class="aspect-[4/3] bg-slate-100 relative">
                                        @if (in_array($asset->type, ['image', 'logo', 'header']) && $asset->file_path)
                                            <img src="{{ $asset->publicUrl() }}" alt="" class="w-full h-full object-cover" />
                                        @elseif ($asset->type === 'pdf')
                                            <div class="absolute inset-0 grid place-items-center text-rose-500">
                                                <svg class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            </div>
                                        @elseif ($asset->type === 'quote')
                                            <div class="absolute inset-0 p-3 flex items-center bg-gradient-to-br from-indigo-50 via-violet-50 to-fuchsia-50">
                                                <p class="text-[10px] text-slate-700 leading-relaxed italic line-clamp-4">&ldquo;{{ \Illuminate\Support\Str::limit($asset->quote_text, 100) }}&rdquo;</p>
                                            </div>
                                        @elseif ($asset->type === 'link')
                                            <div class="absolute inset-0 grid place-items-center text-brand-500">
                                                <svg class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                                            </div>
                                        @endif
                                        @if ($selected)
                                            <span class="absolute top-1.5 right-1.5 grid place-items-center h-5 w-5 rounded-full bg-brand-600 text-white">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="p-2">
                                        <div class="text-xs font-medium text-slate-900 line-clamp-1">{{ $asset->name }}</div>
                                        <div class="text-[10px] text-slate-500 mt-0.5 uppercase tracking-wider">{{ $asset->type }}</div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>

            <aside class="space-y-6">
                <section class="bg-white border border-slate-200 rounded-xl p-5 text-sm">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">For this match</h3>
                    <div class="space-y-2 text-slate-700">
                        <div><span class="text-slate-500">Triggered by:</span> {{ \Illuminate\Support\Str::limit($match->pressRelease->title, 60) }}</div>
                        <div><span class="text-slate-500">Target:</span> {{ $match->author?->name ?? $match->publicationItem->source->name }}</div>
                        <div><span class="text-slate-500">Match score:</span> <span class="font-semibold">{{ number_format($match->score * 100) }}%</span></div>
                    </div>
                </section>

                <section class="bg-white border border-slate-200 rounded-xl p-5">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Engagement</h3>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <div class="text-2xl font-semibold tabular text-slate-900">{{ $onePager->view_count }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">Total views</div>
                        </div>
                        <div>
                            <div class="text-2xl font-semibold tabular text-slate-900">{{ $onePager->unique_view_count }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">Unique viewers</div>
                        </div>
                    </div>
                    @if ($onePager->last_viewed_at)
                        <div class="text-xs text-slate-500 pt-3 border-t border-slate-100">
                            Last opened {{ $onePager->last_viewed_at->diffForHumans() }}
                        </div>
                    @else
                        <div class="text-xs text-slate-400 pt-3 border-t border-slate-100">No views yet.</div>
                    @endif
                </section>

                <section class="bg-white border border-slate-200 rounded-xl p-5 text-xs text-slate-600 leading-relaxed">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Branding</h3>
                    <p>The page uses the company's <a href="{{ route('companies.branding', $match->company) }}" wire:navigate class="text-brand-700 hover:underline">brand settings</a> — logo, header, accent color, contact details.</p>
                </section>
            </aside>
        </div>
    </div>
</div>
