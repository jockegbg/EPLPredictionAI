<section>
    <header>
        <h2 class="text-lg font-medium text-white">
            {{ __('Passkeys') }}
        </h2>

        <p class="mt-1 text-sm text-zinc-400">
            {{ __('Manage your passkeys for passwordless authentication.') }}
        </p>
    </header>

    <div class="mt-6 space-y-6">
        <!-- List Existing Passkeys -->
        @if($user->passkeys->count() > 0)
            <div class="space-y-2">
                @foreach($user->passkeys as $passkey)
                    <div class="flex items-center justify-between p-3 bg-black border border-zinc-700 rounded-md">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">🔑</span>
                            <div>
                                <div class="text-white font-medium">{{ $passkey->name ?? 'Passkey' }}</div>
                                <div class="text-xs text-zinc-500">Added {{ $passkey->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('passkeys.destroy', $passkey) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300 text-sm font-medium">
                                {{ __('Remove') }}
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-zinc-500 italic">{{ __('No passkeys configured.') }}</p>
        @endif

        <!-- Create New Passkey -->
        <div x-data="{ loading: false }">
            <button @click="registerPasskey()" :disabled="loading" type="button"
                class="inline-flex items-center px-4 py-2 bg-pl-pink border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-pl-pink/80 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Create Passkey') }}
            </button>
        </div>

        <form id="passkey-register-form" method="POST" action="{{ route('passkeys.store') }}" style="display: none;">
            @csrf
            <input type="hidden" name="passkey" id="passkey-response-input">
            <input type="hidden" name="passkey_options" id="passkey-options-input">
        </form>

        <script>
            async function registerPasskey() {
                try {
                    // 1. Fetch Options
                    const response = await fetch('{{ route('passkeys.register_options') }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    if (!response.ok) throw new Error('Failed to fetch registration options');
                    const options = await response.json();

                    // 2. Browser Ceremony
                    const startRegistrationResponse = await SimpleWebAuthnBrowser.startRegistration({ optionsJSON: options });

                    // 3. Submit Response with options
                    document.getElementById('passkey-response-input').value = JSON.stringify(startRegistrationResponse);
                    document.getElementById('passkey-options-input').value = JSON.stringify(options);
                    document.getElementById('passkey-register-form').submit();

                } catch (error) {
                    console.error(error);
                    alert('Passkey creation failed: ' + error.message);
                }
            }
        </script>
    </div>
</section>