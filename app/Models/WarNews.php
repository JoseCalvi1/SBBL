<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarNews extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'type'];

    // 1. Color del Texto (Título)
    public function getColorClassAttribute()
    {
        $colors = [
            'attack'   => 'text-red-400',
            'conquest' => 'text-green-400',
            'defense'  => 'text-blue-400',
        ];

        // Devuelve el color si existe, si no devuelve el gris por defecto
        return $colors[$this->type] ?? 'text-gray-400';
    }

    // 2. Clase del Borde
    public function getBorderClassAttribute()
    {
        $borders = [
            'attack'   => 'border-red-500/50',
            'conquest' => 'border-green-500/50',
            'defense'  => 'border-blue-500/50',
        ];

        return $borders[$this->type] ?? 'border-gray-700';
    }

    // 3. Icono
    public function getIconAttribute()
    {
        $icons = [
            'attack'   => '⚠️',
            'conquest' => '🚩',
            'defense'  => '🛡️',
        ];

        return $icons[$this->type] ?? '📡';
    }
}
