<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tienda extends Model
{
    use HasFactory;

    // Campos que pueden ser asignados masivamente
    protected $fillable = [
        'nombre',
        'descripcion',
        'fotos',
        'enlaces',
    ];

    // Casts para que se manejen automáticamente como array
    protected $casts = [
        'fotos' => 'array',
        'enlaces' => 'array',
    ];

}
