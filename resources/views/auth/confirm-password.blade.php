<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-slate-900 tracking-tight">Confirm your password.</h1>
            <p class="text-sm text-slate-500 mt-2">This is a secure area. Re-enter your password to continue.</p>
        </div>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
            @csrf

            <div>
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" autofocus />
            </div>

            <div class="pt-2">
                <x-button class="w-full justify-center !bg-slate-900 hover:!bg-slate-800 !px-4 !py-2.5 !text-sm !normal-case !tracking-normal">
                    {{ __('Confirm') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
