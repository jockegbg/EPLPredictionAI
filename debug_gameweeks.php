<?php

use App\Models\Gameweek;
use Carbon\Carbon;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$gameweeks = Gameweek::all();

echo "Current Time: " . Carbon::now()->toDateTimeString() . "\n";
echo "Gameweeks:\n";
foreach ($gameweeks as $gw) {
    echo "ID: {$gw->id} | Name: {$gw->name} | Start: {$gw->start_date} | Status: {$gw->status} | Custom: " . ($gw->is_custom ? 'Yes' : 'No') . "\n";
}
