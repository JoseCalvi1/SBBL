<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $fillable = [
        'category_id', 'province_id', 'user_id', 'name', 'brand', 'status', 'notes'
    ];

    public function category() {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }

    public function province() {
        return $this->belongsTo(Province::class);
    }

    public function custodian() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
