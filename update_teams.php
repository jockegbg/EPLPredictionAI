<?php

use App\Models\GameMatch;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::transaction(function () {
    // Man City
    GameMatch::where('home_team', 'Manchester City')->update(['home_team' => 'Man City']);
    GameMatch::where('away_team', 'Manchester City')->update(['away_team' => 'Man City']);

    // Man Utd
    GameMatch::where('home_team', 'Manchester United')->update(['home_team' => 'Man Utd']);
    GameMatch::where('away_team', 'Manchester United')->update(['away_team' => 'Man Utd']);
});

echo "Database updated.\n";
