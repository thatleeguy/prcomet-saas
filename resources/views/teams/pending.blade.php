<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Account pending activation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-lg p-8 space-y-6">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center">
                            <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Thanks for signing up, {{ auth()->user()->name }}.
                        </h3>
                        <p class="text-gray-600">
                            Your team <strong>{{ auth()->user()->currentTeam?->name ?? '—' }}</strong>
                            is currently pending activation. PrComet is in early access while we
                            calibrate against real customer use, so we activate new teams manually.
                        </p>
                        <p class="text-gray-600">
                            We'll reach out at <strong>{{ auth()->user()->email }}</strong> to set up
                            billing and grant you access. If you haven't heard from us within a business
                            day, reply to your signup confirmation email and we'll get it sorted.
                        </p>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6">
                    <h4 class="font-medium text-gray-900 mb-2">While you wait</h4>
                    <ul class="list-disc pl-5 text-gray-600 space-y-1 text-sm">
                        <li>Have your company's press-release RSS feed URL handy.</li>
                        <li>If you're an agency, think about how many client companies you'll want to monitor.</li>
                        <li>Note any specific publications, podcasts, or journalists you want us to make sure are tracked.</li>
                    </ul>
                </div>

                <div class="border-t border-gray-100 pt-6 flex items-center justify-between text-sm">
                    <span class="text-gray-500">Signed in as {{ auth()->user()->email }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-600 underline hover:text-gray-900">
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
