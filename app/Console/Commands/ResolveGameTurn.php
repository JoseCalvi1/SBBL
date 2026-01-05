<?php

namespace App\Console\Commands;

use App\Models\Team;
use Illuminate\Console\Command;
use App\Models\Zone;
use App\Models\WarNews;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ResolveGameTurn extends Command
{
    protected $signature = 'game:resolve';
    protected $description = 'Resuelve los votos calculando puntos basados en torneos semanales';

    // Cache para no contar los participantes del mismo torneo mil veces
    private $eventParticipantsCache = [];

    public function handle()
    {
        $this->info('⚔️  CALCULANDO PODER DE COMBATE (TORNEOS SEMANALES)...');

        $zones = Zone::all();
        $changesCount = 0;

        // Cacheamos equipos
        $teamsCache = Team::all()->keyBy('id');

        // Definimos el rango de fechas de la semana actual
        $startOfWeek = Carbon::now()->startOfWeek()->format('Y-m-d');
        $endOfWeek   = Carbon::now()->endOfWeek()->format('Y-m-d');

        foreach ($zones as $zone) {
            $rawVotes = DB::table('conquest_votes')
                ->where('zone_id', $zone->id)
                ->get();

            if ($rawVotes->isEmpty()) {
                continue;
            }

            $battlePower = []; // [team_id => total_damage]

            foreach($rawVotes as $vote) {
                // --- NUEVA LÓGICA DE PUNTOS DE TORNEO ---

                // 1. Buscamos torneos de esta semana donde el usuario participó
                $participations = DB::table('assist_user_event')
                    ->join('events', 'assist_user_event.event_id', '=', 'events.id')
                    ->where('assist_user_event.user_id', $vote->user_id)
                    ->whereBetween('events.date', [$startOfWeek, $endOfWeek])
                    ->whereNotNull('assist_user_event.puesto') // Solo si tiene puesto asignado
                    ->select('assist_user_event.puesto', 'events.id as event_id')
                    ->get();

                $userPoints = 0;

                foreach ($participations as $part) {
                    $eventId = $part->event_id;
                    $puesto = (int) $part->puesto; // Asumimos que guardas "1", "2", etc.

                    // Si no tenemos cacheado cuánta gente fue a ese torneo, lo contamos ahora
                    if (!isset($this->eventParticipantsCache[$eventId])) {
                        $count = DB::table('assist_user_event')
                            ->where('event_id', $eventId)
                            ->count();
                        $this->eventParticipantsCache[$eventId] = $count;
                    }

                    $totalParticipants = $this->eventParticipantsCache[$eventId];

                    // Obtenemos la tabla de puntos según cantidad de gente
                    $pointsTable = $this->getPointsTable($totalParticipants);

                    // Asignamos puntos según el puesto (Array index 0 es el puesto 1)
                    // Si el puesto es 1, index es 0. Si puesto es 2, index es 1.
                    $index = $puesto - 1;

                    if (isset($pointsTable[$index])) {
                        $userPoints += $pointsTable[$index];
                    } else {
                        // Si quedó en un puesto muy bajo que no sale en la tabla (ej: quedó el 20 de 33)
                        // Sumamos 1 punto por participación (ajustable según tus reglas)
                        $userPoints += 1;
                    }
                }

                // --- FIN NUEVA LÓGICA ---

                // 2. Puntos del Equipo (Multiplier)
                $teamPoints = $teamsCache[$vote->team_id]->points_x2 ?? 0;
                $multiplier = 1 + ($teamPoints / 2000);

                // 3. Cálculo Final (Base 10 + Puntos Torneos) * Multiplicador Equipo
                $damage = (10 + $userPoints) * $multiplier;

                // 4. Sumar al equipo
                if (!isset($battlePower[$vote->team_id])) {
                    $battlePower[$vote->team_id] = 0;
                }
                $battlePower[$vote->team_id] += $damage;
            }

            // --- DETERMINAR GANADOR ---
            arsort($battlePower);

            if (empty($battlePower)) continue;

            $winnerTeamId = array_key_first($battlePower);
            $winnerDamage = round(reset($battlePower));
            $winnerTeamName = $teamsCache[$winnerTeamId]->name;

            // --- RESOLUCIÓN ---
            if ($zone->team_id == $winnerTeamId) {
                $this->line("🛡️  {$zone->name}: Defendida por {$winnerTeamName} (Poder: $winnerDamage)");
            } else {
                $oldOwner = $zone->team ? $zone->team->name : 'Nadie';

                $zone->team_id = $winnerTeamId;
                $zone->save();

                WarNews::create([
                    'title' => "VICTORIA EN " . strtoupper($zone->name),
                    'content' => "Gracias a los torneos ganados esta semana (Poder Total: {$winnerDamage}), el equipo {$winnerTeamName} ha arrebatado la región a {$oldOwner}.",
                    'type' => 'conquest'
                ]);

                $this->info("🚩 {$zone->name}: CONQUISTADA por {$winnerTeamName}!");
                $changesCount++;
            }
        }

        // Limpieza
        DB::table('conquest_votes')->truncate();
        DB::table('team_chat_messages')->truncate();

        $this->newLine();
        $this->info("✅ TURNO RESUELTO. Zonas cambiadas: $changesCount");
    }

    /**
     * Devuelve la tabla de puntos según número de participantes.
     */
    private function getPointsTable(int $count): array
    {
        if ($count >= 33) return [7, 6, 5, 4, 3, 2, 1];
        if ($count >= 25) return [6, 5, 4, 3, 2, 1, 1];
        if ($count >= 17) return [5, 4, 3, 2, 1, 1, 1];
        if ($count >= 9)  return [4, 3, 2, 1, 1, 1, 1];
        if ($count >= 6)  return [3, 2, 1, 1, 1, 1, 1];

        // Mínimo (menos de 6 jugadores)
        return [2, 1, 1, 1, 1, 1, 1];
    }
}
