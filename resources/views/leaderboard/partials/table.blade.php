<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-white/10">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-pl-green uppercase tracking-wider w-16">
                    Rank</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-pl-green uppercase tracking-wider">
                    User</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-pl-green uppercase tracking-wider">
                    GW Wins</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-pl-green uppercase tracking-wider">
                    Hit Rate</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-pl-green uppercase tracking-wider">
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
                                <img src="{{ $user->favorite_team_logo }}" alt="{{ $user->favorite_team }}"
                                    class="w-6 h-6 object-contain" title="{{ $user->favorite_team }}">
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
                            <span
                                class="text-xs text-white/40 font-mono">{{ $user->predictions_hit }}/{{ $user->predictions_played }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-lg font-bold text-pl-blue">
                        {{ $user->predictions_sum_points_awarded ?? 0 }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-white/50">No predictions yet for
                        this season.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">
    {{ $users->links() }}
</div>