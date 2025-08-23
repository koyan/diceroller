import os
import json
import random
from flask import Flask, send_from_directory
from flask_socketio import SocketIO, emit, join_room, leave_room

app = Flask(__name__, static_folder=".", static_url_path="")
socketio = SocketIO(app, cors_allowed_origins="*")

GAMES_FOLDER = "./games"
os.makedirs(GAMES_FOLDER, exist_ok=True)

games = {}  # in-memory cache of games

def save_game(game_id):
    path = os.path.join(GAMES_FOLDER, f"{game_id}.json")
    with open(path, "w") as f:
        json.dump(games[game_id], f)

def load_game(game_id):
    path = os.path.join(GAMES_FOLDER, f"{game_id}.json")
    if os.path.exists(path):
        with open(path, "r") as f:
            games[game_id] = json.load(f)

def roll_one_die(sides, allow_explosion=False):
    roll = random.randint(1, sides)
    exploded = [roll]
    if allow_explosion and roll == sides:
        extra = roll_one_die(sides, allow_explosion)
        exploded.extend(extra['exploded'])
        roll += sum(extra['exploded'])
    return {"total": roll, "exploded": exploded}

# ---------------------------
# WebSocket events
# ---------------------------

@socketio.on("create_game")
def handle_create_game(data):
    creator = data.get("creatorName")
    num_players = int(data.get("numPlayers", 2))
    game_id = "".join(random.choices("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789", k=8))

    games[game_id] = {
        "gameId": game_id,
        "players": [creator],
        "numPlayers": num_players,
        "lastRequest": None
    }
    save_game(game_id)
    join_room(game_id)
    emit("game_created", {"gameId": game_id, "creatorName": creator})
    emit("game_state", games[game_id], room=game_id)

@socketio.on("join_game")
def handle_join_game(data):
    game_id = data.get("gameId")
    player = data.get("playerName")
    if game_id not in games:
        load_game(game_id)
    if game_id not in games:
        emit("error", {"message": "Game not found"})
        return

    games[game_id]["players"].append(player)
    save_game(game_id)
    join_room(game_id)
    emit("game_joined", {"gameId": game_id, "playerName": player})
    emit("game_state", games[game_id], room=game_id)

@socketio.on("roll_dice")
def handle_roll_dice(data):
    game_id = data.get("gameId")
    player = data.get("playerName")
    num_dice = int(data.get("numDice", 1))
    num_sides = int(data.get("numSides", 6))
    allow_explosion = data.get("allowExplosion", False)
    keep = int(data.get("keep")) if data.get("keep") else None

    if game_id not in games:
        emit("error", {"message": "Game not found"})
        return

    # Roll dice
    results = [roll_one_die(num_sides, allow_explosion) for _ in range(num_dice)]

    # Determine kept dice indices
    kept_indices = []
    if keep and keep > 0:
        sorted_indices = sorted(range(len(results)), key=lambda i: results[i]["total"], reverse=True)
        kept_indices = sorted_indices[:keep]

    total_sum = sum(results[i]["total"] for i in kept_indices) if kept_indices else sum(r["total"] for r in results)

    last_request = {
        "player": player,
        "results": results,
        "sum": total_sum,
        "keep": keep,
        "kept_indices": kept_indices
    }

    games[game_id]["lastRequest"] = last_request
    save_game(game_id)

    emit("game_state", games[game_id], room=game_id)


# ---------------------------
# Serve the HTML
# ---------------------------

@app.route("/")
def index():
    return send_from_directory(".", "diceroller.html")

if __name__ == "__main__":
    socketio.run(app, host="0.0.0.0", port=5000)
