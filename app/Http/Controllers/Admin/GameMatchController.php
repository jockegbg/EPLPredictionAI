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
            // Use the service to calculate points
            $scoringService = app(\App\Services\ScoringService::class);
            $scoringService->calculatePoints($match);
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
