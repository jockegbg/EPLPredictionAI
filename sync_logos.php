<?php

use Illuminate\Support\Str;

require __DIR__ . '/vendor/autoload.php';

$outputDir = __DIR__ . '/public/images/teams';

// 1. Renames
$renames = [
    'tottenham-hotspur.png' => 'spurs.png',
    'wolverhampton-wanderers.png' => 'wolves.png',
    'nottingham-forest.png' => 'nottm-forest.png',
    'brighton-hove-albion.png' => 'brighton.png',
    'newcastle-united.png' => 'newcastle.png',
    'west-ham-united.png' => 'west-ham.png',
];

foreach ($renames as $old => $new) {
    if (file_exists("$outputDir/$old")) {
        rename("$outputDir/$old", "$outputDir/$new");
        echo "Renamed $old -> $new\n";
    }
}

// 2. Downloads for New Teams
// Need to find codes for Burnley, Leeds, Sunderland from fpl_data.json
$fplJson = file_get_contents(__DIR__ . '/fpl_data.json');
$fplData = json_decode($fplJson, true);
$teamsToCheck = ['Burnley', 'Leeds', 'Sunderland'];

foreach ($teamsToCheck as $teamName) {
    foreach ($fplData['teams'] as $fplTeam) {
        if ($fplTeam['name'] === $teamName) {
            $code = $fplTeam['code'];
            $slug = Str::slug($teamName);
            $url = "https://resources.premierleague.com/premierleague/badges/t{$code}.png";
            $dest = "$outputDir/{$slug}.png";

            if (!file_exists($dest)) {
                echo "Downloading $teamName ($code)...\n";
                try {
                    copy($url, $dest);
                } catch (\Exception $e) {
                    echo "Failed to download $teamName: " . $e->getMessage() . "\n";
                }
            }
            break;
        }
    }
}
echo "Done.\n";
