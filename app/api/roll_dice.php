<?php
header('Content-Type: application/json');
require_once __DIR__.'/helpers.php';

$gameId = trim($_POST['gameId'] ?? '');
$playerName = trim($_POST['playerName'] ?? '');
$numDice = intval($_POST['numDice'] ?? 0);
$numSides = intval($_POST['numSides'] ?? 0);
$allowExplosion = isset($_POST['allowExplosion']) && ($_POST['allowExplosion'] == 1 || $_POST['allowExplosion'] === true);

if(!$gameId || !$playerName || $numDice < 1 || $numSides < 2){
    echo json_encode(["success"=>false,"message"=>"Invalid input"]);
    exit;
}

$gameFile = GAMES_DIR."/$gameId.json";
if(!file_exists($gameFile)){
    echo json_encode(["success"=>false,"message"=>"Game not found"]);
    exit;
}

// Load game
$game = json_decode(file_get_contents($gameFile), true);

// Optional: check if player exists in game
if(!in_array($playerName, $game['players'])){
    echo json_encode(["success"=>false,"message"=>"Player not in game"]);
    exit;
}

// Roll dice with optional explosion
$results = [];

for ($i=0; $i<$numDice; $i++) {
    $roll = rand(1, $numSides);
    $extras = [];

    if ($allowExplosion) {
        while ($roll == $numSides) {
            $extra = rand(1, $numSides);
            $extras[] = $extra;
            $roll += $extra;
        }
    }

    $results[] = [
        "base" => $roll - array_sum($extras),
        "extras" => $extras,
        "total" => $roll
    ];
}

$sum = array_sum(array_map(fn($r) => $r['total'], $results));

$game['lastRequest'] = [
    "player" => $playerName,
    "numDice" => $numDice,
    "numSides" => $numSides,
    "allowExplosion" => $allowExplosion,
    "results" => $results,
    "sum" => $sum
];

$game['lastUpdated'] = time();

// Save back to JSON
save_game($game);

echo json_encode([
    "success" => true,
    "results" => $results,
    "sum" => $sum
]);
