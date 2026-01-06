<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'region',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            $user->profile()->create();
        });
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'assist_event');
    }

    public function versus_1()
    {
        return $this->belongsToMany(Versus::class, 'user_id_1');
    }

    public function versus_2()
    {
        return $this->belongsToMany(Versus::class, 'user_id_2');
    }

    public function assistsEvents()
    {
        return $this->belongsToMany(Event::class, 'assist_user_event', 'user_id' /* de user */, 'event_id' /* de subject */)->withPivot('puesto');
    }

    public function equipo()
    {
        return $this->belongsTo(Team::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_user')->withPivot('is_captain')->withTimestamps();
    }

    // HELPER ÚTIL PARA EL JUEGO:
    // Como un usuario suele competir solo por UN equipo principal a la vez,
    // creamos este "acceso directo" para no volvernos locos en la vista.
    public function getActiveTeamAttribute()
    {
        // Devuelve el primer equipo al que pertenece (o null si no tiene)
        return $this->teams->first();
    }

    public function activeTeam()
    {
        // Cambia 'active_team_id' por 'team_id' si así se llama tu columna en la base de datos
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function trophies()
    {
        return $this->belongsToMany(Trophy::class, 'profilestrophies', 'profiles_id', 'trophies_id')
                    ->withPivot('count'); // Incluye la columna count
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
                    ->where('status', 'active')
                    ->where('ended_at', '>=', now()); // solo activas
    }

    public function hasPlan($slug)
    {
        return $this->activeSubscription && $this->activeSubscription->plan->slug === $slug;
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
