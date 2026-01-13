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
        }

        $users = $query->orderByDesc('predictions_sum_points_awarded')
            ->paginate(20);

        // Fetch Gameweeks for the matrix
        if ($currentTournament) {
            $gameweeks = $currentTournament->gameweeks()
                ->orderBy('start_date', 'asc')
                ->with(['matches.predictions']) // Eager load for points calc
                ->get();
        } else {
            $gameweeks = collect();
        }

        return view('leaderboard.index', compact('users', 'tournaments', 'currentTournament', 'gameweeks'));
    }

    public function showRound(\App\Models\Gameweek $gameweek): View
    {
        $gameweek->load(['matches.predictions.user']);

        return view('leaderboard.round', compact('gameweek'));
    }
}
