<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-slate-900 tracking-tight">Verify your email.</h1>
            <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                We sent a verification link to your inbox. Click it to continue. If it didn't arrive, we'll happily resend.
            </p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-6 font-medium text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-md px-3 py-2">
                A new verification link is on its way.
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="mb-6">
            @csrf
            <x-button class="w-full justify-center !bg-slate-900 hover:!bg-slate-800 !px-4 !py-2.5 !text-sm !normal-case !tracking-normal">
                {{ __('Resend verification email') }}
            </x-button>
        </form>

        <div class="flex items-center justify-between text-sm pt-6 border-t border-slate-100">
            <a href="{{ route('profile.show') }}" class="text-slate-500 hover:text-slate-900 transition-colors">
                Edit profile
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-slate-500 hover:text-slate-900 transition-colors">
                    Sign out
                </button>
            </form>
        </div>
    </x-authentication-card>
</x-guest-layout>
