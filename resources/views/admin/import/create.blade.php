<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Import Gameweek from FPL') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white/10 backdrop-blur-md overflow-hidden shadow-sm sm:rounded-lg border border-white/20">
                <div class="p-6 text-white">
                    <form method="POST" action="{{ route('admin.import.store') }}">
                        @csrf

                        <div class="mb-6">
                            <h3 class="text-lg font-bold mb-2">How this works</h3>
                            <p class="text-gray-300 text-sm mb-4">
                                Enter a Gameweek number (e.g. 1). We will fetch the official fixtures from the Fantasy
                                Premier League API.
                                <br>
                                We will try to map the teams to your local team list using the FPL Data file.
                                <br>
                                A new Gameweek will be created (or updated if exists) with these matches.
                            </p>
                        </div>

                        <!-- Tournament Selection -->
                        <div class="mb-4">
                            <x-input-label for="tournament_id" :value="__('Assign to Tournament')" class="text-white" />
                            <select id="tournament_id" name="tournament_id"
                                class="block mt-1 w-full rounded-md shadow-sm border-gray-600 bg-slate-700 text-white focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                @foreach($tournaments as $tournament)
                                    <option value="{{ $tournament->id }}" {{ $tournament->is_active ? 'selected' : '' }}>
                                        {{ $tournament->name }} {{ $tournament->is_active ? '(Active)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('tournament_id')" class="mt-2" />
                        </div>

                        <!-- Gameweek Number -->
                        <div class="mb-4">
                            <x-input-label for="gameweek_number" :value="__('FPL Gameweek Number')"
                                class="text-white" />
                            <x-text-input id="gameweek_number"
                                class="block mt-1 w-full bg-slate-700 border-gray-600 text-white" type="number" min="1"
                                max="38" name="gameweek_number" required autofocus placeholder="e.g. 21" />
                            <x-input-error :messages="$errors->get('gameweek_number')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.gameweeks.index') }}"
                                class="text-gray-400 hover:text-white mr-4">Cancel</a>
                            <x-primary-button
                                class="bg-white !text-black font-black uppercase tracking-wider hover:bg-pl-green hover:text-pl-purple transition-all duration-300 transform hover:scale-105 shadow-[0_0_10px_rgba(255,255,255,0.2)] hover:shadow-[0_0_20px_rgba(0,255,135,0.5)] border-0">
                                {{ __('Fetch & Import') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>