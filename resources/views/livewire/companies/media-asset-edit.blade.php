<div>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('companies.show', $company) }}" wire:navigate class="hover:text-slate-900">{{ $company->name }}</a>
            <span class="text-slate-300">/</span>
            <a href="{{ route('companies.library', $company) }}" wire:navigate class="hover:text-slate-900">Media library</a>
            <span class="text-slate-300">/</span>
            <span class="text-slate-700">{{ $this->isEditing ? \Illuminate\Support\Str::limit($asset->name, 40) : 'New asset' }}</span>
        </div>
        <h1 class="text-base font-semibold text-slate-900 mt-0.5">
            {{ $this->isEditing ? $asset->name : 'New '.($this->typeMeta[$type]['label'] ?? 'asset') }}
        </h1>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6 animate-fade-in">
        @if (session('status'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif

        {{-- Type selector (create only) — wire:click swaps the form layout in-place. --}}
        @if (! $this->isEditing)
            <div class="bg-white border border-slate-200 rounded-xl p-4">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">What are you adding?</div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    @foreach ($this->typeMeta as $key => $meta)
                        <button type="button" wire:click="$set('type', '{{ $key }}')"
                                class="text-left rounded-lg border p-3 transition-all
                                       {{ $type === $key ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-100' : 'border-slate-200 hover:border-slate-300' }}">
                            <div class="text-sm font-semibold text-slate-900">{{ $meta['label'] }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">{{ $meta['blurb'] }}</div>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Main column ─────────────────────────────────────────── --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Basics --}}
                <section class="bg-white border border-slate-200 rounded-xl p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-1.5">Name</label>
                        <input type="text" wire:model.blur="name"
                               class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500"
                               placeholder="@if ($type === 'image') Big Sky drill core, hole BS-24-018 @elseif ($type === 'pdf') Q1 2026 corporate presentation @elseif ($type === 'quote') CEO on Walker Lane potential @else Recent Mining.com profile @endif" />
                        @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-1.5">Internal description <span class="text-xs font-normal text-slate-500">(optional)</span></label>
                        <textarea wire:model.blur="description" rows="3"
                                  class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500"
                                  placeholder="Where you took the photo, what's notable about it, any caveats for the team."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-1.5">Tags</label>
                        <input type="text" wire:model.blur="tagsCsv"
                               class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500 font-mono"
                               placeholder="drill, walker-lane, gold, big-sky" />
                        <p class="text-xs text-slate-500 mt-1">Comma-separated. We use these to auto-curate which assets land on each one-pager.</p>
                    </div>
                </section>

                {{-- Type-specific payload --}}
                @if (in_array($type, ['image', 'pdf']))
                    <section class="bg-white border border-slate-200 rounded-xl p-6 space-y-4">
                        <h3 class="text-sm font-semibold text-slate-900">{{ $type === 'pdf' ? 'PDF file' : 'Image file' }}</h3>

                        @if ($this->isEditing && $asset->file_path)
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 flex items-center gap-3">
                                @if ($type === 'image')
                                    <img src="{{ $asset->publicUrl() }}" alt="" class="h-16 w-16 rounded object-cover" />
                                @else
                                    <div class="grid place-items-center h-16 w-16 rounded bg-rose-50 text-rose-500">
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1 text-xs">
                                    <div class="font-medium text-slate-900 truncate">{{ basename($asset->file_path) }}</div>
                                    <div class="text-slate-500">{{ $asset->mime_type }} · {{ $asset->size_bytes ? number_format($asset->size_bytes / 1024, 0).' KB' : '—' }}</div>
                                </div>
                                <a href="{{ $asset->publicUrl() }}" target="_blank" rel="noopener" class="text-xs text-brand-700 hover:underline shrink-0">Open ↗</a>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-semibold text-slate-900 mb-1.5">
                                {{ $this->isEditing && $asset->file_path ? 'Replace file' : ($type === 'pdf' ? 'Upload PDF' : 'Upload image') }}
                                <span class="text-xs font-normal text-slate-500">(max 10 MB)</span>
                            </label>
                            <input type="file" wire:model="file"
                                   accept="{{ $type === 'pdf' ? 'application/pdf' : 'image/*' }}"
                                   class="block w-full text-sm text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border file:border-slate-200 file:bg-slate-50 file:text-slate-700 file:font-medium hover:file:bg-slate-100" />
                            @error('file') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror

                            <div wire:loading wire:target="file" class="text-xs text-slate-500 mt-1">Uploading…</div>

                            @if ($this->isEditing && $asset->file_path)
                                <div class="mt-3">
                                    <label class="block text-xs font-medium text-slate-700 mb-1">What changed? <span class="text-xs font-normal text-slate-400">(optional, attached to the revision)</span></label>
                                    <input type="text" wire:model.blur="revisionNotes"
                                           class="w-full rounded-md border-slate-200 text-xs focus:border-brand-500 focus:ring-brand-500"
                                           placeholder="Final colour-corrected version" />
                                </div>
                            @endif
                        </div>
                    </section>
                @elseif ($type === 'quote')
                    <section class="bg-white border border-slate-200 rounded-xl p-6 space-y-4">
                        <h3 class="text-sm font-semibold text-slate-900">The quote</h3>
                        <div>
                            <label class="block text-sm font-semibold text-slate-900 mb-1.5">Quote text</label>
                            <textarea wire:model.blur="quoteText" rows="4"
                                      class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                            @error('quoteText') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-900 mb-1.5">Attribution</label>
                            <input type="text" wire:model.blur="quoteAttribution"
                                   class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500"
                                   placeholder="Sarah Chen, VP Exploration" />
                            @error('quoteAttribution') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </section>
                @elseif ($type === 'link')
                    <section class="bg-white border border-slate-200 rounded-xl p-6 space-y-4">
                        <h3 class="text-sm font-semibold text-slate-900">External link</h3>
                        <div>
                            <label class="block text-sm font-semibold text-slate-900 mb-1.5">URL</label>
                            <input type="url" wire:model.blur="url"
                                   class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500 font-mono"
                                   placeholder="https://…" />
                            @error('url') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </section>
                @endif

                {{-- Credit (file-backed types only) --}}
                @if (in_array($type, ['image', 'pdf']))
                    <section class="bg-white border border-slate-200 rounded-xl p-6 space-y-4">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Photo credit</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Shown next to the image on the public one-pager.</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Credit</label>
                                <input type="text" wire:model.blur="credit"
                                       class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500"
                                       placeholder="© Mary Sutton / Aurelian Gold" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Credit URL <span class="text-slate-400">(optional)</span></label>
                                <input type="url" wire:model.blur="creditUrl"
                                       class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500 font-mono"
                                       placeholder="https://marysutton.photography" />
                            </div>
                        </div>
                    </section>
                @endif

                {{-- Media release --}}
                <section class="bg-white border border-slate-200 rounded-xl p-6 space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Media release</h3>
                            <p class="text-xs text-slate-500 mt-0.5">The permission grant the journalist sees when they download the file.</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="radio" wire:model.live="usesBlanketRelease" value="1"
                                   class="mt-1 border-slate-300 text-brand-600 focus:ring-brand-500" />
                            <div>
                                <div class="text-sm font-medium text-slate-900">Use {{ $company->name }}'s blanket release</div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    @if ($company->blanket_media_release_text || $company->blanket_media_release_file_path)
                                        Set on the <a href="{{ route('companies.branding', $company) }}" wire:navigate class="text-brand-700 hover:underline">Branding page</a>.
                                    @else
                                        Not set yet — <a href="{{ route('companies.branding', $company) }}" wire:navigate class="text-brand-700 hover:underline">add one on the Branding page</a> to apply it across the library.
                                    @endif
                                </div>
                            </div>
                        </label>

                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="radio" wire:model.live="usesBlanketRelease" value="0"
                                   class="mt-1 border-slate-300 text-brand-600 focus:ring-brand-500" />
                            <div class="flex-1">
                                <div class="text-sm font-medium text-slate-900">Custom release for this asset</div>
                                <div class="text-xs text-slate-500 mt-0.5">Override when this image has different terms (third-party photographer, embargo, etc.)</div>
                            </div>
                        </label>
                    </div>

                    @if (! $usesBlanketRelease)
                        <div class="space-y-4 pt-3 border-t border-slate-100">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Release text</label>
                                <textarea wire:model.blur="mediaReleaseText" rows="4"
                                          class="w-full rounded-md border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500"
                                          placeholder="Aurelian Gold grants single-use editorial reproduction of this image, with credit, for stories published before 30 June 2026."></textarea>
                            </div>

                            @if ($this->isEditing && $asset->media_release_file_path)
                                <div class="rounded-md border border-slate-200 bg-slate-50 p-3 flex items-center justify-between gap-3 text-xs">
                                    <span class="text-slate-700">Current release PDF attached.</span>
                                    <a href="{{ $asset->mediaReleaseFileUrl() }}" target="_blank" rel="noopener" class="text-brand-700 hover:underline">Open ↗</a>
                                </div>
                            @endif

                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">
                                    {{ $this->isEditing && $asset->media_release_file_path ? 'Replace release PDF' : 'Attach release PDF' }}
                                    <span class="text-slate-400">(optional, max 5 MB)</span>
                                </label>
                                <input type="file" wire:model="mediaReleaseFile" accept="application/pdf"
                                       class="block w-full text-sm text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border file:border-slate-200 file:bg-slate-50 file:text-slate-700 file:font-medium hover:file:bg-slate-100" />
                                @error('mediaReleaseFile') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endif
                </section>

                {{-- Save bar --}}
                <div class="flex items-center justify-between gap-3 bg-white border border-slate-200 rounded-xl p-3 pl-5 sticky bottom-4">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                        <input type="checkbox" wire:model="isActive" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
                        Active
                    </label>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('companies.library', $company) }}" wire:navigate class="btn-secondary">Back to library</a>
                        <button wire:click="save" wire:loading.attr="disabled" class="btn-primary">
                            <span wire:loading.remove wire:target="save">{{ $this->isEditing ? 'Save changes' : 'Create asset' }}</span>
                            <span wire:loading wire:target="save">Saving…</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Side column ─────────────────────────────────────────── --}}
            <aside class="space-y-6">
                @if ($this->isEditing)
                    <section class="bg-white border border-slate-200 rounded-xl p-5 text-sm">
                        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">At a glance</h3>
                        <dl class="space-y-2">
                            <div class="flex justify-between gap-3">
                                <dt class="text-slate-500">Type</dt>
                                <dd class="text-slate-900 font-medium">{{ $this->typeMeta[$asset->type]['label'] ?? $asset->type }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-slate-500">Created</dt>
                                <dd class="text-slate-900">{{ $asset->created_at->diffForHumans() }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-slate-500">Updated</dt>
                                <dd class="text-slate-900">{{ $asset->updated_at->diffForHumans() }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-slate-500">In use on</dt>
                                <dd class="text-slate-900">{{ $asset->onePagers()->count() }} {{ \Illuminate\Support\Str::plural('one-pager', $asset->onePagers()->count()) }}</dd>
                            </div>
                        </dl>
                    </section>

                    {{-- Revisions panel: only file-backed types accrue history. --}}
                    @if (in_array($asset->type, ['image', 'pdf']))
                        <section class="bg-white border border-slate-200 rounded-xl p-5">
                            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Revisions</h3>

                            @php $revisions = $asset->revisions; @endphp

                            @if ($revisions->isEmpty())
                                <p class="text-xs text-slate-500">No prior versions yet. Replacing the file above will start the history.</p>
                            @else
                                <ul class="space-y-3">
                                    @foreach ($revisions as $rev)
                                        <li class="text-xs border-l-2 border-slate-200 pl-3">
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="min-w-0">
                                                    <div class="font-medium text-slate-900">{{ $rev->created_at->format('M j, Y · g:ia') }}</div>
                                                    <div class="text-slate-500 truncate">{{ basename($rev->file_path) }} · {{ $rev->humanSize() }}</div>
                                                    @if ($rev->uploadedBy)
                                                        <div class="text-slate-400 mt-0.5">by {{ $rev->uploadedBy->name }}</div>
                                                    @endif
                                                    @if ($rev->notes)
                                                        <div class="text-slate-700 mt-1 italic">"{{ $rev->notes }}"</div>
                                                    @endif
                                                </div>
                                                <div class="flex flex-col items-end gap-1 shrink-0">
                                                    <a href="{{ $rev->publicUrl() }}" target="_blank" rel="noopener" class="text-brand-700 hover:underline">Open ↗</a>
                                                    <button wire:click="revertTo({{ $rev->id }})"
                                                            wire:confirm="Make this the current file? The current file will be moved into the revision history."
                                                            class="text-slate-700 hover:text-slate-900 underline-offset-2 hover:underline">
                                                        Make current
                                                    </button>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </section>
                    @endif

                    <section class="bg-white border border-rose-100 rounded-xl p-5">
                        <h3 class="text-xs font-semibold text-rose-600 uppercase tracking-wider mb-2">Danger zone</h3>
                        <p class="text-xs text-slate-600 mb-3">Deletes the asset and all its file history. One-pagers that referenced it will lose this asset.</p>
                        <button wire:click="delete"
                                wire:confirm="Permanently delete this asset and its revision history?"
                                class="text-xs font-medium text-rose-600 hover:text-rose-700">Delete asset</button>
                    </section>
                @else
                    <section class="bg-white border border-slate-200 rounded-xl p-5 text-sm text-slate-600">
                        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Tips</h3>
                        <ul class="space-y-2 text-xs leading-relaxed">
                            <li>Tag richly — they drive auto-curation onto one-pagers.</li>
                            <li>Add credit and release info now so you're not chasing it under deadline.</li>
                            <li>Use the blanket release for company-owned material; override only when terms differ.</li>
                        </ul>
                    </section>
                @endif
            </aside>
        </div>
    </div>
</div>
