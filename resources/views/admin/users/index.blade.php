<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Manage Users') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Search -->
            <div class="mb-6">
                <form action="{{ route('admin.users.index') }}" method="GET" class="flex gap-2">
                    <x-text-input name="search" value="{{ request('search') }}" placeholder="Search name or email..."
                        class="w-full md:w-1/3" />
                    <x-primary-button>Search</x-primary-button>
                </form>
            </div>

            <div class="bg-pl-purple-dark overflow-hidden shadow-sm sm:rounded-lg border border-pl-pink/20">
                <div class="p-6 text-gray-100">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-pl-pink border-b border-pl-pink/20">
                                <th class="p-3">ID</th>
                                <th class="p-3">Name</th>
                                <th class="p-3">Email</th>
                                <th class="p-3">Role</th>
                                <th class="p-3">Joined</th>
                                <th class="p-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr class="border-b border-pl-pink/10 hover:bg-pl-purple/50">
                                    <td class="p-3 text-gray-400">#{{ $user->id }}</td>
                                    <td class="p-3 font-bold">{{ $user->name }}</td>
                                    <td class="p-3 text-gray-300">{{ $user->email }}</td>
                                    <td class="p-3">
                                        @if($user->is_admin)
                                            <span
                                                class="bg-pl-green text-black px-2 py-0.5 rounded text-xs font-bold">ADMIN</span>
                                        @else
                                            <span class="text-gray-500 text-xs">User</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-sm text-gray-400">{{ $user->created_at->format('d M Y') }}</td>
                                    <td class="p-3 text-right">
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                            class="text-pl-blue hover:text-white underline mr-2">Edit</a>
                                        <button
                                            @click="$dispatch('open-score-modal', { userId: {{ $user->id }}, userName: '{{ addslashes($user->name) }}' })"
                                            class="text-pl-green hover:text-white underline text-sm mr-2">Submit
                                            Score</button>
                                        <button
                                            @click="$dispatch('open-log-modal', { userId: {{ $user->id }}, userName: '{{ addslashes($user->name) }}' })"
                                            class="text-gray-400 hover:text-white underline text-sm">Logs</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>

            <!-- Manual Score Modal -->
            <div x-data="scoreSubmissionModal"
                @open-score-modal.window="openModal($event.detail.userId, $event.detail.userName)" class="relative z-50"
                aria-labelledby="modal-title" role="dialog" aria-modal="true" x-show="isOpen" style="display: none;">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-show="isOpen"
                    x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div class="relative transform overflow-hidden rounded-lg bg-zinc-900 border border-zinc-700 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                            x-show="isOpen" x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                            <form :action="submitUrl" method="POST">
                                @csrf
                                <div class="bg-zinc-900 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                    <h3 class="text-lg font-semibold leading-6 text-white" id="modal-title">
                                        Submit Score for <span x-text="userName" class="text-pl-green"></span>
                                    </h3>

                                    <div class="mt-4 space-y-4">
                                        <!-- Tournament -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300">Tournament</label>
                                            <select x-model="selectedTournament" @change="fetchGameweeks()"
                                                class="mt-1 block w-full rounded-md border-gray-600 bg-black text-white shadow-sm focus:border-pl-green focus:ring-pl-green sm:text-sm">
                                                <option value="">Select Tournament</option>
                                                <template x-for="t in tournaments" :key="t.id">
                                                    <option :value="t.id" x-text="t.name"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <!-- Gameweek -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300">Gameweek</label>
                                            <select x-model="selectedGameweek" @change="fetchMatches()"
                                                :disabled="!selectedTournament"
                                                class="mt-1 block w-full rounded-md border-gray-600 bg-black text-white shadow-sm focus:border-pl-green focus:ring-pl-green sm:text-sm disabled:opacity-50">
                                                <option value="">Select Gameweek</option>
                                                <template x-for="gw in gameweeks" :key="gw.id">
                                                    <option :value="gw.id" x-text="gw.name"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <!-- Match -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300">Match</label>
                                            <select name="match_id" x-model="selectedMatch"
                                                :disabled="!selectedGameweek" required
                                                class="mt-1 block w-full rounded-md border-gray-600 bg-black text-white shadow-sm focus:border-pl-green focus:ring-pl-green sm:text-sm disabled:opacity-50">
                                                <option value="">Select Match</option>
                                                <template x-for="m in matches" :key="m.id">
                                                    <option :value="m.id" x-text="m.display_name"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <!-- Scores -->
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-300">Home</label>
                                                <input type="number" name="predicted_home" required min="0"
                                                    class="mt-1 block w-full rounded-md border-gray-600 bg-black text-white shadow-sm focus:border-pl-green focus:ring-pl-green sm:text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-300">Away</label>
                                                <input type="number" name="predicted_away" required min="0"
                                                    class="mt-1 block w-full rounded-md border-gray-600 bg-black text-white shadow-sm focus:border-pl-green focus:ring-pl-green sm:text-sm">
                                            </div>
                                        </div>

                                        <!-- Chip -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300">Active Chip</label>
                                            <select name="chip"
                                                class="mt-1 block w-full rounded-md border-gray-600 bg-black text-white shadow-sm focus:border-pl-green focus:ring-pl-green sm:text-sm">
                                                <option value="">None</option>
                                                <option value="double_points">Double Points (x2)</option>
                                                <option value="defence_chip">Defence Chip (No penalty)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="bg-gray-900 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-gray-700">
                                    <button type="submit"
                                        class="inline-flex w-full justify-center rounded-md bg-pl-green px-3 py-2 text-sm font-semibold text-black shadow-sm hover:bg-green-400 sm:ml-3 sm:w-auto">Submit</button>
                                    <button type="button" @click="isOpen = false"
                                        class="mt-3 inline-flex w-full justify-center rounded-md bg-black px-3 py-2 text-sm font-semibold text-gray-300 shadow-sm ring-1 ring-inset ring-gray-600 hover:bg-gray-800 sm:mt-0 sm:w-auto">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Log Viewer Modal -->
            <div x-data="logViewerModal"
                @open-log-modal.window="openModal($event.detail.userId, $event.detail.userName)" class="relative z-50"
                aria-labelledby="modal-title" role="dialog" aria-modal="true" x-show="isOpen" style="display: none;">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-show="isOpen"
                    x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div class="relative transform overflow-hidden rounded-lg bg-zinc-900 border border-zinc-700 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl"
                            x-show="isOpen" x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                            <div class="bg-zinc-900 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-semibold leading-6 text-white" id="modal-title">
                                        Activity Logs: <span x-text="userName" class="text-pl-green"></span>
                                    </h3>
                                    <button @click="isOpen = false" class="text-gray-400 hover:text-white">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="overflow-x-auto max-h-[60vh] space-y-2">
                                    <template x-if="isLoading">
                                        <div class="p-4 text-center text-gray-500">Loading logs...</div>
                                    </template>

                                    <template x-for="session in logs" :key="session.id">
                                        <div class="border border-gray-700 rounded-lg overflow-hidden"
                                            x-data="{ expanded: false }">
                                            <!-- Session Header -->
                                            <div class="bg-black p-3 flex justify-between items-center cursor-pointer hover:bg-gray-900"
                                                @click="expanded = !expanded">
                                                <div class="flex items-center gap-3">
                                                    <span class="text-pl-green font-bold text-sm"
                                                        x-text="new Date(session.created_at).toLocaleString()"></span>
                                                    <span class="text-xs text-gray-500 font-mono"
                                                        x-text="session.ip_address"></span>
                                                    <span
                                                        class="text-xs text-gray-400 bg-gray-800 px-1.5 py-0.5 rounded"
                                                        x-text="session.method"></span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs text-gray-500"
                                                        x-text="session.session_logs.length + ' actions'"></span>
                                                    <svg class="w-4 h-4 text-gray-500 transform transition-transform"
                                                        :class="expanded ? 'rotate-180' : ''" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </div>
                                            </div>

                                            <!-- Session Actions -->
                                            <div x-show="expanded" x-collapse>
                                                <table class="w-full text-left border-collapse text-xs">
                                                    <thead class="bg-gray-900">
                                                        <tr class="text-gray-500 border-b border-gray-700">
                                                            <th class="p-2 w-32">Time</th>
                                                            <th class="p-2 w-24">Action</th>
                                                            <th class="p-2">Details</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-800 bg-zinc-900/50">
                                                        <!-- Original Login Event -->
                                                        <tr class="hover:bg-white/5 font-mono">
                                                            <td class="p-2 text-gray-500"
                                                                x-text="new Date(session.created_at).toLocaleTimeString()">
                                                            </td>
                                                            <td class="p-2 font-bold text-pl-green">LOGIN</td>
                                                            <td class="p-2 text-gray-400">User logged in via <span
                                                                    x-text="session.method"></span></td>
                                                        </tr>

                                                        <template x-for="log in session.session_logs" :key="log.id">
                                                            <tr class="hover:bg-white/5 font-mono">
                                                                <td class="p-2 text-gray-500"
                                                                    x-text="new Date(log.created_at).toLocaleTimeString()">
                                                                </td>
                                                                <td class="p-2 font-bold" :class="{
                                                                    'text-pl-blue': log.action === 'prediction',
                                                                    'text-gray-400': log.action === 'page_visit'
                                                                }" x-text="log.action"></td>
                                                                <td class="p-2 text-gray-300 break-all">
                                                                    <div class="max-w-xl overflow-hidden text-xs text-wrap"
                                                                        x-text="JSON.stringify(log.details)"></div>
                                                                </td>
                                                            </tr>
                                                        </template>

                                                        <template x-if="session.session_logs.length === 0">
                                                            <tr>
                                                                <td colspan="3"
                                                                    class="p-2 text-center text-gray-600 italic">No
                                                                    further actions in this session.</td>
                                                            </tr>
                                                        </template>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="!isLoading && logs.length === 0">
                                        <div
                                            class="p-8 text-center text-gray-500 border border-gray-700 border-dashed rounded-lg">
                                            No login sessions found for this user.
                                        </div>
                                    </template>
                                </div>

                                <!-- Pagination (Simple Next/Prev if needed, or just scrolling for now) -->
                                <div class="mt-4 flex justify-between" x-show="lastPage > 1">
                                    <button @click="fetchLogs(currentPage - 1)" :disabled="currentPage <= 1"
                                        class="px-3 py-1 bg-gray-800 rounded disabled:opacity-50 text-white">Prev</button>
                                    <span class="text-gray-400" x-text="`Page ${currentPage} of ${lastPage}`"></span>
                                    <button @click="fetchLogs(currentPage + 1)" :disabled="currentPage >= lastPage"
                                        class="px-3 py-1 bg-gray-800 rounded disabled:opacity-50 text-white">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.data('scoreSubmissionModal', () => ({
                        isOpen: false,
                        userId: null,
                        userName: '',
                        submitUrl: '',
                        tournaments: [],
                        gameweeks: [],
                        matches: [],
                        selectedTournament: '',
                        selectedGameweek: '',
                        selectedMatch: '',

                        init() {
                            this.fetchTournaments();
                        },

                        openModal(id, name) {
                            this.userId = id;
                            this.userName = name;
                            this.submitUrl = `/admin/users/${id}/score`;
                            this.isOpen = true;
                            // Reset selections
                            this.selectedGameweek = '';
                            this.selectedMatch = '';
                        },

                        async fetchTournaments() {
                            const url = this.userId
                                ? `{{ route('admin.users.score-data') }}?all_tournaments=1&user_id=${this.userId}`
                                : '{{ route('admin.users.score-data') }}?all_tournaments=1';

                            const res = await fetch(url);
                            this.tournaments = await res.json();
                        },

                        async fetchGameweeks() {
                            if (!this.selectedTournament) return;
                            const res = await fetch(`{{ route('admin.users.score-data') }}?tournament_id=${this.selectedTournament}`);
                            this.gameweeks = await res.json();
                            this.selectedGameweek = '';
                            this.matches = [];
                        },

                        async fetchMatches() {
                            if (!this.selectedGameweek) return;
                            const res = await fetch(`{{ route('admin.users.score-data') }}?gameweek_id=${this.selectedGameweek}`);
                            this.matches = await res.json();
                        }
                    }));

                    Alpine.data('logViewerModal', () => ({
                        isOpen: false,
                        userId: null,
                        userName: '',
                        logs: [],
                        isLoading: false,
                        currentPage: 1,
                        lastPage: 1,

                        openModal(id, name) {
                            this.userId = id;
                            this.userName = name;
                            this.isOpen = true;
                            this.fetchLogs(1);
                        },

                        async fetchLogs(page) {
                            this.isLoading = true;
                            this.logs = []; // Clear while loading
                            try {
                                const res = await fetch(`/admin/users/${this.userId}/logs?page=${page}`);
                                const data = await res.json();
                                this.logs = data.data;
                                this.currentPage = data.current_page;
                                this.lastPage = data.last_page;
                            } catch (e) {
                                console.error(e);
                            } finally {
                                this.isLoading = false;
                            }
                        }
                    }));
                });
            </script>

        </div>
    </div>
</x-app-layout>