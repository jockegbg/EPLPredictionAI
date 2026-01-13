<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Gameweek;
use App\Models\GameMatch;
use App\Services\ScoringService;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class SyncFPLScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pundit:sync-fpl';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync match scores from FPL and calculate points';

    protected $teamMap = [];

    /**
     * Execute the console command.
     */
    public function handle(ScoringService $scoringService)
    {
        $this->info('Starting FPL Score Sync...');

        // 1. Build Team Map
        $this->buildTeamMap();

        // 2. Find relevant Gameweeks (Active or Upcoming but started)
        // We look for gameweeks that are NOT completed.
        // potentially check date range to avoid checking gw 38 when we are in gw 1
        $gameweeks = Gameweek::where('status', '!=', 'completed')
            ->whereDate('start_date', '<=', Carbon::now()->addDays(1)) // Look at started or about to start
            ->get();

        if ($gameweeks->isEmpty()) {
            $this->info('No active gameweeks found.');
            return;
        }

        foreach ($gameweeks as $gameweek) {
            $this->processGameweek($gameweek, $scoringService);
        }

        $this->info('Sync complete.');
    }

    private function buildTeamMap()
    {
        // Default Map
        $map = [
            1 => 'Arsenal',
            2 => 'Aston Villa',
            3 => 'Bournemouth',
            4 => 'Brentford',
            5 => 'Brighton',
            6 => 'Burnley',
            7 => 'Chelsea',
            8 => 'Crystal Palace',
            9 => 'Everton',
            10 => 'Fulham',
            11 => 'Leeds',
            12 => 'Liverpool',
            13 => 'Man City',
            14 => 'Man Utd',
            15 => 'Newcastle',
            16 => "Nott'm Forest",
            17 => 'Southampton',
            18 => 'Spurs',
            19 => 'West Ham',
            20 => 'Wolves'
        ];

        try {
            $response = Http::get("https://fantasy.premierleague.com/api/bootstrap-static/");
            if ($response->successful()) {
                foreach ($response->json()['teams'] as $team) {
                    $name = $team['name'];
                    // Normalization
                    if ($name == 'Manchester City')
                        $name = 'Man City';
                    if ($name == 'Manchester United')
                        $name = 'Man Utd';
                    if ($name == 'Tottenham Hotspur')
                        $name = 'Spurs';
                    if ($name == 'Nottingham Forest')
                        $name = "Nott'm Forest";
                    if ($name == 'Wolverhampton Wanderers')
                        $name = 'Wolves';
                    if ($name == 'Brighton & Hove Albion')
                        $name = 'Brighton';
                    if ($name == 'West Ham United')
                        $name = 'West Ham';
                    if ($name == 'Newcastle United')
                        $name = 'Newcastle';
                    if ($name == 'Leicester City')
                        $name = 'Leicester';
                    if ($name == 'Ipswich Town')
                        $name = 'Ipswich';

                    $map[$team['id']] = $name;
                }
                $this->info('Fetched Team Map from FPL.');
            }
        } catch (\Exception $e) {
            $this->warn('Failed to fetch dynamic map, using defaults.');
        }

        $this->teamMap = $map;
    }

    private function processGameweek(Gameweek $gameweek, ScoringService $scoringService)
    {
        $this->info("Processing {$gameweek->name}...");

        // Extract Number from "Gameweek 12"
        if (!preg_match('/Gameweek (\d+)/i', $gameweek->name, $matches)) {
            $this->warn("Could not parse gameweek number from: {$gameweek->name}");
            return;
        }
        $gwNum = $matches[1];

        $response = Http::get("https://fantasy.premierleague.com/api/fixtures/?event={$gwNum}");
        if ($response->failed()) {
            $this->error("Failed to fetch fixtures for GW {$gwNum}");
            return;
        }

        $fixtures = $response->json();
        $allMatchesCompleted = true; // Assume true, prove false
        $updatesCount = 0;

        foreach ($fixtures as $fix) {
            $homeId = $fix['team_h'];
            $awayId = $fix['team_a'];
            $homeName = $this->teamMap[$homeId] ?? null;
            $awayName = $this->teamMap[$awayId] ?? null;

            if (!$homeName || !$awayName)
                continue;

            // Find match in DB
            $match = $gameweek->matches()
                ->where('home_team', $homeName)
                ->where('away_team', $awayName)
                ->first();

            if (!$match) {
                // Match not in our DB, ignore (per user request to only sync scores for imported matches)
                continue;
            }

            // Check status
            $fplFinished = $fix['finished'];
            $fplStarted = $fix['started'] ?? false;

            // If fixture is not finished, gameweek is not fully complete (unless FPL marks it finished later)
            // But wait, user said "when last match of the gameweek close the gameround"
            // So if ANY match in FPL list is NOT finished, then our gameweek is NOT completed.
            if (!$fplFinished) {
                $allMatchesCompleted = false;
            }

            // Update Match Logic
            // We update if scores changed or status changed
            if ($fplStarted || $fplFinished) {
                $newStatus = $fplFinished ? 'completed' : 'upcoming'; // Or 'in_progress' if we add it
                // Keeping 'upcoming' for in-progress to match existing enum unless we added it?
                // Existing Controller uses 'completed' or 'upcoming'. 
                // Let's assume 'upcoming' covers in-progress for now, or users might be confused if we introduce new status without UI support.
                // Actually, seeing 'upcoming' for a live match is annoying. 
                // But sticking to 'upcoming' vs 'completed' is safest for now.

                $homeScore = $fix['team_h_score']; // Can be null if not started
                $awayScore = $fix['team_a_score'];

                // Only update if something changed
                if ($match->status !== $newStatus || $match->home_score != $homeScore || $match->away_score != $awayScore) {
                    $match->update([
                        'status' => $newStatus,
                        'home_score' => $homeScore,
                        'away_score' => $awayScore,
                    ]);
                    $updatesCount++;

                    // If COMPLETED, calculate points
                    if ($newStatus === 'completed') {
                        $scoringService->calculatePoints($match);
                        $this->info("Updated & Scored: {$homeName} vs {$awayName} ($homeScore - $awayScore)");
                    } else {
                        $this->info("Updated Live: {$homeName} vs {$awayName} ($homeScore - $awayScore)");
                    }
                }
            }
        }

        // Close Gameweek if all FPL fixtures are finished
        if ($allMatchesCompleted && count($fixtures) > 0) {
            if ($gameweek->status !== 'completed') {
                $gameweek->update(['status' => 'completed']);
                $this->info("Gameweek {$gameweek->name} marked as COMPLETED.");
            }
        }
    }
}
