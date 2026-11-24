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
        'province_id',
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
        'max_players',          // Nuevo
        'is_paid',
        'entry_fee',
        'has_stadium_limit', // Nuevo
    ];

    // --- 2. CASTS (CONVERSIÓN DE TIPOS) ---
    // Esto es vital para que 'has_stadium_limit' funcione bien en los @if de la vista
    protected $casts = [
        'has_stadium_limit' => 'boolean', // Convierte 1/0 a true/false
        'is_paid' => 'boolean',
        'entry_fee' => 'float',
        'stadiums' => 'integer',
        'max_players' => 'integer',
        'date' => 'datetime',             // Permite usar ->format('d/m/Y') en la vista
    ];

    public function isQualifier(): bool
    {
        return $this->beys === 'clasificatorio';
    }

    public function isPaidEvent(): bool
    {
        return (bool) $this->is_paid || $this->isQualifier() || $this->beys === 'copapaypal' || ((float) $this->entry_fee > 0);
    }

    public function getEffectiveEntryFee(?int $userId = null): ?float
    {
        if ($this->isQualifier()) {
            if ($userId !== null) {
                $eval = app(\App\Services\Tournament\QualifierService::class)->evaluateForUser($this, $userId);
                return $eval['fee'];
            }
            return \App\Services\Tournament\QualifierService::FEE_FIRST_ATTEMPT;
        }

        if ($this->entry_fee !== null && (float) $this->entry_fee > 0) {
            return round((float) $this->entry_fee, 2);
        }

        // Retrocompatibilidad con eventos antiguos 'copapaypal' sin entry_fee configurado
        if ($this->beys === 'copapaypal' && !$this->is_paid) {
            return 2.00;
        }

        return null;
    }

    // --- 3. RELACIONES ---

    public function videos()
    {
        return $this->hasMany(Video::class, 'event_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
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

    public function judges()
    {
        return $this->belongsToMany(User::class, 'event_judge');
    }

    public function tournamentMatches()
    {
        return $this->hasMany(TournamentMatch::class);
    }
}
