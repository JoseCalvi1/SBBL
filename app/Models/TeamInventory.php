<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamInventory extends Model
{
    use HasFactory;

    // Importante: especificar la tabla si Laravel se lía con el plural
    protected $table = 'team_inventory';

    protected $fillable = [
        'team_id',
        'item_id',
        'quantity'
    ];

    // Relación: Un registro de inventario pertenece a un Item
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // Relación: Pertenece a un equipo
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
