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
        $query = Gameweek::query();

        if ($request->has('tournament_id') && $request->tournament_id != '') {
            $query->where('tournament_id', $request->tournament_id);
        }

        $gameweeks = $query->orderBy('start_date', 'desc')->paginate(10);
        $tournaments = \App\Models\Tournament::orderBy('created_at', 'desc')->get();

        return view('admin.gameweeks.index', compact('gameweeks', 'tournaments'));
    }

    public function create(): View
    {
        $tournaments = \App\Models\Tournament::orderBy('created_at', 'desc')->get();
        return view('admin.gameweeks.create', compact('tournaments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tournament_id' => 'required|exists:tournaments,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

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
        ]);

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
}
