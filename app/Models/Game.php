<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = ['name', 'status', 'current_player_id'];

    public function players() {
        return $this->hasMany(Player::class);
    }
}