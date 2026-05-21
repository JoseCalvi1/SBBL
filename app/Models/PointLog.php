<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointLog extends Model
{
    protected $table = 'points_log';

    // Esto permite que puedas escribir datos en ella
    protected $fillable = ['user_id', 'puntos', 'torneo_id'];
}
