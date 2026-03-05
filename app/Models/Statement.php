<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Statement extends Model
{
    protected $fillable = ['player_id', 'content', 'is_lie', 'ai_verdict'];
    
    protected $casts = [
        'is_lie' => 'boolean',
        'ai_verdict' => 'array'
    ];

    public function player() { 
        return $this->belongsTo(Player::class); 
    }

    public function votes() { 
        return $this->hasMany(Vote::class); 
    }
}