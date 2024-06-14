<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamsVersus extends Model
{
    use HasFactory;

    protected $table = 'teams_versus';

    public function versus_1()
    {
        return $this->belongsTo(Team::class, 'team_id_1');
    }

    public function versus_2()
    {
        return $this->belongsTo(Team::class, 'team_id_2');
    }
}
