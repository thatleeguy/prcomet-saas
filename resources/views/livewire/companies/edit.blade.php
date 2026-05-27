<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $company ? 'Edit company' : 'Add company' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form wire:submit="save" class="bg-white shadow-sm sm:rounded-lg p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="name" class="mt-1 block w-full rounded-md border-gray-300 focus:border-amber-500 focus:ring-amber-500" />
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ticker</label>
                        <input type="text" wire:model="ticker" class="mt-1 block w-full rounded-md border-gray-300" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Exchange</label>
                        <input type="text" wire:model="exchange" placeholder="TSX-V, ASX, etc." class="mt-1 block w-full rounded-md border-gray-300" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Website</label>
                        <input type="url" wire:model="website" placeholder="https://example.com" class="mt-1 block w-full rounded-md border-gray-300" />
                        @error('website') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Press-release RSS feed URL</label>
                        <input type="url" wire:model="rss_feed_url" placeholder="https://example.com/news/rss.xml" class="mt-1 block w-full rounded-md border-gray-300" />
                        <p class="mt-1 text-xs text-gray-500">We poll this feed for new releases. Most company sites expose one — ask IR if you can't find it.</p>
                        @error('rss_feed_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Sector tags</label>
                        <input type="text" wire:model="sector_tags_csv" placeholder="gold, copper, nevada" class="mt-1 block w-full rounded-md border-gray-300" />
                        <p class="mt-1 text-xs text-gray-500">Comma-separated. Used to find matching publications. Commodities + jurisdictions work well.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">IR contact name</label>
                        <input type="text" wire:model="ir_contact_name" class="mt-1 block w-full rounded-md border-gray-300" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">IR contact email</label>
                        <input type="email" wire:model="ir_contact_email" class="mt-1 block w-full rounded-md border-gray-300" />
                        @error('ir_contact_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">IR contact phone</label>
                        <input type="text" wire:model="ir_contact_phone" class="mt-1 block w-full rounded-md border-gray-300" />
                    </div>

                    <div class="md:col-span-2 flex items-center gap-2">
                        <input id="is_active" type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500" />
                        <label for="is_active" class="text-sm text-gray-700">Active — poll feeds and generate matches for this company.</label>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-5 flex items-center justify-between">
                    <a href="{{ route('companies.index') }}" wire:navigate class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-md">
                        {{ $company ? 'Save changes' : 'Add company' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
