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
        return match($this->type) {
            'attack' => 'text-red-400',
            'conquest' => 'text-green-400',
            'defense' => 'text-blue-400',
            default => 'text-gray-400',
        };
    }

    // 2. Clase del Borde (El borde lateral izquierdo)
    public function getBorderClassAttribute()
    {
        return match($this->type) {
            'attack' => 'border-red-500/50',
            'conquest' => 'border-green-500/50',
            'defense' => 'border-blue-500/50',
            default => 'border-gray-700',
        };
    }

    // 3. Icono
    public function getIconAttribute()
    {
        return match($this->type) {
            'attack' => '⚠️',
            'conquest' => '🚩',
            'defense' => '🛡️',
            default => '📡',
        };
    }
}
