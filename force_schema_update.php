<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking for missing columns...\n";

if (!Schema::hasColumn('predictions', 'points_adjustment')) {
    echo "Adding points_adjustment to predictions...\n";
    Schema::table('predictions', function (Blueprint $table) {
        $table->integer('points_adjustment')->default(0)->after('points_awarded');
    });
    echo "Done.\n";
} else {
    echo "points_adjustment already exists.\n";
}

if (!Schema::hasColumn('gameweeks', 'is_custom')) {
    echo "Adding is_custom to gameweeks...\n";
    Schema::table('gameweeks', function (Blueprint $table) {
        $table->boolean('is_custom')->default(false)->after('status');
    });
    echo "Done.\n";
} else {
    echo "is_custom already exists.\n";
}
