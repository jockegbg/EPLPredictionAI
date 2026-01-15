<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    public function index(Request $request): View
    {
        $tournaments = \App\Models\Tournament::orderByDesc('created_at')->get();
        $currentTournament = $request->has('tournament_id')
            ? $tournaments->find($request->get('tournament_id'))
            : $tournaments->firstWhere('is_active', true) ?? $tournaments->first();

        // Calculate points ONLY for the selected tournament
        $query = User::withSum([
            'predictions' => function ($query) use ($currentTournament) {
                if ($currentTournament) {
                    $query->whereHas('match', function ($q) use ($currentTournament) {
                        $q->whereHas('gameweek', function ($gw) use ($currentTournament) {
                            $gw->where('tournament_id', $currentTournament->id);
                        });
                    });
                }
            }
        ], 'points_awarded');

        if ($currentTournament) {
            $query->whereHas('tournaments', function ($q) use ($currentTournament) {
                $q->where('tournaments.id', $currentTournament->id);
            });

            // Eager load predictions for stat calculation
            $query->with([
                'predictions' => function ($q) use ($currentTournament) {
                    $q->whereHas('match', function ($m) use ($currentTournament) {
                        $m->where('status', 'completed')
                            ->whereHas('gameweek', function ($g) use ($currentTournament) {
                                $g->where('tournament_id', $currentTournament->id);
                            });
                    })->with('match');
                }
            ]);
        }

        $users = $query->orderByDesc('predictions_sum_points_awarded')
            ->paginate(20);

        // ... existing hit rate calc ...

        // Fetch Gameweeks for the matrix
        if ($currentTournament) {
            $gameweeks = $currentTournament->gameweeks()
                ->orderBy('start_date', 'desc') // Changed to DESC
                ->with(['matches.predictions']) // Eager load for points calc
                ->get();
        } else {
            $gameweeks = collect();
        }

        // Calculate Gameweek Wins and High Scores
        $gameweekWins = []; // [userId => count]
        $roundWinners = []; // [gwId => ['score' => int, 'users' => [id, id]]]

        if ($currentTournament) {
            foreach ($gameweeks as $gw) {
                // Determine scores for this GW for ALL users
                $gwUserPoints = [];

                // Assuming 'completed' status is the source of truth for "Winning" a round.
                if ($gw->status !== 'completed')
                    continue;

                foreach ($gw->matches as $match) {
                    foreach ($match->predictions as $pred) {
                        if (!isset($gwUserPoints[$pred->user_id])) {
                            $gwUserPoints[$pred->user_id] = 0;
                        }
                        $gwUserPoints[$pred->user_id] += $pred->points_awarded;
                    }
                }

                if (empty($gwUserPoints))
                    continue;

                $maxPoints = max($gwUserPoints);

                // Track round info
                $roundWinners[$gw->id] = [
                    'score' => $maxPoints,
                    'users' => []
                ];

                // Only award/track if there are points > 0
                if ($maxPoints > 0) {
                    foreach ($gwUserPoints as $uId => $pts) {
                        if ($pts === $maxPoints) {
                            if (!isset($gameweekWins[$uId])) {
                                $gameweekWins[$uId] = 0;
                            }
                            $gameweekWins[$uId]++;

                            $roundWinners[$gw->id]['users'][] = $uId;
                        }
                    }
                }
            }
        }

        return view('leaderboard.index', compact('users', 'tournaments', 'currentTournament', 'gameweeks', 'gameweekWins', 'roundWinners'));
    }

    public function showRound(\App\Models\Gameweek $gameweek): View
    {
        $gameweek->load(['matches.predictions.user']);

        return view('leaderboard.round', compact('gameweek'));
    }
}
