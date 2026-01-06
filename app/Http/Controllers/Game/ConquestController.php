<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamChatMessage;
use App\Models\WarNews;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// use App\Models\Zone; // Lo descomentaremos cuando tengamos el modelo

class ConquestController extends Controller
{
    private $eventParticipantsCache = [];
    /**
     * Muestra la Landing Page gamificada (Portada)
     */
    public function welcome()
    {
        // Contar datos reales
        $zonesCount = \App\Models\Zone::count();
        $bladersCount = \App\Models\User::count();

        // Calcular próximo cierre (Domingo)
        $nextClose = \Carbon\Carbon::now()->next(\Carbon\Carbon::SUNDAY)->format('d/m');

        return view('game.welcome', compact('zonesCount', 'bladersCount', 'nextClose'));
    }

    public function map()
    {
        $user = Auth::user();

        // --- LÓGICA DE TIEMPO QUINCENAL ---
        $now = Carbon::now();
        $isResolutionWeek = ($now->weekOfYear % 2 === 0); // Semanas pares cierran

        if ($isResolutionWeek) {
            // Estamos en la semana final del turno.
            // El inicio fue la semana pasada.
            $startDate = $now->copy()->subWeek()->startOfWeek();
            // El cierre es ESTE domingo.
            $nextClose = $now->copy()->endOfWeek()->setTime(23, 59, 59);
        } else {
            // Estamos en la semana de apertura del turno.
            // El inicio es esta semana.
            $startDate = $now->copy()->startOfWeek();
            // El cierre es el DOMINGO DE LA SEMANA QUE VIENE.
            $nextClose = $now->copy()->addWeek()->endOfWeek()->setTime(23, 59, 59);
        }

        // Ajuste visual si ya es domingo noche
        if ($now->dayOfWeek == Carbon::SUNDAY && $now->hour >= 23) {
             $nextClose = $now->copy()->setTime(23, 59, 59);
        }

        // 1. Cargar Zonas
        $zones = Zone::with('team')->get();

        // 2. RADAR DE ATAQUE
        $teamAttackStats = [];
        $teamsCache = Team::all()->keyBy('id');
        $this->eventParticipantsCache = []; // Reset cache

        // Obtenemos votos DESDE EL INICIO DEL TURNO (puede ser hace 10 días)
        $votes = DB::table('conquest_votes')
            ->where('created_at', '>=', $startDate)
            ->get();

        foreach($votes as $vote) {
            $zid = $vote->zone_id;
            $tid = $vote->team_id;
            $uid = $vote->user_id;

            if(!isset($teamAttackStats[$zid])) {
                $teamAttackStats[$zid] = ['total_power' => 0, 'teams' => []];
            }

            // Pasamos $startDate a la función para que calcule torneos desde esa fecha
            $userTurnPoints = $this->calculateUserTournamentPoints($uid, $startDate);

            $teamPoints = $teamsCache[$tid]->points_x2 ?? 0;
            $multiplier = 1 + ($teamPoints / 2000);
            $damage = round((10 + $userTurnPoints) * $multiplier);

            if(!isset($teamAttackStats[$zid]['teams'][$tid])) {
                $teamAttackStats[$zid]['teams'][$tid] = [
                    'id' => $tid,
                    'name' => $teamsCache[$tid]->name,
                    'color' => $teamsCache[$tid]->color,
                    'votes' => 0
                ];
            }
            $teamAttackStats[$zid]['teams'][$tid]['votes'] += $damage;
            $teamAttackStats[$zid]['total_power'] += $damage;
        }

        // Re-formatear para JS
        foreach($teamAttackStats as &$zoneStat) {
            $zoneStat['teams'] = array_values($zoneStat['teams']);
            $zoneStat['total_votes'] = $zoneStat['total_power'];
        }
        unset($zoneStat);

        // 3. MI PODER
        $myPower = 0;
        if ($user && $user->active_team) {
            $myPoints = $this->calculateUserTournamentPoints($user->id, $startDate);

            $teamId = $user->active_team->id ?? $user->active_team;
            $teamData = $teamsCache[$teamId] ?? null;
            $teamPointsVal = $teamData ? $teamData->points_x2 : 0;
            $myMultiplier = 1 + ($teamPointsVal / 2000);

            $myPower = round((10 + $myPoints) * $myMultiplier);
        }

        // 4. RANKING y LOGS
        $globalLeaderboard = Zone::select('team_id', DB::raw('count(*) as total'))
            ->whereNotNull('team_id')
            ->groupBy('team_id')
            ->with('team')
            ->orderByDesc('total')
            ->get();

        $teamActivity = collect();
        if ($user && $user->active_team) {
             $myTeamId = $user->active_team->id ?? $user->active_team;
             $teamActivity = DB::table('conquest_votes')
                ->join('users', 'users.id', '=', 'conquest_votes.user_id')
                ->join('zones', 'zones.id', '=', 'conquest_votes.zone_id')
                ->where('conquest_votes.team_id', $myTeamId)
                ->where('conquest_votes.created_at', '>=', $startDate) // Desde inicio de turno
                ->select('users.name as user_name', 'zones.name as zone_name', 'conquest_votes.created_at')
                ->orderByDesc('conquest_votes.created_at')
                ->limit(15)
                ->get();
        }

        // Variables de vista
        $currentRound = $now->weekOfYear;
        $nextRound = $currentRound + 2; // Salto de 2 semanas

        // Fase visual: Solo es "conquista" (rojo) si estamos en finde Y es semana par
        $isWeekend = $now->isWeekend();
        // Solo es fase de conquista si es finde Y semana par (resolución)
        $isConquestPhase = ($isResolutionWeek && $isWeekend);

        if ($isConquestPhase) {
            $phaseName = "FASE DE CONQUISTA (RESOLUCIÓN)";
            $phaseColor = "text-red-500";
        } elseif (!$isResolutionWeek) {
            // Semana 1 (Impar)
            $phaseName = "FASE DE DESGASTE (SEMANA 1/2)";
            $phaseColor = "text-yellow-500"; // Amarillo para indicar proceso
        } else {
            // Semana 2 (Par) entre lunes y viernes
            $phaseName = "FASE FINAL (SEMANA 2/2)";
            $phaseColor = "text-green-500";
        }

        $votingEnabled = !$isConquestPhase;

        $zonesCount = $zones->where('team_id', '!=', null)->count();
        $bladersCount = \App\Models\User::count();

        return view('game.map', compact(
            'zones', 'teamAttackStats', 'myPower', 'globalLeaderboard',
            'nextClose', 'teamActivity', 'currentRound', 'nextRound',
            'phaseName', 'phaseColor', 'votingEnabled', 'zonesCount', 'bladersCount'
        ));
    }

    // API para obtener mensajes (Polling simple)
    public function getChatMessages()
    {
        $user = Auth::user();
        if (!$user->active_team) return response()->json([]);

        // Traer últimos 50 mensajes del equipo
        $messages = TeamChatMessage::with('user:id,name') // Solo traemos nombre para ahorrar
            ->where('team_id', $user->active_team->id)
            ->latest()
            ->take(50)
            ->get()
            ->reverse() // Para que salgan en orden cronológico
            ->values();

        return response()->json($messages);
    }

    // API para enviar mensaje
    public function sendChatMessage(Request $request)
    {
        $request->validate(['message' => 'required|max:255']);
        $user = Auth::user();

        if (!$user->active_team) return response()->json(['error' => 'Sin equipo'], 403);

        TeamChatMessage::create([
            'team_id' => $user->active_team->id,
            'user_id' => $user->id,
            'message' => $request->message
        ]);

        return response()->json(['success' => true]);
    }

    public function vote(Request $request)
    {
        // 🛑 BLOQUEO DE FIN DE SEMANA 🛑
        if (Carbon::now()->isWeekend()) {
            return response()->json([
                'success' => false,
                'error' => '⛔ FASE DE CONQUISTA EN CURSO. Las votaciones están cerradas hasta el lunes.'
            ], 422);
        }

        // 1. Validar entrada
        $request->validate([
            'zone_slug' => 'required|exists:zones,slug',
        ]);

        $user = Auth::user();
        $team = $user->active_team; // Usamos el helper que acabamos de crear

        // 2. Validaciones de Reglas del Juego
        if (!$team) {
            return response()->json(['error' => 'Necesitas unirte a un equipo para participar en la guerra.'], 403);
        }

        // Buscar la zona por el slug (que viene del mapa SVG)
        $zone = Zone::where('slug', $request->zone_slug)->firstOrFail();

        // --- 🛑 NUEVA VALIDACIÓN: FUEGO AMIGO 🛑 ---
        if ($zone->team_id === $team->id) {
            return response()->json([
                'success' => false,
                'error' => '¡No puedes atacar tu propio territorio! Defiéndelo ganando puntos en la liga.'
            ], 422); // 422 Unprocessable Entity
        }

        // Regla: ¿Ya votó esta semana? (Opcional: borrar voto anterior y poner el nuevo)
        // Aquí borramos votos previos de esta semana para permitir "cambiar de opinión"
        // Asumimos que la tabla de votos se llama 'conquest_votes'
        DB::table('conquest_votes')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->startOfWeek()) // Solo votos de esta semana
            ->delete();

        // 3. Registrar el Voto (Ataque)
        DB::table('conquest_votes')->insert([
            'user_id' => $user->id,
            'zone_id' => $zone->id,
            'team_id' => $team->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "¡Ataque coordinado en " . $zone->name . "!",
            'team_color' => $team->color ?? '#fff'
        ]);
    }

    public function news()
    {
        // Cargar noticias ordenadas por la más reciente
        $news = WarNews::latest()->take(20)->get();
        return view('game.news', compact('news'));
    }

    // ESTO ES SOLO PARA PROBAR (LUEGO LO BORRAREMOS O MOVEREMOS AL COMANDO)
    public function generateTestNews()
    {
        // Simulamos un reporte de "Cierre de Votaciones"
        WarNews::create([
            'title' => '⚠️ ALERTA DE INTELIGENCIA',
            'content' => 'Los satélites detectan movimientos masivos de tropas hacia MADRID. Se estima una fuerza de ataque de 5.000 unidades del Equipo Rojo.',
            'type' => 'attack'
        ]);

        // Simulamos una "Conquista"
        WarNews::create([
            'title' => '🚩 CAMBIO DE RÉGIMEN EN BARCELONA',
            'content' => 'Tras una semana de asedio, las defensas han caído. El territorio pasa a estar bajo control del Equipo Azul.',
            'type' => 'conquest'
        ]);

        // Simulamos una "Defensa"
        WarNews::create([
            'title' => '🛡️ MURALLA INQUEBRANTABLE',
            'content' => 'El Equipo Amarillo ha repelido con éxito la invasión en SEVILLA. Los atacantes se retiran con graves bajas.',
            'type' => 'defense'
        ]);

        return redirect()->route('conquest.news');
    }

    // ==========================================
    //  FUNCIONES PRIVADAS (COPIAR AL FINAL DE LA CLASE)
    // ==========================================

    private function calculateUserTournamentPoints($userId, $startDate)
    {
        // El fin siempre es "hoy" (o el futuro cercano)
        $endDate = Carbon::now()->endOfWeek();

        $participations = DB::table('assist_user_event')
            ->join('events', 'assist_user_event.event_id', '=', 'events.id')
            ->where('assist_user_event.user_id', $userId)
            ->whereBetween('events.date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereNotNull('assist_user_event.puesto')
            ->select('assist_user_event.puesto', 'events.id as event_id')
            ->get();

        $points = 0;

        foreach ($participations as $part) {
            $eventId = $part->event_id;
            $puesto = (int) $part->puesto;

            if (!isset($this->eventParticipantsCache[$eventId])) {
                $this->eventParticipantsCache[$eventId] = DB::table('assist_user_event')
                    ->where('event_id', $eventId)
                    ->count();
            }

            $totalParticipants = $this->eventParticipantsCache[$eventId];
            $pointsTable = $this->getPointsTable($totalParticipants);
            $index = $puesto - 1;

            if (isset($pointsTable[$index])) {
                $points += $pointsTable[$index];
            } else {
                $points += 1;
            }
        }
        return $points;
    }

    /**
     * Tabla de puntos según participantes
     */
    private function getPointsTable(int $count): array
    {
        if ($count >= 33) return [7, 6, 5, 4, 3, 2, 1];
        if ($count >= 25) return [6, 5, 4, 3, 2, 1, 1];
        if ($count >= 17) return [5, 4, 3, 2, 1, 1, 1];
        if ($count >= 9)  return [4, 3, 2, 1, 1, 1, 1];
        if ($count >= 6)  return [3, 2, 1, 1, 1, 1, 1];
        return [2, 1, 1, 1, 1, 1, 1];
    }
}
