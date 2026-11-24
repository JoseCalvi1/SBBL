<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Raffle extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'prizes_info',
        'banner_b64',
        'ticket_price',
        'max_tickets',
        'max_per_user',
        'ticket_prefix',
        'winners_count',
        'is_active',
        'ends_at',
        'drawn_at',
        'winners_json',
        'created_by',
    ];

    protected $casts = [
        'ticket_price' => 'float',
        'max_tickets'  => 'integer',
        'max_per_user' => 'integer',
        'winners_count'=> 'integer',
        'is_active'    => 'boolean',
        'ends_at'      => 'datetime',
        'drawn_at'     => 'datetime',
        'winners_json' => 'array',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function treasuryLogs()
    {
        return $this->hasMany(TreasuryLog::class, 'raffle_id');
    }

    public function isOpenForPurchase(): bool
    {
        if (!$this->is_active) {
            return false;
        }
        if ($this->drawn_at !== null) {
            return false;
        }
        if ($this->ends_at !== null && $this->ends_at->isPast()) {
            return false;
        }
        if ($this->max_tickets !== null && $this->tickets()->count() >= $this->max_tickets) {
            return false;
        }
        return true;
    }
}
