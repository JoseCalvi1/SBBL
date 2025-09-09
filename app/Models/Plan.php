<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'monthly_price',
        'annual_price',
        'paypal_plan_monthly_id',
        'paypal_plan_annual_id',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
