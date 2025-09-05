<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventJudgeReview extends Model
{
    protected $fillable = [
        'event_id',
        'judge_id',
        'final_status',
        'comment',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function judge()
    {
        return $this->belongsTo(User::class, 'judge_id');
    }
}
