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
                                            {{ $user->name }}
                                            @if($user->id === auth()->id())
                                                <span
                                                    class="ml-2 text-[10px] font-bold bg-pl-green text-pl-purple px-2 py-0.5 rounded-full uppercase tracking-wide">You</span>
                                            @endif
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
                                                <div class="flex flex-col">
                                                    <span class="font-bold text-white">{{ $gw->name }}</span>
                                                    <span class="text-xs text-white/50">{{ $gw->status }}</span>
                                                </div>
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

                                                <td class="px-4 py-4 whitespace-nowrap text-center text-sm border-l border-white/5">
                                                    @if($hasPredictions)
                                                        <a href="{{ route('leaderboard.round', $gw) }}"
                                                            class="inline-block px-3 py-1 rounded-full bg-white/10 hover:bg-pl-green hover:text-pl-purple transition font-bold text-white">
                                                            {{ $gwPoints }}
                                                        </a>
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