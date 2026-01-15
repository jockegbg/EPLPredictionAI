<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Gameweek;
use App\Models\GameMatch;
use App\Models\Prediction;
use App\Models\Tournament;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ImportData extends Command
{
    protected $signature = 'data:import';
    protected $description = 'Import data from storage/app/data_export.json';

    public function handle()
    {
        $this->info('Importing data...');

        if (!Storage::exists('data_export.json')) {
            $this->error('File not found: storage/app/data_export.json');
            return;
        }

        $json = Storage::get('data_export.json');
        $data = json_decode($json, true);

        DB::transaction(function () use ($data) {
            // Disable foreign key checks for mass import
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Users
            $this->info('Importing Users: ' . count($data['users_flat']));
            foreach ($data['users_flat'] as $row) {
                User::unguard();
                User::updateOrCreate(['id' => $row['id']], $row);
                User::reguard();
            }

            // Tournaments
            if (isset($data['tournaments_flat'])) {
                $this->info('Importing Tournaments: ' . count($data['tournaments_flat']));
                foreach ($data['tournaments_flat'] as $row) {
                    Tournament::unguard();
                    Tournament::updateOrCreate(['id' => $row['id']], $row);
                    Tournament::reguard();
                }
            }

            // Gameweeks
            $this->info('Importing Gameweeks: ' . count($data['gameweeks_flat']));
            foreach ($data['gameweeks_flat'] as $row) {
                Gameweek::unguard();
                Gameweek::updateOrCreate(['id' => $row['id']], $row);
                Gameweek::reguard();
            }

            // Matches
            $this->info('Importing Matches: ' . count($data['matches_flat']));
            foreach ($data['matches_flat'] as $row) {
                GameMatch::unguard();
                // Handle date casting if needed, but Eloquent might handle strings ok
                GameMatch::updateOrCreate(['id' => $row['id']], $row);
                GameMatch::reguard();
            }

            // Predictions
            $this->info('Importing Predictions: ' . count($data['predictions_flat']));
            foreach ($data['predictions_flat'] as $row) {
                Prediction::unguard();
                Prediction::updateOrCreate(['id' => $row['id']], $row);
                Prediction::reguard();
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        });

        $this->info('Import complete!');
    }
}
