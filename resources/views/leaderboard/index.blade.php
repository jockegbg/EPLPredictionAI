<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-white leading-tight drop-shadow-md">
                {{ __('Leaderboard') }}
            </h2>

            @if(isset($tournaments) && $tournaments->count() > 0)
                <form method="GET" action="{{ route('leaderboard.index') }}" class="flex items-center">
                    <select name="tournament_id" onchange="this.form.submit()"
                        class="rounded-md border-white/20 shadow-sm focus:border-pl-green focus:ring-pl-green bg-white/10 text-white backdrop-blur-md">
                        @foreach($tournaments as $tournament)
                            <option value="{{ $tournament->id }}" class="bg-pl-purple text-white" {{ isset($currentTournament) && $currentTournament->id == $tournament->id ? 'selected' : '' }}>
                                {{ $tournament->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- 1. Tournament Standings (Moved to Top) -->
            <div class="bg-white/10 backdrop-blur-lg overflow-hidden shadow-2xl sm:rounded-2xl border border-white/10">
                <div class="p-6" x-data="{ 
                    liveMode: false, 
                    originalContent: null,
                    loading: false,
                    async toggleLive() {
                        this.liveMode = !this.liveMode;
                        const container = this.$refs.tableContainer;
                        
                        if (this.liveMode) {
                            if (!this.originalContent) {
                                this.originalContent = container.innerHTML;
                            }
                            this.loading = true;
                            // Add loading state UI
                            container.style.opacity = '0.5';
                            
                            try {
                                const response = await fetch(`{{ route('leaderboard.live', [], false) }}?tournament_id={{ $currentTournament->id ?? '' }}`);
                                const html = await response.text();
                                container.innerHTML = html;
                            } catch (error) {
                                console.error('Failed to load live table', error);
                                this.liveMode = false; // Revert on error
                            } finally {
                                this.loading = false;
                                container.style.opacity = '1';
                            }
                        } else {
                            if (this.originalContent) {
                                container.innerHTML = this.originalContent;
                            }
                        }
                    }
                }">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <span class="text-pl-green">●</span>
                            <span x-text="liveMode ? 'Live Standings (Provisional)' : 'Tournament Standings'"></span>
                        </h3>

                        @php
                            // Check if any gameweek is active to show the toggle
                            $hasActiveGameweek = isset($gameweeks) && $gameweeks->contains('status', 'active');
                        @endphp

                        @if($hasActiveGameweek)
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-white/60"
                                    :class="{ 'text-pl-green animate-pulse': liveMode }">Live</span>
                                <button @click="toggleLive()"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-pl-green focus:ring-offset-2 focus:ring-offset-gray-900"
                                    :class="liveMode ? 'bg-pl-green' : 'bg-gray-700'">
                                    <span class="sr-only">Enable Live Mode</span>
                                    <span
                                        class="inline-block h-4 w-4 transform rounded-full bg-white transition duration-200 ease-in-out"
                                        :class="liveMode ? 'translate-x-6' : 'translate-x-1'"></span>
                                </button>
                            </div>
                        @endif
                    </div>

                    <div x-ref="tableContainer" class="min-h-[200px] transition-opacity duration-200">
                        @include('leaderboard.partials.table')
                    </div>
                </div>

        </div>

        <!-- 2. Round-by-Round Matrix -->
        @if(isset($gameweeks) && $gameweeks->count() > 0)
            <div class="bg-white/10 backdrop-blur-lg overflow-hidden shadow-2xl sm:rounded-2xl border border-white/10">
                <div class="p-6">
                    <h3 class="text-lg font-bold mb-4 text-white flex items-center gap-2">
                        <span class="text-pl-green">●</span> Round-by-Round Performance
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10">
                            <thead>
                                <tr>
                                    <!-- Row Header: Round Name -->
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-pl-green uppercase tracking-wider sticky left-0 bg-[#2f0034] z-10 border-r border-white/10 min-w-[150px]">
                                        Round
                                    </th>
                                    <!-- Columns: Users (from the pagination above, or maybe we should fetch all if feasible?) -->
                                    <!-- Note: Using the paginated users ensures alignment with the table above -->
                                    @foreach($users as $user)
                                        <th
                                            class="px-4 py-3 text-center text-xs font-bold text-white uppercase tracking-wider min-w-[100px]">
                                            {{ $user->name }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @foreach($gameweeks as $gw)
                                    <tr class="hover:bg-pl-purple/50 transition group">
                                        <!-- Round Name Column -->
                                        <td
                                            class="px-6 py-4 whitespace-nowrap sticky left-0 bg-[#2f0034]/95 backdrop-blur-sm z-10 border-r border-white/5 group-hover:bg-[#2f0034]">
                                            <a href="{{ route('leaderboard.round', $gw) }}"
                                                class="flex flex-col hover:scale-105 transition-transform duration-200">
                                                <span
                                                    class="font-bold text-pl-green hover:underline decoration-pl-green/50 underline-offset-4">{{ $gw->name }}</span>
                                                <span class="text-xs text-white/50">{{ $gw->status }}</span>
                                            </a>
                                        </td>

                                        <!-- User Columns -->
                                        @foreach($users as $user)
                                                                @php
                                                                    // Calculate total points for this user in this gameweek
                                                                    // Efficient because we eager loaded matches.predictions
                                                                    $gwPoints = $gw->matches->flatMap->predictions
                                                                        ->where('user_id', $user->id)
                                                                        ->sum('points_awarded');

                                                                    // Check if user has participated (has any predictions) in this GW
                                                                    $hasPredictions = $gw->matches->flatMap->predictions
                                                                        ->where('user_id', $user->id)
                                                                        ->isNotEmpty();
                                                                @endphp

                                             <td
                                                                    class="px-4 py-4 whitespace-nowrap text-center text-sm border-l border-white/5 {{ in_array($user->id, $roundWinners[$gw->id]['users'] ?? []) ? 'bg-pl-pink/10 shadow-inner' : '' }}">
                                                                    @if($hasPredictions)
                                                                        @if(in_array($user->id, $roundWinners[$gw->id]['users'] ?? []) && $roundWinners[$gw->id]['score'] > 0)
                                                                            <div class="flex items-center justify-center gap-1">
                                                                                <span class="text-sm">🏆</span>
                                                                                <span
                                                                                    class="inline-block px-3 py-1 rounded-full bg-pl-pink text-white font-bold shadow-[0_0_10px_rgba(255,40,130,0.5)]">
                                                                                    {{ $gwPoints }}
                                                                                </span>
                                                                            </div>
                                                                        @else
                                                                            <span class="inline-block px-3 py-1 rounded-full bg-white/10 font-bold text-white">
                                                                                {{ $gwPoints }}
                                                                            </span>
                                                                        @endif
                                                                    @else
                                                                        <span class="text-white/20">-</span>
                                                                    @endif
                                                                </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

    </div>
    </div>
</x-app-layout>