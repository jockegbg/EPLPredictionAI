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

    public function __construct(\App\Services\PunditService $punditService)
    {
        $this->punditService = $punditService;
    }

    public function index(): View
    {
        // Get ALL active/upcoming gameweeks
        $activeGameweeks = Gameweek::whereIn('status', ['active', 'upcoming'])
            ->with([
                'matches' => function ($query) {
                    $query->orderBy('start_time', 'asc');
                }
            ])
            ->orderBy('start_date', 'asc')
            ->get();

        // Attach AI Commentary using the Service
        foreach ($activeGameweeks as $gameweek) {
            foreach ($gameweek->matches as $match) {
                // We use extended commentary but we can pick just the 'prediction' or 'context' if needed
                // For the modal, we want the full array
                $cacheKey = "match_commentary_{$match->id}";

                // Cache for 24 hours (or until cleared manually)
                $match->ai_commentary = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addDay(), function () use ($match) {
                    return $this->punditService->generateExtendedCommentary($match);
                });
            }
        }

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

            Prediction::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'match_id' => $pred['match_id'],
                ],
                [
                    'predicted_home' => $pred['home'],
                    'predicted_away' => $pred['away'],
                    'is_double_points' => $isDouble,
                ]
            );
        }

        return back()->with('success', 'Predictions saved!');
    }


}
