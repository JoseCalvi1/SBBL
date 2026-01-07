<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    // database/seeders/ItemsSeeder.php
    public function run()
    {
        DB::table('items')->insert([
            [
                'name' => 'Estimulante de Combate',
                'code' => 'attack_boost_1.2',
                'description' => 'Aumenta x1.2 la puntuación de un jugador en el siguiente ataque.',
                'cost' => 500, // Precio ejemplo en Coins
                'image' => 'images/items/attack.png',
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'name' => 'Escudo Fantasma',
                'code' => 'defense_random_x2',
                'description' => 'Añade una probabilidad de defensa x2 extra en tu zona.',
                'cost' => 1000,
                'image' => 'images/items/shield.png',
                'created_at' => now(), 'updated_at' => now(),
            ]
        ]);
    }
}
