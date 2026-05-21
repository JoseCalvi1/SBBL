<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FantasyRoster extends Model
{
    protected $guarded = [];

    public function player()
    {
        return $this->belongsTo(FantasyRealPlayer::class, 'fantasy_real_player_id');
    }
}
