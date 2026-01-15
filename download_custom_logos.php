<?php

use Illuminate\Support\Str;

require __DIR__ . '/vendor/autoload.php';

$outputDir = __DIR__ . '/public/images/teams';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Teams to find
$missingTeams = [
    'Barnsley',
    'Birmingham',
    'Blackburn',
    'Blackpool',
    'Boreham Wood',
    'Bristol City',
    'Burton',
    'Cambridge',
    'Charlton',
    'Cheltenham',
    'Coventry',
    'Derby',
    'Doncaster',
    'Exeter City',
    'Grimsby',
    'Hull',
    'Ipswich',
    'Leicester',
    'Macclesfield',
    'Mansfield',
    'Middlesbrough',
    'Millwall',
    'Norwich',
    'Portsmouth',
    'QPR',
    'Sheffield Utd',
    'Sheffield Wed',
    'Shrewsbury',
    'Stoke',
    'Swansea',
    'Walsall',
    'Watford',
    'West Brom',
    'Weston-super-Mare'
];

// Map input name to likely file name (mostly adding 'City', 'Town', 'United', 'Rovers')
$nameMap = [
    'Birmingham' => 'Birmingham City',
    'Blackburn' => 'Blackburn Rovers',
    'Burton' => 'Burton Albion',
    'Cambridge' => 'Cambridge United',
    'Charlton' => 'Charlton Athletic',
    'Cheltenham' => 'Cheltenham Town',
    'Coventry' => 'Coventry City',
    'Derby' => 'Derby County',
    'Doncaster' => 'Doncaster Rovers',
    'Grimsby' => 'Grimsby Town',
    'Hull' => 'Hull City',
    'Ipswich' => 'Ipswich Town',
    'Leicester' => 'Leicester City',
    'Macclesfield' => 'Macclesfield Town', // Or FC
    'Mansfield' => 'Mansfield Town',
    'Norwich' => 'Norwich City',
    'QPR' => 'Queens Park Rangers',
    'Sheffield Utd' => 'Sheffield United',
    'Sheffield Wed' => 'Sheffield Wednesday',
    'Shrewsbury' => 'Shrewsbury Town',
    'Stoke' => 'Stoke City',
    'Swansea' => 'Swansea City',
    'West Brom' => 'West Bromwich Albion',
    'Weston-super-Mare' => 'Weston-super-Mare', // Might be hard
];

// Base URLs for GitHub raw content
$baseUrls = [
    // Try main branch
    "https://raw.githubusercontent.com/luukhopman/football-logos/main/logos/England/Championship",
    "https://raw.githubusercontent.com/luukhopman/football-logos/main/logos/England/League%20One",
    "https://raw.githubusercontent.com/luukhopman/football-logos/main/logos/England/League%20Two",
    "https://raw.githubusercontent.com/luukhopman/football-logos/main/logos/England/National%20League",
    "https://raw.githubusercontent.com/luukhopman/football-logos/main/logos/England/Premier%20League",

    // Try master branch
    "https://raw.githubusercontent.com/luukhopman/football-logos/master/logos/England/Championship",
    "https://raw.githubusercontent.com/luukhopman/football-logos/master/logos/England/League%20One",
    "https://raw.githubusercontent.com/luukhopman/football-logos/master/logos/England/League%20Two",
    "https://raw.githubusercontent.com/luukhopman/football-logos/master/logos/England/National%20League",
    "https://raw.githubusercontent.com/luukhopman/football-logos/master/logos/England/Premier%20League",

    // Alternative Repo (menziess) - flatter structure?
    "https://raw.githubusercontent.com/menziess/football_badges/master/logos/England",
];

foreach ($missingTeams as $shortName) {
    if ($shortName === 'Sheffield Wed')
        $slug = 'sheffield-wednesday';
    else if ($shortName === 'Exeter City')
        $slug = 'exeter'; // Match logic
    else
        $slug = Str::slug($shortName);

    $dest = "$outputDir/{$slug}.png";

    if (file_exists($dest)) {
        echo "Skipping $shortName (Already exists)\n";
        continue;
    }

    $searchNames = [];
    $searchNames[] = $shortName;
    if (isset($nameMap[$shortName])) {
        $searchNames[] = $nameMap[$shortName];
    }
    // Also try adding FC?
    $searchNames[] = $shortName . " FC";

    $downloaded = false;

    foreach ($searchNames as $searchName) {
        $encodedName = rawurlencode($searchName); // Handle spaces

        foreach ($baseUrls as $baseUrl) {
            $url = "$baseUrl/$encodedName.png";

            // Debug output
            // echo "Checking: $url\n"; 

            // Check headers to see if file exists (avoid downloading 404 page)
            $headers = @get_headers($url);
            if ($headers && strpos($headers[0], '200')) {
                echo "Found $searchName at .../" . basename(dirname($url)) . "/$encodedName.png\n";
                try {
                    copy($url, $dest);
                    $downloaded = true;
                    break 2; // Break both loops
                } catch (\Exception $e) {
                    echo "Failed copy: " . $e->getMessage() . "\n";
                }
            }
        }
    }

    if (!$downloaded) {
        echo "Could not find logo for $shortName\n";
    }
}
