<?php

namespace App\Observers;

use App\Models\User;
use App\Models\FantasyManager;

class UserObserver
{
    /**
     * Se ejecuta automáticamente cuando se crea un nuevo usuario en la web.
     */
    public function created(User $user)
    {
        // 1. Le creamos su cuenta de Mánager de Fantasy con 10.000 monedas de bienvenida
        FantasyManager::create([
            'user_id' => $user->id,
            'coins_balance' => 10000,
            'gems_balance' => 0,
            'total_points' => 0,
        ]);

        // (Opcional) Si en tu liga TODOS los usuarios también son competidores,
        // podrías crear aquí también su carta para el mercado:
        // $user->fantasyRealPlayer()->create(['base_price' => 100, 'current_price' => 100]);
    }
}
