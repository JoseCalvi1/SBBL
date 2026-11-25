<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TreasuryLog;
use Illuminate\Http\Request;

class QualifierController extends Controller
{
    public function index(Request $request)
    {
        $showAllSeasons = $request->boolean('all_seasons');
        $seasonStart = config('sbbl.season_start', '2026-09-01');

        $query = Event::with(['region', 'province', 'users.profile.region'])
            ->where('beys', 'clasificatorio')
            ->where('status', '!=', 'INVALID');

        if (!$showAllSeasons) {
            $query->whereDate('date', '>=', $seasonStart);
        }

        $qualifiers = $query->orderBy('date', 'asc')->orderBy('time', 'asc')->get();

        $positionOrder = [
            'primero' => 1,
            'segundo' => 2,
            'tercero' => 3,
            'cuarto'  => 4,
            'quinto'  => 5,
        ];

        $eventIds = $qualifiers->pluck('id')->all();

        $revenuesByEvent = TreasuryLog::whereIn('event_id', $eventIds)
            ->where('type', 'ingreso')
            ->where('status', 'completado')
            ->selectRaw('event_id, SUM(gross_amount) as gross_total, SUM(net_amount) as net_total, COUNT(*) as payments_count')
            ->groupBy('event_id')
            ->get()
            ->keyBy('event_id');

        $qualifiedList = collect();
        $uniqueParticipantIds = [];
        $totalNetRevenue = 0.0;

        foreach ($qualifiers as $ev) {
            $activeUsers = $ev->users->filter(function ($u) {
                $puesto = strtolower(trim((string) ($u->pivot->puesto ?? '')));
                return !in_array($puesto, ['dropped', 'nopresentado'], true);
            });

            foreach ($activeUsers as $u) {
                $uniqueParticipantIds[$u->id] = true;
            }

            $top5 = $activeUsers->filter(function ($u) use ($positionOrder) {
                $puesto = strtolower(trim((string) ($u->pivot->puesto ?? '')));
                return isset($positionOrder[$puesto]);
            })->sortBy(function ($u) use ($positionOrder) {
                return $positionOrder[strtolower(trim((string) $u->pivot->puesto))];
            })->values();

            $ev->active_participants_count = $activeUsers->count();
            $ev->top5 = $top5;
            $ev->finalists = $top5->filter(function ($u) {
                $puesto = strtolower(trim((string) ($u->pivot->puesto ?? '')));
                return in_array($puesto, ['primero', 'segundo'], true);
            })->values();

            $rev = $revenuesByEvent->get($ev->id);
            $ev->gross_revenue = $rev ? (float) $rev->gross_total : 0.0;
            $ev->net_revenue = $rev ? (float) $rev->net_total : 0.0;
            $totalNetRevenue += $ev->net_revenue;

            foreach ($ev->finalists as $finalist) {
                $puesto = strtolower(trim((string) $finalist->pivot->puesto));
                $qualifiedList->push([
                    'user_id'    => $finalist->id,
                    'user_name'  => $finalist->name,
                    'user_email' => $finalist->email,
                    'puesto'     => $puesto,
                    'event_id'   => $ev->id,
                    'event_name' => $ev->name,
                    'event_city' => $ev->city ?: ($ev->region->name ?? '-'),
                    'event_date' => $ev->date,
                ]);
            }
        }

        $totalUniqueParticipants = count($uniqueParticipantIds);
        $totalQualifiedUnique = $qualifiedList->pluck('user_id')->unique()->count();

        return view('admin.qualifiers.index', compact(
            'qualifiers',
            'qualifiedList',
            'totalUniqueParticipants',
            'totalQualifiedUnique',
            'totalNetRevenue',
            'showAllSeasons',
            'seasonStart'
        ));
    }
}
