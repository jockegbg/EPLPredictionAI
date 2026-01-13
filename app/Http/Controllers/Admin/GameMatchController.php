<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gameweek;
use App\Models\GameMatch;
use App\Models\Prediction;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GameMatchController extends Controller
{
    public function create(Gameweek $gameweek): View
    {
        $teams = config('teams');
        return view('admin.matches.create', compact('gameweek', 'teams'));
    }

    public function store(Request $request, Gameweek $gameweek): RedirectResponse
    {
        $validated = $request->validate([
            'home_team' => 'required|string|max:255',
            'away_team' => 'required|string|max:255',
            'start_time' => 'required|date',
        ]);

        $gameweek->matches()->create($validated);

        return redirect()->route('admin.gameweeks.index')
            ->with('success', 'Match added successfully.');
    }

    public function edit(GameMatch $match): View
    {
        $teams = config('teams');
        return view('admin.matches.edit', compact('match', 'teams'));
    }

    public function update(Request $request, GameMatch $match): RedirectResponse
    {
        $validated = $request->validate([
            'home_team' => 'required|string|max:255',
            'away_team' => 'required|string|max:255',
            'start_time' => 'required|date',
            'home_score' => 'nullable|integer',
            'away_score' => 'nullable|integer',
            'status' => 'required|string',
        ]);

        $match->update($validated);

        if ($validated['status'] === 'completed' && isset($validated['home_score']) && isset($validated['away_score'])) {
            $homeScore = $validated['home_score'];
            $awayScore = $validated['away_score'];

            // Determine match outcome
            $matchResult = 'draw';
            if ($homeScore > $awayScore) {
                $matchResult = 'home_win';
            } elseif ($awayScore > $homeScore) {
                $matchResult = 'away_win';
            }

            // Calculate points for all predictions
            foreach ($match->predictions as $prediction) {
                $points = 0;

                // Determine prediction outcome
                $predResult = 'draw';
                if ($prediction->predicted_home > $prediction->predicted_away) {
                    $predResult = 'home_win';
                } elseif ($prediction->predicted_away > $prediction->predicted_home) {
                    $predResult = 'away_win';
                }

                // Scoring Logic
                if ($prediction->predicted_home == $homeScore && $prediction->predicted_away == $awayScore) {
                    // Exact score
                    $points = 40;
                } elseif ($predResult === $matchResult) {
                    // Correct outcome (but not exact score)
                    $points = 10;
                }

                // Apply Double Chip multiplier
                if ($prediction->is_double_points) {
                    $points *= 2;
                }

                $prediction->update(['points_awarded' => $points]);
            }
        }

        return redirect()->route('admin.gameweeks.index')
            ->with('success', 'Match updated and points calculated.');
    }

    public function destroy(GameMatch $match): RedirectResponse
    {
        $match->delete();
        return back()->with('success', 'Match deleted.');
    }
}
