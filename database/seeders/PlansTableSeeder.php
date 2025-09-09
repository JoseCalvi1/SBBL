<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlansTableSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'bronce',
                'name' => 'Nivel 1',
                'monthly_price' => 2.00,
                'annual_price' => 22.00,
                'paypal_plan_monthly_id' => 'P-1X657411GU0088618NC77KEI',
                'paypal_plan_annual_id' => 'P-2RB08490DN179502MNC77BIY',
            ],
            [
                'slug' => 'plata',
                'name' => 'Nivel 2',
                'monthly_price' => 3.50,
                'annual_price' => 36.50,
                'paypal_plan_monthly_id' => 'P-2S41183369023920GNDAAAZY',
                'paypal_plan_annual_id' => 'P-6K592089KN690864BNDAAANA',
            ],
            [
                'slug' => 'oro',
                'name' => 'Nivel 3',
                'monthly_price' => 5.00,
                'annual_price' => 50.00,
                'paypal_plan_monthly_id' => 'P-389338823B569620NNDAABQA',
                'paypal_plan_annual_id' => 'P-44B29505HU436204HNDAABIA',
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
