<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    use HasFactory;
    protected $table = 'challenges';

    public function profiles()
    {
        return $this->belongsToMany(Profile::class, 'challenges_profiles', 'challenges_id' /* de challenges */, 'profiles_id' /* de profiles */)->withPivot('done');
    }

}
