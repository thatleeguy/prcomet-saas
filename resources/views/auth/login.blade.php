<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-slate-900 tracking-tight">Welcome back.</h1>
            <p class="text-sm text-slate-500 mt-2">Sign in to see the writers we've surfaced for you.</p>
        </div>

        <x-validation-errors class="mb-4" />

        @session('status')
            <div class="mb-4 font-medium text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-md px-3 py-2">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div>
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            </div>

            <div class="flex items-center justify-between">
                <label for="remember_me" class="flex items-center cursor-pointer">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="ms-2 text-sm text-slate-600">{{ __('Remember me') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm text-slate-500 hover:text-slate-900 transition-colors" href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <div>
                <x-button class="w-full justify-center !bg-slate-900 hover:!bg-slate-800 !px-4 !py-2.5 !text-sm !normal-case !tracking-normal">
                    {{ __('Sign in') }}
                </x-button>
            </div>
        </form>

        @if (Route::has('register'))
            <p class="mt-8 pt-6 border-t border-slate-100 text-sm text-slate-500 text-center">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-medium text-slate-900 hover:text-brand-700 transition-colors">Create one</a>
            </p>
        @endif
    </x-authentication-card>
</x-guest-layout>
