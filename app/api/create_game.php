<?php
header('Content-Type: application/json');
require_once __DIR__.'/helpers.php';

cleanup_old_games();

$creatorName = trim($_POST['creatorName'] ?? '');
$numPlayers = intval($_POST['numPlayers'] ?? 0);

if(!$creatorName || $numPlayers < 2){
    echo json_encode(["success"=>false,"message"=>"Invalid input"]);
    exit;
}

// Limit max games to mitigate DDOS
$existingGames = glob(GAMES_DIR."/*.json");
if(count($existingGames) >= 200){
    echo json_encode(["success"=>false,"message"=>"Server full. Try again later."]);
    exit;
}

$gameId = generate_game_id();

$game = [
    "id" => $gameId,
    "creator" => $creatorName,       // <-- explicitly store creator
    "players" => [$creatorName],     // add creator as first player
    "numPlayers" => $numPlayers,
    "lastRequest" => null,
    "lastUpdated" => time()
];

// Save game JSON
save_game($game);

// Return game info
echo json_encode([
    "success" => true,
    "gameId" => $gameId,
    "creatorName" => $creatorName
]);
