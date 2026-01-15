<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Gameweek;
use App\Models\GameMatch;
use App\Models\User;
use App\Models\Prediction;
use App\Services\ScoringService;

class ImportLegacyData extends Command
{
    protected $signature = 'pundit:import-legacy {gameweek=FA Cup Round 3}';
    protected $description = 'Import legacy data with chips support';

    public function handle(ScoringService $scoringService)
    {
        $gwName = $this->argument('gameweek'); // Expect "FA Cup Round 3"

        // Data from user. Corrected typos where obvious.
        $data = [
            ['Wolves', 6, 1, 'Shrewsbury', 3, 1, 3, 0, 2, 0, 1, 0],
            ['Everton', 1, 1, 'Sunderland', 2, 2, 2, 1, '(1', '1)', 1, 2],
            ['Cheltenham', 0, 2, 'Leicester', 1, 2, 0, 2, 1, 2, 0, 2],
            ['Macclesfield', 2, 1, 'Crystal Palace', 1, 3, 0, 3, 0, 2, 0, 2],
            ['Doncaster', 2, 3, 'Southampton', 1, 1, 1, 2, 1, 2, 1, 2],
            ['Ipswich', 2, 1, 'Blackpool', 4, 1, 2, 1, 3, 1, 2, 0],
            ['Man City', 10, 1, 'Exeter City', 6, 0, 4, 0, 6, 0, 4, 1],
            ['Sheffield Wed', 0, 2, 'Brentford', 1, 3, 1, 2, 0, 3, 1, 3],
            ['Fulham', 3, 1, 'Middlesbrough', 2, 2, 2, 1, 1, 2, 3, 0],
            ['Burnley', 5, 1, 'Millwall', 3, 1, 1, 0, 2, 0, 2, 1],
            ['Boreham Wood', 0, 5, 'Burton', '(2', '2)', 0, 2, 2, 2, 1, 1],
            ['Newcastle', 2, 2, 'Bournemouth', 3, 1, 2, 1, 2, 1, 2, 2],
            ['Stoke', 1, 0, 'Coventry', 1, 2, 1, 1, 1, 1, 1, 2],
            ['Spurs', 1, 2, 'Aston Villa', 1, 2, 2, 1, 2, 2, 2, 2],
            ['Grimsby', 3, 2, 'Weston-super-Mare', 2, 2, 3, 1, 3, 0, 2, 0],
            ['Cambridge', 2, 3, 'Birmingham', 2, 1, 0, 2, 2, 1, 1, 1],
            ['Bristol City', 5, 1, 'Watford', 2, 2, 1, 1, 1, 1, 1, 1], // Assuming Bristol City
            ['Charlton', 1, 5, 'Chelsea', 1, 3, '(0', '3)', 1, 3, 0, 2],
            ['Derby', 1, 3, 'Leeds', 1, 3, 1, 2, 1, 1, '(1', '3)'],
            ['Portsmouth', 1, 4, 'Arsenal', 1, 3, 0, 3, 0, 4, 0, 5],
            ['Hull', 0, 0, 'Blackburn', 1, 1, 2, 0, 1, 1, 1, 1],
            ['Norwich', 5, 1, 'Walsall', 3, 0, 1, 2, 2, 1, 2, 0],
            ['Sheffield Utd', 3, 4, 'Mansfield', 3, 1, 1, 1, 2, 0, 3, 0],
            ['Swansea', 1, 1, 'West Brom', 1, 2, 2, 1, 1, 1, 2, 2],
            ['West Ham', 1, 1, 'QPR', 2, 0, 1, 1, 2, 1, 3, 1],
            ['Man Utd', 1, 2, 'Brighton', 2, 2, 1, 2, 3, 1, 2, 2],
            ['Liverpool', 4, 1, 'Barnsley', 4, 1, 3, 2, 4, 0, 4, 1],
        ];

        // Ensure Gameweek Exists (Custom)
        $gameweek = Gameweek::firstOrCreate(
            ['name' => $gwName],
            [
                'is_custom' => true,
                'start_date' => '2026-01-10 15:00:00',
                'end_date' => '2026-01-10 23:59:59',
                'status' => 'active',
                'tournament_id' => \App\Models\Tournament::first()->id
            ]
        );

        $this->info("Processing {$gwName}...");

        $users = [];
        $names = ['Phil', 'Jocke', 'Knowles', 'Christian'];

        foreach ($names as $name) {
            $user = User::where('name', 'like', "%{$name}%")->first();
            if (!$user) {
                $this->error("User not found: {$name}");
                continue;
            }
            $users[] = $user;
        }

        foreach ($data as $row) {
            // Clean names to ensuring mapping (though for custom we just use the string)
            $homeText = $row[0];
            $hScore = $row[1];
            $aScore = $row[2];
            $awayText = $row[3];

            // For custom rounds, we create matches on the fly if they don't exist
            $match = $gameweek->matches()
                ->where('home_team', $homeText)
                ->where('away_team', $awayText)
                ->first();

            if (!$match) {
                // Determine scheduled time (just use GW start for now)
                $match = $gameweek->matches()->create([
                    'home_team' => $homeText,
                    'away_team' => $awayText,
                    'start_time' => $gameweek->start_date,
                    'status' => 'scheduled'
                ]);
                $this->info("Created Match: {$homeText} vs {$awayText}");
            }

            // Update Score
            if ($hScore !== null && $aScore !== null) {
                $match->update([
                    'home_score' => $hScore,
                    'away_score' => $aScore,
                    'status' => 'completed',
                ]);
                $this->info("Updated {$homeText} vs {$awayText}: {$hScore}-{$aScore}");
            } else {
                $this->warn("Skipping score update for {$homeText} vs {$awayText} (No score provided)");
            }

            $indices = [
                0 => [4, 5],
                1 => [6, 7],
                2 => [8, 9],
                3 => [10, 11]
            ];

            foreach ($users as $key => $user) {
                if ($user) {
                    $rawH = (string) $row[$indices[$key][0]];
                    $rawA = (string) $row[$indices[$key][1]];

                    $isDouble = false;

                    // Check for chips format "(X" "Y)"
                    if (str_contains($rawH, '(') || str_contains($rawA, ')')) {
                        $isDouble = true;
                        $rawH = trim($rawH, '() ');
                        $rawA = trim($rawA, '() ');
                    }

                    // Validate numeric
                    if (!is_numeric($rawH) || !is_numeric($rawA)) {
                        if ($rawH === '' || $rawA === '')
                            continue;
                        $this->warn("  - Invalid prediction data for {$user->name}: {$rawH}-{$rawA}");
                        continue;
                    }

                    $predH = (int) $rawH;
                    $predA = (int) $rawA;

                    Prediction::updateOrCreate(
                        ['user_id' => $user->id, 'match_id' => $match->id],
                        [
                            'predicted_home' => $predH,
                            'predicted_away' => $predA,
                            'is_double_points' => $isDouble
                        ]
                    );
                    $chipMsg = $isDouble ? " [DOUBLE CHIP]" : "";
                    $this->line("  - Saved prediction for {$user->name}: {$predH}-{$predA}{$chipMsg}");
                }
            }

            // Calculate Points
            if ($match->status === 'completed') {
                $scoringService->calculatePoints($match);
            }
        }

        $gameweek->update(['status' => 'completed']);
        $this->info("{$gwName} marked completed.");
    }
}
