<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\GameMatch;
use App\Models\Prediction;
use Carbon\Carbon;

class ApiFootballService
{
    protected $apiKey;
    protected $baseUrl;
    protected $leagueId = 39; // Premier League
    protected $season = 2024; // Current season

    public function __construct()
    {
        $this->apiKey = config('services.api-football.key');
        $this->baseUrl = config('services.api-football.base_url');
    }

    public function cancelRun(string $reason)
    {
        Log::info("ApiFootball: Live sync cancelled - {$reason}");
        return;
    }

    /**
     * Check if we should call the API (rate limit guard)
     * Matches live or scheduled to start now?
     * 5 mins passed since last call?
     */
    public function syncLiveScores()
    {
        // 1. Check for live matches in OUR database
        // Matches that are 'in_progress' OR 'scheduled' and start_time <= now
        $liveMatches = GameMatch::where('status', 'in_progress')
            ->orWhere(function ($q) {
                $q->where('status', 'scheduled')
                    ->where('start_time', '<=', now());
            })->get();

        if ($liveMatches->isEmpty()) {
            return $this->cancelRun("No live matches found in DB.");
        }

        // 2. Check Cache for Rate Limiting (5 mins)
        if (Cache::has('api_football_last_call')) {
            return $this->cancelRun("Rate limit active (5 min window).");
        }

        // 3. Make API Call
        $this->fetchAndProcessLiveFixtures();

        // 4. Set Cache (5 mins = 300 seconds)
        Cache::put('api_football_last_call', true, 300);
    }

    protected function fetchAndProcessLiveFixtures()
    {
        Log::info("ApiFootball: Fetching live scores...");

        $response = Http::withHeaders([
            'x-rapidapi-key' => $this->apiKey,
            'x-rapidapi-host' => 'v3.football.api-sports.io'
        ])->get("{$this->baseUrl}/fixtures", [
                    'live' => 'all',
                    // 'league' => $this->leagueId // Optional: Filter by league strictly if needed
                ]);

        if ($response->failed()) {
            Log::error("ApiFootball: Request failed", ['status' => $response->status(), 'body' => $response->body()]);
            return;
        }

        $fixtures = $response->json()['response'] ?? [];

        foreach ($fixtures as $fixture) {
            $this->processFixture($fixture);
        }
    }

    protected function processFixture($data)
    {
        $homeTeamName = $data['teams']['home']['name'];
        $awayTeamName = $data['teams']['away']['name'];

        // Find match in our DB - Using fuzzy match or precise name logic needed?
        // For now, assuming names match or we have a mapping. 
        // Simple name matching:
        $match = GameMatch::where('home_team', $homeTeamName)
            ->where('away_team', $awayTeamName)
            // Relaxed date check: within 24 hours just in case
            ->whereBetween('start_time', [now()->subDay(), now()->addDay()])
            ->first();

        if (!$match) {
            // Log::warning("ApiFootball: Match not found in DB: $homeTeamName vs $awayTeamName");
            return;
        }

        // Update Status
        $apiStatus = $data['fixture']['status']['short']; // 1H, 2H, HT, FT, etc.
        $goalsHome = $data['goals']['home'];
        $goalsAway = $data['goals']['away'];

        // Map API status to our status
        $newStatus = match ($apiStatus) {
            '1H', '2H', 'HT', 'ET', 'P', 'BT' => 'in_progress',
            'FT', 'AET', 'PEN' => 'completed',
            'PST', 'CANC', 'ABD' => 'postponed', // or cancelled
            default => 'scheduled',
        };

        // Don't revert 'completed' to 'scheduled' accidentally
        if ($match->status === 'completed' && $newStatus !== 'completed') {
            return;
        }

        $match->update([
            'home_score' => $goalsHome,
            'away_score' => $goalsAway,
            'status' => $newStatus,
            'minutes' => $data['fixture']['status']['elapsed'],
        ]);

        // If completed just now, calculate points
        if ($newStatus === 'completed' && $match->wasChanged('status')) {
            // We need to trigger scoring
            // ... We can dispatch an event or call service directly
            Log::info("ApiFootball: Match {$match->id} completed. Calculating points.");
            app(\App\Services\ScoringService::class)->calculatePoints($match);
        }
    }
}
