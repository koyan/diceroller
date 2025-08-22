<?php
// api/helpers.php

define('GAMES_DIR', __DIR__ . '/games');

// Ensure games directory exists
if(!file_exists(GAMES_DIR)){
    mkdir(GAMES_DIR, 0777, true);
}

// Load a game JSON
function load_game($gameId){
    $file = GAMES_DIR . "/$gameId.json";
    if(file_exists($file)){
        return json_decode(file_get_contents($file), true);
    }
    return null;
}

// Save a game JSON
function save_game($game){
    $file = GAMES_DIR . "/" . $game['id'] . ".json";
    file_put_contents($file, json_encode($game));
}

// Clean up old games (>4 hours)
function cleanup_old_games(){
    foreach(glob(GAMES_DIR."/*.json") as $file){
        if(time() - filemtime($file) > 4*3600){
            unlink($file);
        }
    }
}

// Generate a unique 8-char alphanumeric game ID
function generate_game_id(){
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    do {
        $id = '';
        for($i=0;$i<8;$i++){
            $id .= $chars[rand(0,strlen($chars)-1)];
        }
    } while(file_exists(GAMES_DIR."/$id.json"));
    return $id;
}
