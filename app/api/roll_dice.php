<?php
header('Content-Type: application/json');
require_once __DIR__ . '/helpers.php';

$gameId = trim($_POST['gameId'] ?? '');
$playerName = trim($_POST['playerName'] ?? '');
$numDice = intval($_POST['numDice'] ?? 0);
$numSides = intval($_POST['numSides'] ?? 6);
$allowExplosion = isset($_POST['allowExplosion']) && $_POST['allowExplosion'] ? true : false;

if (!$gameId || !$playerName || $numDice < 1 || $numSides < 2) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

$game = load_game($gameId);
if (!$game) {
    echo json_encode(["success" => false, "message" => "Game not found"]);
    exit;
}

// Roll the dice
$results = [];
$sum = 0;
for ($i = 0; $i < $numDice; $i++) {
    $r = roll_one_die($numSides, $allowExplosion);
    $results[] = ['total' => $r['roll'], 'exploded' => $r['exploded']];
    $sum += $r['roll'];
}

// Save last roll
$game['lastRequest'] = [
    'player' => $playerName,
    'numDice' => $numDice,
    'numSides' => $numSides,
    'allowExplosion' => $allowExplosion,
    'results' => $results,
    'sum' => $sum
];

save_game($game);

echo json_encode(["success" => true, "results" => $results, "sum" => $sum]);
?>
