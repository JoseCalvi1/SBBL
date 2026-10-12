<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OverBlade extends Model
{
    use HasFactory;

    protected $table = 'over_blades';

    protected $fillable = [
        'nombre',
        'marca_hasbro',
        'marca_takara',
        'tipo',
        'color',
        'giro',
        'descripcion',
        'analisis',
        'imagen',
        'tarjeta',
        'sistema',
        'wave_hasbro',
        'fecha_takara',
    ];

    // Casting para manejar tipos automáticamente
    protected $casts = [
        'marca_hasbro' => 'boolean',
        'marca_takara' => 'boolean',
        'fecha_takara' => 'date',
    ];
}
