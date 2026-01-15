<div x-data="{ 
        open: false, 
        gameweekId: null, 
        selectedMatchId: null,
        matches: [], 
        users: [],
        init() {
            this.handleBack = this.handleBack.bind(this);
            this.$watch('open', value => {
                if (value) {
                    history.pushState({modal: true}, '', window.location.href);
                    window.addEventListener('popstate', this.handleBack);
                } else {
                    window.removeEventListener('popstate', this.handleBack);
                }
            });
        },
        handleBack() {
             this.open = false;
        },
        closeModal() {
            // When manually closing (Cancel/ESC), we want to revert the history state
            // which will trigger popstate and run handleBack to close the modal.
            history.back();
        }
    }" @open-sidebet-modal.window="
        gameweekId = $event.detail.gameweekId;
        matches = $event.detail.matches;
        users = $event.detail.users;
        open = true;
        selectedMatchId = null; 
        $nextTick(() => { selectedMatchId = $event.detail.selectedMatchId; });
    " @keydown.escape.window="closeModal()" class="relative z-50" aria-labelledby="modal-title" role="dialog"
    aria-modal="true" x-show="open" style="display: none;">
    class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-show="open"
    style="display: none;">

    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-zinc-900 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-white/10"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                <form method="POST" action="{{ route('admin.gameweeks.adjust-points') }}">
                    @csrf
                    <input type="hidden" name="gameweek_id" x-model="gameweekId">

                    <div class="px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-semibold leading-6 text-white mb-4" id="modal-title">Manage Sidebets
                            (Point Adjustment)</h3>

                        <!-- Select User -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-300 mb-1">Select User</label>
                            <select name="user_id" required
                                class="block w-full rounded-md border-0 bg-white/5 py-1.5 text-white shadow-sm ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-inset focus:ring-pl-green sm:text-sm sm:leading-6">
                                <template x-for="user in users" :key="user.id">
                                    <option :value="user.id" x-text="user.name" class="bg-gray-800 text-white"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Select Match -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-300 mb-1">Select Match</label>
                            <select name="match_id" required x-model="selectedMatchId"
                                class="block w-full rounded-md border-0 bg-white/5 py-1.5 text-white shadow-sm ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-inset focus:ring-pl-green sm:text-sm sm:leading-6">
                                <template x-for="match in matches" :key="match.id">
                                    <option :value="match.id" x-text="match.home_team + ' vs ' + match.away_team"
                                        class="bg-gray-800 text-white">
                                    </option>
                                </template>
                            </select>
                        </div>

                        <!-- Adjustment Value -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-300 mb-1">Points Adjustment (+/-)</label>
                            <div class="text-xs text-gray-500 mb-1">Enter a positive number to add points (e.g. 5) or
                                negative to deduct (e.g. -2).</div>
                            <input type="number" name="points_adjustment" required placeholder="0"
                                class="block w-full rounded-md border-0 bg-white/5 py-1.5 text-white shadow-sm ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-inset focus:ring-pl-green sm:text-sm sm:leading-6">
                        </div>

                    </div>
                    <div class="bg-black/20 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="submit"
                            class="inline-flex w-full justify-center rounded-md bg-pl-pink px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-pl-pink/80 sm:ml-3 sm:w-auto">Save
                            Adjustment</button>
                        <button type="button" @click="closeModal()"
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white/10 px-3 py-2 text-sm font-semibold text-white shadow-sm ring-1 ring-inset ring-white/10 hover:bg-white/20 sm:mt-0 sm:w-auto">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>