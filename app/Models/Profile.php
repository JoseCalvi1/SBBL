<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function trophies()
    {
        return $this->belongsToMany(Trophy::class, 'profilestrophies', 'profiles_id' /* de profiles */, 'trophies_id' /* de trophies */)->withPivot('count');
    }

    public function challenges()
    {
        return $this->belongsToMany(Trophy::class, 'challenges_profiles', 'profiles_id' /* de profiles */, 'challenges_id' /* de challenges */)->withPivot('done');
    }
}
