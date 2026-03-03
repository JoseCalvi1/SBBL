<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Models\User; // <--- IMPORTANTE: Importamos el modelo User
use Illuminate\Console\Command;
use App\Models\Zone;
use App\Models\WarNews;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ResolveGameTurn extends Command
{
    protected $signature = 'game:resolve';
    protected $description = 'Resuelve el turno: Defensa Global, MVP Defensivo y Reportes Detallados con Facciones';

    private $eventParticipantsCache = [];

    public function handle()
    {
        // 1. CONTROL DE CICLO (SEMANAS PARES)
        if (Carbon::now()->weekOfYear % 2 != 0) {
            $this->info("📅 Semana IMPAR (Mantenimiento). El turno continúa.");
            return;
        }

        $this->info('⚔️  INICIANDO RESOLUCIÓN DEL TURNO (ANIVERSARIO)...');

        $zones = Zone::all();
        $teamsCache = Team::all()->keyBy('id');
        $changesCount = 0;

        $discordConquests = [];

        // Rango de fechas INTELIGENTE
        $lastTurn = WarNews::latest()->first();
        if ($lastTurn) {
            $startOfPeriod = $lastTurn->created_at->addDay()->format('Y-m-d');
        } else {
            $startOfPeriod = Carbon::now()->subWeek()->startOfWeek()->format('Y-m-d');
        }
        $endOfPeriod = Carbon::now()->endOfWeek()->format('Y-m-d');

        // 2. CARGAR BUFFS
        $activeBuffs = DB::table('team_active_buffs')
            ->where('expires_at', '>', now())
            ->get()
            ->groupBy('team_id');

        // 3. OBTENER VOTOS Y MVP
        $allVotes = DB::table('conquest_votes')->get();
        $teamGlobalDefense = [];
        $zoneAttacks = [];

        $teamVoters = [];
        foreach ($allVotes as $vote) {
            $teamVoters[$vote->team_id][] = $vote->user_id;
        }

        $luckyMembers = [];
        foreach ($teamVoters as $tId => $userIds) {
            $uniqueUsers = array_unique($userIds);
            if (!empty($uniqueUsers)) {
                $luckyMembers[$tId] = $uniqueUsers[array_rand($uniqueUsers)];
            }
        }

        // 4. CALCULAR PODER CON FACCIONES
        foreach ($allVotes as $vote) {
            $teamId = $vote->team_id;
            $userId = $vote->user_id;
            $zoneId = $vote->zone_id;

            // Buscamos la facción del usuario
            $user = User::find($userId);
            $faction = $user ? $user->faction : null;

            $basePower = $this->calculateBasePower($userId, $teamId, $startOfPeriod, $endOfPeriod, $teamsCache);

            // --- BUFF BAKUTEN: +2 Poder Base Fijo ---
            if ($faction === 'bakuten') {
                $basePower += 2;
            }

            $attackMult = 1.0;
            $defenseMult = 1.0;

            if (isset($activeBuffs[$teamId])) {
                foreach ($activeBuffs[$teamId] as $buff) {
                    if (str_starts_with($buff->item_code, 'buff_attack')) $attackMult *= $buff->multiplier;
                    if (str_starts_with($buff->item_code, 'buff_defense')) $defenseMult *= $buff->multiplier;
                }
            }

            // --- LÓGICA DE ATAQUE ---
            $finalAttackPower = $basePower * $attackMult;

            // --- BUFF BURST: 20% de probabilidad de x1.2 en ataque ---
            if ($faction === 'burst' && rand(1, 100) <= 20) {
                $finalAttackPower *= 1.2;
            }

            if (!isset($zoneAttacks[$zoneId][$teamId])) $zoneAttacks[$zoneId][$teamId] = 0;
            $zoneAttacks[$zoneId][$teamId] += $finalAttackPower;

            // --- LÓGICA DE DEFENSA ---
            $defenseBasePower = $basePower;

            // MVP Defensivo (x2)
            if (isset($luckyMembers[$teamId]) && $luckyMembers[$teamId] == $userId) {
                $defenseBasePower *= 2;
            }

            // --- BUFF METAL: 33% de probabilidad de x1.1 en defensa ---
            if ($faction === 'metal' && rand(1, 100) <= 33) {
                $defenseBasePower *= 1.1;
            }

            $finalDefensePower = $defenseBasePower * $defenseMult;

            if (!isset($teamGlobalDefense[$teamId])) $teamGlobalDefense[$teamId] = 0;
            $teamGlobalDefense[$teamId] += $finalDefensePower;
        }

        // 5. RESOLVER BATALLAS
        foreach ($zones as $zone) {
            $this->line("--- Analizando {$zone->name} ---");

            $currentOwnerId = $zone->team_id;
            $defensePower = isset($teamGlobalDefense[$currentOwnerId]) ? round($teamGlobalDefense[$currentOwnerId]) : 0;
            $ownerName = $zone->team ? $zone->team->name : 'Zona Neutral';

            $attackers = $zoneAttacks[$zone->id] ?? [];
            if (isset($attackers[$currentOwnerId])) unset($attackers[$currentOwnerId]);

            if (empty($attackers) && $currentOwnerId) continue;

            arsort($attackers);
            $bestAttackerId = array_key_first($attackers);
            $bestAttackerPower = 0;
            $bestAttackerName = "Nadie";

            if ($bestAttackerId) {
                $bestAttackerPower = round($attackers[$bestAttackerId]);
                $bestAttackerName = $teamsCache[$bestAttackerId]->name;
            }

            $otherAttackers = $attackers;
            unset($otherAttackers[$bestAttackerId]);
            $topOthers = array_slice($otherAttackers, 0, 3, true);

            if ($bestAttackerPower > $defensePower) {
                $diff = $bestAttackerPower - $defensePower;
                $oldOwnerName = $currentOwnerId ? $ownerName : 'Zona Neutral';

                $zone->team_id = $bestAttackerId;
                $zone->save();
                $changesCount++;

                $discordConquests[] = "🚩 **{$zone->name}**: {$oldOwnerName} ➔ **{$bestAttackerName}**";

                $narrative = $this->generateNarrative('conquest', [
                    'winner' => $bestAttackerName,
                    'loser'  => $oldOwnerName,
                    'zone'   => $zone->name,
                    'power'  => $bestAttackerPower,
                    'diff'   => $diff
                ]);

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

                $this->info("🚩 {$zone->name}: GANA {$bestAttackerName}");

            } else {
                if ($bestAttackerPower > 0) {
                    $this->line("🛡️ {$zone->name}: DEFIENDE $ownerName ($defensePower)");
                }
            }
        }

        // 6. ENVIAR REPORTE A DISCORD
        $this->sendDiscordReport($changesCount, $discordConquests);

        // 7. LIMPIEZA
        $this->info("🧹 Limpiando tablas...");
        DB::table('conquest_votes')->truncate();
        DB::table('team_chat_messages')->truncate();
        DB::table('team_active_buffs')->truncate();

        $this->newLine();
        $this->info("✅ TURNO FINALIZADO. Zonas cambiadas: $changesCount");
    }

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

    private function generateNarrative($type, $data)
    {
        $titles = [];
        $contents = [];

        if ($type === 'conquest') {
            $titles = ["CAÍDA DE " . strtoupper($data['zone']), "NUEVO ORDEN EN " . strtoupper($data['zone']), "INCURSIÓN EXITOSA: " . strtoupper($data['winner']), "CAMBIO DE BANDERA EN " . strtoupper($data['zone'])];
            $contents = ["Las defensas de **{$data['loser']}** han colapsado. Las tropas de **{$data['winner']}** marchan victoriosas sobre las ruinas.", "¡Brutalidad táctica! **{$data['winner']}** ha expulsado a **{$data['loser']}** de la región con una diferencia de {$data['diff']} puntos.", "Se ha detectado una firma de energía masiva. **{$data['winner']}** reclama el control absoluto de la zona."];
        } else {
            $titles = ["MURALLA EN " . strtoupper($data['zone'])];
            $contents = ["**{$data['winner']}** se mantiene firme."];
        }

        return ['title' => $titles[array_rand($titles)], 'content' => $contents[array_rand($contents)]];
    }

    private function sendDiscordReport($changesCount, $conquests)
    {
        $webhookUrl = config('services.discord.warfeed');
        if (!$webhookUrl) return;

        $embedDescription = "El ciclo de guerra ha terminado. Se han registrado **{$changesCount} cambios de territorio**.";
        if (!empty($conquests)) {
            $embedDescription .= "\n\n**🔥 CONQUISTAS DESTACADAS:**\n" . implode("\n", $conquests);
        } else {
            $embedDescription .= "\n\n💤 *El mapa se mantiene estable. Ninguna zona ha cambiado de manos.*";
        }
        $embedDescription .= "\n\n📡 [Ver Reporte Completo en la Web](https://conquista.sbbl.es)";

        try {
            Http::post($webhookUrl, [
                'content' => "@everyone 📢 **RESULTADOS DEL TURNO**",
                'embeds' => [[
                    'title' => '🌍 Actualización del Mapa Táctico',
                    'description' => $embedDescription,
                    'color' => hexdec('ff0000'),
                    'timestamp' => now()->toIso8601String(),
                    'footer' => ['text' => 'Sistema WAR-NET AI v2.0']
                ]],
                'allowed_mentions' => ['parse' => ['everyone']],
            ]);
        } catch (\Exception $e) {
            Log::error("Error enviando reporte a Discord: " . $e->getMessage());
        }
    }
}
