<?php

use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = Schema::getColumnListing('predictions');
echo "Columns in predictions table:\n";
foreach ($columns as $col) {
    echo "- $col\n";
}

$columnsGw = Schema::getColumnListing('gameweeks');
echo "\nColumns in gameweeks table:\n";
foreach ($columnsGw as $col) {
    echo "- $col\n";
}
