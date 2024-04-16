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
}
