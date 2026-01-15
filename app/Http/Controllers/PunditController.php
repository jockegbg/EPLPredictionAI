<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PunditService;

class PunditController extends Controller
{
    protected $punditService;

    public function __construct(PunditService $punditService)
    {
        $this->punditService = $punditService;
    }

    public function index()
    {
        $gameweeks = \App\Models\Gameweek::orderBy('start_date', 'desc')
            ->paginate(10);

        return view('pundit.index', compact('gameweeks'));
    }

    public function show(\App\Models\Gameweek $gameweek)
    {
        // Eager load matches for performance
        $gameweek->load([
            'matches' => function ($query) {
                $query->orderBy('start_time');
            }
        ]);

        // 1. Identify matches that need AI generation (missing from DB)
        $matchesToGenerate = $gameweek->matches->filter(function ($match) {
            return empty($match->ai_commentary);
        });

        // 2. Batch Generate if needed
        if ($matchesToGenerate->isNotEmpty()) {
            $results = $this->punditService->generateBatchCommentary($matchesToGenerate);

            // Store results in database
            foreach ($results as $matchId => $data) {
                $match = $matchesToGenerate->firstWhere('id', $matchId);
                if ($match) {
                    $match->update(['ai_commentary' => $data]);
                }
            }
        }

        // 3. Retrieve all commentary from database (or fallback)
        foreach ($gameweek->matches as $match) {
            if (empty($match->ai_commentary)) {
                $match->ai_commentary = $this->punditService->getFallback($match);
            }
        }

        // 4. Get User Predictions for this Gameweek
        $userPredictions = [];
        if (auth()->check()) {
            $userPredictions = auth()->user()->predictions()
                ->whereIn('match_id', $gameweek->matches->pluck('id'))
                ->get()
                ->keyBy('match_id');
        }

        // 5. Get Gameweek Summary (Headline/Subheadline)
        $summary = $gameweek->pundit_summary ?? [
            'headline' => "Gameweek {$gameweek->name}",
            'subheadline' => "Predictions and chaos."
        ];

        return view('pundit.show', compact('gameweek', 'userPredictions', 'summary'));
    }
    public function matchCommentary(\App\Models\GameMatch $match)
    {
        // Use the same cache key as the Pundit Article view to ensure consistency
        // Check database first
        if (!empty($match->ai_commentary)) {
            return response()->json($match->ai_commentary);
        }

        $commentary = $this->punditService->generateExtendedCommentary($match);
        $match->update(['ai_commentary' => $commentary]);

        return response()->json($commentary);
    }
}
