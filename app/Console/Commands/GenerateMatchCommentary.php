<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateMatchCommentary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pundit:generate';
    protected $description = 'Pre-generate AI commentary for upcoming matches to warm the cache.';

    public function handle(\App\Services\PunditService $punditService)
    {
        $this->info('Starting Pundit Cache Warmer...');

        // 1. Get upcoming gameweeks
        $gameweeks = \App\Models\Gameweek::whereIn('status', ['active', 'upcoming'])
            ->with('matches')
            ->get();

        if ($gameweeks->isEmpty()) {
            $this->warn('No active or upcoming gameweeks found.');
            return;
        }

        $matchCount = $gameweeks->pluck('matches')->flatten()->count();
        $this->info("Found {$matchCount} upcoming matches.");

        $bar = $this->output->createProgressBar($matchCount);
        $bar->start();

        foreach ($gameweeks as $gw) {
            foreach ($gw->matches as $match) {
                // Key matches the one in PredictionController
                $cacheKey = "match_commentary_{$match->id}";

                // Force refresh or just ensure it exists?
                // cache()->forget($cacheKey); // Uncomment to force refresh

                \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addDay(), function () use ($punditService, $match) {
                    // This will trigger the AI call if not cached
                    return $punditService->generateExtendedCommentary($match);
                });

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Pundit commentary cache warmed successfully! 🧠🔥');
    }
}
