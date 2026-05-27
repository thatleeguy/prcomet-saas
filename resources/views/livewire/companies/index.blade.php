<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Companies</h2>
            <div class="text-sm text-gray-500">
                {{ $this->seatsRemaining }} of {{ auth()->user()->currentTeam->max_companies }} seats free
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-md px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="flex items-center justify-between gap-4">
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search companies…"
                    class="flex-1 max-w-sm rounded-md border-gray-300 focus:border-amber-500 focus:ring-amber-500"
                />

                @if ($this->canAdd)
                    <a href="{{ route('companies.create') }}" wire:navigate
                        class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-md">
                        Add company
                    </a>
                @else
                    <span class="text-sm text-gray-500" title="Contact us to increase your seat limit">
                        Seat limit reached
                    </span>
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                @if ($this->companies->isEmpty())
                    <div class="p-12 text-center text-gray-500">
                        <p class="text-lg font-medium text-gray-700">No companies yet</p>
                        <p class="text-sm mt-1">Add your first monitored company to start ingesting press releases.</p>
                    </div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ticker</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tags</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($this->companies as $company)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm">
                                        <a href="{{ route('companies.show', $company) }}" wire:navigate
                                            class="font-medium text-amber-700 hover:text-amber-900">
                                            {{ $company->name }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $company->ticker }}{{ $company->exchange ? ' · '.$company->exchange : '' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ implode(', ', $company->sector_tags ?? []) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        @if ($company->is_active)
                                            <span class="inline-flex px-2 text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                        @else
                                            <span class="inline-flex px-2 text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-700">Paused</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>
