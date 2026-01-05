<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamChatMessage extends Model
{
    use HasFactory;

    // ESTO ES VITAL: Permite guardar estos datos
    protected $fillable = ['team_id', 'user_id', 'message'];

    // ESTO ES VITAL: Permite usar with('user') en el controlador
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
