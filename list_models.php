<?php

use Illuminate\Support\Facades\Http;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apiKey = env('GEMINI_API_KEY');
$url = "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}";

echo "Checking available models...\n";
$response = Http::get($url);

if ($response->successful()) {
    $models = $response->json()['models'] ?? [];
    foreach ($models as $model) {
        if (strpos($model['name'], 'generateContent') !== false || true) {
            echo "- " . $model['name'] . "\n";
        }
    }
} else {
    echo "Error: " . $response->status() . " " . $response->body() . "\n";
}
