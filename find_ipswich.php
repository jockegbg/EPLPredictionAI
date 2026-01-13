<?php
$fplJson = file_get_contents('fpl_data.json');
$fplData = json_decode($fplJson, true);
foreach ($fplData['teams'] as $team) {
    if (str_contains($team['name'], 'Ipswich')) {
        echo "Found Ipswich: " . $team['name'] . " - Code: " . $team['code'] . "\n";
    }
}
