<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TournamentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $tournaments = Tournament::all();
        return view('admin.tournaments.index', compact('tournaments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $users = \App\Models\User::orderBy('name')->get();
        return view('admin.tournaments.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'users' => 'array',
            'users.*' => 'exists:users,id',
            'score_correct_score' => 'required|integer',
            'score_correct_outcome' => 'required|integer',
            'score_goal_difference' => 'required|integer',
            'score_wrong_outcome_penalty' => 'required|integer',
            'is_cashout_enabled' => 'boolean',
            'is_double_down_enabled' => 'boolean',
            'is_defence_enabled' => 'boolean',
        ]);

        $tournament = Tournament::create([
            'name' => $validated['name'],
            'is_active' => $request->has('is_active'),
            'score_correct_score' => $validated['score_correct_score'],
            'score_correct_outcome' => $validated['score_correct_outcome'],
            'score_goal_difference' => $validated['score_goal_difference'],
            'score_wrong_outcome_penalty' => $validated['score_wrong_outcome_penalty'],
            'is_cashout_enabled' => $request->has('is_cashout_enabled'),
            'is_double_down_enabled' => $request->has('is_double_down_enabled'),
            'is_defence_enabled' => $request->has('is_defence_enabled'),
        ]);

        if (isset($validated['users'])) {
            $tournament->users()->sync($validated['users']);
        }

        return redirect()->route('admin.tournaments.index')
            ->with('success', 'Tournament created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tournament $tournament): View
    {
        $users = \App\Models\User::orderBy('name')->get();
        // Load participants to check checkboxes
        $tournament->load('users');
        return view('admin.tournaments.edit', compact('tournament', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tournament $tournament): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'users' => 'array',
            'users.*' => 'exists:users,id',
            'score_correct_score' => 'required|integer',
            'score_correct_outcome' => 'required|integer',
            'score_goal_difference' => 'required|integer',
            'score_wrong_outcome_penalty' => 'required|integer',
            'is_cashout_enabled' => 'boolean',
            'is_double_down_enabled' => 'boolean',
            'is_defence_enabled' => 'boolean',
        ]);

        $tournament->update([
            'name' => $validated['name'],
            'is_active' => $request->has('is_active'),
            'score_correct_score' => $validated['score_correct_score'],
            'score_correct_outcome' => $validated['score_correct_outcome'],
            'score_goal_difference' => $validated['score_goal_difference'],
            'score_wrong_outcome_penalty' => $validated['score_wrong_outcome_penalty'],
            'is_cashout_enabled' => $request->has('is_cashout_enabled'),
            'is_double_down_enabled' => $request->has('is_double_down_enabled'),
            'is_defence_enabled' => $request->has('is_defence_enabled'),
        ]);

        if (isset($validated['users'])) {
            $tournament->users()->sync($validated['users']);
        } else {
            $tournament->users()->detach();
        }

        return redirect()->route('admin.tournaments.index')
            ->with('success', 'Tournament updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tournament $tournament): RedirectResponse
    {
        $tournament->delete();
        return redirect()->route('admin.tournaments.index')
            ->with('success', 'Tournament deleted successfully.');
    }

    public function syncAllUsers(Tournament $tournament): RedirectResponse
    {
        $allUserIds = User::pluck('id');
        $tournament->users()->syncWithoutDetaching($allUserIds);

        return back()->with('success', 'All users have been added to this tournament.');
    }
}
