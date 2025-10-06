<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    protected $fillable = ['session_id','nombre','email','direccion','enviado'];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'carrito_productos')
                    ->withPivot('cantidad', 'precio_unitario', 'atributos', 'hash')
                    ->withTimestamps();
    }
}
