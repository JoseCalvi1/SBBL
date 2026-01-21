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

    public function getAvatarUrlAttribute()
    {
        if ($this->imagen) {
            // Si la ruta en la BD YA empieza con "upload-profiles", no lo añadimos otra vez
            if (strpos($this->imagen, 'upload-profiles/') === 0) {
                return asset('storage/' . $this->imagen);
            }

            // Si no lo tiene (ej: "Base/img.webp"), lo añadimos
            return asset('storage/upload-profiles/' . $this->imagen);
        }

        // Imagen por defecto
        return asset('storage/upload-profiles/Base/DranDagger.webp');
    }

    public function getMarcoUrlAttribute()
    {
        if ($this->marco) {
            // Detectar si ya tiene la carpeta duplicada
            if (strpos($this->marco, 'upload-profiles/') === 0) {
                return asset('storage/' . $this->marco);
            }

            return asset('storage/upload-profiles/' . $this->marco);
        }

        return asset('storage/upload-profiles/Marcos/BaseBlue.png');
    }

    public function getFondoUrlAttribute()
    {
        if ($this->fondo) {
            if (strpos($this->fondo, 'upload-profiles/') === 0) {
                return asset('storage/' . $this->fondo);
            }

            return asset('storage/upload-profiles/' . $this->fondo);
        }

        return asset('storage/upload-profiles/SBBLFondo.png');
    }

}
