<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'raffle_id',
        'identifier',
        'paypal_order_id',
        'paypal_capture_id',
        'price_paid',
    ];

    protected $casts = [
        'price_paid' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function raffle()
    {
        return $this->belongsTo(Raffle::class);
    }
}
