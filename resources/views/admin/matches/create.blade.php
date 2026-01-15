<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white dark:text-gray-200 leading-tight">
            {{ __('Add Match to') }} {{ $gameweek->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg backdrop-blur-md bg-opacity-80">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form method="POST" action="{{ route('admin.matches.store', $gameweek) }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Home Team -->
                            <div>
                                <x-input-label for="home_team" :value="__('Home Team')" />
                                @if($gameweek->is_custom)
                                    <x-text-input id="home_team" class="block mt-1 w-full" type="text" name="home_team"
                                        placeholder="Enter Home Team Name" required />
                                @else
                                    <select id="home_team" name="home_team"
                                        class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-slate-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                        required>
                                        <option value="">Select Home Team</option>
                                        @foreach($teams as $team)
                                            <option value="{{ $team }}">{{ $team }}</option>
                                        @endforeach
                                    </select>
                                @endif
                                <x-input-error :messages="$errors->get('home_team')" class="mt-2" />
                            </div>

                            <!-- Away Team -->
                            <div>
                                <x-input-label for="away_team" :value="__('Away Team')" />
                                @if($gameweek->is_custom)
                                    <x-text-input id="away_team" class="block mt-1 w-full" type="text" name="away_team"
                                        placeholder="Enter Away Team Name" required />
                                @else
                                    <select id="away_team" name="away_team"
                                        class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-slate-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                        required>
                                        <option value="">Select Away Team</option>
                                        @foreach($teams as $team)
                                            <option value="{{ $team }}">{{ $team }}</option>
                                        @endforeach
                                    </select>
                                @endif
                                <x-input-error :messages="$errors->get('away_team')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Kick-off Time -->
                        <div class="mt-4">
                            <x-input-label for="start_time" :value="__('Kick-off Time (UTC)')" />
                            <div class="text-xs text-gray-500 mb-1">Please enter the time in UTC. It will be converted
                                to user's local time on display.</div>
                            <x-text-input id="start_time"
                                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-slate-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                type="datetime-local" name="start_time" required />
                            <x-input-error :messages="$errors->get('start_time')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.gameweeks.index') }}"
                                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 mr-4">Cancel</a>
                            <x-primary-button
                                class="bg-white !text-black font-black uppercase tracking-wider hover:bg-pl-green hover:text-pl-purple transition-all duration-300 transform hover:scale-105 shadow-[0_0_10px_rgba(255,255,255,0.2)] hover:shadow-[0_0_20px_rgba(0,255,135,0.5)] border-0">
                                {{ __('Create Match') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>