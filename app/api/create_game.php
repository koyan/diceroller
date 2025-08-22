<?php
header('Content-Type: application/json');
require_once __DIR__ . '/helpers.php';

$creatorName = trim($_POST['creatorName'] ?? '');
$numPlayers = intval($_POST['numPlayers'] ?? 0);

if (!$creatorName || $numPlayers < 2) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

$gameId = generate_game_id();

$game = [
    "id" => $gameId,
    "players" => [$creatorName],
    "numPlayers" => $numPlayers,
    "lastRequest" => null
];

save_game($game);

echo json_encode(["success" => true, "gameId" => $gameId, "creatorName" => $creatorName]);
?>
