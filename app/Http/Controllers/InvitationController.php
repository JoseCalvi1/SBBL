<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Team;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function accept(Invitation $invitation)
{
    $team = $invitation->team;

    // 1. Condición de ventana de fichajes:
    // Los equipos con 0 puntos (o recién creados) siempre pueden recibir miembros.
    // Para el resto, solo en los primeros 15 días de Septiembre (9), Octubre (10), Diciembre (12) y Marzo (3).
    $esEquipoNuevo = ($team->points_x3 ?? 0) == 0;
    $mesesPermitidos = [9, 10, 12, 3];
    $estaEnPeriodoFichajes = in_array(now()->month, $mesesPermitidos) && now()->day <= 15;

    if (!$esEquipoNuevo && !$estaEnPeriodoFichajes) {
        return redirect()->back()->with('error', 'El mercado de fichajes está cerrado. Solo puedes unirte a equipos existentes durante las primeras semanas del split o de apertura.');
    }

    if ($team->members->count() >= 6) {
        return redirect()->back()->with('error', 'El equipo ya tiene el número máximo de miembros.');
    }

    // Verificar si el usuario ya es miembro de otro equipo
    $user = $invitation->user;
    if ($user->teams->isNotEmpty()) {
        // Eliminar la asociación con el equipo anterior
        foreach ($user->teams as $userTeam) {
            $userTeam->members()->detach($user->id);
        }
    }

    $invitation->update(['accepted' => true]);

    $team->members()->attach($invitation->user_id, ['is_captain' => 0]);

    $invitation->delete(); // Eliminar la invitación después de aceptarla

    return redirect()->route('equipos.show', $team)
        ->with('success', 'Has aceptado la invitación.');
}

    public function reject(Invitation $invitation)
    {
        $invitation->delete(); // Eliminar la invitación al rechazarla

        return redirect()->back()->with('success', 'Has rechazado la invitación.');
    }

}
