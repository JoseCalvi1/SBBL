<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TournamentMatch extends Model
{
    protected $guarded = [];

    protected $fillable = [
        'event_id', 'round_number', 'bracket_type', 'player1_id', 'player2_id', 
        'status', 'winner_id', 'player1_score', 'player2_score', 'match_number' // <-- Añadido
    ];

    // MAGIA: Se ejecuta justo antes de crear una partida nueva en la BD
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($match) {
            // 1. Identificamos si es una partida "Falsa" o "Bye"
            $isBye = false;

            // Condición A: Fantasmas del motor de Doble Eliminación
            if ($match->player1_score == 99 || $match->player2_score == 99) {
                $isBye = true;
            }
            // Condición B: Byes automáticos iniciales (Se crean ya terminados)
            elseif ($match->status === 'completed') {
                $isBye = true;
            }
            // Condición C: Byes del Sistema Suizo (Falta el jugador 2 y no va a llegar)
            elseif ($match->bracket_type === 'swiss' && is_null($match->player2_id)) {
                $isBye = true;
            }

            // 2. Solo asignamos número de MESA/PARTIDA si es un combate REAL
            if (!$isBye && empty($match->match_number)) {
                $maxMatch = static::where('event_id', $match->event_id)->max('match_number');
                $match->match_number = $maxMatch ? $maxMatch + 1 : 1;
            }
        });
    }

    public function event() {
        return $this->belongsTo(Event::class);
    }

    public function player1() {
        return $this->belongsTo(User::class, 'player1_id');
    }

    public function player2() {
        return $this->belongsTo(User::class, 'player2_id');
    }

    public function winner() {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function battles() {
        return $this->hasMany(MatchBattle::class);
    }
}