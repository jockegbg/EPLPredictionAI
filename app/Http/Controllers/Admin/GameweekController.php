<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gameweek;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GameweekController extends Controller
{
    public function index(Request $request): View
    {
        $query = \App\Models\Tournament::query()
            ->with([
                'gameweeks' => function ($q) {
                    $q->orderBy('start_date', 'desc')->with('matches');
                }
            ])
            ->orderBy('created_at', 'desc');

        if ($request->has('tournament_id') && $request->tournament_id != '') {
            $query->where('id', $request->tournament_id);
        }

        $activeTournaments = $query->paginate(5);
        $allTournamentsList = \App\Models\Tournament::orderBy('name')->get(); // For the dropdown filter

        return view('admin.gameweeks.index', compact('activeTournaments', 'allTournamentsList'));
    }

    public function create(Request $request): View
    {
        $tournaments = \App\Models\Tournament::orderBy('created_at', 'desc')->get();
        $selectedTournamentId = $request->query('tournament_id');
        return view('admin.gameweeks.create', compact('tournaments', 'selectedTournamentId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tournament_id' => 'required|exists:tournaments,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_custom' => 'sometimes|boolean',
        ]);

        $validated['is_custom'] = $request->has('is_custom');

        Gameweek::create($validated);

        return redirect()->route('admin.gameweeks.index')
            ->with('success', 'Gameweek created successfully.');
    }

    public function edit(Gameweek $gameweek): View
    {
        return view('admin.gameweeks.edit', compact('gameweek'));
    }

    public function update(Request $request, Gameweek $gameweek): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:upcoming,active,completed',
            'is_custom' => 'sometimes|boolean',
        ]);

        $validated['is_custom'] = $request->has('is_custom');

        $gameweek->update($validated);

        return redirect()->route('admin.gameweeks.index')
            ->with('success', 'Gameweek updated successfully.');
    }

    public function destroy(Gameweek $gameweek): RedirectResponse
    {
        $gameweek->delete();
        return redirect()->route('admin.gameweeks.index')
            ->with('success', 'Gameweek deleted successfully.');
    }

    public function adjustPoints(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'match_id' => 'required|exists:matches,id',
            'points_adjustment' => 'required|integer',
        ]);

        $prediction = \App\Models\Prediction::firstOrCreate(
            ['user_id' => $validated['user_id'], 'match_id' => $validated['match_id']],
            ['predicted_home' => 0, 'predicted_away' => 0] // Default if no prediction exists yet
        );

        $prediction->points_adjustment = $validated['points_adjustment'];
        $prediction->save();

        // Recalculate Points for this match
        $scoringService = app(\App\Services\ScoringService::class);
        $scoringService->calculatePoints($prediction->match);

        return back()->with('success', 'Sidebet adjustment applied successfully.');
    }

    public function recalculateScores(Gameweek $gameweek): RedirectResponse
    {
        $scoringService = app(\App\Services\ScoringService::class);
        $count = 0;
        foreach ($gameweek->matches as $match) {
            if ($match->status === 'completed') {
                $scoringService->calculatePoints($match);
                $count++;
            }
        }
        return back()->with('success', "Recalculated scores for {$count} completed matches.");
    }
}
