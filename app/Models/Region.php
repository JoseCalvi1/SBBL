<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    public function profile()
    {
        return $this->hasMany(Profile::class);
    }

    public function event()
    {
        return $this->hasMany(Event::class);
    }
}
