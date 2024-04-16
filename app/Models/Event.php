<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    public function Videos()
    {
        return $this->hasMany(Video::class, 'event_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function assists() {
        return $this->belongsToMany(User::class, 'assist_event');
    }

    public function event()
    {
        return $this->belongsToMany(Versus::class, 'event_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'assist_user_event', 'event_id' /* de subject */, 'user_id' /* de user */)->withPivot('puesto');
    }
}
