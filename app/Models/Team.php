<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'captain_id', 'image', 'logo', 'status', 'pinned_message', 'pinned_message_updated_at'];

    public function miembros()
    {
        return $this->hasMany(User::class);
    }

    public function zones()
    {
        // Un equipo puede dominar muchas zonas
        return $this->hasMany(Zone::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'team_user')->withPivot('is_captain')->withPivot('user_id')->withTimestamps();
    }

    public function is_captain()
    {
        return $this->members()->wherePivot('is_captain', true)->first();
    }

    public function captain()
    {
        return $this->belongsTo(User::class, 'captain_id');
    }

    public function versus_1()
    {
        return $this->belongsToMany(TeamsVersus::class, 'team_id_1');
    }

    public function versus_2()
    {
        return $this->belongsToMany(TeamsVersus::class, 'team_id_2');
    }
    // RELACIÓN: Un equipo tiene muchos mensajes de chat
    public function messages()
    {
        // Asumiendo que tu modelo de mensajes se llama TeamChatMessage
        // Si no tienes modelo, créalo o ajusta el nombre
        return $this->hasMany(TeamChatMessage::class);
    }

    public function inventory()
    {
        return $this->hasMany(\App\Models\TeamInventory::class);
    }

    public function activeBuffs()
    {
        return $this->hasMany(TeamActiveBuff::class);
    }

}
