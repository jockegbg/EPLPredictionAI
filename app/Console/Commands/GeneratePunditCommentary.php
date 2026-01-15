<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Gameweek;
use App\Services\PunditService;
use Illuminate\Support\Facades\Cache;

class GeneratePunditCommentary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pundit:generate {gameweek_id? : ID of the gameweek to generate for (optional)} {--all : Process ALL gameweeks} {--force : Force regenerate existing commentary}';

    protected $description = 'Generate AI Pundit commentary for a gameweek and cache it.';

    protected $punditService;

    public function __construct(PunditService $punditService)
    {
        parent::__construct();
        $this->punditService = $punditService;
    }

    public function handle()
    {
        $gameweekId = $this->argument('gameweek_id');
        $processAll = $this->option('all');
        $force = $this->option('force');

        $gameweeks = collect();

        if ($processAll) {
            $this->info("Fetching ALL gameweeks...");
            $gameweeks = Gameweek::with('matches.predictions.user')
                ->orderBy('start_date', 'desc')
                ->get();
        } elseif ($gameweekId) {
            $gameweeks = collect([Gameweek::with('matches.predictions.user')->find($gameweekId)]);
        } else {
            // Find the most recent active or upcoming gameweek
            $gw = Gameweek::with('matches.predictions.user')
                ->where('start_date', '>=', now()->subDays(7)) // Look back 7 days to catch active one
                ->orderBy('start_date', 'asc')
                ->first();

            // Fallback: If no "recent" gameweek (e.g. off-season or old data), grab the very latest one
            if (!$gw) {
                $this->warn("No active/recent gameweek found. Falling back to the latest gameweek in DB.");
                $gw = Gameweek::with('matches.predictions.user')
                    ->orderBy('start_date', 'desc')
                    ->first();
            }

            if ($gw)
                $gameweeks->push($gw);
        }

        if ($gameweeks->isEmpty()) {
            $this->error('No suitable gameweek found.');
            return 1;
        }

        foreach ($gameweeks as $gameweek) {
            $this->info("------------------------------------------------");
            $this->info("Processing Gameweek: {$gameweek->name}");

            // 1. Generate Summary (Always regenerate if running this command)
            $this->info("Generating Headline...");
            try {
                $summary = $this->punditService->generateGameweekSummary($gameweek);
                Cache::put("pundit_summary_{$gameweek->id}", $summary, 3600 * 24);
            } catch (\Exception $e) {
                $this->error("Summary failed: " . $e->getMessage());
            }

            // 1b. Generate Image (Only if missing or forced)
            if (!$gameweek->image_path || $force) {
                $this->info("Generating AI Image...");
                try {
                    $imagePath = $this->punditService->generateGameweekImage($gameweek);
                    if ($imagePath) {
                        $gameweek->update(['image_path' => $imagePath]);
                        $this->info("Image generated: $imagePath");
                    }
                } catch (\Exception $e) {
                    $this->error("Image generation failed: " . $e->getMessage());
                }
            }

            // 2. Generate Match Commentary
            $matchesToGenerate = $gameweek->matches;
            if (!$force) {
                $matchesToGenerate = $gameweek->matches->filter(function ($match) {
                    $cacheKey = "match_commentary_{$match->id}_" . $match->updated_at->timestamp;
                    return !Cache::has($cacheKey);
                });
            }

            if ($matchesToGenerate->isEmpty()) {
                $this->info("Matches already cached. Skipping matches.");
                continue;
            }

            $this->info("Generating commentary for " . $matchesToGenerate->count() . " matches...");

            $results = $this->punditService->generateBatchCommentary($matchesToGenerate);

            foreach ($results as $matchId => $data) {
                $match = $gameweek->matches->firstWhere('id', $matchId);
                if ($match) {
                    $cacheKey = "match_commentary_{$match->id}_" . $match->updated_at->timestamp;
                    Cache::put($cacheKey, $data, 3600);
                }
            }
        }

        $this->newLine();
        $this->info('Pundit Generation Complete!');

        return 0;
    }
}
