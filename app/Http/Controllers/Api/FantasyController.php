<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FantasyRealPlayer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FantasyRoster;
use Illuminate\Support\Facades\DB;
use App\Models\PointLog; // Asegúrate de importar el modelo

class FantasyController extends Controller
{
    public function getMyTeam()
    {
        $manager = \App\Models\FantasyManager::where('user_id', Auth::id())->first();
        if (!$manager) return response()->json(['error' => 'No mánager profile'], 400);

        // 1. Conseguimos TODOS los objetos que posee el mánager
        $todosLosObjetos = \App\Models\FantasyItem::where('fantasy_manager_id', $manager->id)->get();

        // 2. Mapeamos el roster inyectando los 4 slots de equipamiento por separado
        $roster = \App\Models\FantasyRoster::where('fantasy_manager_id', $manager->id)
            ->with('player.user')
            ->get()
            ->map(function ($carta) use ($todosLosObjetos) {
                // Filtramos los objetos asignados a esta carta concreta del roster
                $equipados = $todosLosObjetos->where('fantasy_roster_id', $carta->id);

                // Estructuramos los 4 slots para que Vue los lea directamente
                $carta->equipped_items = [
                    'lanzador'   => $equipados->where('slot_type', 'lanzador')->first(),
                    'equipacion' => $equipados->where('slot_type', 'equipacion')->first(),
                    'guantes'    => $equipados->where('slot_type', 'guantes')->first(),
                    'mascara'    => $equipados->where('slot_type', 'mascara')->first(),
                ];

                // 🔥 AQUÍ ESTÁ LA SOLUCIÓN:
                // Si la carta tiene un jugador real asociado, calculamos sus métricas
                // con tu función exacta y se las inyectamos en una propiedad dinámica
                if ($carta->player) {
                    $metrics = $this->calculatePlayerMetrics($carta->player);
                    $carta->player->detailed_stats = $metrics;
                }

                return $carta;
            });

        // 3. Separamos los objetos que están sueltos en el inventario (mochila)
        $inventarioLibre = $todosLosObjetos->whereNull('fantasy_roster_id')->values();

        // 4. Calculamos los puntos totales del mánager
        $totalPoints = \DB::table('points_log')
            ->where('user_id', Auth::id())
            ->where('modo', 'fantasy_reward')
            ->sum('puntos');

        return response()->json([
            'roster'          => $roster,
            'inventory_items' => $inventarioLibre, // Mochila de objetos sueltos
            'wallet'          => ['coins' => $manager->coins_balance, 'gems' => $manager->gems_balance],
            'total_points'    => $totalPoints
        ]);
    }

    // 2. Mercado (Modificado con precios dinámicos)
    public function getMarketPlayers()
    {
        $manager = Auth::user()->fantasyManager;
        $myPlayerIds = FantasyRoster::where('fantasy_manager_id', $manager->id)
                        ->pluck('fantasy_real_player_id');

        $marketPlayers = FantasyRealPlayer::whereNotIn('id', $myPlayerIds)
                        ->with('user')
                        ->get();

        // 🔥 APLICAMOS MÉTRICAS Y PRECIO DINÁMICO AL MERCADO 🔥
        return $marketPlayers->map(function ($p) {
            // Calculamos las métricas una sola vez por jugador
            $metrics = $this->calculatePlayerMetrics($p);

            return [
                'id' => $p->id,
                'name' => $p->user->name,
                // SUSTITUIMOS el precio fijo por el precio dinámico calculado
                'price' => $metrics['dynamic_price'],
                'detailed_stats' => $metrics
            ];
        });
    }

    // 🔥 MOTOR DE ESTADÍSTICAS BASADO EN points_log 🔥
    private function calculatePlayerMetrics($player)
    {
        // Obtenemos el ID del usuario real asociado a esta carta de Blader
        // Asumimos que la tabla fantasy_real_players tiene un user_id
        $realUserId = $player->user_id ?? ($player->user ? $player->user->id : null);

        // 1. OBTENER PUNTUACIONES REALES DE LA TABLA points_log
        $ultimasPuntuaciones = [];
        if ($realUserId) {
            $ultimasPuntuaciones = \DB::table('points_log')
                ->where('user_id', $realUserId)
                ->orderByDesc('created_at')
                ->take(5)
                ->pluck('puntos')
                ->toArray();
        }

        // Le damos la vuelta para que el más antiguo salga a la izquierda y el más reciente a la derecha
        $ultimasPuntuaciones = array_reverse($ultimasPuntuaciones);

        // Aseguramos que siempre haya 5 elementos (rellenamos por la izquierda con 0 si faltan)
        $ultimasPuntuaciones = array_pad($ultimasPuntuaciones, -5, 0);

        // 2. CÁLCULO DE LA SUMA
        $sumaTotal = array_sum($ultimasPuntuaciones);

        // 3. RANGOS DE RAREZA (Max 35 pts teóricos, 7 por torneo)
        $rareza = 'COMÚN';
        $color = '#c5c6c7'; // Gris
        $multiplicador = 1; // Precio x1

        if ($sumaTotal >= 6 && $sumaTotal <= 13) {
            $rareza = 'RARO';
            $color = '#66fcf1'; // Neón Cian
            $multiplicador = 1.5;
        } elseif ($sumaTotal >= 14 && $sumaTotal <= 19) {
            $rareza = 'ÉPICO';
            $color = '#e0129a'; // Rosa
            $multiplicador = 2.5;
        } elseif ($sumaTotal >= 20) {
            $rareza = 'LEGENDARIO';
            $color = '#f2a900'; // Dorado
            $multiplicador = 4;
        }

        // 4. CÁLCULO DEL PRECIO DINÁMICO
        // Usamos el precio base original del jugador, si existe, o 100 por defecto
        $precioBase = $player->base_price ?? 100; // Puedes cambiar base_price a como se llame en tu BD
        $precioDinamico = round($precioBase * $multiplicador);

        return [
            'scores' => $ultimasPuntuaciones,
            'total_last_5' => $sumaTotal,
            'rarity' => $rareza,
            'rarity_color' => $color,
            'dynamic_price' => $precioDinamico
        ];
    }

    public function saveLineup(Request $request)
    {
        $manager = Auth::user()->fantasyManager;
        $rosterData = $request->roster;

        // Actualizamos cada jugador con su nuevo status y si es capitán o no
        foreach ($rosterData as $item) {
            \App\Models\FantasyRoster::where('id', $item['id'])
                ->where('fantasy_manager_id', $manager->id)
                ->update([
                    'status' => $item['status'],
                    'is_captain' => $item['is_captain'] ?? false
                ]);
        }

        return response()->json(['message' => '¡Alineación y Capitán guardados!']);
    }


    public function buyPlayer(Request $request)
    {
        $manager = Auth::user()->fantasyManager;
        $playerToBuy = FantasyRealPlayer::findOrFail($request->player_id);

        // 🔥 OBTENEMOS EL PRECIO DINÁMICO EXACTO EN ESTE MOMENTO 🔥
        $metrics = $this->calculatePlayerMetrics($playerToBuy);
        $precioDinamico = $metrics['dynamic_price'];

        $saldoActual = $manager->coins_balance;

        if ($saldoActual < $precioDinamico) {
            return response()->json(['error' => 'Fondos insuficientes. Necesitas ' . $precioDinamico . ' créditos.'], 400);
        }

        \DB::transaction(function () use ($manager, $playerToBuy, $precioDinamico) {
            $manager->coins_balance -= $precioDinamico;
            $manager->save();

            FantasyRoster::create([
                'fantasy_manager_id' => $manager->id,
                'fantasy_real_player_id' => $playerToBuy->id,
                'status' => 'bench',
                'buy_price' => $precioDinamico // Guardamos a qué precio lo compró por si quieres hacer estadísticas luego
            ]);
        });

        return response()->json(['message' => '¡Fichaje completado por ' . $precioDinamico . ' créditos!']);
    }

    public function sellPlayer(Request $request)
    {
        $manager = Auth::user()->fantasyManager;

        // Añadimos ->with('player') para asegurarnos de que tenemos los datos del jugador real
        $rosterItem = FantasyRoster::with('player')->where('id', $request->roster_id)
                        ->where('fantasy_manager_id', $manager->id)
                        ->first();

        if (!$rosterItem) {
            return response()->json(['error' => 'Jugador no encontrado en tu equipo'], 404);
        }

        // 🔥 CÁLCULO DE VENTA BASADO EN EL VALOR DE MERCADO ACTUAL 🔥
        $metrics = $this->calculatePlayerMetrics($rosterItem->player);
        $precioActualMercado = $metrics['dynamic_price'];

        // El usuario recupera el 80% del valor ACTUAL en el mercado
        $sellPrice = round($precioActualMercado * 0.8);

        \DB::transaction(function () use ($manager, $rosterItem, $sellPrice) {
            // 1. Devolvemos el dinero al mánager
            $manager->coins_balance += $sellPrice;
            $manager->save();

            // 2. Eliminamos la carta de su equipo
            $rosterItem->delete();
        });

        return response()->json([
            'message' => '¡Jugador vendido por ' . $sellPrice . ' créditos!',
            'sell_price' => $sellPrice
        ]);
    }

    public function getRanking()
    {
        // 🔥 MURO DE CONTENCIÓN EN EL RANKING 🔥
        $ranking = \App\Models\User::withSum(['pointLogs as total_points' => function($query) {
            $query->where('modo', 'fantasy_reward');
        }], 'puntos')
            ->get()
            ->map(function ($user) {
                return [
                    'name' => $user->name,
                    'points' => (int)($user->total_points ?? 0)
                ];
            })
            ->sortByDesc('points')
            ->values();

        $finalRanking = $ranking->map(function ($item, $index) {
            $item['position'] = $index + 1;
            return $item;
        });

        return response()->json($finalRanking->toArray());
    }

    // --- 1. ABRIR SOBRES (x1 y x10 con filtro de Puntos > 3) ---
    public function openGachaPack(Request $request)
    {
        $manager = \App\Models\FantasyManager::firstOrCreate(
            ['user_id' => Auth::id()],
            ['coins_balance' => 500, 'gems_balance' => 100]
        );

        $tipo = $request->input('type', 'standard');
        $cantidad = $request->input('amount', 1);

        $costeGemas = (($tipo === 'premium') ? 150 : 50) * $cantidad;
        $multiplicador = ($tipo === 'premium') ? 1.2 : 1.0;

        if ($manager->gems_balance < $costeGemas) {
            return response()->json(['error' => 'No tienes Gemas suficientes.'], 400);
        }

        $manager->decrement('gems_balance', $costeGemas);

        // Obtenemos el mercado completo (que ya trae el 'total_last_5' calculado por tu función)
        $mercado = $this->getMarketPlayers()->toArray();

        // 🔥 1. FILTRAR POR PUNTOS MÍNIMOS (Mínimo 3 puntos en los últimos 5 torneos)
        $mercadoFiltrado = array_filter($mercado, function($p) {
            // Accedemos a las stats dinámicas que calculas en calculatePlayerMetrics
            $puntos = $p['detailed_stats']['total_last_5'] ?? 0;
            return $puntos >= 3;
        });

        // ⚠️ FALLBACK: Si nadie llega a 3 puntos (ej: inicio de liga), cogemos todo el mercado
        if (empty($mercadoFiltrado)) {
            $mercadoFiltrado = $mercado;
        }

        // 🔥 2. ORDENAR POR PRECIO (Los más caros/mejores primero)
        usort($mercadoFiltrado, function($a, $b) {
            $precioA = $a['price'] ?? 0;
            $precioB = $b['price'] ?? 0;
            return $precioB <=> $precioA; // Orden Descendente
        });

        // 🔥 3. QUEDARNOS CON EL TOP 500
        $mercadoTop500 = array_slice($mercadoFiltrado, 0, 500);

        $resultados = [];

        // Repetimos la ruleta tantas veces como sobres se hayan comprado (1 o 10)
        for ($i = 0; $i < $cantidad; $i++) {
            $tirada = rand(1, 100);
            $rareza = 'COMÚN';
            if ($tirada > 75 && $tirada <= 90) $rareza = 'RARO';
            elseif ($tirada > 90 && $tirada <= 98) $rareza = 'ÉPICO';
            elseif ($tirada > 98) $rareza = 'LEGENDARIO';

            // Buscamos dentro de nuestro Top 500 VIP
            $jugadoresPosibles = array_filter($mercadoTop500, function($p) use ($rareza) {
                return ($p['detailed_stats']['rarity'] ?? 'COMÚN') === $rareza;
            });

            // Si en nuestro Top 500 no hay nadie de esa rareza, cogemos a cualquiera del Top 500
            if (empty($jugadoresPosibles)) {
                $jugadoresPosibles = $mercadoTop500;
            }

            $jugadorPremiado = $jugadoresPosibles[array_rand($jugadoresPosibles)];

            // Comprobamos si ya lo tiene (solo para avisarle en la pantalla)
            $rosterExistente = \App\Models\FantasyRoster::where('fantasy_manager_id', $manager->id)
                ->where('fantasy_real_player_id', $jugadorPremiado['id'])
                ->first();

            // 🔥 AHORA SIEMPRE GUARDAMOS LA CARTA EN EL BANQUILLO 🔥
            $nuevoRoster = \App\Models\FantasyRoster::create([
                'fantasy_manager_id' => $manager->id,
                'fantasy_real_player_id' => $jugadorPremiado['id'],
                'status' => 'bench',
                'buy_price' => $jugadorPremiado['price'],
                'is_captain' => false,
                'level_multiplier' => $multiplicador
            ]);

            $jugadorPremiado['roster_id'] = $nuevoRoster->id; // Pasamos el ID para poder venderlo desde el modal
            $jugadorPremiado['is_duplicate'] = $rosterExistente ? true : false;
            $jugadorPremiado['level_multiplier'] = $multiplicador;

            $resultados[] = $jugadorPremiado;
        }

        return response()->json([
            'players' => $resultados
        ]);
    }

    // --- 2. VENTA MÚLTIPLE (BULK SELL) ---
    public function bulkSellPlayers(Request $request)
    {
        // VERSIÓN SEGURA: Busca tu cartera en la BD usando tu ID
        $manager = \App\Models\FantasyManager::where('user_id', Auth::id())->first();

        if (!$manager) {
            return response()->json(['error' => 'No tienes cuenta de Mánager iniciada.'], 400);
        }

        $rosterIds = $request->input('roster_ids', []);

        if (empty($rosterIds)) {
            return response()->json(['error' => 'No hay jugadores seleccionados'], 400);
        }

        // Buscamos los jugadores seleccionados que estén en TU banquillo
        $rosters = \App\Models\FantasyRoster::whereIn('id', $rosterIds)
            ->where('fantasy_manager_id', $manager->id)
            ->where('status', 'bench')
            ->get();

        if ($rosters->isEmpty()) {
            return response()->json(['error' => 'No se encontraron esos jugadores en tu banquillo.'], 400);
        }

        $totalGanado = 0;
        foreach ($rosters as $r) {
            $totalGanado += round($r->buy_price * 0.8);
            $r->delete(); // Los borramos de tu plantilla
        }

        // Te ingresamos los créditos
        $manager->increment('coins_balance', $totalGanado);

        return response()->json(['message' => "¡Vendidos " . count($rosters) . " Bladers por $totalGanado Créditos!"]);
    }

    public function getHistory(Request $request)
    {
        $manager = Auth::user()->fantasyManager;

        // 1. Buscamos todas las recompensas de Fantasy que ha recibido este mánager
        $logs = \DB::table('points_log')
            ->where('user_id', $manager->user_id)
            ->where('modo', 'fantasy_reward')
            ->orderBy('created_at', 'desc')
            ->get();

        $historial = [];

        foreach ($logs as $log) {
            // 2. Por cada torneo, buscamos cómo era su alineación en ESA foto finish
            $lineup = \DB::table('fantasy_event_lineups')
                ->join('users', 'fantasy_event_lineups.fantasy_real_player_id', '=', 'users.id')
                ->where('fantasy_event_lineups.event_id', $log->event_id)
                ->where('fantasy_event_lineups.fantasy_manager_id', $manager->id)
                ->select(
                    'users.name as player_name',
                    'fantasy_event_lineups.is_captain',
                    'fantasy_event_lineups.level_multiplier'
                )
                ->get();

            $historial[] = [
                'event_id' => $log->event_id,
                'total_points' => $log->puntos,
                // Formateamos la fecha para que se vea bonita (Ej: 14/05/2026)
                'date' => \Carbon\Carbon::parse($log->created_at)->format('d/m/Y'),
                'lineup' => $lineup
            ];
        }

        return response()->json($historial);
    }

    public function claimDailyReward(Request $request)
    {
        $manager = \App\Models\FantasyManager::where('user_id', Auth::id())->first();
        if (!$manager) {
            return response()->json(['error' => 'No manager found.'], 400);
        }

        $today = \Carbon\Carbon::today();
        $lastReward = $manager->last_login_reward_at ? \Carbon\Carbon::parse($manager->last_login_reward_at)->startOfDay() : null;

        // Si ya reclamó hoy, no hacemos nada
        if ($lastReward && $lastReward->equalTo($today)) {
            return response()->json(['status' => 'already_claimed']);
        }

        // ¿Entró ayer? Aumentamos la racha. ¿Se saltó un día? Reiniciamos a 1.
        if ($lastReward && $lastReward->equalTo($today->copy()->subDay())) {
            $manager->login_streak += 1;
        } else {
            $manager->login_streak = 1;
        }

        // Calculamos en qué día del ciclo de 7 días está (del 1 al 7)
        $streakDay = (($manager->login_streak - 1) % 7) + 1;

        $coinsReward = 0;
        $gemsReward = 0;

        // TABLA DE PREMIOS
        switch ($streakDay) {
            case 1: $coinsReward = 100; break;
            case 2: $coinsReward = 200; break;
            case 3: $coinsReward = 300; break;
            case 4: $gemsReward = 50; break;
            case 5: $coinsReward = 400; break;
            case 6: $coinsReward = 500; break;
            case 7: $gemsReward = 100; break;
        }

        $manager->increment('coins_balance', $coinsReward);
        $manager->increment('gems_balance', $gemsReward);

        $manager->last_login_reward_at = $today->toDateString();
        $manager->save();

        return response()->json([
            'status' => 'claimed',
            'day' => $streakDay,
            'coins' => $coinsReward,
            'gems' => $gemsReward,
            'wallet' => [
                'coins' => $manager->coins_balance,
                'gems' => $manager->gems_balance
            ]
        ]);
    }

    // --- 4. LABORATORIO DE FUSIÓN MULTINIVEL (x1.0 -> x1.2 -> x1.5) ---
    public function fusePlayers(Request $request)
    {
        $manager = \App\Models\FantasyManager::where('user_id', Auth::id())->first();
        $realPlayerId = $request->input('fantasy_real_player_id');
        // Capturamos el nivel actual que se quiere fusionar (por defecto 1.0)
        $currentLevel = (float) $request->input('current_level', 1.0);

        if (!$realPlayerId) {
            return response()->json(['error' => 'ID de jugador no válido.'], 400);
        }

        // Determinamos cuál será el siguiente nivel de evolución
        $nextLevel = 1.2;
        $nombreRango = "Premium (x1.2)";

        if ($currentLevel == 1.2) {
            $nextLevel = 1.5;
            $nombreRango = "Súper Cibernético (x1.5)";
        } elseif ($currentLevel >= 1.5) {
            return response()->json(['error' => 'Este Blader ya ha alcanzado el nivel máximo de evolución.'], 400);
        }

        // Buscamos estrictamente 3 copias DEL MISMO NIVEL que estén en el BANQUILLO
        $copias = \App\Models\FantasyRoster::where('fantasy_manager_id', $manager->id)
            ->where('fantasy_real_player_id', $realPlayerId)
            ->where('status', 'bench')
            ->where('level_multiplier', $currentLevel)
            ->take(3)
            ->get();

        if ($copias->count() < 3) {
            return response()->json(['error' => 'Necesitas al menos 3 copias idénticas de este mismo nivel en el banquillo.'], 400);
        }

        $precioCompraOriginal = $copias->first()->buy_price;

        \DB::transaction(function () use ($copias, $manager, $realPlayerId, $precioCompraOriginal, $nextLevel) {
            // 1. Destruimos las 3 copias del nivel actual
            foreach ($copias as $carta) {
                $carta->delete();
            }

            // 2. Alquimizamos la nueva versión mejorada
            \App\Models\FantasyRoster::create([
                'fantasy_manager_id' => $manager->id,
                'fantasy_real_player_id' => $realPlayerId,
                'status' => 'bench',
                'buy_price' => $precioCompraOriginal,
                'is_captain' => false,
                'level_multiplier' => $nextLevel // 🔥 Guardamos el nuevo multiplicador (1.2 o 1.5)
            ]);
        });

        return response()->json([
            'message' => "🧬 ¡Mutación exitosa! Tu Blader ha evolucionado al rango: $nombreRango"
        ]);
    }

    public function equipItem(Request $request)
    {
        $manager = \App\Models\FantasyManager::where('user_id', Auth::id())->first();
        $rosterId = $request->input('roster_id'); // A qué Blader
        $itemId   = $request->input('item_id');   // Qué objeto (si viene null, es desequipar)
        $slotType = $request->input('slot_type'); // Qué slot se está tocando ('lanzador', 'guantes', etc.)

        // Seguridad: Comprobamos que el Blader pertenezca al mánager
        $blader = \App\Models\FantasyRoster::where('id', $rosterId)->where('fantasy_manager_id', $manager->id)->first();
        if (!$blader) return response()->json(['error' => 'Blader no encontrado en tu plantilla.'], 400);

        // CASO A: DESEQUIPAR
        if (!$itemId) {
            \App\Models\FantasyItem::where('fantasy_roster_id', $rosterId)
                ->where('slot_type', $slotType)
                ->update(['fantasy_roster_id' => null]);

            return response()->json(['message' => 'Objeto enviado de vuelta al inventario.']);
        }

        // CASO B: EQUIPAR
        // Seguridad: Comprobamos que el objeto exista, sea del mánager y coincida con el tipo de slot
        $item = \App\Models\FantasyItem::where('id', $itemId)
            ->where('fantasy_manager_id', $manager->id)
            ->where('slot_type', $slotType)
            ->first();

        if (!$item) return response()->json(['error' => 'El objeto seleccionado no es válido para esta ranura.'], 400);

        \DB::transaction(function () use ($rosterId, $itemId, $slotType, $manager) {
            // 1. Si este Blader YA tenía un objeto en ESTA ranura concreta, lo mandamos al inventario (liberar)
            \App\Models\FantasyItem::where('fantasy_roster_id', $rosterId)
                ->where('slot_type', $slotType)
                ->update(['fantasy_roster_id' => null]);

            // 2. Si el objeto que queremos poner estaba equipado en OTRO Blader, lo liberamos de allí primero
            \App\Models\FantasyItem::where('id', $itemId)->update(['fantasy_roster_id' => null]);

            // 3. Equipamos el objeto en el Blader actual
            \App\Models\FantasyItem::where('id', $itemId)->update(['fantasy_roster_id' => $rosterId]);
        });

        return response()->json(['message' => '¡Equipamiento acoplado y calibrado con éxito!']);
    }

    // --- 5. GACHA DE SUMINISTROS (EQUIPAMIENTO MIXTO) ---
    public function openItemPack(Request $request)
    {
        $manager = \App\Models\FantasyManager::where('user_id', Auth::id())->first();
        $currency = $request->input('currency', 'coins'); // Recibimos 'coins' o 'gems'

        if (!$manager) {
            return response()->json(['error' => 'No se encontró el perfil de mánager.'], 400);
        }

        // Comprobación de saldo según el método de pago
        if ($currency === 'gems') {
            if ($manager->gems_balance < 50) {
                return response()->json(['error' => 'No tienes Gemas suficientes (Cuesta 50 💎).'], 400);
            }
            $manager->decrement('gems_balance', 50);
        } else {
            if ($manager->coins_balance < 150) {
                return response()->json(['error' => 'No tienes Créditos suficientes (Cuesta 150 ⟠).'], 400);
            }
            $manager->decrement('coins_balance', 150);
        }

        // 1. Elegimos el SLOT al azar (25% cada uno)
        $slots = ['lanzador', 'equipacion', 'guantes', 'mascara'];
        $slotElegido = $slots[array_rand($slots)];

        // 2. Calculamos RAREZA y MULTIPLICADOR
        $tirada = rand(1, 100);
        if ($tirada <= 70) {
            $rareza = 'COMÚN'; $color = '#c5c6c7'; $mult = 1.01;
            $nombres = [
                'lanzador'   => 'Lanzador Ligero Proto',
                'equipacion' => 'Camiseta SBBL de Entrenamiento',
                'guantes'    => 'Guante de Agarre Básico',
                'mascara'    => 'Visor de Datos Simple'
            ];
        } elseif ($tirada > 70 && $tirada <= 92) {
            $rareza = 'RARO'; $color = '#66fcf1'; $mult = 1.03;
            $nombres = [
                'lanzador'   => 'String Launcher Custom',
                'equipacion' => 'Sudadera Neón Espectador',
                'guantes'    => 'Mitones de Cuero Reforzado',
                'mascara'    => 'Máscara Antigas Filtrante'
            ];
        } else {
            $rareza = 'ÉPICO'; $color = '#e0129a'; $mult = 1.05;
            $nombres = [
                'lanzador'   => 'Lanzador Digital SBBL Pro',
                'equipacion' => 'Chaqueta de Competición Élite',
                'guantes'    => 'Guantelete de Titanio Termoajustado',
                'mascara'    => 'Visor Holográfico Cyberpunk'
            ];
        }

        $nombreObjeto = $nombres[$slotElegido];

        // 3. Guardamos el objeto
        $item = \App\Models\FantasyItem::create([
            'fantasy_manager_id' => $manager->id,
            'name'               => $nombreObjeto,
            'slot_type'          => $slotElegido,
            'rarity'             => $rareza,
            'rarity_color'       => $color,
            'stat_multiplier'    => $mult,
            'fantasy_roster_id'  => null
        ]);

        return response()->json([
            'message' => '¡Suministros desencriptados con éxito!',
            'item'    => $item,
            'wallet'  => ['coins' => $manager->coins_balance, 'gems' => $manager->gems_balance]
        ]);
    }
}
