<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = ['nombre','seccion','descripcion','precio','stock','fotos','atributos'];

    protected $casts = [
        'atributos' => 'array',
    ];

    public function carritos()
    {
        return $this->belongsToMany(Carrito::class, 'carrito_productos')
                    ->withPivot('cantidad', 'precio_unitario', 'atributos', 'hash')
                    ->withTimestamps();
    }

}


