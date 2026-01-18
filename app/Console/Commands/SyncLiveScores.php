<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ApiFootballService;

class SyncLiveScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scores:sync-live';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync live scores from API-Football (Rate limited)';

    /**
     * Execute the console command.
     */
    public function handle(ApiFootballService $service)
    {
        $this->info('Starting live score sync check...');

        $service->syncLiveScores();

        $this->info('Sync check completed.');
    }
}
