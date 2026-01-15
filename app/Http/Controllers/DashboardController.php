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

        // AI Pundit Humor (Cached)
        $humor = Cache::remember("dashboard_humor_{$user->id}", 60 * 6, function () use ($punditService, $user, $rank, $users, $upcomingGameweek) {
            $context = [
                'rank' => $rank,
                'leader_name' => $users->first()->name ?? 'Nobody',
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

        $banter = $humor['greeting'];

        return view('dashboard', compact('humor', 'banter', 'upcomingMatches', 'upcomingGameweek', 'rank'));
    }



}
