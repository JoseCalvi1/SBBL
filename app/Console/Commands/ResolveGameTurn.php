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
    protected $description = 'Resuelve los votos calculando puntos (Ciclo Quincenal)';

    private $eventParticipantsCache = [];

    public function handle()
    {
        // 1. CONTROL DE CICLO QUINCENAL
        // Usamos el número de semana del año.
        // Si es IMPAR (1, 3, 5...), NO se resuelve (estamos a mitad de turno).
        // Si es PAR (2, 4, 6...), SÍ se resuelve.
        if (Carbon::now()->weekOfYear % 2 != 0) {
            $this->info("📅 Semana IMPAR (Mantenimiento). El turno continúa hasta la semana que viene.");
            return;
        }

        $this->info('⚔️  CALCULANDO PODER DE COMBATE (CICLO DE 2 SEMANAS)...');

        $zones = Zone::all();
        $changesCount = 0;
        $teamsCache = Team::all()->keyBy('id');

        // 2. RANGO DE FECHAS (14 DÍAS)
        // Como estamos en domingo de semana par, el inicio fue el Lunes de la semana PASADA.
        $startOfPeriod = Carbon::now()->subWeek()->startOfWeek()->format('Y-m-d');
        $endOfPeriod   = Carbon::now()->endOfWeek()->format('Y-m-d');

        foreach ($zones as $zone) {
            // Buscamos votos desde el inicio del periodo (hace 2 semanas)
            $rawVotes = DB::table('conquest_votes')
                ->where('zone_id', $zone->id)
                ->get();

            if ($rawVotes->isEmpty()) {
                continue;
            }

            $battlePower = [];

            foreach($rawVotes as $vote) {
                // Buscamos torneos en el rango de 2 semanas
                $participations = DB::table('assist_user_event')
                    ->join('events', 'assist_user_event.event_id', '=', 'events.id')
                    ->where('assist_user_event.user_id', $vote->user_id)
                    ->whereBetween('events.date', [$startOfPeriod, $endOfPeriod]) // <-- CAMBIO AQUÍ
                    ->whereNotNull('assist_user_event.puesto')
                    ->select('assist_user_event.puesto', 'events.id as event_id')
                    ->get();

                $userPoints = 0;

                foreach ($participations as $part) {
                    $eventId = $part->event_id;
                    $puesto = (int) $part->puesto;

                    if (!isset($this->eventParticipantsCache[$eventId])) {
                        $count = DB::table('assist_user_event')->where('event_id', $eventId)->count();
                        $this->eventParticipantsCache[$eventId] = $count;
                    }

                    $totalParticipants = $this->eventParticipantsCache[$eventId];
                    $pointsTable = $this->getPointsTable($totalParticipants);
                    $index = $puesto - 1;

                    if (isset($pointsTable[$index])) {
                        $userPoints += $pointsTable[$index];
                    } else {
                        $userPoints += 1;
                    }
                }

                // Cálculo Final
                $teamPoints = $teamsCache[$vote->team_id]->points_x2 ?? 0;
                $multiplier = 1 + ($teamPoints / 2000);
                $damage = (10 + $userPoints) * $multiplier;

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

            if ($zone->team_id == $winnerTeamId) {
                $this->line("🛡️  {$zone->name}: Defendida por {$winnerTeamName}");
            } else {
                $oldOwner = $zone->team ? $zone->team->name : 'Nadie';
                $zone->team_id = $winnerTeamId;
                $zone->save();

                WarNews::create([
                    'title' => "VICTORIA EN " . strtoupper($zone->name),
                    'content' => "Tras dos semanas de combates (Poder: {$winnerDamage}), el equipo {$winnerTeamName} ha tomado la región de manos de {$oldOwner}.",
                    'type' => 'conquest'
                ]);

                $this->info("🚩 {$zone->name}: CONQUISTADA por {$winnerTeamName}!");
                $changesCount++;
            }
        }

        // Limpieza de votos y chat
        DB::table('conquest_votes')->truncate();
        DB::table('team_chat_messages')->truncate();

        $this->newLine();
        $this->info("✅ TURNO QUINCENAL RESUELTO. Zonas cambiadas: $changesCount");
    }

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
