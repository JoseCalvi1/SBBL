<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'captain_id', 'image', 'logo', 'status'];

    public function miembros()
    {
        return $this->hasMany(User::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'team_user')->withPivot('is_captain')->withPivot('user_id')->withTimestamps();
    }

    public function captain()
    {
        return $this->members()->wherePivot('is_captain', true)->first();
    }

    public function versus_1()
    {
        return $this->belongsToMany(TeamsVersus::class, 'team_id_1');
    }

    public function versus_2()
    {
        return $this->belongsToMany(TeamsVersus::class, 'team_id_2');
    }

}
