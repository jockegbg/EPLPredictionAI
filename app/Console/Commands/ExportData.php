<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Gameweek;
use App\Models\GameMatch;
use App\Models\Prediction;
use App\Models\Tournament;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ExportData extends Command
{
    protected $signature = 'data:export';
    protected $description = 'Export local data to storage/app/data_export.json';

    public function handle()
    {
        $this->info('Exporting data...');

        $data = [
            'users' => User::all(),
            'tournaments' => Tournament::with('gameweeks')->get(), // Eager load to check relationships if needed? No, just raw table dumps is safer.
            // Actually, flat dumps are better for raw import
            'users_flat' => User::all()->makeVisible(['password', 'remember_token']), // Need passwords!
            'tournaments_flat' => Tournament::all(),
            'gameweeks_flat' => Gameweek::all(),
            'matches_flat' => GameMatch::all(),
            'predictions_flat' => Prediction::all(),
        ];

        // Also export Pundit Summaries from Cache if possible?
        // It's hard to iterate cache. Let's stick to DB. Pundit can regenerate.

        $json = json_encode($data, JSON_PRETTY_PRINT);
        Storage::put('data_export.json', $json);

        $this->info('Data exported to storage/app/data_export.json');
        $this->info('Size: ' . strlen($json) . ' bytes');
    }
}
