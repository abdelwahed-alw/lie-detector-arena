<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Statement;
use App\Services\LieDetectorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StatementController extends Controller
{
    /**
     * Store player statements and get Claude 4.5 verdict
     */
    public function store(Request $request, LieDetectorService $detector)
    {
        // Validate input
        $data = $request->validate([
            'player_id'            => 'required|exists:players,id',
            'statements'           => 'required|array|size:3',
            'statements.*.content' => 'required|string|max:200',
            'lie_index'            => 'required|integer|between:0,2',
        ]);

        $player = Player::findOrFail($data['player_id']);

        // Delete any previous statements from this player
        $player->statements()->delete();

        // Save all 3 statements to database
        foreach ($data['statements'] as $i => $stmt) {
            Statement::create([
                'player_id' => $player->id,
                'content'   => $stmt['content'],
                'is_lie'    => ($i === (int)$data['lie_index']),
            ]);
        }

        // Refresh player to load new statements
        $player->load('statements');

        // Call Claude 4.5 and store verdict
        try {
            Log::info('Calling Claude 4.5 for player: ' . $player->id);
            
            $verdict = $detector->analyze($data['statements']);
            
            Log::info('Claude 4.5 verdict received', $verdict);
            
            // Update each statement with AI verdict
            foreach ($player->statements as $i => $statement) {
                $statement->update([
                    'ai_verdict' => [
                        'score'     => $verdict['scores'][$i] ?? 33,
                        'ai_guess'  => (($i + 1) === ($verdict['lie_index'] ?? 3)),
                        'reasoning' => $verdict['reasoning'] ?? 'Claude 4.5 is thinking...',
                    ]
                ]);
            }

            // Update game status to playing
            $player->game->update(['status' => 'playing']);

            $message = 'Statements submitted! Claude 4.5 has delivered its verdict!';

        } catch (\Exception $e) {
            Log::error('Claude 4.5 failed: ' . $e->getMessage());
            
            // Graceful fallback - game continues without AI verdict
            foreach ($player->statements as $i => $statement) {
                $statement->update([
                    'ai_verdict' => [
                        'score'     => 33,
                        'ai_guess'  => ($i === 2),
                        'reasoning' => 'Claude 4.5 is temporarily unavailable. Default guess applied.',
                    ]
                ]);
            }
            
            $message = 'Statements submitted! (AI offline - using default verdict)';
        }

        return redirect()->route('games.results', $player->game_id)
                         ->with('success', $message);
    }
}