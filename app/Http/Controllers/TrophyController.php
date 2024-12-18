<?php
namespace App\Http\Controllers;

use App\Models\Trophy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrophyController extends Controller
{
    // Mostrar la lista de trofeos
    public function index()
    {
        $trophies = Trophy::all();
        return view('trophies.index', compact('trophies'));
    }

    public function show(Trophy $trophy)
    {
        // Pasamos el trofeo específico a la vista
        return view('trophies.show', compact('trophy'));
    }


    // Mostrar el formulario para crear un nuevo trofeo
    public function create()
    {
        return view('trophies.create');
    }

    // Almacenar un nuevo trofeo
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'season' => 'required|string',
        ]);

        Trophy::create($request->all());

        return redirect()->route('trophies.index')->with('success', 'Trofeo creado exitosamente.');
    }

    // Mostrar el formulario de edición de un trofeo
    public function edit(Trophy $trophy)
    {
        return view('trophies.edit', compact('trophy'));
    }

    // Actualizar un trofeo existente
    public function update(Request $request, Trophy $trophy)
    {
        $request->validate([
            'name' => 'required|string',
            'season' => 'required|string',
        ]);

        $trophy->update($request->all());

        return redirect()->route('trophies.index')->with('success', 'Trofeo actualizado exitosamente.');
    }

    // Eliminar un trofeo
    public function destroy(Trophy $trophy)
    {
        $trophy->delete();
        return redirect()->route('trophies.index')->with('success', 'Trofeo eliminado exitosamente.');
    }

    public function assignForm()
    {
        $users = User::all();  // Obtener todos los usuarios
        $trophies = Trophy::all();  // Obtener todos los trofeos
        return view('trophies.assign', compact('users', 'trophies'));
    }

    public function assign(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'trophy_id' => 'required',
        ]);

        // Buscar si ya existe una asignación de este trofeo al usuario
        $existingAssignment = DB::table('profilestrophies')
                                ->where('profiles_id', $request->user_id)
                                ->where('trophies_id', $request->trophy_id)
                                ->first();

        if ($existingAssignment) {
            // Si ya existe, incrementar el contador
            DB::table('profilestrophies')
                ->where('profiles_id', $request->user_id)
                ->where('trophies_id', $request->trophy_id)
                ->update([
                    'count' => DB::raw('count + 1'), // Incrementar el contador
                    'updated_at' => now(),
                ]);
        } else {
            // Si no existe, crear una nueva entrada con count = 1
            DB::table('profilestrophies')->insert([
                'profiles_id' => $request->user_id,
                'trophies_id' => $request->trophy_id,
                'count' => 1, // Inicializar el contador en 1
                'created_at' => now(),
            ]);
        }

        return redirect()->route('trophies.index')->with('success', 'Trofeo asignado correctamente.');
    }

    public function showUserTrophies($userId)
    {
        // Obtener los trofeos asignados a un usuario con la cantidad (count)
        $userTrophies = DB::table('profilestrophies')
            ->join('trophies', 'profilestrophies.trophies_id', '=', 'trophies.id')
            ->where('profilestrophies.profiles_id', $userId)
            ->select('trophies.name', 'trophies.season', 'profilestrophies.count', 'profilestrophies.id')
            ->get();

        $user = User::find($userId); // Obtener los datos del usuario

        return view('trophies.user_trophies', compact('userTrophies', 'user'));
    }

    public function removeAssignment($assignmentId)
    {
        // Eliminar la asignación de trofeo al usuario
        DB::table('profilestrophies')->where('id', $assignmentId)->delete();

        return redirect()->back()->with('success', 'Trofeo eliminado de la asignación.');
    }

    public function usersWithTrophies()
    {
        // Obtener usuarios con sus trofeos asignados (con count de trofeos)
        $users = User::whereHas('trophies')
                    ->with('trophies')
                    ->get();

        return view('trophies.user_trophies', compact('users'));
    }

    public function updateCount(Request $request, $userId, $trophyId)
    {
        // Validar el número de trofeos
        $request->validate([
            'count' => 'required|integer|min:1',
        ]);

        // Actualizar el count en la tabla intermedia 'profilestrophies'
        DB::table('profilestrophies')
            ->where('profiles_id', $userId)
            ->where('trophies_id', $trophyId)
            ->update(['count' => $request->count]);

        return redirect()->route('trophies.usersWithTrophies')->with('success', 'Número de trofeos actualizado correctamente.');
    }

    public function remove($userId, $trophyId)
    {
        // Eliminar el trofeo de la tabla intermedia
        DB::table('profilestrophies')
            ->where('profiles_id', $userId)
            ->where('trophies_id', $trophyId)
            ->delete();

        return redirect()->route('trophies.usersWithTrophies')->with('success', 'Trofeo eliminado correctamente.');
    }





}
