<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;   // <--- ¡IMPORTANTE! Sin esto, falla.
use App\Models\Item;
use App\Models\TeamActiveBuff;
use App\Models\TeamInventory;        // <--- ¡IMPORTANTE!
use App\Models\Zone;
use Carbon\Carbon;

class MarketController extends Controller
{
    public function index()
    {
        $items = Item::where('is_active', true)->get();
        // Comprobar si ya tiró la ruleta hoy (más de 24h)
        $canSpin = false;
        if (!Auth::user()->last_daily_reward_at || Carbon::parse(Auth::user()->last_daily_reward_at)->diffInHours(now()) >= 24) {
            $canSpin = true;
        }
        $zones = \App\Models\Zone::all();

        return view('game.market', compact('items', 'canSpin', 'zones'));
    }

    public function buy(Request $request)
    {
        $user = Auth::user();
        $item = Item::findOrFail($request->item_id);
        $team = $user->activeTeam;

        if (!$team) {
            return back()->with('error', '❌ ERROR: No perteneces a ningún equipo activo.');
        }

        try {
            DB::transaction(function () use ($user, $item, $team) {
                // 1. Intentar pagar (el método payCoins devuelve true/false)
                if ($user->payCoins($item->cost)) {

                    // 2. Añadir al inventario
                    $inventory = DB::table('team_inventory')
                        ->where('team_id', $team->id)
                        ->where('item_id', $item->id)
                        ->first();

                    if ($inventory) {
                        DB::table('team_inventory')
                            ->where('id', $inventory->id)
                            ->increment('quantity');
                    } else {
                        DB::table('team_inventory')->insert([
                            'team_id' => $team->id,
                            'item_id' => $item->id,
                            'quantity' => 1,
                            'created_at' => now(), 'updated_at' => now()
                        ]);
                    }

                } else {
                    // Si payCoins devuelve false, lanzamos excepción para que la capture el catch
                    throw new \Exception("FONDOS INSUFICIENTES. Tienes {$user->coins} y necesitas {$item->cost}.");
                }
            });

            return back()->with('success', "Has adquirido {$item->name}. Guardado en el arsenal del equipo.");

        } catch (\Exception $e) {
            // Aquí capturamos el error de fondos o cualquier otro
            return back()->with('error', '⛔ ' . $e->getMessage());
        }
    }

    public function spinRoulette()
    {
        $user = Auth::user();

        if ($user->last_daily_reward_at && Carbon::parse($user->last_daily_reward_at)->diffInHours(now()) < 24) {
            return response()->json(['error' => 'Ya has girado hoy. Vuelve mañana.'], 403);
        }

        $rand = rand(1, 100);
        $reward = 0;
        $message = "";

        // LÓGICA DE MENSAJES PERSONALIZADOS
        if ($rand <= 30) {
            $reward = 0;
            $message = "Parece que la incursión no ha salido bien.";
        } elseif ($rand <= 60) {
            $reward = 50;
            $message = "Has encontrado una billetera perdida: +50 COINS.";
        }
        elseif ($rand <= 95) {
            $reward = 100;
            $message = "¡Buen trabajo mercenario! Recompensa estándar: +100 COINS.";
        }
        else {
            $reward = 500;
            $message = "¡JACKPOT! Has hackeado una cuenta corporativa: +500 COINS.";
        }

        $user->addCoins($reward);
        $user->last_daily_reward_at = now();
        $user->save();

        return response()->json([
            'success' => true,
            'reward' => $reward,
            'message' => $message, // <-- Enviamos el mensaje personalizado
            'new_balance' => $user->coins
        ]);
    }

    // IMPORTANTE: Asegúrate de tener estos USE arriba del todo del archivo
    // use Illuminate\Support\Facades\DB;
    // use Illuminate\Support\Facades\Log;

    public function useRadar(Request $request)
    {
        // BLOQUE DE SEGURIDAD PARA VER EL ERROR REAL
        try {
            $user = Auth::user();

            // 1. Verificamos si carga el equipo
            // Intentamos acceder de forma segura (por si la relación falla)
            $team = $user->activeTeam;
            if (!$team) {
                // Si falla activeTeam, probamos active_team (por si acaso)
                $team = $user->active_team;
            }

            if (!$team) {
                return response()->json(['error' => 'DEBUG: No se detecta equipo activo en el usuario.'], 400);
            }

            // 2. Verificamos Capitán (Adaptable a tu lógica)
            // Comprobamos si usas 'captain_id' en teams o 'is_captain' en users
            $esCapitan = false;
            if (isset($team->captain_id) && $team->captain_id == $user->id) $esCapitan = true;
            if (isset($user->is_captain) && $user->is_captain) $esCapitan = true;

            if (!$esCapitan) {
                return response()->json(['error' => 'Solo el Capitán puede operar el satélite.'], 403);
            }

            $request->validate(['zone_slug' => 'required|exists:zones,slug']);

            // 3. Buscar el item
            $inventoryItem = \App\Models\TeamInventory::where('team_id', $team->id)
                ->whereHas('item', function($q) {
                    $q->where('code', 'intel_radar_zone');
                })
                ->where('quantity', '>', 0)
                ->first();

            if (!$inventoryItem) {
                return response()->json(['error' => 'No tienes satélites disponibles (Stock 0 o no encontrado).'], 400);
            }

            // 4. Buscar Zona
            $zone = \App\Models\Zone::where('slug', $request->zone_slug)->first();
            if (!$zone) {
                return response()->json(['error' => 'Zona no encontrada.'], 404);
            }

            // 5. CONSULTA A LA BASE DE DATOS (Aquí suele estar el fallo)
            // Usamos 'conquest_votes' como me indicaste
            $stats = DB::table('conquest_votes')
                ->join('teams', 'conquest_votes.team_id', '=', 'teams.id')
                ->where('conquest_votes.zone_id', $zone->id)
                ->select('teams.name', 'teams.color', DB::raw('count(*) as total_votes'))
                ->groupBy('teams.id', 'teams.name', 'teams.color')
                ->orderByDesc('total_votes')
                ->get();

            // 6. Gastar item
            $inventoryItem->decrement('quantity');

            return response()->json([
                'success' => true,
                'zone_name' => $zone->name,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            // AQUÍ ESTÁ LA MAGIA: Esto devolverá el error exacto al navegador
            return response()->json([
                'error' => 'ERROR DEL SISTEMA: ' . $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    public function activate(Request $request)
    {
        $user = Auth::user();
        $team = $user->activeTeam;

        // 1. Verificar Capitán
        if (!$team || $team->captain_id != $user->id) {
            return back()->with('error', '⛔ Solo el Comandante puede activar protocolos de guerra.');
        }

        // 2. Buscar Item en Inventario
        $inventoryItem = \App\Models\TeamInventory::where('id', $request->inventory_id)
            ->where('team_id', $team->id)
            ->first();

        if (!$inventoryItem || $inventoryItem->quantity < 1) {
            return back()->with('error', 'No hay stock de este suministro.');
        }

        $code = $inventoryItem->item->code;

        // 3. Verificar si es un BUFF (por el código)
        if (!str_starts_with($code, 'buff_')) {
            return back()->with('error', 'Este objeto no se puede activar aquí (¿Es pasivo o instantáneo?).');
        }

        // 4. Evitar duplicados (Si ya tienen ataque activo, no dejar poner otro igual)
        $exists = TeamActiveBuff::where('team_id', $team->id)
            ->where('item_code', $code)
            ->exists();

        if ($exists) {
            return back()->with('error', '⚠️ Este sistema YA está en línea. Espera al próximo ciclo.');
        }

        // 5. Extraer el multiplicador del código (ej: buff_attack_1.2 -> 1.2)
        $multiplier = 1.0;
        if (preg_match('/_([\d\.]+)$/', $code, $matches)) {
            $multiplier = (float)$matches[1];
        }

        // 6. EJECUTAR ACTIVACIÓN
        DB::transaction(function () use ($team, $code, $multiplier, $inventoryItem) {
            // Crear el Buff Activo
            TeamActiveBuff::create([
                'team_id' => $team->id,
                'item_code' => $code,
                'multiplier' => $multiplier,
                'expires_at' => now()->next('Sunday')->endOfDay() // Dura hasta el domingo noche
            ]);

            // Restar del inventario
            $inventoryItem->decrement('quantity');
        });

        return back()->with('success', "SISTEMA ACTIVADO: Potencia aumentada (x{$multiplier}) hasta el fin del ciclo.");
    }
}
