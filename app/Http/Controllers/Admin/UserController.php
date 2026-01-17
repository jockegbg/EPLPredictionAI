<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'is_admin' => ['boolean'],
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function resetPassword(User $user)
    {
        // Generate a random 8-char password
        $password = Str::random(10);
        $user->update([
            'password' => Hash::make($password),
        ]);

        return back()->with('success', "Password reset to: {$password} (Copy this now!)");
    }

    public function removePasskeys(User $user)
    {
        $user->passkeys()->delete();
        return back()->with('success', 'All passkeys removed for this user.');
    }

    public function scoreData(Request $request)
    {
        if ($request->has('all_tournaments')) {
            if ($request->has('user_id')) {
                $user = User::findOrFail($request->user_id);
                return $user->tournaments()->get(['tournaments.id', 'tournaments.name']);
            }
            return \App\Models\Tournament::all(['id', 'name']);
        }

        if ($request->has('tournament_id')) {
            return \App\Models\Gameweek::where('tournament_id', $request->tournament_id)
                ->orderBy('start_date', 'desc')
                ->get(['id', 'name']);
        }

        if ($request->has('gameweek_id')) {
            $matches = \App\Models\GameMatch::where('gameweek_id', $request->gameweek_id)
                ->get();

            return $matches->map(function ($match) {
                return [
                    'id' => $match->id,
                    'display_name' => $match->home_team . ' vs ' . $match->away_team,
                ];
            });
        }

        return [];
    }

    public function submitScore(Request $request, User $user)
    {
        $validated = $request->validate([
            'match_id' => 'required|exists:matches,id',
            'predicted_home' => 'required|integer|min:0',
            'predicted_away' => 'required|integer|min:0',
            'chip' => 'nullable|string|in:double_points,defence_chip',
        ]);

        $prediction = \App\Models\Prediction::updateOrCreate(
            [
                'user_id' => $user->id,
                'match_id' => $validated['match_id'],
            ],
            [
                'predicted_home' => $validated['predicted_home'],
                'predicted_away' => $validated['predicted_away'],
                'chip' => $validated['chip'] ?: null,
            ]
        );

        // Auto-calculate points if the match is already completed
        $match = $prediction->match;
        if ($match && $match->status === 'completed') {
            $scoringService = app(\App\Services\ScoringService::class);
            $points = $scoringService->calculatePredictionScore($match, $prediction);
            $prediction->update(['points_awarded' => $points]);
        }

        return back()->with('success', 'Score submitted for ' . $user->name);
    }

}
