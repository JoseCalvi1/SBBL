<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FantasyItem extends Model
{
    use HasFactory;

    protected $table = 'fantasy_items';

    protected $fillable = [
        'fantasy_manager_id',
        'name',
        'slot_type',
        'rarity',
        'rarity_color',
        'stat_multiplier',
        'fantasy_roster_id'
    ];

    // Relación con el mánager dueño del objeto
    public function manager()
    {
        return $this->belongsTo(FantasyManager::class, 'fantasy_manager_id');
    }

    // Relación con el jugador de la plantilla que lo lleva puesto
    public function roster()
    {
        return $this->belongsTo(FantasyRoster::class, 'fantasy_roster_id');
    }
}
