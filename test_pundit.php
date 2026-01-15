<?php

use App\Models\Gameweek;
use App\Services\PunditService;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$punditService = app(PunditService::class);
$gameweek = Gameweek::find(17);

if (!$gameweek) {
    echo "Gameweek 17 not found.\n";
    exit(1);
}

echo "Testing PunditService for GW 17...\n";
foreach ($gameweek->matches as $match) {
    echo "Match: {$match->home_team} vs {$match->away_team}\n";

    // Attempt generation
    $result = $punditService->generateExtendedCommentary($match);

    echo "Result (Prediction): " . ($result['prediction'] ?? 'N/A') . "\n";
    echo "---------------------------------------------------\n";
}
