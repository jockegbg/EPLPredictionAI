<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white dark:text-gray-200 leading-tight">
            {{ __('Edit Match') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-zinc-900 overflow-hidden shadow-2xl sm:rounded-lg border border-zinc-800">
                <div class="p-6 text-white">

                    <form method="POST" action="{{ route('admin.matches.update', $match) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Home Team -->
                            <div>
                                <x-input-label for="home_team" :value="__('Home Team')" class="text-white" />
                                @if($match->gameweek && $match->gameweek->is_custom)
                                    <x-text-input id="home_team" class="block mt-1 w-full bg-black border-zinc-700 text-white focus:border-pl-green focus:ring-pl-green" 
                                        type="text" name="home_team" :value="old('home_team', $match->home_team)" required />
                                @else
                                    <select id="home_team" name="home_team"
                                        class="block mt-1 w-full rounded-md shadow-sm border-zinc-700 bg-black text-white focus:border-pl-green focus:ring-pl-green"
                                        required>
                                        @foreach($teams as $team)
                                            <option value="{{ $team }}" {{ old('home_team', $match->home_team) == $team ? 'selected' : '' }}>
                                                {{ $team }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                                <x-input-error :messages="$errors->get('home_team')" class="mt-2" />
                            </div>

                            <!-- Away Team -->
                            <div>
                                <x-input-label for="away_team" :value="__('Away Team')" class="text-white" />
                                @if($match->gameweek && $match->gameweek->is_custom)
                                    <x-text-input id="away_team" class="block mt-1 w-full bg-black border-zinc-700 text-white focus:border-pl-green focus:ring-pl-green" 
                                        type="text" name="away_team" :value="old('away_team', $match->away_team)" required />
                                @else
                                    <select id="away_team" name="away_team"
                                        class="block mt-1 w-full rounded-md shadow-sm border-zinc-700 bg-black text-white focus:border-pl-green focus:ring-pl-green"
                                        required>
                                        @foreach($teams as $team)
                                            <option value="{{ $team }}" {{ old('away_team', $match->away_team) == $team ? 'selected' : '' }}>
                                                {{ $team }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                                <x-input-error :messages="$errors->get('away_team')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Scores -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <div>
                                <x-input-label for="home_score" :value="__('Home Score')" class="text-white" />
                                <x-text-input id="home_score"
                                    class="block mt-1 w-full bg-black border-zinc-700 text-white focus:border-pl-green focus:ring-pl-green"
                                    type="number" name="home_score" :value="old('home_score', $match->home_score)"
                                    min="0" />
                                <x-input-error :messages="$errors->get('home_score')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="away_score" :value="__('Away Score')" class="text-white" />
                                <x-text-input id="away_score"
                                    class="block mt-1 w-full bg-black border-zinc-700 text-white focus:border-pl-green focus:ring-pl-green"
                                    type="number" name="away_score" :value="old('away_score', $match->away_score)"
                                    min="0" />
                                <x-input-error :messages="$errors->get('away_score')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Kick-off Time & Status -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <div>
                                <x-input-label for="start_time" :value="__('Kick-off Time (UTC)')" class="text-white" />
                                <x-text-input id="start_time"
                                    class="block mt-1 w-full border-zinc-700 bg-black text-white focus:border-pl-green focus:ring-pl-green rounded-md shadow-sm"
                                    type="datetime-local" name="start_time"
                                    :value="$match->start_time->format('Y-m-d\TH:i')" required />
                                <x-input-error :messages="$errors->get('start_time')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="status" :value="__('Status')" class="text-white" />
                                <select id="status" name="status"
                                    class="block mt-1 w-full rounded-md shadow-sm border-zinc-700 bg-black text-white focus:border-pl-green focus:ring-pl-green"
                                    required>
                                    <option value="scheduled" {{ $match->status == 'scheduled' ? 'selected' : '' }}>
                                        Scheduled</option>
                                    <option value="in_progress" {{ $match->status == 'in_progress' ? 'selected' : '' }}>In
                                        Progress</option>
                                    <option value="completed" {{ $match->status == 'completed' ? 'selected' : '' }}>
                                        Completed</option>
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-6">
                            <button type="button" class="text-red-500 hover:text-red-700 font-bold"
                                onclick="if(confirm('Are you sure?')) document.getElementById('delete-form').submit();">
                                Delete Match
                            </button>

                            <div class="flex items-center">
                                <a href="{{ route('admin.gameweeks.index') }}"
                                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 mr-4">Cancel</a>
                                <x-primary-button
                                    class="bg-white !text-black font-black uppercase tracking-wider hover:bg-pl-green hover:text-pl-purple transition-all duration-300 transform hover:scale-105 shadow-[0_0_10px_rgba(255,255,255,0.2)] hover:shadow-[0_0_20px_rgba(0,255,135,0.5)] border-0">
                                    {{ __('Update Match') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>

                    <!-- Delete Form Separation (Nested form issue fix) -->
                    <form id="delete-form" method="POST" action="{{ route('admin.matches.destroy', $match) }}"
                        class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>