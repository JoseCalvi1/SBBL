<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'color', 'team_id', 'defense_bonus'];

    // Relación: Una zona pertenece a un Equipo (dueño)
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
