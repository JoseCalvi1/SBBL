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
    protected $description = 'Resuelve los votos calculando puntos y aplicando potenciadores (Ciclo Quincenal)';

    private $eventParticipantsCache = [];

    public function handle()
    {
        // 1. CONTROL DE CICLO QUINCENAL
        // Si es semana IMPAR, no se resuelve.
        if (Carbon::now()->weekOfYear % 2 != 0) {
            $this->info("📅 Semana IMPAR (Mantenimiento). El turno continúa hasta la semana que viene.");
            return;
        }

        $this->info('⚔️  CALCULANDO PODER DE COMBATE (CON BUFFS DE MERCADO)...');

        $zones = Zone::all();
        $changesCount = 0;
        $teamsCache = Team::all()->keyBy('id');

        // 2. CARGAR BUFFS ACTIVOS (Optimización)
        // Cargamos todos los buffs activos en memoria agrupados por equipo
        // para no consultar la BD en cada voto.
        $activeBuffs = DB::table('team_active_buffs')
            ->where('expires_at', '>', now())
            ->get()
            ->groupBy('team_id');

        $this->info("   > Buffs activos cargados en memoria.");

        // 3. RANGO DE FECHAS (14 DÍAS)
        $startOfPeriod = Carbon::now()->subWeek()->startOfWeek()->format('Y-m-d');
        $endOfPeriod   = Carbon::now()->endOfWeek()->format('Y-m-d');

        foreach ($zones as $zone) {
            // Buscamos votos
            $rawVotes = DB::table('conquest_votes')
                ->where('zone_id', $zone->id)
                ->get();

            if ($rawVotes->isEmpty()) {
                continue;
            }

            $battlePower = [];

            foreach($rawVotes as $vote) {
                // A. CALCULO DE PUNTOS DE TORNEO (Tu lógica original)
                $participations = DB::table('assist_user_event')
                    ->join('events', 'assist_user_event.event_id', '=', 'events.id')
                    ->where('assist_user_event.user_id', $vote->user_id)
                    ->whereBetween('events.date', [$startOfPeriod, $endOfPeriod])
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

                // B. DAÑO BASE (10 + Puntos Torneo * Ranking Global)
                $teamPoints = $teamsCache[$vote->team_id]->points_x2 ?? 0;
                $rankMultiplier = 1 + ($teamPoints / 2000);
                $baseDamage = (10 + $userPoints) * $rankMultiplier;

                // C. APLICAR BUFFS DE MERCADO (NUEVO) 🚀
                $marketMultiplier = 1.0;

                // Verificamos si este equipo tiene buffs activos
                if (isset($activeBuffs[$vote->team_id])) {
                    foreach ($activeBuffs[$vote->team_id] as $buff) {

                        // 1. Buff de ATAQUE (Aplica siempre)
                        // Buscamos códigos como 'buff_attack_1.2'
                        if (str_starts_with($buff->item_code, 'buff_attack')) {
                            $marketMultiplier *= $buff->multiplier;
                        }

                        // 2. Buff de DEFENSA (Aplica solo si son dueños de la zona)
                        // Buscamos códigos como 'buff_defense_1.5'
                        if ($zone->team_id == $vote->team_id && str_starts_with($buff->item_code, 'buff_defense')) {
                            $marketMultiplier *= $buff->multiplier;
                        }
                    }
                }

                // Cálculo Final con Buffs
                $finalDamage = $baseDamage * $marketMultiplier;

                if (!isset($battlePower[$vote->team_id])) {
                    $battlePower[$vote->team_id] = 0;
                }
                $battlePower[$vote->team_id] += $finalDamage;
            }

            // --- DETERMINAR GANADOR ---
            arsort($battlePower);
            if (empty($battlePower)) continue;

            $winnerTeamId = array_key_first($battlePower);
            $winnerDamage = round(reset($battlePower));
            $winnerTeamName = $teamsCache[$winnerTeamId]->name;

            if ($zone->team_id == $winnerTeamId) {
                $this->line("🛡️  {$zone->name}: Defendida por {$winnerTeamName} (Poder: {$winnerDamage})");
            } else {
                $oldOwner = $zone->team ? $zone->team->name : 'Nadie';
                $zone->team_id = $winnerTeamId;
                $zone->save();

                WarNews::create([
                    'title' => "VICTORIA EN " . strtoupper($zone->name),
                    'content' => "Tras dos semanas de combates (Poder Total: {$winnerDamage}), el equipo {$winnerTeamName} ha tomado la región de manos de {$oldOwner}.",
                    'type' => 'conquest'
                ]);

                $this->info("🚩 {$zone->name}: CONQUISTADA por {$winnerTeamName}!");
                $changesCount++;
            }
        }

        // 4. LIMPIEZA DE DATOS (RESET DEL CICLO)
        $this->info("🧹 Limpiando base de datos para el nuevo ciclo...");

        DB::table('conquest_votes')->truncate();       // Borrar votos
        DB::table('team_chat_messages')->truncate();   // Borrar chat antiguo
        DB::table('team_active_buffs')->truncate();    // Borrar buffs (Deben comprarse de nuevo)

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
