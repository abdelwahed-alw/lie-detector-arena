<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    public function voter() { 
    return $this->belongsTo(Player::class, 'voter_id'); 
}

public function statement() { 
    return $this->belongsTo(Statement::class); 
}
}
