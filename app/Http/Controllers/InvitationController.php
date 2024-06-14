<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Team;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function accept(Invitation $invitation)
    {
        if ($invitation->team->members->count() >= 5) {
            return redirect()->back()->with('error', 'El equipo ya tiene el número máximo de miembros.');
        }

        // Verificar si el usuario ya es miembro de otro equipo
        $user = $invitation->user;
        if ($user->teams->isNotEmpty()) {
            // Eliminar la asociación con el equipo anterior
            foreach ($user->teams as $team) {
                $team->members()->detach($user->id);
            }
        }

        $invitation->update(['accepted' => true]);

        $team = $invitation->team;
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
