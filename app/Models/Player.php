<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $fillable = ['game_id', 'nickname', 'score'];

    public function game() { 
        return $this->belongsTo(Game::class); 
    }

    public function statements() { 
        return $this->hasMany(Statement::class); 
    }

    public function votes() { 
        return $this->hasMany(Vote::class, 'voter_id'); 
    }
}