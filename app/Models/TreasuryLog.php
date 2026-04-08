<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreasuryLog extends Model
{
    protected $fillable = [
        'type', 'category', 'gross_amount', 'fee', 'net_amount',
        'description', 'reference_id', 'receipt_b64', 'user_id', 'event_id'
    ];

    // Para saber de quién es el pago o a quién se le dio el dinero
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Para saber si este movimiento pertenece a un torneo específico
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
