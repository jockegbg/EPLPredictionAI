<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Gameweek;
use App\Models\GameMatch;
use App\Models\User;
use App\Services\ScoringService;

class ApplyLegacyChips extends Command
{
    protected $signature = 'pundit:apply-legacy-chips';
    protected $description = 'Apply double chips for GW15 legacy data';

    public function handle(ScoringService $scoringService)
    {
        $gameweek = Gameweek::where('name', 'Gameweek 15')->first();
        if (!$gameweek) {
            $this->error('Gameweek 15 not found!');
            return;
        }

        // Config: User Partial Name => [Home Team, Away Team]
        $chips = [
            'Jocke' => ['Bournemouth', 'Chelsea'],
            'Phil' => ['Man City', 'Sunderland'],
            'Knowles' => ['Fulham', 'Crystal Palace'],
            'Christian' => ['Spurs', 'Brentford'],
        ];

        foreach ($chips as $userName => $teams) {
            $user = User::where('name', 'like', "%{$userName}%")->first();
            if (!$user) {
                $this->warn("User not found: $userName");
                continue;
            }

            $match = $gameweek->matches()
                ->where('home_team', $teams[0])
                ->where('away_team', $teams[1])
                ->first();

            if (!$match) {
                $this->warn("Match not found: {$teams[0]} vs {$teams[1]}");
                continue;
            }

            $prediction = $match->predictions()->where('user_id', $user->id)->first();
            if ($prediction) {
                $prediction->update(['is_double_points' => true]);
                $this->info("Applied Double Chip for {$user->name} on {$teams[0]} vs {$teams[1]}");

                // Recalculate points for this match
                $scoringService->calculatePoints($match);
            } else {
                $this->warn("Prediction not found for {$user->name} on this match.");
            }
        }

        $this->info('Chips applied and points recalculated.');
    }
}
