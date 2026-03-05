<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Statement extends Model
{
    public function player() { 
    return $this->belongsTo(Player::class); 
}

public function votes() { 
    return $this->hasMany(Vote::class); 
}
}
