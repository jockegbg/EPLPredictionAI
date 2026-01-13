<?php

namespace App\Http\Controllers;

use App\Models\Season;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $banter = $this->generateBanter($user);

        // Fetch Upcoming/Current Gameweek
        $upcomingGameweek = \App\Models\Gameweek::where('end_date', '>=', now())
            ->orderBy('start_date')
            ->first();

        $upcomingMatches = collect();
        if ($upcomingGameweek) {
            $upcomingMatches = $upcomingGameweek->matches()
                ->orderBy('start_time')
                ->get()
                ->map(function ($match) {
                    $match->ai_commentary = $this->generateMatchCommentary($match);
                    return $match;
                });
        }

        return view('dashboard', compact('banter', 'upcomingMatches', 'upcomingGameweek'));
    }

    private function generateBanter(User $currentUser): string
    {
        // 1. Get Current Tournament & Standings
        $currentTournament = \App\Models\Tournament::where('is_active', true)->first();

        if (!$currentTournament) {
            return "Tournament hasn't started yet. Everyone's a winner! (For now...)";
        }

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

        if ($users->isEmpty()) {
            return "It's quiet... too quiet. Get your predictions in!";
        }

        // 2. Identify Key Players
        $leader = $users->first();
        $lastPlace = $users->last();
        $userRank = $users->search(function ($u) use ($currentUser) {
            return $u->id === $currentUser->id;
        }); // 0-indexed

        $userPoints = $currentUser->predictions_sum_points_awarded ?? 0;
        $leaderPoints = $leader->predictions_sum_points_awarded ?? 0;

        // 3. Generate "Roast" based on context
        $rankDisplay = $userRank + 1;

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
        if ($userRank > 0 && $userRank <= 2) {
            $templates = [
                "So close to the top! {$gap} points behind the leader. Need a ladder? 🪜",
                "Podium spot! You're in the Champions League places. Don't bottle it now.",
                "You're breathing down {$leader->name}'s neck. Make them nervous! 😤",
            ];
            return $templates[array_rand($templates)];
        }

        // SCENARIO: Top 10 (Upper Mid-table)
        if ($userRank <= 9) {
            $templates = [
                "Top 10! Respectable. But 'respectable' doesn't win trophies. 🤷‍♂️",
                "You're in the mix. a decent gameweek could change everything.",
                "Not bad, not great. Just... solid. Is that what you're aiming for?",
            ];
            return $templates[array_rand($templates)];
        }

        // SCENARIO: Relegation Battle (Bottom 3 but not last)
        if ($userRank >= $users->count() - 3) {
            $templates = [
                "Relegation battle? Seriously? Pull your socks up! 🧦",
                "It's getting hot down here. The Championship is calling your name.",
                "You're flirting with disaster. One bad week and you're rock bottom.",
            ];
            return $templates[array_rand($templates)];
        }

        // SCENARIO: Generic Mid-table
        $templates = [
            "Sitting at #{$rankDisplay}. {$leader->name} is {$gap} points ahead. Time to lock in? 🤔",
            "You're rank #{$rankDisplay}. Not great, not terrible. Just... existing.",
            "{$leader->name} is running away with it (Rank 1). You going to let that happen?",
            "Rank #{$rankDisplay}? My grandma predicts better than this. Step it up! 👵",
        ];

        return $templates[array_rand($templates)];
    }

    private function generateMatchCommentary(\App\Models\GameMatch $match): string
    {
        $home = $match->home_team;
        $away = $match->away_team;

        $templates = [
            "{$home} vs {$away}? I predict 22 people running around and one very angry manager.",
            "If {$home} loses this, the fans might riot. If {$away} wins, it's a miracle.",
            "{$home} playing at home... fortress or bouncy castle? We shall see.",
            "Calling it now: {$away} to score a screamer in the 90th minute. Or lose 4-0. No in-between.",
            "Is it too late to cancel this match? asking for a friend (and {$home}'s defense).",
            "This has 0-0 written all over it. Don't say I didn't warn you.",
            "{$home} vs {$away}. The battle of... well, two football teams.",
            "My algorithm says {$home} wins. My heart says chaos. Let's go with chaos.",
            "{$home}: great attack. {$away}: great bus parking. Unstoppable force vs immovable object.",
            "The scriptwriters are cooking with this one. {$home} win, but with VAR drama.",
        ];

        return $templates[array_rand($templates)];
    }
}
