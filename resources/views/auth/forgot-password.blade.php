<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-slate-900 tracking-tight">Reset your password.</h1>
            <p class="text-sm text-slate-500 mt-2">Enter your email and we'll send a link to set a new one.</p>
        </div>

        @session('status')
            <div class="mb-4 font-medium text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-md px-3 py-2">
                {{ $value }}
            </div>
        @endsession

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div>
                <x-button class="w-full justify-center !bg-slate-900 hover:!bg-slate-800 !px-4 !py-2.5 !text-sm !normal-case !tracking-normal">
                    {{ __('Send reset link') }}
                </x-button>
            </div>
        </form>

        <p class="mt-8 pt-6 border-t border-slate-100 text-sm text-slate-500 text-center">
            Remembered it?
            <a href="{{ route('login') }}" class="font-medium text-slate-900 hover:text-brand-700 transition-colors">Sign in</a>
        </p>
    </x-authentication-card>
</x-guest-layout>
