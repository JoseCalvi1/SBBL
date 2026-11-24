<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreasuryLog extends Model
{
    protected $fillable = [
        'type',
        'source',
        'category',
        'gross_amount',
        'fee',
        'net_amount',
        'status',
        'refund_requested',
        'refunded_at',
        'description',
        'notes',
        'reference_id',
        'paypal_order_id',
        'paypal_capture_id',
        'receipt_b64',
        'user_id',
        'event_id',
        'raffle_id',
        'carrito_id',
        'created_by',
        'archived_at',
    ];

    protected $casts = [
        'gross_amount'     => 'float',
        'fee'              => 'float',
        'net_amount'       => 'float',
        'refund_requested' => 'boolean',
        'refunded_at'      => 'datetime',
        'archived_at'      => 'datetime',
    ];

    public function scopeActivePeriod($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchivedPeriod($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function raffle()
    {
        return $this->belongsTo(Raffle::class);
    }

    public function carrito()
    {
        return $this->belongsTo(Carrito::class, 'carrito_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
