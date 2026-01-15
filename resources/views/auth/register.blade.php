<x-guest-layout>
    <div x-data="{ isLoading: false }">
        <form method="POST" action="{{ route('register') }}" @submit="isLoading = true">
            @csrf

            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input id="name"
                    class="block mt-1 w-full bg-black/50 border-white/10 text-white focus:border-pl-green focus:ring-pl-green"
                    type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div class="mt-4">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email"
                    class="block mt-1 w-full bg-black/50 border-white/10 text-white focus:border-pl-green focus:ring-pl-green"
                    type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Favorite Team -->
            <div class="mt-4">
                <x-input-label for="favorite_team" :value="__('Favorite Team')" />
                <select id="favorite_team" name="favorite_team"
                    class="block mt-1 w-full bg-black/50 border-white/10 text-white focus:border-pl-green focus:ring-pl-green rounded-md shadow-sm">
                    <option value="">Select a team...</option>
                    @foreach(config('teams', []) as $team)
                        <option value="{{ $team }}" {{ old('favorite_team') == $team ? 'selected' : '' }}>
                            {{ $team }}
                        </option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('favorite_team')" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />

                <x-text-input id="password"
                    class="block mt-1 w-full bg-black/50 border-white/10 text-white focus:border-pl-green focus:ring-pl-green"
                    type="password" name="password" required autocomplete="new-password" />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

                <x-text-input id="password_confirmation"
                    class="block mt-1 w-full bg-black/50 border-white/10 text-white focus:border-pl-green focus:ring-pl-green"
                    type="password" name="password_confirmation" required autocomplete="new-password" />

                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-300 hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <x-primary-button
                    class="ms-4 bg-white text-black font-black uppercase tracking-wider hover:bg-pl-green hover:text-pl-purple transition-all duration-300 transform hover:scale-105 shadow-[0_0_10px_rgba(255,255,255,0.2)] hover:shadow-[0_0_20px_rgba(0,255,135,0.5)] border-0 disabled:opacity-50 disabled:scale-100 disabled:cursor-not-allowed"
                    x-bind:disabled="isLoading">
                    <span x-show="!isLoading">{{ __('Register') }}</span>
                    <span x-show="isLoading" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-black" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Creating Account...
                    </span>
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>