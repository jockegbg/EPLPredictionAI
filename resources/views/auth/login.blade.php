<x-guest-layout>
    <div x-data="{ 
        hasPasskey: localStorage.getItem('has_passkey') === 'true',
        showEmailForm: false,
        isLoading: false
    }" x-init="$watch('showEmailForm', value => { if(value) $nextTick(() => $refs.email.focus()) })">

        <!-- Smart Passkey Prompt -->
        <template x-if="hasPasskey && !showEmailForm">
            <div class="text-center">
                <div class="mb-6">
                    <div class="mx-auto w-16 h-16 bg-pl-pink/20 rounded-full flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-pl-pink" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-white mb-2">Welcome Back!</h2>
                    <p class="text-gray-400 text-sm">Sign in quickly with your passkey.</p>
                </div>

                <x-passkeys::authenticate>
                    <x-primary-button type="button"
                        class="w-full justify-center py-3 text-lg bg-pl-pink hover:bg-pl-pink/90 disabled:opacity-50 disabled:cursor-not-allowed"
                        x-bind:disabled="isLoading" @click="isLoading = true">
                        <span x-show="!isLoading">{{ __('Sign in with Passkey') }}</span>
                        <span x-show="isLoading" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Wait...
                        </span>
                    </x-primary-button>
                </x-passkeys::authenticate>

                <div class="mt-6">
                    <button @click="showEmailForm = true"
                        class="text-sm text-gray-400 hover:text-white underline decoration-dashed underline-offset-4">
                        or sign in with password
                    </button>
                </div>
            </div>
        </template>

        <!-- Standard Login Form -->
        <div x-show="!hasPasskey || showEmailForm" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100">
            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" @submit="isLoading = true">
                @csrf

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input x-ref="email" id="email" class="block mt-1 w-full" type="email" name="email"
                        :value="old('email')" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password" :value="__('Password')" />

                    <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                        autocomplete="current-password" />

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Remember Me -->
                <div class="block mt-4">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            name="remember">
                        <span class="ms-2 text-sm text-gray-400">{{ __('Remember me') }}</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-4">
                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-gray-400 hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif

                    <a class="underline text-sm text-gray-400 hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 ms-4"
                        href="{{ route('register') }}">
                        {{ __('Register') }}
                    </a>

                    <x-primary-button class="ms-3 disabled:opacity-50 disabled:cursor-not-allowed"
                        x-bind:disabled="isLoading">
                        <span x-show="!isLoading">{{ __('Log in') }}</span>
                        <span x-show="isLoading" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Log. in...
                        </span>
                    </x-primary-button>
                </div>
            </form>

            <div class="mt-6 border-t border-white/10 pt-6">
                <div class="relative flex justify-center text-sm mb-4">
                    <span class="px-2 bg-transparent text-gray-400">Or continue with</span>
                </div>
                <div class="flex justify-center">
                    <x-passkeys::authenticate>
                        <x-primary-button type="button"
                            class="w-full justify-center bg-white/10 hover:bg-white/20 border-white/10 disabled:opacity-50"
                            x-bind:disabled="isLoading" @click="isLoading = true">
                            <span x-show="!isLoading">{{ __('Sign in with Passkey') }}</span>
                            <span x-show="isLoading">Authenticating...</span>
                        </x-primary-button>
                    </x-passkeys::authenticate>
                </div>

                <!-- Back to Smart Login (only if has passkey) -->
                <template x-if="hasPasskey">
                    <div class="text-center mt-4">
                        <button @click="showEmailForm = false" class="text-xs text-pl-pink hover:text-pl-pink/80">
                            &larr; Back to One-Click Login
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-guest-layout>