<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeybladeCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'part_id',
        'weight',
        'color',
        'comment',
        'quantity',
        'type',
    ];

    // Opcional: relaciones según el tipo
    // App\Models\BeybladeCollection

        public function partBlade()
        {
            // Relación específica para Blade
            return $this->belongsTo(\App\Models\Blade::class, 'part_id');
        }

        public function partRatchet()
        {
            // Relación específica para Ratchet
            return $this->belongsTo(\App\Models\Ratchet::class, 'part_id');
        }

        public function partBit()
        {
            // Relación específica para Bit
            return $this->belongsTo(\App\Models\Bit::class, 'part_id');
        }

        public function partAssistBlade()
        {
            // Relación específica para Assist Blade
            return $this->belongsTo(\App\Models\AssistBlade::class, 'part_id');
        }


}
