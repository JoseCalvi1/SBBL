<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $fillable = ['region_id', 'name'];

    // Una provincia pertenece a una región
    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
