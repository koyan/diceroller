<?php
header('Content-Type: application/json');
require_once __DIR__.'/helpers.php';

$playerName = trim($_POST['playerName'] ?? '');
$gameId = trim($_POST['gameId'] ?? '');

if(!$playerName || !$gameId){
    echo json_encode(["success"=>false,"message"=>"Invalid input"]);
    exit;
}

$game = load_game($gameId);
if(!$game){
    echo json_encode(["success"=>false,"message"=>"Game not found"]);
    exit;
}

// Prevent duplicate player names
if (in_array($playerName, $game['players'])) {
    echo json_encode(["success"=>false,"message"=>"Player name already taken"]);
    exit;
}

// Prevent exceeding max players
if (count($game['players']) >= $game['numPlayers']) {
    echo json_encode(["success"=>false,"message"=>"Game is full. Cannot join."]);
    exit;
}

// Add player
$game['players'][] = $playerName;
$game['lastUpdated'] = time();
save_game($game);

echo json_encode(["success"=>true,"gameId"=>$gameId,"playerName"=>$playerName]);
