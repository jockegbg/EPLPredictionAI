<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gameweek;
use App\Models\GameMatch;
use App\Models\Tournament;
use App\Models\User;
use App\Services\ScoringService;
use Illuminate\Http\Request;

class UserScoreController extends Controller
{
    public function getData(Request $request)
    {
        if ($request->has('tournament_id')) {
            $gameweeks = Gameweek::where('tournament_id', $request->tournament_id)
                ->orderBy('start_date', 'desc')
                ->get(['id', 'name']);
            return response()->json($gameweeks);
        }

        if ($request->has('gameweek_id')) {
            $matches = GameMatch::where('gameweek_id', $request->gameweek_id)
                ->get(['id', 'home_team', 'away_team', 'home_score', 'away_score', 'start_time']);

            // Format match name for display
            $matches->transform(function ($match) {
                $match->display_name = "{$match->home_team} vs {$match->away_team} (" . ($match->start_time ? $match->start_time->format('D H:i') : 'TBA') . ")";
                return $match;
            });

            return response()->json($matches);
        }

        if ($request->has('all_tournaments')) {
            $tournaments = Tournament::where('is_active', true)->get(['id', 'name']);
            return response()->json($tournaments);
        }

        return response()->json([]);
    }

    public function store(Request $request, User $user, ScoringService $scoringService)
    {
        $validated = $request->validate([
            'match_id' => 'required|exists:matches,id',
            'predicted_home' => 'required|integer|min:0',
            'predicted_away' => 'required|integer|min:0',
            'chip' => 'nullable|in:double_points,defence_chip',
        ]);

        // Find or Create Prediction
        $prediction = $user->predictions()->updateOrCreate(
            ['match_id' => $validated['match_id']],
            [
                'predicted_home' => $validated['predicted_home'],
                'predicted_away' => $validated['predicted_away'],
                'is_double_points' => $validated['chip'] === 'double_points',
                'is_defence_chip' => $validated['chip'] === 'defence_chip',
            ]
        );

        // Immediately calculate points if match is finished
        $match = GameMatch::find($validated['match_id']);
        if ($match && $match->status === 'completed' && !is_null($match->home_score) && !is_null($match->away_score)) {
            $points = $scoringService->calculatePredictionScore($match, $prediction);
            $prediction->update(['points_awarded' => $points]);

            return back()->with('success', "Score submitted! Points calculated: {$points}");
        }

        return back()->with('success', 'Score submitted successfully. Points will be calculated when the match finishes.');
    }
}
