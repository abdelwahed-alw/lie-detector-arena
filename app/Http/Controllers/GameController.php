<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /**
     * Show welcome page
     */
    public function index()
    {
        return view('welcome');
    }
    
    /**
     * Create a new game
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);
        
        $game = Game::create([
            'name' => $request->name
        ]);
        
        return redirect()->route('games.show', $game);
    }
    
    /**
     * Show specific game page (lobby)
     */
    public function show(Game $game)
    {
        return view('games.show', compact('game'));
    }
    
    /**
     * Join an existing game
     */
    public function join(Request $request, Game $game)
    {
        $request->validate([
            'nickname' => 'required|string|max:255'
        ]);
        
        $player = Player::create([
            'game_id' => $game->id,
            'nickname' => $request->nickname
        ]);
        
        // Store player ID in session
        session(['player_id' => $player->id]);
        
        return redirect()->route('games.show', $game)
                         ->with('success', 'Welcome to the game!');
    }
    
    /**
     * Show game results and leaderboard
     */
    public function results(Game $game)
    {
        $leaderboard = $game->players()->orderByDesc('score')->get();
        return view('games.results', compact('game', 'leaderboard'));
    }
    
    /**
     * Calculate scores for a round
     */
    public function calculateRoundScores(Player $currentPlayer): void
    {
        // Find the lie statement
        $lieStatement = $currentPlayer->statements
            ->firstWhere('is_lie', true);

        // Award 150 points to each correct voter
        foreach ($lieStatement->votes as $vote) {
            $vote->voter->increment('score', 150);
        }

        // Award 100 points to author for each wrong vote (successful bluff)
        $wrongVotes = $currentPlayer->statements
            ->where('is_lie', false)
            ->flatMap->votes
            ->count();

        $currentPlayer->increment('score', $wrongVotes * 100);
    }
}