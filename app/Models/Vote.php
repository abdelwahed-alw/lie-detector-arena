<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $fillable = ['voter_id', 'statement_id'];

    public function voter() { 
        return $this->belongsTo(Player::class, 'voter_id'); 
    }

    public function statement() { 
        return $this->belongsTo(Statement::class); 
    }
}