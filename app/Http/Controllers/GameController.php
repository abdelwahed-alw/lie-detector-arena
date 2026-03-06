<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'nickname' => 'required|string|max:255',
        ]);

        $game = Game::create(['name' => $request->name]);

        $player = Player::create([
            'game_id'  => $game->id,
            'nickname' => $request->nickname,
        ]);

        session(['player_id' => $player->id]);

        return redirect()->route('games.show', $game)
                         ->with('success', 'Game created! Share code: ' . $game->id);
    }

    public function show(Game $game)
    {
        $game->load('players');
        return view('games.show', compact('game'));
    }

    public function join(Request $request, Game $game)
    {
        $request->validate(['nickname' => 'required|string|max:255']);

        $player = Player::create([
            'game_id'  => $game->id,
            'nickname' => $request->nickname,
        ]);

        session(['player_id' => $player->id]);

        return redirect()->route('games.show', $game)
                         ->with('success', 'Welcome to the game!');
    }

    public function joinByCode(Request $request)
    {
        $request->validate([
            'game_id'  => 'required|exists:games,id',
            'nickname' => 'required|string|max:255',
        ]);

        $game = Game::findOrFail($request->game_id);

        $player = Player::create([
            'game_id'  => $game->id,
            'nickname' => $request->nickname,
        ]);

        session(['player_id' => $player->id]);

        return redirect()->route('games.show', $game)
                         ->with('success', 'Welcome to the game!');
    }

   public function results(Game $game)
{
    $leaderboard   = $game->players()->orderByDesc('score')->get();
    $currentPlayer = Player::find(session('player_id'));

    // Pass $currentPlayer as $player to the view
    return view('games.results', [
        'game'        => $game,
        'leaderboard' => $leaderboard,
        'player'      => $currentPlayer,  // ← هذا كان ناقص
    ]);
}
}