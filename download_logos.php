<?php

use Illuminate\Support\Str;

require __DIR__ . '/vendor/autoload.php';

// 1. Load my app's team list
$myTeams = require __DIR__ . '/config/teams.php';

// 2. Load FPL data
$fplJson = file_get_contents(__DIR__ . '/fpl_data.json');
$fplData = json_decode($fplJson, true);

if (!$fplData || !isset($fplData['teams'])) {
    die("Error parsing FPL data.\n");
}

$fplTeams = $fplData['teams'];

// Helper to normalize names for matching
function normalize($name)
{
    // Handle specific mappings
    $map = [
        "Spurs" => "Tottenham Hotspur",
        "Wolves" => "Wolverhampton Wanderers",
        "Nott'm Forest" => "Nottingham Forest",
        "Man Utd" => "Manchester United",
        "Man City" => "Manchester City",
        "Newcastle" => "Newcastle United",
        "Leicester" => "Leicester City",
        "Ipswich" => "Ipswich Town",
        // Add others if needed
    ];

    if (isset($map[$name]))
        return $map[$name];

    // Reverse map check (My config might use full names, FPL might use short)
    $reverseMap = array_flip($map);
    if (isset($reverseMap[$name]))
        return $reverseMap[$name];

    return $name;
}

$outputDir = __DIR__ . '/public/images/teams';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

foreach ($myTeams as $myTeamName) {
    $found = false;
    $targetCode = null;

    foreach ($fplTeams as $fplTeam) {
        // Try strict match
        if ($fplTeam['name'] === $myTeamName) {
            $targetCode = $fplTeam['code'];
            $found = true;
            break;
        }

        // Try normalized match
        if (normalize($fplTeam['name']) === normalize($myTeamName)) {
            $targetCode = $fplTeam['code'];
            $found = true;
            break;
        }

        // Try contains
        if (str_contains($myTeamName, $fplTeam['name']) || str_contains($fplTeam['name'], $myTeamName)) {
            $targetCode = $fplTeam['code'];
            $found = true;
            break;
        }
    }

    if ($found && $targetCode) {
        $slug = Str::slug($myTeamName);
        // Try to get high-res if possible, otherwise standard
        // Badge URLs: https://resources.premierleague.com/premierleague/badges/t{code}.png
        // Sometimes 50x50 is default? Let's try to get larger if possible? 
        // t{code}@x2.png provides higher res usually.

        $url = "https://resources.premierleague.com/premierleague/badges/t{$targetCode}.png";
        $dest = "$outputDir/{$slug}.png";

        echo "Downloading $myTeamName (code: $targetCode) to $slug.png... ";

        try {
            $content = file_get_contents($url);
            if ($content) {
                file_put_contents($dest, $content);
                echo "OK\n";
            } else {
                echo "FAILED (empty content)\n";
            }
        } catch (\Exception $e) {
            echo "FAILED (" . $e->getMessage() . ")\n";
        }

    } else {
        echo "WARNING: Check mapping for '$myTeamName' - not found in FPL data.\n";
    }
}

echo "Done.\n";
