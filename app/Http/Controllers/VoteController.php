<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use App\Models\Statement;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    /**
     * Store a player's vote
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'voter_id'     => 'required|exists:players,id',
            'statement_id' => 'required|exists:statements,id',
        ]);

        $statement = Statement::findOrFail($data['statement_id']);
        $player = $statement->player;

        // Check if player already voted on this player's statements
        $alreadyVoted = Vote::where('voter_id', $data['voter_id'])
            ->whereIn('statement_id', 
                Statement::where('player_id', $player->id)->pluck('id')
            )->exists();

        if ($alreadyVoted) {
            return back()->with('error', 'You already voted on this round!');
        }

        // Create vote
        Vote::create($data);

        // Check if all players have voted
        $totalPlayers = $player->game->players->count();
        $totalVotes = Vote::whereIn('statement_id', 
            Statement::where('player_id', $player->id)->pluck('id')
        )->count();

        // If all players voted (excluding the statement author), calculate scores
        if ($totalVotes >= $totalPlayers - 1) {
            $this->calculateScores($player);
            $player->game->update(['status' => 'finished']);
        }

        return redirect()->route('games.results', $statement->player->game_id)
                         ->with('success', 'Vote submitted!');
    }

    /**
     * Calculate scores after voting is complete
     */
    private function calculateScores($currentPlayer)
    {
        $lieStatement = $currentPlayer->statements
            ->firstWhere('is_lie', true);

        if (!$lieStatement) return;

        // +150 for each correct guess
        foreach ($lieStatement->votes as $vote) {
            $vote->voter->increment('score', 150);
        }

        // +100 for author for each wrong vote (successful bluff)
        $wrongVotes = $currentPlayer->statements
            ->where('is_lie', false)
            ->flatMap->votes
            ->count();

        $currentPlayer->increment('score', $wrongVotes * 100);
    }
}