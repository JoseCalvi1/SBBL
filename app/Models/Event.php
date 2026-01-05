<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    // --- 1. OBLIGATORIO: CAMPOS QUE SE PUEDEN GUARDAR ---
    // Sin esto, Event::create() fallará.
    protected $fillable = [
        'name',
        'mode',
        'city',
        'location',
        'region_id',
        'date',
        'time',
        'imagen',
        'beys',         // Tipo de evento (ranking, quedada, etc)
        'image_mod',    // Imagen personalizada en base64
        'deck',
        'configuration',
        'note',
        'status',
        'created_by',
        'iframe',
        'challonge',
    ];

    // --- 2. RELACIONES ---

    // Cambiado 'Videos' a 'videos' (estándar Laravel: camelCase)
    public function videos()
    {
        return $this->hasMany(Video::class, 'event_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    // Relación principal de participantes
    public function users()
    {
        return $this->belongsToMany(User::class, 'assist_user_event', 'event_id', 'user_id')
                    ->withPivot('puesto')
                    ->withTimestamps(); // Añadido timestamps por si la tabla pivote los usa
    }

    // Esta relación es necesaria si usas $event->results en el controlador
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

    // He mantenido esta por si la usas en otro lado, pero 'users' parece ser la principal
    public function assists() {
        return $this->belongsToMany(User::class, 'assist_event');
    }

    // He mantenido esta, aunque el nombre 'event' dentro del modelo Event es confuso
    public function event()
    {
        return $this->belongsToMany(Versus::class, 'event_id');
    }
}
