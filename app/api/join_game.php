<?php
header('Content-Type: application/json');
require_once __DIR__ . '/helpers.php';

$playerName = trim($_POST['playerName'] ?? '');
$gameId = trim($_POST['gameId'] ?? '');

if (!$playerName || !$gameId) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

$game = load_game($gameId);
if (!$game) {
    echo json_encode(["success" => false, "message" => "Game not found"]);
    exit;
}

// Limit players to creator's declared number
if (!in_array($playerName, $game['players'])) {
    if (count($game['players']) >= $game['numPlayers']) {
        echo json_encode(["success" => false, "message" => "Game is full"]);
        exit;
    }
    $game['players'][] = $playerName;
    save_game($game);
}

echo json_encode(["success" => true, "gameId" => $gameId, "playerName" => $playerName]);
?>
