<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamActiveBuff extends Model
{
    use HasFactory;

    protected $table = 'team_active_buffs';

    protected $fillable = [
        'team_id',
        'item_code',
        'multiplier',
        'expires_at'
    ];

    // Relación con el Equipo
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
