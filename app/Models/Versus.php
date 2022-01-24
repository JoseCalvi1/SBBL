<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Versus extends Model
{
    use HasFactory;

    protected $table = 'versus';

    public function versus_1()
    {
        return $this->belongsTo(User::class, 'user_id_1');
    }

    public function versus_2()
    {
        return $this->belongsTo(User::class, 'user_id_2');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
