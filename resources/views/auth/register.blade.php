<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-slate-900 tracking-tight">Create your account.</h1>
            <p class="text-sm text-slate-500 mt-2">It takes a minute. We'll review and activate within a business day.</p>
        </div>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <div>
                <x-label for="name" value="{{ __('Name') }}" />
                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            </div>

            <div>
                <x-label for="email" value="{{ __('Work email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            </div>

            <div>
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            </div>

            <div>
                <x-label for="password_confirmation" value="{{ __('Confirm password') }}" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div>
                    <x-label for="terms">
                        <div class="flex items-start gap-2">
                            <x-checkbox name="terms" id="terms" required class="mt-0.5" />

                            <div class="text-sm text-slate-600 leading-relaxed">
                                {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                        'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="text-slate-900 hover:text-brand-700 underline underline-offset-2">'.__('Terms of Service').'</a>',
                                        'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="text-slate-900 hover:text-brand-700 underline underline-offset-2">'.__('Privacy Policy').'</a>',
                                ]) !!}
                            </div>
                        </div>
                    </x-label>
                </div>
            @endif

            <div class="pt-2">
                <x-button class="w-full justify-center !bg-slate-900 hover:!bg-slate-800 !px-4 !py-2.5 !text-sm !normal-case !tracking-normal">
                    {{ __('Create account') }}
                </x-button>
            </div>
        </form>

        <p class="mt-8 pt-6 border-t border-slate-100 text-sm text-slate-500 text-center">
            Already have an account?
            <a href="{{ route('login') }}" class="font-medium text-slate-900 hover:text-brand-700 transition-colors">Sign in</a>
        </p>
    </x-authentication-card>
</x-guest-layout>
