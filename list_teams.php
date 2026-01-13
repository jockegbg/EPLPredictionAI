<?php
$fplJson = file_get_contents('fpl_data.json');
$fplData = json_decode($fplJson, true);
$names = [];
foreach ($fplData['teams'] as $team) {
    echo $team['name'] . "\n";
}
