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
                <div class="p-6">
                    <h3 class="text-lg font-bold mb-4 text-white flex items-center gap-2">
                        <span class="text-pl-green">●</span> Tournament Standings
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10">
                            <thead>
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-pl-green uppercase tracking-wider w-16">
                                        Rank</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-pl-green uppercase tracking-wider">
                                        User</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-pl-green uppercase tracking-wider">
                                        GW Wins</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-pl-green uppercase tracking-wider">
                                        Hit Rate</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-pl-green uppercase tracking-wider">
                                        Total Points</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @forelse ($users as $index => $user)
                                    <tr class="hover:bg-pl-purple/50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">
                                            @if ($users->firstItem() + $index == 1)
                                                <span class="text-pl-pink text-xl drop-shadow-lg">👑 1</span>
                                            @elseif ($users->firstItem() + $index == 2)
                                                <span class="text-gray-300 text-lg">🥈 2</span>
                                            @elseif ($users->firstItem() + $index == 3)
                                                <span class="text-amber-600 text-lg">🥉 3</span>
                                            @else
                                                <span class="text-white/60">#{{ $users->firstItem() + $index }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">
                                            <div class="flex items-center gap-2">
                                                @if($user->favorite_team_logo)
                                                    <img src="{{ $user->favorite_team_logo }}" alt="{{ $user->favorite_team }}" class="w-6 h-6 object-contain" title="{{ $user->favorite_team }}">
                                                @endif
                                                {{ $user->name }}
                                                @if($user->id === auth()->id())
                                                    <span
                                                        class="text-[10px] font-bold bg-pl-green text-pl-purple px-2 py-0.5 rounded-full uppercase tracking-wide">You</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-md font-bold text-pl-pink">
                                            @if(isset($gameweekWins[$user->id]) && $gameweekWins[$user->id] > 0)
                                                🏆 {{ $gameweekWins[$user->id] }}
                                            @else
                                                <span class="text-white/20">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col">
                                                <span class="text-lg font-bold text-white">{{ $user->hit_rate }}%</span>
                                                <span class="text-xs text-white/40 font-mono">{{ $user->predictions_hit }}/{{ $user->predictions_played }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-lg font-bold text-pl-blue">
                                            {{ $user->predictions_sum_points_awarded ?? 0 }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-center text-white/50">No predictions yet for
                                            this season.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $users->links() }}
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

                                                <td class="px-4 py-4 whitespace-nowrap text-center text-sm border-l border-white/5 {{ in_array($user->id, $roundWinners[$gw->id]['users'] ?? []) ? 'bg-pl-pink/10 shadow-inner' : '' }}">
                                                    @if($hasPredictions)
                                                        @if(in_array($user->id, $roundWinners[$gw->id]['users'] ?? []) && $roundWinners[$gw->id]['score'] > 0)
                                                            <div class="flex items-center justify-center gap-1">
                                                                <span class="text-sm">🏆</span>
                                                                <span class="inline-block px-3 py-1 rounded-full bg-pl-pink text-white font-bold shadow-[0_0_10px_rgba(255,40,130,0.5)]">
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