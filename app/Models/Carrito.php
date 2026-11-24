<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'nombre',
        'email',
        'telefono',
        'direccion',
        'tipo_entrega',
        'enviado',
        'referencia',
        'paypal_order_id',
        'paypal_capture_id',
        'metodo_pago',
        'estado_pago',
        'estado_envio',
        'total',
        'gastos_envio',
        'total_lagartos',
        'solicitado',
    ];

    protected $casts = [
        'enviado'      => 'boolean',
        'solicitado'   => 'boolean',
        'total'        => 'float',
        'gastos_envio' => 'float',
    ];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'carrito_productos')
                    ->withPivot('cantidad', 'precio_unitario', 'atributos', 'hash')
                    ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function treasuryLog()
    {
        return $this->hasOne(TreasuryLog::class, 'carrito_id');
    }
}
