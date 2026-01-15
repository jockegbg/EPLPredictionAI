<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Add New Tournament') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white/10 backdrop-blur-md overflow-hidden shadow-sm sm:rounded-lg border border-white/20">
                <div class="p-6 text-white">
                    <form method="POST" action="{{ route('admin.tournaments.store') }}">
                        @csrf

                        <!-- Name -->
                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Tournament Name (e.g., Premier League 25/26)')"
                                class="text-white" />
                            <x-text-input id="name" class="block mt-1 w-full bg-slate-700 border-gray-600 text-white"
                                type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Participants -->
                        <div class="mb-6">
                            <h3 class="text-lg font-bold mb-2">Participants</h3>
                            <div
                                class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 max-h-64 overflow-y-auto p-4 bg-slate-800 rounded-md border border-gray-700">
                                @foreach($users as $user)
                                    <label
                                        class="inline-flex items-center space-x-2 cursor-pointer hover:bg-slate-700 p-2 rounded">
                                        <input type="checkbox" name="users[]" value="{{ $user->id }}"
                                            class="rounded border-gray-600 bg-slate-900 text-pl-green focus:ring-pl-green"
                                            {{ (is_array(old('users')) && in_array($user->id, old('users'))) ? 'checked' : '' }}>
                                        <span class="text-sm">{{ $user->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-xs text-gray-400 mt-2">Select users who are competing in this tournament.</p>
                        </div>

                        <!-- Scoring Configuration -->
                        <div class="mb-6 border-t border-white/10 pt-6">
                            <h3 class="text-lg font-bold mb-4 text-pl-green">Scoring Logic</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="score_correct_score" :value="__('Exact Score Points')"
                                        class="text-white" />
                                    <x-text-input id="score_correct_score"
                                        class="block mt-1 w-full bg-slate-700 border-gray-600 text-white" type="number"
                                        name="score_correct_score" :value="old('score_correct_score', 40)" required />
                                </div>
                                <div>
                                    <x-input-label for="score_correct_outcome" :value="__('Correct Outcome Points')"
                                        class="text-white" />
                                    <x-text-input id="score_correct_outcome"
                                        class="block mt-1 w-full bg-slate-700 border-gray-600 text-white" type="number"
                                        name="score_correct_outcome" :value="old('score_correct_outcome', 10)"
                                        required />
                                </div>
                                <div>
                                    <x-input-label for="score_goal_difference" :value="__('Goal Difference Bonus')"
                                        class="text-white" />
                                    <x-text-input id="score_goal_difference"
                                        class="block mt-1 w-full bg-slate-700 border-gray-600 text-white" type="number"
                                        name="score_goal_difference" :value="old('score_goal_difference', 0)"
                                        required />
                                    <p class="text-xs text-xs text-gray-400 mt-1">Awarded if outcome & diff are correct
                                        but not exact score.</p>
                                </div>
                                <div>
                                    <x-input-label for="score_wrong_outcome_penalty" :value="__('Wrong Outcome Penalty')" class="text-white" />
                                    <x-text-input id="score_wrong_outcome_penalty"
                                        class="block mt-1 w-full bg-slate-700 border-gray-600 text-white" type="number"
                                        name="score_wrong_outcome_penalty" :value="old('score_wrong_outcome_penalty', 0)" required />
                                    <p class="text-xs text-gray-400 mt-1">Use POSITIVE number. It will be subtracted.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Features -->
                        <div class="mb-6 border-t border-white/10 pt-6">
                            <h3 class="text-lg font-bold mb-4 text-pl-blue">Features</h3>

                            <label for="is_cashout_enabled" class="inline-flex items-center">
                                <input id="is_cashout_enabled" type="checkbox"
                                    class="rounded border-gray-600 bg-slate-700 text-pl-pink shadow-sm focus:ring-pl-pink"
                                    name="is_cashout_enabled" value="1" {{ old('is_cashout_enabled') ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-300">{{ __('Enable Cash Out System') }}</span>
                            </label>

                            <div class="mt-4 flex flex-col gap-4">
                                <label for="is_double_down_enabled" class="inline-flex items-center">
                                    <input id="is_double_down_enabled" type="checkbox"
                                        class="rounded border-gray-600 bg-slate-700 text-pl-pink shadow-sm focus:ring-pl-pink"
                                        name="is_double_down_enabled" value="1" {{ old('is_double_down_enabled', true) ? 'checked' : '' }}>
                                    <span
                                        class="ml-2 text-sm text-gray-300">{{ __('Enable "Double Down" (2x) Chip') }}</span>
                                </label>

                                <label for="is_defence_enabled" class="inline-flex items-center">
                                    <input id="is_defence_enabled" type="checkbox"
                                        class="rounded border-gray-600 bg-slate-700 text-pl-pink shadow-sm focus:ring-pl-pink"
                                        name="is_defence_enabled" value="1" {{ old('is_defence_enabled', true) ? 'checked' : '' }}>
                                    <span
                                        class="ml-2 text-sm text-gray-300">{{ __('Enable "Defence" (Shield) Chip') }}</span>
                                </label>
                            </div>

                            <!-- Active Tournament -->
                            <div class="block mt-4 mb-4">
                                <label for="is_active" class="inline-flex items-center">
                                    <input id="is_active" type="checkbox"
                                        class="rounded border-gray-600 bg-slate-700 text-pl-pink shadow-sm focus:ring-pl-pink"
                                        name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-300">{{ __('Set as Active Tournament') }}</span>
                                </label>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <a href="{{ route('admin.tournaments.index') }}"
                                    class="text-gray-400 hover:text-white mr-4">Cancel</a>
                                <x-primary-button
                                    class="bg-white !text-black font-black uppercase tracking-wider hover:bg-pl-green hover:text-pl-purple transition-all duration-300 transform hover:scale-105 shadow-[0_0_10px_rgba(255,255,255,0.2)] hover:shadow-[0_0_20px_rgba(0,255,135,0.5)] border-0">
                                    {{ __('Create Tournament') }}
                                </x-primary-button>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>