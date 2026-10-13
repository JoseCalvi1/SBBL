<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchBattle extends Model
{
    protected $guarded = [];

    public function tournamentMatch() {
        return $this->belongsTo(TournamentMatch::class);
    }

    public function player1Combo() {
        return $this->belongsTo(TournamentResult::class, 'player1_combo_id');
    }

    public function player2Combo() {
        return $this->belongsTo(TournamentResult::class, 'player2_combo_id');
    }

    public function winner() {
        return $this->belongsTo(User::class, 'winner_id');
    }
}