<?php
define('DB_PATH', __DIR__ . '/../games.db');

function get_db() {
    static $db = null;
    if (!$db) {
        $db = new SQLite3(DB_PATH);
    }
    return $db;
}

function save_game(array $game) {
    $db = get_db();
    $stmt = $db->prepare('
        INSERT OR REPLACE INTO games (id, players, numPlayers, lastRequest, lastUpdated)
        VALUES (:id, :players, :numPlayers, :lastRequest, :lastUpdated)
    ');
    $stmt->bindValue(':id', $game['id'], SQLITE3_TEXT);
    $stmt->bindValue(':players', json_encode($game['players']), SQLITE3_TEXT);
    $stmt->bindValue(':numPlayers', $game['numPlayers'], SQLITE3_INTEGER);
    $stmt->bindValue(':lastRequest', isset($game['lastRequest']) ? json_encode($game['lastRequest']) : null, SQLITE3_TEXT);
    $stmt->bindValue(':lastUpdated', time(), SQLITE3_INTEGER);
    $stmt->execute();
}

function load_game($gameId) {
    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM games WHERE id = :id');
    $stmt->bindValue(':id', $gameId, SQLITE3_TEXT);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    if (!$row) return null;

    $row['players'] = json_decode($row['players'], true) ?? [];
    $row['lastRequest'] = isset($row['lastRequest']) ? json_decode($row['lastRequest'], true) : null;
    return $row;
}

// Dice roll helper
function roll_one_die($numSides, $allowExplosion = false) {
    $roll = rand(1, $numSides);
    $exploded = [$roll];
    if ($allowExplosion) {
        while ($roll == $numSides) {
            $roll = rand(1, $numSides);
            $exploded[] = $roll;
        }
    }
    return [
        'roll' => array_sum($exploded),
        'exploded' => $exploded
    ];
}

// Generate a 6-character alphanumeric game ID
function generate_game_id() {
    return substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
}
?>
