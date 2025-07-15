<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventReview extends Model
{
    protected $fillable = [
        'event_id',
        'referee_id',
        'status',
        'comment',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function referee()
    {
        return $this->belongsTo(User::class, 'referee_id');
    }
}
