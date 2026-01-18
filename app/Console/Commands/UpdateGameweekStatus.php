<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Gameweek;
use Illuminate\Database\Eloquent\Builder;

class UpdateGameweekStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gameweeks:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update gameweek statuses based on match activity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking gameweek statuses...');

        // 1. Activate Upcoming Gameweeks
        // Find 'upcoming' gameweeks where ANY match has started (start_time <= now)
        $upcomingGameweeks = Gameweek::where('status', 'upcoming')
            ->whereHas('matches', function (Builder $query) {
                $query->where('start_time', '<=', now());
            })->get();

        foreach ($upcomingGameweeks as $gameweek) {
            $gameweek->update(['status' => 'active']);
            $this->info("Gameweek '{$gameweek->name}' set to Active.");
        }

        // 2. Complete Active Gameweeks
        // Find 'active' gameweeks where ALL matches are completed
        $activeGameweeks = Gameweek::where('status', 'active')
            ->whereHas('matches') // Ensure it actually has matches before completing
            ->withCount([
                'matches as pending_matches_count' => function (Builder $query) {
                    $query->where('status', '!=', 'completed');
                }
            ])
            ->get();

        foreach ($activeGameweeks as $gameweek) {
            // If No pending matches (all are completed)
            if ($gameweek->pending_matches_count === 0) {
                $gameweek->update(['status' => 'completed']);
                $this->info("Gameweek '{$gameweek->name}' set to Completed.");
            }
        }

        $this->info('Gameweek status check finished.');
    }
}
