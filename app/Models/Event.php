<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    // --- 1. CAMPOS QUE SE PUEDEN GUARDAR ---
    protected $fillable = [
        'name',
        'mode',
        'city',
        'location',
        'region_id',
        'date',
        'time',
        'imagen',
        'beys',
        'image_mod',
        'deck',
        'configuration',
        'note',
        'status',
        'created_by',
        'iframe',
        'challonge',
        'stadiums',          // Nuevo
        'has_stadium_limit', // Nuevo
    ];

    // --- 2. CASTS (CONVERSIÓN DE TIPOS) ---
    // Esto es vital para que 'has_stadium_limit' funcione bien en los @if de la vista
    protected $casts = [
        'has_stadium_limit' => 'boolean', // Convierte 1/0 a true/false
        'stadiums' => 'integer',
        'date' => 'datetime',             // Permite usar ->format('d/m/Y') en la vista
    ];

    // --- 3. RELACIONES ---

    public function videos()
    {
        return $this->hasMany(Video::class, 'event_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'assist_user_event', 'event_id', 'user_id')
                    ->withPivot('puesto')
                    ->withTimestamps();
    }

    public function results()
    {
        return $this->hasMany(TournamentResult::class, 'event_id');
    }

    public function reviews()
    {
        return $this->hasMany(EventReview::class);
    }

    public function judgeReview()
    {
        return $this->hasOne(EventJudgeReview::class);
    }

    public function assists() {
        return $this->belongsToMany(User::class, 'assist_event');
    }

    public function event()
    {
        return $this->belongsToMany(Versus::class, 'event_id');
    }
}
