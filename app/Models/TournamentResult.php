<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TournamentResult extends Model
{
    use HasFactory;

    // Definir la tabla asociada al modelo (opcional si la tabla sigue la convención de nombres de Laravel)
    protected $table = 'tournament_results';

    // Permitir la asignación masiva para estos campos
    protected $fillable = [
        'user_id',
        'event_id',
        'versus_id',
        'blade',
        'ratchet',
        'bit',
        'victorias',
        'derrotas',
        'puntos_ganados',
        'puntos_perdidos',
    ];

    /**
     * Relación con el modelo User (Un resultado pertenece a un usuario)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el modelo Event (Un resultado pertenece a un evento)
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
