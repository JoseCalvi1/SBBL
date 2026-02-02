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
    protected $description = 'Resuelve el turno: Defensa Global, MVP Defensivo y Reportes Detallados';

    private $eventParticipantsCache = [];

    public function handle()
    {
        // 1. CONTROL DE FECHAS (Semanas PARES)
        // Por tanto, si la semana es IMPAR, detenemos el script (Mantenimiento).
        if (Carbon::now()->weekOfYear % 2 != 0) {
            $this->info("📅 Semana IMPAR (Mantenimiento). El turno continúa.");
            return;
        }

        $this->info('⚔️  INICIANDO RESOLUCIÓN DEL TURNO...');

        $zones = Zone::all();
        $teamsCache = Team::all()->keyBy('id');
        $changesCount = 0;

        // Rango de fechas para buscar eventos (Últimas 2 semanas)
        $startOfPeriod = Carbon::now()->subWeek()->startOfWeek()->format('Y-m-d');
        $endOfPeriod   = Carbon::now()->endOfWeek()->format('Y-m-d');

        // 2. CARGAR BUFFS DE MERCADO
        $activeBuffs = DB::table('team_active_buffs')
            ->where('expires_at', '>', now())
            ->get()
            ->groupBy('team_id');

        // 3. OBTENER VOTOS Y SELECCIONAR MVP (ALEATORIO)
        $allVotes = DB::table('conquest_votes')->get();
        $teamGlobalDefense = []; // Bolsa de vida del equipo
        $zoneAttacks = [];       // Ataques específicos por zona

        // Agrupar votantes por equipo para sacar el MVP
        $teamVoters = [];
        foreach ($allVotes as $vote) {
            $teamVoters[$vote->team_id][] = $vote->user_id;
        }

        $luckyMembers = []; // [team_id => user_id]
        foreach ($teamVoters as $tId => $userIds) {
            $uniqueUsers = array_unique($userIds);
            if (!empty($uniqueUsers)) {
                $luckyMembers[$tId] = $uniqueUsers[array_rand($uniqueUsers)];
            }
        }

        // 4. CALCULAR PODER DE CADA VOTO
        foreach ($allVotes as $vote) {
            $teamId = $vote->team_id;
            $userId = $vote->user_id;
            $zoneId = $vote->zone_id;

            // A. Calcular Base: (10 + Eventos) * (1 + Rango/100)
            $basePower = $this->calculateBasePower($userId, $teamId, $startOfPeriod, $endOfPeriod, $teamsCache);

            // B. Multiplicadores de Mercado
            $attackMult = 1.0;
            $defenseMult = 1.0;

            if (isset($activeBuffs[$teamId])) {
                foreach ($activeBuffs[$teamId] as $buff) {
                    if (str_starts_with($buff->item_code, 'buff_attack')) $attackMult *= $buff->multiplier;
                    if (str_starts_with($buff->item_code, 'buff_defense')) $defenseMult *= $buff->multiplier;
                }
            }

            // --- C. SUMAR AL ATAQUE (Específico de la zona) ---
            // Nota: El MVP NO aplica al ataque, solo defensa.
            $finalAttackPower = $basePower * $attackMult;

            if (!isset($zoneAttacks[$zoneId][$teamId])) $zoneAttacks[$zoneId][$teamId] = 0;
            $zoneAttacks[$zoneId][$teamId] += $finalAttackPower;

            // --- D. SUMAR A DEFENSA GLOBAL (Cuenta para TODAS sus zonas) ---
            $defenseBasePower = $basePower;

            // Aplicar MVP x2 solo en defensa
            if (isset($luckyMembers[$teamId]) && $luckyMembers[$teamId] == $userId) {
                $defenseBasePower *= 2;
                // $this->info("   🍀 MVP Defensivo en equipo $teamId (User $userId)");
            }

            $finalDefensePower = $defenseBasePower * $defenseMult;

            if (!isset($teamGlobalDefense[$teamId])) $teamGlobalDefense[$teamId] = 0;
            $teamGlobalDefense[$teamId] += $finalDefensePower;
        }

        // 5. RESOLVER BATALLAS POR ZONA
        foreach ($zones as $zone) {
            $this->line("--- Analizando {$zone->name} ---");

            // --- DEFENSOR (Dueño Actual) ---
            $currentOwnerId = $zone->team_id;
            // Su defensa es la suma global de actividad de su equipo
            $defensePower = isset($teamGlobalDefense[$currentOwnerId]) ? round($teamGlobalDefense[$currentOwnerId]) : 0;
            $ownerName = $zone->team ? $zone->team->name : 'Zona Neutral';

            // --- ATACANTES ---
            $attackers = $zoneAttacks[$zone->id] ?? [];

            // Eliminar fuego amigo (si el dueño se atacó a sí mismo por error)
            if (isset($attackers[$currentOwnerId])) unset($attackers[$currentOwnerId]);

            // Si no hay ataques y ya tiene dueño, no pasa nada
            if (empty($attackers) && $currentOwnerId) continue;

            // Ordenar atacantes por fuerza
            arsort($attackers);

            // Mejor Atacante
            $bestAttackerId = array_key_first($attackers);
            $bestAttackerPower = 0;
            $bestAttackerName = "Nadie";

            if ($bestAttackerId) {
                $bestAttackerPower = round($attackers[$bestAttackerId]);
                $bestAttackerName = $teamsCache[$bestAttackerId]->name;
            }

            // Datos para el Desglose (Los 3 siguientes mejores atacantes)
            $otherAttackers = $attackers;
            unset($otherAttackers[$bestAttackerId]);
            $topOthers = array_slice($otherAttackers, 0, 3, true);

            // --- RESULTADO DEL COMBATE ---
            if ($bestAttackerPower > $defensePower) {
                // === CONQUISTA ===
                $diff = $bestAttackerPower - $defensePower;
                $oldOwnerName = $currentOwnerId ? $ownerName : 'Zona Neutral';

                // Actualizar DB
                $zone->team_id = $bestAttackerId;
                $zone->save();
                $changesCount++;

                // 1. Generar Narrativa
                $narrative = $this->generateNarrative('conquest', [
                    'winner' => $bestAttackerName,
                    'loser'  => $oldOwnerName,
                    'zone'   => $zone->name,
                    'power'  => $bestAttackerPower,
                    'diff'   => $diff
                ]);

                // 2. Añadir Desglose de Puntos
                $breakdown = "\n\n📊 **REPORTE DE BATALLA:**";
                $breakdown .= "\n• 👑 **{$bestAttackerName}** (Ataque): **{$bestAttackerPower}** pts";
                $breakdown .= "\n• 🛡️ {$oldOwnerName} (Defensa): {$defensePower} pts";

                if (!empty($topOthers)) {
                    foreach ($topOthers as $tId => $dmg) {
                        $tName = $teamsCache[$tId]->name ?? 'Desconocido';
                        $breakdown .= "\n• ⚔️ {$tName}: " . round($dmg) . " pts";
                    }
                }

                $narrative['content'] .= $breakdown;

                WarNews::create([
                    'title'   => $narrative['title'],
                    'content' => $narrative['content'],
                    'type'    => 'conquest'
                ]);

                $this->info("🚩 {$zone->name}: GANA {$bestAttackerName} ($bestAttackerPower) vs $oldOwnerName ($defensePower)");

            } else {
                // === DEFENSA EXITOSA ===
                if ($bestAttackerPower > 0) {
                    $diff = $defensePower - $bestAttackerPower;

                    // Solo generamos noticia en consola, pero si quieres guardarla en BD, descomenta abajo:
                    $narrative = $this->generateNarrative('defense', [
                        'winner' => $ownerName,
                        'loser'  => $bestAttackerName,
                        'zone'   => $zone->name,
                        'power'  => $defensePower,
                        'diff'   => $diff
                    ]);

                    $breakdown = "\n\n📊 **REPORTE DE BATALLA:**";
                    $breakdown .= "\n• 🛡️ **{$ownerName}** (Defensa): **{$defensePower}** pts";
                    $breakdown .= "\n• 💥 {$bestAttackerName} (Ataque): {$bestAttackerPower} pts";

                    if (!empty($topOthers)) {
                        foreach ($topOthers as $tId => $dmg) {
                            $tName = $teamsCache[$tId]->name ?? 'Desconocido';
                            $breakdown .= "\n• ⚔️ {$tName}: " . round($dmg) . " pts";
                        }
                    }
                    $narrative['content'] .= $breakdown;

                    WarNews::create([
                        'title' => $narrative['title'],
                        'content' => $narrative['content'],
                        'type' => 'defense'
                    ]);

                    $this->line("🛡️ {$zone->name}: DEFIENDE $ownerName ($defensePower) vs $bestAttackerName ($bestAttackerPower)");
                }
            }
        }

        // 6. LIMPIEZA
        $this->info("🧹 Limpiando tablas...");
        DB::table('conquest_votes')->truncate();
        DB::table('team_chat_messages')->truncate();
        DB::table('team_active_buffs')->truncate();

        $this->newLine();
        $this->info("✅ TURNO FINALIZADO. Zonas cambiadas: $changesCount");
    }

    // --- CÁLCULO DE PODER ---
    private function calculateBasePower($userId, $teamId, $start, $end, $teamsCache)
    {
        $participations = DB::table('assist_user_event')
            ->join('events', 'assist_user_event.event_id', '=', 'events.id')
            ->where('assist_user_event.user_id', $userId)
            ->whereBetween('events.date', [$start, $end])
            ->whereNotNull('assist_user_event.puesto')
            ->select('assist_user_event.puesto', 'events.id as event_id')
            ->get();

        $userPoints = 0;

        foreach ($participations as $part) {
            $eventId = $part->event_id;
            if (!isset($this->eventParticipantsCache[$eventId])) {
                $this->eventParticipantsCache[$eventId] = DB::table('assist_user_event')
                    ->where('event_id', $eventId)->count();
            }

            $pointsTable = $this->getPointsTable($this->eventParticipantsCache[$eventId]);
            $index = ((int)$part->puesto) - 1;

            $userPoints += $pointsTable[$index] ?? 1;
        }

        // Fórmula de Rango: Dividir entre 100 para que se note más
        $teamPoints = $teamsCache[$teamId]->points_x2 ?? 0;
        $rankMultiplier = 1 + ($teamPoints / 100);

        return (10 + $userPoints) * $rankMultiplier;
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

    // --- GENERADOR DE NARRATIVA ---
    private function generateNarrative($type, $data)
    {
        $titles = [];
        $contents = [];

        if ($type === 'conquest') {
            $titles = [
                "CAÍDA DE " . strtoupper($data['zone']),
                "NUEVO ORDEN EN " . strtoupper($data['zone']),
                "INCURSIÓN EXITOSA: " . strtoupper($data['winner']),
                "CAMBIO DE BANDERA EN " . strtoupper($data['zone']),
            ];
            $contents = [
                "Las defensas de **{$data['loser']}** han colapsado. Las tropas de **{$data['winner']}** marchan victoriosas sobre las ruinas.",
                "¡Brutalidad táctica! **{$data['winner']}** ha expulsado a **{$data['loser']}** de la región con una diferencia de {$data['diff']} puntos.",
                "Se ha detectado una firma de energía masiva. **{$data['winner']}** reclama el control absoluto de la zona.",
            ];
        }
        elseif ($type === 'defense') {
            $titles = [
                "MURALLA IMPENETRABLE EN " . strtoupper($data['zone']),
                "ATAQUE REPELIDO: " . strtoupper($data['zone']),
            ];
            $contents = [
                "**{$data['winner']}** se mantiene firme. El asalto de **{$data['loser']}** se estrelló contra sus defensas.",
                "A pesar de los intentos de **{$data['loser']}**, la zona sigue bajo el control de **{$data['winner']}**.",
            ];
        }

        return [
            'title'   => $titles[array_rand($titles)],
            'content' => $contents[array_rand($contents)]
        ];
    }
}
