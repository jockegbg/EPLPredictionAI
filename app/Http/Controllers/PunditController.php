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

        // 1. Identify matches that need AI generation (not in cache)
        $matchesToGenerate = $gameweek->matches->filter(function ($match) {
            $cacheKey = "match_commentary_{$match->id}_" . $match->updated_at->timestamp;
            return !\Illuminate\Support\Facades\Cache::has($cacheKey);
        });

        // 2. Batch Generate if needed
        if ($matchesToGenerate->isNotEmpty()) {
            $results = $this->punditService->generateBatchCommentary($matchesToGenerate);

            // Store results in cache
            foreach ($results as $matchId => $data) {
                // Find original match to get timestamp for key correctness
                $match = $matchesToGenerate->firstWhere('id', $matchId);
                if ($match) {
                    $cacheKey = "match_commentary_{$match->id}_" . $match->updated_at->timestamp;
                    \Illuminate\Support\Facades\Cache::put($cacheKey, $data, 86400);
                }
            }
        }

        // 3. Retrieve all commentary from cache (or fallback)
        foreach ($gameweek->matches as $match) {
            $cacheKey = "match_commentary_{$match->id}_" . $match->updated_at->timestamp;
            $match->ai_commentary = \Illuminate\Support\Facades\Cache::get($cacheKey, function () use ($match) {
                return $this->punditService->getFallback($match);
            });
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
        $summary = \Illuminate\Support\Facades\Cache::get("pundit_summary_{$gameweek->id}", [
            'headline' => "Gameweek {$gameweek->name}",
            'subheadline' => "Predictions and chaos."
        ]);

        return view('pundit.show', compact('gameweek', 'userPredictions', 'summary'));
    }
    public function matchCommentary(\App\Models\GameMatch $match)
    {
        // Use the same cache key as the Pundit Article view to ensure consistency
        $cacheKey = "match_commentary_{$match->id}_" . $match->updated_at->timestamp;

        // Cache for 24 hours (timestamp in key handles invalidation on update)
        $commentary = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addDay(), function () use ($match) {
            return $this->punditService->generateExtendedCommentary($match);
        });

        return response()->json($commentary);
    }
}
