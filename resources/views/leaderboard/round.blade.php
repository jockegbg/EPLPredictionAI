<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight drop-shadow-md">
            {{ $gameweek->name }} <span class="text-white/50 text-base font-normal">- Detailed Results</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white/10 backdrop-blur-lg overflow-hidden shadow-2xl sm:rounded-2xl border border-white/10">
                <div class="p-6">
                    <div class="mb-4">
                        <a href="{{ route('leaderboard.index', ['tournament_id' => $gameweek->tournament_id]) }}"
                            class="text-pl-blue hover:text-white transition flex items-center gap-1 font-medium">
                            <span>&larr;</span> Back to Standings
                        </a>
                    </div>

                    @php
                        // Data prep
                        $matches = $gameweek->matches;
                        $allPredictions = $matches->flatMap->predictions->groupBy('user_id');

                        // Get users and calc totals for sorting
                        $users = \App\Models\User::whereIn('id', $allPredictions->keys())->get()
                            ->map(function ($user) use ($allPredictions) {
                                $user->round_total = $allPredictions[$user->id]->sum('points_awarded');
                                return $user;
                            })
                            ->sortByDesc('round_total');
                    @endphp

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10">
                            <thead>
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-pl-green uppercase tracking-wider sticky left-0 bg-[#2f0034] z-10 border-r border-white/10 min-w-[200px]">
                                        Match Result
                                    </th>
                                    @foreach($users as $user)
                                        <th
                                            class="px-4 py-3 text-center text-xs font-bold text-white uppercase tracking-wider min-w-[100px]">
                                            <div class="flex flex-col items-center">
                                                <span class="text-sm">{{ $user->name }}</span>
                                                <span
                                                    class="text-[10px] text-pl-blue font-bold tracking-widest mt-0.5">{{ $user->round_total }}
                                                    PTS</span>
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @foreach($matches as $match)
                                    <tr class="hover:bg-pl-purple/50 transition">
                                        <td
                                            class="px-6 py-4 whitespace-nowrap sticky left-0 bg-[#2f0034]/95 backdrop-blur-sm z-10 border-r border-white/5">
                                            <div class="flex flex-col">
                                                <div class="flex items-center gap-2">
                                                    @if($match->homeTeamLogo)
                                                        <img src="{{ $match->homeTeamLogo }}" class="h-5 w-5 object-contain"
                                                            alt="">
                                                    @endif
                                                    <span
                                                        class="font-bold text-white text-sm">{{ $match->home_team }}</span>
                                                </div>
                                                <span class="text-[10px] text-white/40 my-1 ml-7">vs</span>
                                                <div class="flex items-center gap-2">
                                                    @if($match->awayTeamLogo)
                                                        <img src="{{ $match->awayTeamLogo }}" class="h-5 w-5 object-contain"
                                                            alt="">
                                                    @endif
                                                    <span
                                                        class="font-bold text-white text-sm">{{ $match->away_team }}</span>
                                                </div>

                                                <span class="text-xs text-pl-green font-mono mt-2 ml-7">
                                                    @if($match->status === 'in_progress')
                                                        <div class="flex flex-col items-start">
                                                            <span class="animate-pulse text-pl-pink font-bold">
                                                                {{ $match->home_score }} - {{ $match->away_score }}
                                                            </span>
                                                            <div class="flex items-center gap-1 mt-1">
                                                                <span
                                                                    class="text-[10px] text-pl-green">{{ $match->display_minutes }}</span>
                                                                <span
                                                                    class="text-[8px] bg-red-600 text-white px-1 py-px rounded font-bold tracking-wider animate-pulse">LIVE</span>
                                                            </div>
                                                        </div>
                                                    @elseif($match->status === 'completed')
                                                        {{ $match->home_score }} - {{ $match->away_score }}
                                                    @else
                                                        {{ $match->start_time->format('H:i') }}
                                                    @endif
                                                </span>
                                            </div>
                                        </td>

                                        @foreach($users as $user)
                                            @php
                                                $pred = $allPredictions[$user->id]->firstWhere('match_id', $match->id);
                                                $points = $pred?->points_awarded ?? 0;

                                                $bgClass = 'text-white/30';

                                                if ($points >= 40) { // Exact score (normal or x2)
                                                    $bgClass = 'bg-pl-green text-pl-purple font-bold';
                                                } elseif ($points > 0) { // Correct result
                                                    $bgClass = 'bg-white/20 text-white';
                                                }

                                                if ($pred?->is_double_points && $points > 0) {
                                                    $bgClass = 'bg-pl-pink text-white font-bold shadow-lg shadow-pl-pink/20';
                                                }
                                            @endphp
                                            <td
                                                class="px-4 py-4 whitespace-nowrap text-center border-l border-white/5 align-middle">
                                                <div class="flex flex-col items-center gap-1">
                                                    @if($pred)
                                                        <span
                                                            class="text-white font-mono text-lg">{{ $pred->predicted_home }}-{{ $pred->predicted_away }}</span>

                                                        <div class="h-6 flex items-center">
                                                            @if($pred->is_double_points)
                                                                <span
                                                                    class="text-[9px] bg-pl-pink text-white px-1 py-px rounded uppercase tracking-wider mr-1">2x</span>
                                                            @endif

                                                            @if($points > 0)
                                                                <span class="text-xs px-2 py-0.5 rounded-full {{ $bgClass }}">
                                                                    +{{ $points }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <span class="text-white/10 text-2xl">-</span>
                                                    @endif
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach

                                {{-- Total Row Footer --}}
                                <tr class="bg-pl-purple/80 border-t-2 border-white/20">
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-pl-green uppercase tracking-wider sticky left-0 bg-[#2f0034] z-10 border-r border-white/10">
                                        Total Points
                                    </td>
                                    @foreach($users as $user)
                                        <td
                                            class="px-4 py-4 whitespace-nowrap text-center text-xl font-bold text-pl-blue border-l border-white/5">
                                            {{ $user->round_total }}
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>