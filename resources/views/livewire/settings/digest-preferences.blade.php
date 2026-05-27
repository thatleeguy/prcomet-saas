<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Digest preferences</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-md px-4 py-3 text-sm">{{ session('status') }}</div>
            @endif

            <form wire:submit="save" class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <h3 class="font-semibold text-gray-900">Email me about new matches</h3>
                <p class="text-sm text-gray-500">Choose how often we should email you a digest of new outreach matches. You can always check the dashboard for matches anytime.</p>

                @foreach ([
                    'daily' => ['title' => 'Daily', 'help' => 'A daily digest of new matches.'],
                    'weekly' => ['title' => 'Weekly', 'help' => 'A weekly roundup. Lighter inbox, less responsive.'],
                    'off' => ['title' => 'Off', 'help' => "Don't email me. I'll check the dashboard."],
                ] as $value => $opt)
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="radio" name="frequency" value="{{ $value }}"
                            wire:model="frequency"
                            class="mt-1 border-gray-300 text-amber-600 focus:ring-amber-500" />
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $opt['title'] }}</div>
                            <div class="text-sm text-gray-500">{{ $opt['help'] }}</div>
                        </div>
                    </label>
                @endforeach

                <div class="pt-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm rounded-md">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
