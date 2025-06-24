<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\User;
use App\Models\Invitation;

class TeamController extends Controller
{
    // Mostrar todos los equipos
    public function index()
    {
        $equipos = Team::where('status', 'accepted')->get();
        return view('equipos.index', compact('equipos'));
    }

    public function indexAdmin()
    {
        $equipos = Team::orderBy('status', 'asc')->orderBy('name', 'asc')->get();
        return view('equipos.indexAdmin', compact('equipos'));
    }

    // Mostrar el formulario para crear un nuevo equipo
    public function create()
    {
        return view('equipos.create');
    }

    // Almacenar un nuevo equipo
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Validación de la imagen
            // Agrega aquí validaciones adicionales según tus necesidades
        ]);

        // Procesamiento de la imagen
        if ($request->hasFile('image')) {
            $imageData = base64_encode(file_get_contents($request->file('image')));
        } else {
            $imageData = null;
        }

        if ($request->hasFile('logo')) {
            $logoData = base64_encode(file_get_contents($request->file('logo')));
        } else {
            $logoData = null;
        }

        // Obtenemos el ID del usuario autenticado y lo asignamos como captain_id del equipo
        $userId = Auth::user()->id;

        // Crear el equipo con los datos proporcionados
        $equipo = Team::create([
            'name' => $request->name,
            'description' => $request->description,
            'captain_id' => $userId,
            'image' => $imageData,
            'logo' => $logoData,
            'status' => 'pending',
            // Añade aquí más campos si es necesario
        ]);

        // Añadir el usuario como miembro y capitán del equipo en la tabla intermedia
        $equipo->members()->attach($userId, ['is_captain' => true]);

        return redirect()->route('equipos.index')
            ->with('success', 'Equipo creado exitosamente.');
    }

    public function show(Team $equipo)
    {
        // Obtener los miembros del equipo
        $miembros = $equipo->members;

        // Obtener todos los usuarios disponibles para agregar al equipo (excepto los que ya son miembros)
        $users = User::whereNotIn('id', $miembros->pluck('id'))->orderBy('name', 'ASC')->get();

        $totalPoints = $miembros->sum(function ($miembro) {
            return $miembro->profile->points_x2 ?? 0;
        });

        return view('equipos.show', compact('equipo', 'miembros', 'users', 'totalPoints'));
    }

    public function ranking_teams()
    {
        // Obtener los equipos ordenados por puntuación de forma descendente
        $teams = Team::orderBy('points_x2', 'desc')->where('points_x2','>',0)->get();

        // Pasar los datos a la vista 'ranking'
        return view('equipos.ranking', compact('teams'));
    }

    // Mostrar el formulario para editar un equipo
    public function edit(Team $equipo)
    {
        return view('equipos.edit', compact('equipo'));
    }

    // Actualizar un equipo
    public function update(Request $request, Team $equipo)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // Agrega aquí validaciones adicionales según tus necesidades
        ]);

        // Almacenar las imágenes actuales antes de actualizar el equipo
        $currentImage = $equipo->image;
        $currentLogo = $equipo->logo;

        // Procesar la imagen si se proporciona un nuevo archivo
        if ($request->hasFile('image')) {
            $imageData = base64_encode(file_get_contents($request->file('image')));
            $equipo->image = $imageData;
        }

        // Procesar el logo si se proporciona un nuevo archivo
        if ($request->hasFile('logo')) {
            $logoData = base64_encode(file_get_contents($request->file('logo')));
            $equipo->logo = $logoData;
        }

        // Actualizar el equipo con los datos proporcionados
        $equipo->update([
            'name' => $request->name,
            'description' => $request->description,
            // No es necesario actualizar la imagen o el logo aquí
        ]);

        // Restaurar la imagen actual si el formulario se envía sin seleccionar un nuevo archivo de imagen
        if (!$request->hasFile('image') && $currentImage !== null) {
            $equipo->image = $currentImage;
        }

        // Restaurar el logo actual si el formulario se envía sin seleccionar un nuevo archivo de logo
        if (!$request->hasFile('logo') && $currentLogo !== null) {
            $equipo->logo = $currentLogo;
        }

        $equipo->save();

        return redirect()->route('equipos.index')
            ->with('success', 'Equipo actualizado exitosamente.');
    }



    // Eliminar un equipo
    public function destroy(Team $equipo)
    {
        $equipo->delete();

        return redirect()->route('equipos.index')
            ->with('success', 'Equipo eliminado exitosamente.');
    }

    public function addMember(Request $request, Team $equipo)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'is_captain' => 'required|boolean',
        ]);

        // Comenzamos una transacción para asegurar consistencia en caso de error
        DB::transaction(function() use ($request, $equipo) {
            // Verificamos si el nuevo miembro será capitán
            if ($request->is_captain) {
                // Revocamos la capitanía del capitán actual
                $equipo->members()->updateExistingPivot($equipo->captain_id, ['is_captain' => false]);
                // Actualizamos el ID del capitán en el equipo
                $equipo->captain_id = $request->user_id;
                $equipo->save();
            }

            // Añadimos al nuevo miembro
            $equipo->members()->attach($request->user_id, ['is_captain' => $request->is_captain]);
        });

        return redirect()->route('equipos.show', $equipo)
            ->with('success', 'Miembro añadido exitosamente.');
    }


    public function removeMember(Team $equipo, User $user)
    {
        $equipo->members()->detach($user->id);

        return redirect()->route('equipos.show', $equipo)
            ->with('success', 'Miembro eliminado exitosamente.');
    }

    public function changeCaptain(Request $request, Team $equipo, User $miembro)
    {
        DB::transaction(function() use ($equipo, $miembro) {
            // Revocar capitanía del capitán actual
            $equipo->members()->updateExistingPivot($equipo->captain_id, ['is_captain' => false]);

            // Asignar nuevo capitán
            $equipo->members()->updateExistingPivot($miembro->id, ['is_captain' => true]);

            // Actualizar el campo captain_id en el equipo
            $equipo->captain_id = $miembro->id;
            $equipo->save();
        });

        return redirect()->route('equipos.show', $equipo)
            ->with('success', 'Capitán actualizado exitosamente.');
    }

    public function leaveTeam(Request $request, Team $equipo)
    {
        $userId = Auth::user()->id;

        // Verificar que el usuario no es el capitán
        if ($equipo->captain_id == $userId) {
            return redirect()->route('equipos.show', $equipo)
                ->with('error', 'El capitán no puede dejar el equipo.');
        }

        // Remover al usuario del equipo
        $equipo->members()->detach($userId);

        return redirect()->route('equipos.index')
            ->with('success', 'Has dejado el equipo exitosamente.');
    }

    public function sendInvitation(Request $request, Team $equipo)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        Invitation::create([
            'team_id' => $equipo->id,
            'user_id' => $request->user_id,
            'accepted' => null,
        ]);

        return redirect()->route('equipos.show', $equipo)
            ->with('success', 'Invitación enviada exitosamente.');
    }

    public function acceptInvitation(Team $equipo)
    {
        // Verificar si el equipo ya tiene el número máximo de miembros
        if ($equipo->members->count() >= 5) {
            return redirect()->back()->with('error', 'El equipo ya tiene el número máximo de miembros.');
        }

        // Actualizar el estado de la invitación
        $equipo->update(['status' => 'accepted']);

        return redirect()->back()->with('success', 'Invitación aceptada exitosamente.');
    }



}
