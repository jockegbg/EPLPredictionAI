<?php

namespace App\Http\Controllers;

use App\Models\Season;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $currentTournament = \App\Models\Tournament::where('is_active', true)->first();
        $rank = '-';
        $myPoints = 0;

        if ($currentTournament) {
            // OPTIMIZED RANK QUERY:
            // 1. Get my points
            $myPoints = $user->predictions()
                ->whereHas('match.gameweek', function ($q) use ($currentTournament) {
                    $q->where('tournament_id', $currentTournament->id);
                })
                ->sum('points_awarded');

            // 2. Count users with MORE points than me (Rank = count + 1)
            // We use the same join logic to ensure accurate comparison
            $higherScorers = User::whereHas('predictions.match.gameweek', function ($q) use ($currentTournament) {
                $q->where('tournament_id', $currentTournament->id);
            })
                ->withSum([
                    'predictions' => function ($q) use ($currentTournament) {
                        $q->whereHas('match.gameweek', function ($gw) use ($currentTournament) {
                            $gw->where('tournament_id', $currentTournament->id);
                        });
                    }
                ], 'points_awarded')
                ->having('predictions_sum_points_awarded', '>', $myPoints)
                ->count();

            // Note: count() with having() in Eloquent sometimes requires get()->count(). 
            // For true SQL efficiency we'd use a subquery, but fetching just id/sum for comparison is fast enough for now vs loading full models.
            // Let's stick to the collection approach but ONLY fetch IDs if the direct DB count fails or is complex.
            // ACTUALLY, simpler approach for stability given Laravel version: 
            // Fetch ID + Sum for all users (lightweight) -> Sort -> Find Index.
            // This avoids complicated GroupBy/Having SQL issues across DB drivers.

            $scores = User::withSum([
                'predictions' => function ($q) use ($currentTournament) {
                    $q->whereHas('match.gameweek', function ($gw) use ($currentTournament) {
                        $gw->where('tournament_id', $currentTournament->id);
                    });
                }
            ], 'points_awarded')
                ->orderByDesc('predictions_sum_points_awarded')
                ->pluck('predictions_sum_points_awarded', 'id'); // key=id, val=points

            $rank = $scores->keys()->search($user->id);
            $rank = $rank !== false ? $rank + 1 : '-';
        }

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

        // Return view WITHOUT blocking AI call
        return view('dashboard', compact('upcomingMatches', 'upcomingGameweek', 'rank', 'myPoints'));
    }

    public function punditHumor(Request $request, \App\Services\PunditService $punditService)
    {
        $user = Auth::user();
        $rank = $request->input('rank', '-');

        // Context for AI
        $currentTournament = \App\Models\Tournament::where('is_active', true)->first();
        $leaderName = 'Unknown';

        if ($currentTournament) {
            $leader = User::withSum([
                'predictions' => function ($q) use ($currentTournament) {
                    $q->whereHas('match.gameweek', function ($gw) use ($currentTournament) {
                        $gw->where('tournament_id', $currentTournament->id);
                    });
                }
            ], 'points_awarded')
                ->orderByDesc('predictions_sum_points_awarded')
                ->first();

            $leaderName = $leader ? $leader->name : 'Nobody';
        }

        $upcomingGameweek = \App\Models\Gameweek::where('end_date', '>=', now())
            ->orderBy('start_date')
            ->first();

        // AI Pundit Humor (Cached)
        $humor = Cache::remember("dashboard_humor_{$user->id}", 60 * 6, function () use ($punditService, $user, $rank, $leaderName, $upcomingGameweek) {
            $context = [
                'rank' => $rank,
                'leader_name' => $leaderName,
                'upcoming_match' => $upcomingGameweek
                    ? ($upcomingGameweek->matches->first() ? $upcomingGameweek->matches->first()->home_team . ' vs ' . $upcomingGameweek->matches->first()->away_team : 'No matches')
                    : 'No upcoming matches',
            ];
            return $punditService->generateDashboardHumor($user, $context);
        });

        // Fallback checks
        if (!isset($humor['greeting'])) {
            $humor = [
                'greeting' => "Welcome back. The pundit is sleeping.",
                'team_roast' => "Your team is fine. Probably.",
                'prediction' => "Goals will happen."
            ];
        }

        return response()->json($humor);
    }



}
