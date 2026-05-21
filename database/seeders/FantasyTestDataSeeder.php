<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\FantasyManager;
use App\Models\FantasyRealPlayer;
use App\Models\FantasyRoster;

class FantasyTestDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Seleccionamos el primer usuario de tu base de datos (Asumo que eres tú, el admin)
        $me = User::first();

        // Te creamos la billetera si no la tienes
        $manager = FantasyManager::firstOrCreate(
            ['user_id' => $me->id],
            ['coins_balance' => 10000, 'gems_balance' => 0, 'total_points' => 0]
        );

        // 2. Cogemos a 6 usuarios distintos a ti para convertirlos en Bladers de mercado
        $usersParaCartas = User::where('id', '!=', $me->id)->take(6)->get();

        foreach ($usersParaCartas as $index => $u) {
            // Damos de alta al usuario como Carta comprable
            $realPlayer = FantasyRealPlayer::firstOrCreate(
                ['user_id' => $u->id],
                ['base_price' => 150, 'current_price' => 150, 'is_active' => true]
            );

            // 3. Te los añadimos a tu plantilla. Los 3 primeros de titulares, el resto al banquillo.
            FantasyRoster::firstOrCreate([
                'fantasy_manager_id' => $manager->id,
                'fantasy_real_player_id' => $realPlayer->id,
            ], [
                'status' => $index < 3 ? 'starter' : 'bench',
                'buy_price' => 150
            ]);
        }
    }
}
