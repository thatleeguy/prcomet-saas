<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div x-data="{ recovery: false }">
            <div class="mb-8">
                <h1 class="text-2xl font-semibold text-slate-900 tracking-tight">Two-factor verification.</h1>
                <p class="text-sm text-slate-500 mt-2 leading-relaxed" x-show="! recovery">
                    Open your authenticator app and enter the six-digit code.
                </p>
                <p class="text-sm text-slate-500 mt-2 leading-relaxed" x-cloak x-show="recovery">
                    Enter one of your emergency recovery codes.
                </p>
            </div>

            <x-validation-errors class="mb-4" />

            <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-4">
                @csrf

                <div x-show="! recovery">
                    <x-label for="code" value="{{ __('Authentication code') }}" />
                    <x-input id="code" class="block mt-1 w-full tracking-widest text-center" type="text" inputmode="numeric" name="code" autofocus x-ref="code" autocomplete="one-time-code" />
                </div>

                <div x-cloak x-show="recovery">
                    <x-label for="recovery_code" value="{{ __('Recovery code') }}" />
                    <x-input id="recovery_code" class="block mt-1 w-full font-mono" type="text" name="recovery_code" x-ref="recovery_code" autocomplete="one-time-code" />
                </div>

                <div class="pt-2">
                    <x-button class="w-full justify-center !bg-slate-900 hover:!bg-slate-800 !px-4 !py-2.5 !text-sm !normal-case !tracking-normal">
                        {{ __('Verify') }}
                    </x-button>
                </div>
            </form>

            <div class="text-center mt-6 pt-6 border-t border-slate-100 text-sm">
                <button type="button" class="text-slate-500 hover:text-slate-900 transition-colors"
                                x-show="! recovery"
                                x-on:click="recovery = true; $nextTick(() => { $refs.recovery_code.focus() })">
                    Lost your authenticator? Use a recovery code
                </button>

                <button type="button" class="text-slate-500 hover:text-slate-900 transition-colors"
                                x-cloak x-show="recovery"
                                x-on:click="recovery = false; $nextTick(() => { $refs.code.focus() })">
                    Use an authentication code instead
                </button>
            </div>
        </div>
    </x-authentication-card>
</x-guest-layout>
