<?php
$dbFile = __DIR__ . "/games.db";

try {
    $db = new SQLite3($dbFile);

    // Create the table if it doesn't exist
    $db->exec("
        CREATE TABLE games (
            id TEXT PRIMARY KEY,
            players TEXT,
            numPlayers INTEGER,
            lastRequest TEXT,
            lastUpdated INTEGER
        );

    ");

    // Confirm table creation
    $result = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='games'");
    if ($result === 'games') {
        echo "✅ Database and 'games' table created successfully at $dbFile\n";
    } else {
        echo "❌ Failed to create 'games' table.\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
