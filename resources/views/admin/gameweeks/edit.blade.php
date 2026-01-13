<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white dark:text-gray-200 leading-tight">
            {{ __('Edit Gameweek') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.gameweeks.update', $gameweek) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                :value="old('name', $gameweek->name)" required autofocus />
                        </div>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="start_date" :value="__('Start Date')" />
                                <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date"
                                    :value="old('start_date', $gameweek->start_date->format('Y-m-d'))" required />
                            </div>
                            <div>
                                <x-input-label for="end_date" :value="__('End Date')" />
                                <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date"
                                    :value="old('end_date', $gameweek->end_date->format('Y-m-d'))" required />
                            </div>
                        </div>
                        <div class="mb-4">
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full">
                                <option value="upcoming" {{ old('status', $gameweek->status) === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                                <option value="active" {{ old('status', $gameweek->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="completed" {{ old('status', $gameweek->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                        <div class="flex items-center justify-end">
                            <a href="{{ route('admin.gameweeks.index') }}"
                                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 mr-4">Cancel</a>
                            <x-primary-button
                                class="bg-white text-black font-black uppercase tracking-wider hover:bg-pl-green hover:text-pl-purple transition-all duration-300 transform hover:scale-105 shadow-[0_0_10px_rgba(255,255,255,0.2)] hover:shadow-[0_0_20px_rgba(0,255,135,0.5)] border-0">
                                {{ __('Update Gameweek') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>