<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Edit User: ') }} {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-pl-green text-black p-4 rounded font-bold border border-white">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-pl-purple-dark overflow-hidden shadow-sm sm:rounded-lg border border-pl-pink/20 p-6">

                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Name -->
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="$user->name"
                            required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <!-- Email -->
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                            :value="$user->email" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Is Admin -->
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="is_admin" value="0">
                        <input type="checkbox" name="is_admin" id="is_admin" value="1" {{ $user->is_admin ? 'checked' : '' }} class="rounded border-gray-700 bg-gray-900 text-pl-pink focus:ring-pl-pink">
                        <label for="is_admin" class="text-white font-medium">Grant Admin Privileges</label>
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Update User') }}</x-primary-button>
                        <a href="{{ route('admin.users.index') }}"
                            class="text-gray-400 hover:text-white text-sm">Cancel</a>
                    </div>
                </form>

            </div>

            <!-- Danger Zone -->
            <div class="mt-8 bg-red-900/20 overflow-hidden shadow-sm sm:rounded-lg border border-red-500/30 p-6">
                <h3 class="text-red-400 font-bold mb-4 uppercase tracking-widest text-xs">Danger Zone</h3>

                <div class="space-y-4">
                    <!-- Reset Password -->
                    <div class="flex justify-between items-center border-b border-red-500/10 pb-4">
                        <div>
                            <div class="text-white font-bold">Reset Password</div>
                            <div class="text-gray-400 text-sm">Generates a random password for the user.</div>
                        </div>
                        <form method="POST" action="{{ route('admin.users.reset-password', $user) }}"
                            onsubmit="return confirm('Resetting password. Are you sure?');">
                            @csrf
                            <button type="submit"
                                class="bg-red-600 hover:bg-red-500 text-white px-3 py-1 rounded text-sm font-bold">
                                Reset Password
                            </button>
                        </form>
                    </div>

                    <!-- Clear Passkeys -->
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-white font-bold">Remove Passkeys</div>
                            <div class="text-gray-400 text-sm">Deletes all WebAuthn passkeys for this user.</div>
                        </div>
                        <form method="POST" action="{{ route('admin.users.remove-passkeys', $user) }}"
                            onsubmit="return confirm('Removing all passkeys. Are you sure?');">
                            @csrf
                            <button type="submit"
                                class="bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm font-bold border border-gray-500">
                                Remove Passkeys
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>