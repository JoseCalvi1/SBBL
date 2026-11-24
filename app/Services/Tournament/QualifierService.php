<?php

namespace App\Services\Tournament;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class QualifierService
{
    public const MIN_RANKINGS_FOR_DISCOUNT = 6;
    public const FEE_FIRST_ATTEMPT = 15.00;
    public const FEE_RETRY_ATTEMPT = 5.00;
    public const FEE_NO_RANKINGS = 25.00;

    /**
     * Cuenta los torneos de ranking jugados esta temporada por el usuario
     * (no futuros, no drop, no nopresentado, con posición o "participado").
     */
    public function countSeasonRankingsPlayed(int $userId): int
    {
        $seasonStart = config('sbbl.season_start', '2026-09-01');
        $today = Carbon::now()->format('Y-m-d');

        return (int) DB::table('assist_user_event')
            ->join('events', 'assist_user_event.event_id', '=', 'events.id')
            ->where('assist_user_event.user_id', $userId)
            ->whereIn('events.beys', ['ranking', 'rankingplus'])
            ->whereDate('events.date', '>=', $seasonStart)
            ->whereDate('events.date', '<=', $today)
            ->whereNotIn('events.status', ['OPEN', 'INVALID'])
            ->whereNotNull('assist_user_event.puesto')
            ->whereRaw("TRIM(assist_user_event.puesto) <> ''")
            ->whereRaw("LOWER(TRIM(assist_user_event.puesto)) NOT IN ('nopresentado', 'dropped')")
            ->distinct('events.id')
            ->count('events.id');
    }

    /**
     * Cuenta los clasificatorios previos en los que ha participado/inscrito el usuario esta temporada
     * (excluyendo el evento actual y excluyendo bajas por drop/nopresentado).
     */
    public function countPreviousQualifiers(int $userId, ?int $excludeEventId = null): int
    {
        $seasonStart = config('sbbl.season_start', '2026-09-01');

        $query = DB::table('assist_user_event')
            ->join('events', 'assist_user_event.event_id', '=', 'events.id')
            ->where('assist_user_event.user_id', $userId)
            ->where('events.beys', 'clasificatorio')
            ->whereDate('events.date', '>=', $seasonStart)
            ->where('events.status', '!=', 'INVALID')
            ->where(function ($q) {
                $q->whereNull('assist_user_event.puesto')
                  ->orWhereRaw("LOWER(TRIM(assist_user_event.puesto)) NOT IN ('nopresentado', 'dropped')");
            });

        if ($excludeEventId !== null) {
            $query->where('events.id', '!=', $excludeEventId);
        }

        return (int) $query->distinct('events.id')->count('events.id');
    }

    /**
     * Comprueba si el usuario ya está clasificado al Nacional (Top 2 / Finalista en algún clasificatorio).
     */
    public function getQualificationRecord(int $userId, ?int $excludeEventId = null): ?object
    {
        $seasonStart = config('sbbl.season_start', '2026-09-01');

        $query = DB::table('assist_user_event')
            ->join('events', 'assist_user_event.event_id', '=', 'events.id')
            ->where('assist_user_event.user_id', $userId)
            ->where('events.beys', 'clasificatorio')
            ->whereDate('events.date', '>=', $seasonStart)
            ->where('events.status', '!=', 'INVALID')
            ->whereRaw("LOWER(TRIM(assist_user_event.puesto)) IN ('primero', 'segundo')")
            ->select(
                'events.id as event_id',
                'events.name as event_name',
                'events.date as event_date',
                'assist_user_event.puesto'
            )
            ->orderBy('events.date', 'asc');

        if ($excludeEventId !== null) {
            $query->where('events.id', '!=', $excludeEventId);
        }

        return $query->first();
    }

    /**
     * Evalúa elegibilidad y tarifa exacta para un jugador en un Clasificatorio.
     */
    public function evaluateForUser(Event $event, int $userId): array
    {
        $qualification = $this->getQualificationRecord($userId, $event->id);
        $rankingsPlayed = $this->countSeasonRankingsPlayed($userId);
        $previousQualifiers = $this->countPreviousQualifiers($userId, $event->id);

        if ($qualification) {
            return [
                'is_qualifier'         => true,
                'can_register'         => false,
                'already_qualified'    => true,
                'qualified_event_name' => $qualification->event_name,
                'qualified_position'   => $qualification->puesto,
                'rankings_played'      => $rankingsPlayed,
                'previous_qualifiers'  => $previousQualifiers,
                'fee'                  => null,
                'tier_label'           => "Ya clasificado al Nacional ({$qualification->event_name})",
            ];
        }

        if ($rankingsPlayed >= self::MIN_RANKINGS_FOR_DISCOUNT) {
            if ($previousQualifiers >= 1) {
                $attempt = $previousQualifiers + 1;
                $fee = self::FEE_RETRY_ATTEMPT;
                $label = "{$rankingsPlayed}/" . self::MIN_RANKINGS_FOR_DISCOUNT . " rankings jugados · {$attempt}.º intento";
            } else {
                $fee = self::FEE_FIRST_ATTEMPT;
                $label = "{$rankingsPlayed}/" . self::MIN_RANKINGS_FOR_DISCOUNT . " rankings jugados · 1.º clasificatorio";
            }
        } else {
            $fee = self::FEE_NO_RANKINGS;
            $label = "{$rankingsPlayed}/" . self::MIN_RANKINGS_FOR_DISCOUNT . " rankings jugados (tarifa general)";
        }

        return [
            'is_qualifier'         => true,
            'can_register'         => true,
            'already_qualified'    => false,
            'qualified_event_name' => null,
            'qualified_position'   => null,
            'rankings_played'      => $rankingsPlayed,
            'previous_qualifiers'  => $previousQualifiers,
            'fee'                  => $fee,
            'tier_label'           => $label,
        ];
    }
}
