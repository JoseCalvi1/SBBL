<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trophy extends Model
{
    use HasFactory;
    protected $table = 'trophies';
    protected $fillable = ['name', 'season'];

    public function profiles()
    {
        return $this->belongsToMany(Profile::class, 'profilestrophies', 'trophies_id' /* de trophies */, 'profiles_id' /* de profiles */)->withPivot('count');
    }
}
