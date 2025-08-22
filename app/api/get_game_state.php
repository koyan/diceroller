<?php
header('Content-Type: application/json');
require_once __DIR__ . '/helpers.php';

$gameId = trim($_GET['gameId'] ?? '');
if (!$gameId) {
    echo json_encode(["success" => false, "message" => "Missing game ID"]);
    exit;
}

$game = load_game($gameId);
if (!$game) {
    echo json_encode(["success" => false, "message" => "Game not found"]);
    exit;
}

echo json_encode([
    "success" => true,
    "gameId" => $game['id'],
    "creator" => $game['players'][0] ?? null,
    "players" => $game['players'],
    "lastRequest" => $game['lastRequest']
]);
?>
