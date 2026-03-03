<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WordleWord extends Model
{
    use HasFactory;

    // Añade estas líneas:
    protected $fillable = [
        'word',           // Permite guardar la palabra
        'scheduled_for',  // Permite guardar la fecha
    ];
}
