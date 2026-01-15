<?php

namespace App\Http\Controllers;

use App\Models\Gameweek;
use App\Models\GameMatch;
use App\Models\Prediction;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class PredictionController extends Controller
{
    protected $punditService;
    protected $scoringService;

    public function __construct(\App\Services\PunditService $punditService, \App\Services\ScoringService $scoringService)
    {
        $this->punditService = $punditService;
        $this->scoringService = $scoringService;
    }

    public function index(): View
    {
        // Get ALL active/upcoming gameweeks
        $activeGameweeks = Gameweek::whereIn('status', ['active', 'upcoming'])
            ->with([
                'matches' => function ($query) {
                    $query->orderBy('start_time', 'asc');
                },
                'tournament' // Eager load tournament for settings
            ])
            ->orderBy('start_date', 'asc')
            ->get();

        // Attach AI Commentary using the Service
        // Attach AI Commentary logic removed (moved to Async AJAX call)

        return view('predictions.index', compact('activeGameweeks'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'predictions' => 'required|array',
            'predictions.*.match_id' => 'required|exists:matches,id',
            'predictions.*.home' => 'nullable|integer|min:0',
            'predictions.*.away' => 'nullable|integer|min:0',
        ]);

        $user = Auth::user();

        // Validate doubles array (optional)
        $doubles = $request->input('doubles', []);
        $defences = $request->input('defence', []);

        // Fetch all relevant matches to check status
        $matchIds = collect($data['predictions'])->pluck('match_id');
        $matches = GameMatch::whereIn('id', $matchIds)->get()->keyBy('id');

        foreach ($data['predictions'] as $pred) {
            // Skip if prediction is incomplete (or missing due to disabled inputs)
            $home = $pred['home'] ?? null;
            $away = $pred['away'] ?? null;

            if (is_null($home) || is_null($away)) {
                continue;
            }

            $match = $matches->get($pred['match_id']);

            // SECURITY CHECK: Skip if match not found or already started/finished
            if (!$match || $match->start_time->isPast() || !is_null($match->home_score)) {
                continue;
            }

            // Check if this match is the selected double for its gameweek
            $gwId = $match->gameweek_id;
            $isDouble = isset($doubles[$gwId]) && $doubles[$gwId] == $pred['match_id'];
            $isDefence = isset($defences[$gwId]) && $defences[$gwId] == $pred['match_id'];

            Prediction::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'match_id' => $pred['match_id'],
                ],
                [
                    'predicted_home' => $pred['home'],
                    'predicted_away' => $pred['away'],
                    'is_double_points' => $isDouble,
                    'is_defence_chip' => $isDefence,
                ]
            );
        }

        return back()->with('success', 'Predictions saved!');
    }

    public function cashout(Request $request, GameMatch $match)
    {
        $user = Auth::user();
        $prediction = Prediction::where('user_id', $user->id)
            ->where('match_id', $match->id)
            ->firstOrFail();

        // VALIDATION
        if (!$match->gameweek->tournament->is_cashout_enabled) {
            return back()->with('error', 'Cashout is not enabled for this tournament.');
        }

        if ($prediction->cashed_out_at) {
            return back()->with('error', 'Already cashed out.');
        }

        // Time Window Check: 10 mins (Testing)
        if ($match->start_time->diffInMinutes(now()) < 10) {
            return back()->with('error', 'Cashout only available after 10 minutes.');
        }

        if ($match->status === 'completed') {
            return back()->with('error', 'Match has ended.');
        }

        // Ensure we have a score (Live Score)
        if (is_null($match->home_score)) {
            return back()->with('error', 'Live score not available.');
        }

        // CALCULATE POINTS (DRY RUN)
        $currentPoints = $this->scoringService->calculatePredictionScore($match, $prediction);

        // HALVE POINTS (Integer math)
        // If negative, taking 50% reduces the penalty (which is good for user)
        // If positive, taking 50% locks in profit
        $cashoutPoints = (int) floor($currentPoints / 2);

        $prediction->update([
            'cashed_out_at' => now(),
            'cashout_points' => $cashoutPoints,
            'points_awarded' => $cashoutPoints, // Lock in points immediately
        ]);

        return back()->with('success', "Cashed out for {$cashoutPoints} points!");
    }


}
