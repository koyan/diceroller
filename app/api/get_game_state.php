<?php
header('Content-Type: application/json');
require_once __DIR__.'/helpers.php';

$gameId = trim($_GET['gameId'] ?? '');
if(!$gameId){
    echo json_encode(["success"=>false,"message"=>"Missing gameId"]);
    exit;
}

$gameFile = GAMES_DIR."/$gameId.json";
if(!file_exists($gameFile)){
    echo json_encode(["success"=>false,"message"=>"Game not found"]);
    exit;
}

// Load game
$game = json_decode(file_get_contents($gameFile), true);

// Ensure creator exists (backwards compatible)
$creator = $game['creator'] ?? ($game['players'][0] ?? null);

echo json_encode([
    "success" => true,
    "gameId" => $game['id'],
    "creator" => $creator,
    "players" => $game['players'],
    "numPlayers" => $game['numPlayers'],
    "lastRequest" => $game['lastRequest']
]);
