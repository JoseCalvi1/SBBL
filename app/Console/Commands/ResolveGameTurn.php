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
    protected $description = 'Resuelve el turno: Defensa Global con MVP x2 (Solo Defensivo)';

    private $eventParticipantsCache = [];

    public function handle()
    {
        // 1. CONTROL DE SEMANA
        if (Carbon::now()->weekOfYear % 2 == 0) {
            $this->info("📅 Semana IMPAR (Mantenimiento).");
            return;
        }

        $this->info('⚔️  INICIANDO RESOLUCIÓN DEL TURNO...');

        $zones = Zone::all();
        $teamsCache = Team::all()->keyBy('id');
        $changesCount = 0;

        $startOfPeriod = Carbon::now()->subWeek()->startOfWeek()->format('Y-m-d');
        $endOfPeriod   = Carbon::now()->endOfWeek()->format('Y-m-d');

        // Cargar Buffs de Mercado
        $activeBuffs = DB::table('team_active_buffs')
            ->where('expires_at', '>', now())
            ->get()
            ->groupBy('team_id');

        // 3. OBTENER TODOS LOS VOTOS Y SELECCIONAR MVP
        $allVotes = DB::table('conquest_votes')->get();
        $teamGlobalDefense = [];
        $zoneAttacks = [];

        // --- SELECCIÓN DEL MIEMBRO ALEATORIO (MVP) POR EQUIPO ---
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

        // 4. CALCULAR PODER (Separando Ataque y Defensa)
        foreach ($allVotes as $vote) {
            $teamId = $vote->team_id;
            $userId = $vote->user_id;
            $zoneId = $vote->zone_id;

            // A. Calcular Base (Igual para todos)
            $basePower = $this->calculateBasePower($userId, $teamId, $startOfPeriod, $endOfPeriod, $teamsCache);

            // B. Calcular Multiplicadores de Mercado
            $attackMult = 1.0;
            $defenseMult = 1.0;

            if (isset($activeBuffs[$teamId])) {
                foreach ($activeBuffs[$teamId] as $buff) {
                    if (str_starts_with($buff->item_code, 'buff_attack')) $attackMult *= $buff->multiplier;
                    if (str_starts_with($buff->item_code, 'buff_defense')) $defenseMult *= $buff->multiplier;
                }
            }

            // --- LÓGICA DE ATAQUE (SIN x2) ---
            $finalAttackPower = $basePower * $attackMult;

            if (!isset($zoneAttacks[$zoneId][$teamId])) $zoneAttacks[$zoneId][$teamId] = 0;
            $zoneAttacks[$zoneId][$teamId] += $finalAttackPower;


            // --- LÓGICA DE DEFENSA (CON x2 MVP) ---
            // El MVP cuenta doble SOLAMENTE para la "bolsa de vida" del equipo
            $defenseBasePower = $basePower;

            if (isset($luckyMembers[$teamId]) && $luckyMembers[$teamId] == $userId) {
                $defenseBasePower *= 2;
                // $this->line("🛡️ MVP Defensivo detectado en Equipo $teamId");
            }

            $finalDefensePower = $defenseBasePower * $defenseMult;

            if (!isset($teamGlobalDefense[$teamId])) $teamGlobalDefense[$teamId] = 0;
            $teamGlobalDefense[$teamId] += $finalDefensePower;
        }

        // 5. RESOLVER BATALLAS
        foreach ($zones as $zone) {
            $this->line("--- Analizando {$zone->name} ---");

            // --- PODER DEL DEFENSOR (Dueño Actual) ---
            $currentOwnerId = $zone->team_id;

            // Su "Vida" es la Defensa Global acumulada
            $defensePower = isset($teamGlobalDefense[$currentOwnerId]) ? round($teamGlobalDefense[$currentOwnerId]) : 0;
            $ownerName = $zone->team ? $zone->team->name : 'Zona Neutral';

            // --- PODER DE LOS ATACANTES ---
            $attackers = $zoneAttacks[$zone->id] ?? [];

            // Quitar al dueño de los atacantes (auto-ataque)
            if (isset($attackers[$currentOwnerId])) unset($attackers[$currentOwnerId]);

            // Si no hay ataques y hay dueño, siguiente
            if (empty($attackers) && $currentOwnerId) continue;

            // Ordenar atacantes
            arsort($attackers);

            $bestAttackerId = array_key_first($attackers);
            $bestAttackerPower = 0;
            $bestAttackerName = "Nadie";

            if ($bestAttackerId) {
                $bestAttackerPower = round($attackers[$bestAttackerId]);
                $bestAttackerName = $teamsCache[$bestAttackerId]->name;
            }

            // --- INFO RIVALES (Noticias) ---
            $rivalsText = "";
            $otherAttackers = $attackers;
            unset($otherAttackers[$bestAttackerId]);
            $topOthers = array_slice($otherAttackers, 0, 2, true);

            if (!empty($topOthers)) {
                $parts = [];
                foreach ($topOthers as $tId => $dmg) {
                    $tName = $teamsCache[$tId]->name ?? 'Unknown';
                    $parts[] = "$tName (" . round($dmg) . ")";
                }
                $rivalsText = "\n⚔️ Otros frentes: " . implode(" | ", $parts);
            }

            // --- RESULTADO DEL COMBATE ---
            if ($bestAttackerPower > $defensePower) {
                // CONQUISTA 🚩
                $diff = $bestAttackerPower - $defensePower;
                $oldOwnerName = $currentOwnerId ? $ownerName : 'Nadie';

                // Actualizar DB
                $zone->team_id = $bestAttackerId;
                $zone->save();
                $changesCount++;

                // Generar Narrativa Épica
                $narrative = $this->generateNarrative('conquest', [
                    'winner' => $bestAttackerName,
                    'loser'  => $oldOwnerName,
                    'zone'   => $zone->name,
                    'power'  => $bestAttackerPower,
                    'diff'   => $diff
                ]);

                // Añadir info de rivales si existe
                if ($rivalsText) $narrative['content'] .= "\n\n" . $rivalsText;

                WarNews::create([
                    'title'   => $narrative['title'],
                    'content' => $narrative['content'],
                    'type'    => 'conquest'
                ]);

                $this->info("🚩 {$zone->name}: GANA {$bestAttackerName}");

            } else {
                // DEFENSA 🛡️
                if ($bestAttackerPower > 0) {
                    $diff = $defensePower - $bestAttackerPower;

                    // Generar Narrativa de Defensa
                    $narrative = $this->generateNarrative('defense', [
                        'winner' => $ownerName, // El ganador es el dueño
                        'loser'  => $bestAttackerName, // El perdedor es el atacante
                        'zone'   => $zone->name,
                        'power'  => $defensePower,
                        'diff'   => $diff
                    ]);

                    if ($rivalsText) $narrative['content'] .= "\n\n" . $rivalsText;

                    WarNews::create([
                        'title'   => $narrative['title'],
                        'content' => $narrative['content'],
                        'type'    => 'defense'
                    ]);

                    $this->line("🛡️ {$zone->name}: DEFIENDE $ownerName");
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

    // --- CÁLCULO DE PODER BASE ---
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

    // ==========================================
    // 🎭 EL CEREBRO DE LA PERSONALIDAD
    // ==========================================
    private function generateNarrative($type, $data)
    {
        $titles = [];
        $contents = [];

        if ($type === 'conquest') {
            // Títulos variados
            $titles = [
                "CAÍDA DE " . strtoupper($data['zone']),
                "NUEVO ORDEN EN " . strtoupper($data['zone']),
                "INCURSIÓN EXITOSA: " . strtoupper($data['winner']),
                "CAMBIO DE BANDERA EN " . strtoupper($data['zone']),
                "REPORTE DE ZONA: " . strtoupper($data['zone']) . " PERDIDA",
            ];

            // Contenidos con "sabor"
            $contents = [
                "Las defensas de **{$data['loser']}** han colapsado. Las tropas de **{$data['winner']}** marchan victoriosas sobre las ruinas con un poder devastador de **{$data['power']}**.",
                "¡Brutalidad táctica! **{$data['winner']}** ha expulsado a **{$data['loser']}** de la región. La diferencia de poder ({$data['diff']}) no dejó lugar a dudas.",
                "Se ha detectado una firma de energía masiva. **{$data['winner']}** reclama el control absoluto de la zona, dejando a **{$data['loser']}** sin opciones.",
                "Informe de Batalla #".rand(1000,9999).": **{$data['winner']}** toma el sector. La resistencia de **{$data['loser']}** fue inútil ante una ofensiva de **{$data['power']}** puntos.",
                "El mapa se reescribe. **{$data['winner']}** planta su estandarte sobre lo que antes pertenecía a **{$data['loser']}**."
            ];
        }
        elseif ($type === 'defense') {
            $titles = [
                "MURALLA IMPENETRABLE EN " . strtoupper($data['zone']),
                "ATAQUE REPELIDO: " . strtoupper($data['zone']),
                "RESISTENCIA HEROICA DE " . strtoupper($data['winner']),
            ];

            $contents = [
                "**{$data['winner']}** se mantiene firme. El asalto de **{$data['loser']}** se estrelló contra una defensa global de **{$data['power']}** puntos.",
                "A pesar de los intentos de **{$data['loser']}**, la zona sigue bajo el estricto control de **{$data['winner']}**. Diferencia táctica: {$data['diff']}.",
                "Sistemas defensivos al 100%. **{$data['winner']}** niega el acceso a **{$data['loser']}** y mantiene la soberanía del territorio."
            ];
        }

        // Elegir aleatoriamente
        return [
            'title'   => $titles[array_rand($titles)],
            'content' => $contents[array_rand($contents)]
        ];
    }
}
