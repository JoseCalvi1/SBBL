<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSetting extends Model
{
    protected $fillable = [
        'pickup_enabled',
        'pickup_label',
        'pickup_instructions',
        'shipping_enabled',
        'shipping_cost',
        'free_shipping_min',
    ];

    protected $casts = [
        'pickup_enabled'   => 'boolean',
        'shipping_enabled' => 'boolean',
        'shipping_cost'    => 'float',
        'free_shipping_min'=> 'float',
    ];

    public static function current(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'pickup_enabled'      => true,
                'pickup_label'        => 'Recogida Presencial en Evento Oficial SBBL',
                'pickup_instructions' => 'Tu pedido quedará registrado a tu nombre y correo. Preséntalo en la mesa de organización del evento para recoger tu merch.',
                'shipping_enabled'    => false,
                'shipping_cost'       => 4.95,
                'free_shipping_min'   => null,
            ]
        );
    }

    public function calculateShipping(float $subtotal, string $tipoEntrega): float
    {
        if ($tipoEntrega !== 'envio' || !$this->shipping_enabled) {
            return 0.00;
        }

        if ($this->free_shipping_min !== null && $this->free_shipping_min > 0 && $subtotal >= $this->free_shipping_min) {
            return 0.00;
        }

        return round((float) $this->shipping_cost, 2);
    }
}
