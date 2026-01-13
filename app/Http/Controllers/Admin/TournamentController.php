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
            'users' => 'array', // Array of user IDs
            'users.*' => 'exists:users,id',
        ]);

        $tournament = Tournament::create([
            'name' => $validated['name'],
            'is_active' => $request->has('is_active'),
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
        ]);

        $tournament->update([
            'name' => $validated['name'],
            'is_active' => $request->has('is_active'),
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
}
