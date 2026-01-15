<?php

namespace App\Http\Controllers;

use App\Models\Season;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(\App\Services\PunditService $punditService): View
    {
        $user = Auth::user();
        $currentTournament = \App\Models\Tournament::where('is_active', true)->first();
        $rank = '-';
        $users = collect();

        if ($currentTournament) {
            // Fetch users sorted by points for this tournament
            $users = User::withSum([
                'predictions' => function ($query) use ($currentTournament) {
                    $query->whereHas('match', function ($q) use ($currentTournament) {
                        $q->whereHas('gameweek', function ($gw) use ($currentTournament) {
                            $gw->where('tournament_id', $currentTournament->id);
                        });
                    });
                }
            ], 'points_awarded')
                ->orderByDesc('predictions_sum_points_awarded')
                ->get();

            $index = $users->search(function ($u) use ($user) {
                return $u->id === $user->id;
            });

            if ($index !== false) {
                $rank = $index + 1;
            }
        }

        $banter = $this->generateBanter($user, $users, $rank);

        // Fetch Upcoming/Current Gameweek
        $upcomingGameweek = \App\Models\Gameweek::where('end_date', '>=', now())
            ->orderBy('start_date')
            ->first();

        $upcomingMatches = collect();
        if ($upcomingGameweek) {
            $upcomingMatches = $upcomingGameweek->matches()
                ->orderBy('start_time')
                ->get();
        }

        return view('dashboard', compact('banter', 'upcomingMatches', 'upcomingGameweek', 'rank'));
    }

    private function generateBanter(User $currentUser, $users, $rank): string
    {
        if ($users->isEmpty()) {
            return "Tournament hasn't started yet. Everyone's a winner! (For now...)";
        }

        // 2. Identify Key Players
        $leader = $users->first();
        $lastPlace = $users->last();

        $userPoints = $currentUser->predictions_sum_points_awarded ?? 0;
        $leaderPoints = $leader->predictions_sum_points_awarded ?? 0;

        // SCENARIO: User is 1st
        if ($currentUser->id === $leader->id) {
            $templates = [
                "Look at you, sitting on the throne with {$userPoints} points! Don't get dizzy up there 👑",
                "Everyone else is playing checkers, you're playing 4D chess. Top of the league! 🚀",
                "Breaking News: {$currentUser->name} is currently unstoppable. The rest of the group is in shambles.",
            ];
            return $templates[array_rand($templates)];
        }

        // Calculate gap for subsequent scenarios
        $gap = $leaderPoints - $userPoints;

        // SCENARIO: User is Last (and there's more than 1 person)
        if ($currentUser->id === $lastPlace->id && $users->count() > 1) {
            $templates = [
                "Currently holding the wooden spoon 🥄. Someone has to do it, right?",
                "You're rock bottom with {$userPoints} points. The only way is up! (Hopefully)",
                "I'd offer you a map, but I think you're lost in the relegation zone. Wake up! 📉",
            ];
            return $templates[array_rand($templates)];
        }

        // SCENARIO: Podium (2nd or 3rd)
        if ($rank > 1 && $rank <= 3) {
            $templates = [
                "So close to the top! {$gap} points behind the leader. Need a ladder? 🪜",
                "Podium spot! You're in the Champions League places. Don't bottle it now.",
                "You're breathing down {$leader->name}'s neck. Make them nervous! 😤",
            ];
            return $templates[array_rand($templates)];
        }

        // SCENARIO: Top 10 (Upper Mid-table)
        if ($rank <= 10) {
            $templates = [
                "Top 10! Respectable. But 'respectable' doesn't win trophies. 🤷‍♂️",
                "You're in the mix. a decent gameweek could change everything.",
                "Not bad, not great. Just... solid. Is that what you're aiming for?",
            ];
            return $templates[array_rand($templates)];
        }

        // SCENARIO: Relegation Battle (Bottom 3 but not last)
        if ($rank >= $users->count() - 3) {
            $templates = [
                "Relegation battle? Seriously? Pull your socks up! 🧦",
                "It's getting hot down here. The Championship is calling your name.",
                "You're flirting with disaster. One bad week and you're rock bottom.",
            ];
            return $templates[array_rand($templates)];
        }

        // SCENARIO: Generic Mid-table
        $templates = [
            "Sitting at #{$rank}. {$leader->name} is {$gap} points ahead. Time to lock in? 🤔",
            "You're rank #{$rank}. Not great, not terrible. Just... existing.",
            "{$leader->name} is running away with it (Rank 1). You going to let that happen?",
            "Rank #{$rank}? My grandma predicts better than this. Step it up! 👵",
        ];

        return $templates[array_rand($templates)];
    }


}
