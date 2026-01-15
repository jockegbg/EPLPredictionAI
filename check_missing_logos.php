<?php

use App\Models\GameMatch;
use Illuminate\Support\Str;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$matches = GameMatch::all();
$teams = [];
foreach ($matches as $match) {
    $teams[] = $match->home_team;
    $teams[] = $match->away_team;
}
$teams = array_unique($teams);
sort($teams);

$missing = [];

foreach ($teams as $team) {
    if ($team === 'Exeter City')
        $slug = 'exeter'; // potential override
    else if ($team === 'Sheffield Wed')
        $slug = 'sheffield-wednesday';
    else if ($team === "Nott'm Forest")
        $slug = 'nottm-forest'; // known override
    else
        $slug = Str::slug($team);

    $path = __DIR__ . "/public/images/teams/{$slug}.png";
    if (!file_exists($path)) {
        // Double check alternates?
        $missing[] = $team;
    }
}

echo "Missing Logos:\n";
foreach ($missing as $m) {
    echo "- $m\n";
}
