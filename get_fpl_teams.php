<?php
$fplJson = file_get_contents('fpl_data.json');
$fplData = json_decode($fplJson, true);

if (!isset($fplData['teams'])) {
    die("No teams found in fpl_data.json\n");
}

$teams = [];
foreach ($fplData['teams'] as $team) {
    echo $team['name'] . "\n";
}
