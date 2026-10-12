<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\User;
use App\Models\Invitation;
use App\Models\Region;

class TeamController extends Controller
{
    /**
     * MÉTODOS PRIVADOS DE OPTIMIZACIÓN
     * Comprime y redimensiona la imagen ANTES de pasarla a Base64
     * Esto salva tu memoria RAM y el espacio de tu base de datos.
     */
    private function resizeAndEncodeImage($file, $maxWidth = 800)
    {
        $info = getimagesize($file);
        $width = $info[0];
        $height = $info[1];
        $type = $info[2];

        // Calcular nuevas dimensiones
        $newWidth = $width > $maxWidth ? $maxWidth : $width;
        $newHeight = round($height * ($newWidth / $width));

        $newImg = imagecreatetruecolor($newWidth, $newHeight);

        // Mantener transparencias
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_WEBP) {
            imagealphablending($newImg, false);
            imagesavealpha($newImg, true);
        }

        // Crear imagen desde origen
        switch ($type) {
            case IMAGETYPE_JPEG: $source = imagecreatefromjpeg($file); break;
            case IMAGETYPE_PNG:  $source = imagecreatefrompng($file); break;
            case IMAGETYPE_WEBP: $source = imagecreatefromwebp($file); break;
            default: return base64_encode(file_get_contents($file)); // Fallback
        }

        imagecopyresampled($newImg, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        ob_start();
        if ($type == IMAGETYPE_PNG) {
            imagepng($newImg, null, 8); // Compresión PNG
        } elseif ($type == IMAGETYPE_WEBP) {
            imagewebp($newImg, null, 80); // Calidad WebP
        } else {
            imagejpeg($newImg, null, 80); // Calidad JPG
        }

        $imageString = ob_get_clean();
        imagedestroy($newImg);
        imagedestroy($source);

        return base64_encode($imageString);
    }

    // =========================================================
    // MÉTODOS PÚBLICOS
    // =========================================================

    // Mostrar el nuevo Centro de Mando / Landing de Equipos
    public function index(Request $request)
    {
        $user = Auth::user();
        $regiones = Region::select('id', 'name')->get();
        $regionFilter = $request->query('region', $user->profile->region_id ?? null);

        // 1. ESTADÍSTICAS GLOBALES PARA LA LANDING
        $stats = [
            'total_teams' => Team::where('status', 'accepted')->count(),
            'total_players' => DB::table('team_user')->count(),
            'total_duels' => DB::table('teams_versus')->where('status', 'CLOSED')->where('created_at', '>=', '2026-09-01 00:00:00')->count(),
        ];

        // 2. EQUIPO DEL USUARIO ACTUAL (Para el panel destacado)
        $myTeam = null;
        if ($user && $user->teams->isNotEmpty()) {
            $myTeam = $user->teams->first(); // Asumiendo que el usuario tiene un equipo activo principal
        }

        // 3. TOP 3 EQUIPOS (Para el podio en la landing)
        $topTeams = Team::where('status', 'accepted')
                        ->where('points_x3', '>', 0)
                        ->orderByDesc('points_x3')
                        ->take(3)
                        ->get(['id', 'name', 'logo', 'points_x3']);

        // 4. LISTADO OPTIMIZADO (Paginación vital para la memoria RAM)
        $query = Team::with('captain.profile.region') // Eager loading
                    ->where('status', 'accepted')
                    ->orderBy('name');

        if ($regionFilter && $regionFilter !== 'all') {
            $query->whereHas('captain.profile', function($q) use ($regionFilter) {
                $q->where('region_id', $regionFilter);
            });
        }

        // Usamos paginate en lugar de get() para no reventar la RAM con las imágenes Base64
        $equipos = $query->paginate(12)->appends(['region' => $regionFilter]);

        return view('equipos.index', compact('equipos', 'regiones', 'regionFilter', 'stats', 'myTeam', 'topTeams'));
    }

    public function indexAdmin(Request $request)
{
    // 1. Recoger filtros del Request
    $regionFilter = $request->input('region', 'all');
    $statusFilter = $request->input('status', 'all');

    // 2. Consulta OPTIMIZADA: Solo seleccionamos las columnas de texto, EXCLUYENDO 'image' y 'logo'
    $query = Team::with('captain.profile.region')
                 ->select('id', 'name', 'description', 'status', 'captain_id');

    // 3. Aplicar Filtro de Estado
    if ($statusFilter !== 'all') {
        $query->where('status', $statusFilter);
    }

    // 4. Aplicar Filtro de Región
    if ($regionFilter !== 'all') {
        $query->whereHas('captain.profile', function($q) use ($regionFilter) {
            $q->where('region_id', $regionFilter);
        });
    }

    // 5. Ordenar y Paginar (de 15 en 15)
    $equipos = $query->orderBy('status', 'asc')
                     ->orderBy('name', 'asc')
                     ->paginate(15)
                     ->appends([
                         'region' => $regionFilter,
                         'status' => $statusFilter
                     ]);

    // 6. Obtener regiones para el desplegable
    $regiones = \App\Models\Region::select('id', 'name')->orderBy('name')->get();

    return view('equipos.indexAdmin', compact('equipos', 'regiones', 'regionFilter', 'statusFilter'));
}

    public function create()
    {
        return view('equipos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:teams,name',
            'description' => 'required|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120', // Máx 5MB antes de comprimir
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $imageData = $request->hasFile('image') ? $this->resizeAndEncodeImage($request->file('image'), 1200) : null;
        $logoData = $request->hasFile('logo') ? $this->resizeAndEncodeImage($request->file('logo'), 400) : null;

        $userId = Auth::id();

        $equipo = Team::create([
            'name' => $request->name,
            'description' => $request->description,
            'captain_id' => $userId,
            'image' => $imageData,
            'logo' => $logoData,
            'status' => 'pending',
            'points_x3' => 0 // Aseguramos que empiezan a 0 para el mercado de fichajes
        ]);

        $equipo->members()->attach($userId, ['is_captain' => true]);

        return redirect()->route('equipos.index')
            ->with('success', 'Equipo creado exitosamente. Pendiente de validación arbitral.');
    }

    public function show(Team $equipo)
    {
        // Optimización: Cargar relaciones necesarias de una vez
        $equipo->load('members.profile.region');
        $miembros = $equipo->members;

        // Optimización: Limitar usuarios disponibles para invitar (solo los que no tienen equipo)
        $users = User::whereDoesntHave('teams')->orderBy('name', 'ASC')->get(['id', 'name']);

        $totalPoints = $miembros->sum(function ($miembro) {
            return $miembro->profile->points_x3 ?? 0;
        });

        return view('equipos.show', compact('equipo', 'miembros', 'users', 'totalPoints'));
    }

    public function ranking_teams()
    {
        $teams = Team::orderByDesc('points_x3')
                     ->orderBy('name')
                     ->where('points_x3', '>', 0)
                     ->get(['id', 'name', 'logo', 'image', 'points_x3']); // Seleccionar solo lo necesario

        return view('equipos.ranking', compact('teams'));
    }

    public function edit(Team $equipo)
    {
        return view('equipos.edit', compact('equipo'));
    }

    public function update(Request $request, Team $equipo)
    {
        $request->validate([
            'name' => 'required|unique:teams,name,' . $equipo->id,
            'description' => 'required|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $equipo->image = $this->resizeAndEncodeImage($request->file('image'), 1200);
        }

        if ($request->hasFile('logo')) {
            $equipo->logo = $this->resizeAndEncodeImage($request->file('logo'), 400);
        }

        $equipo->update([
            'name' => $request->name,
            'description' => $request->description,
            'status' => 'updated',
        ]);

        return redirect()->route('equipos.index')
            ->with('success', 'Equipo actualizado exitosamente. Los cambios están bajo revisión.');
    }

    public function destroy(Team $equipo)
    {
        $equipo->delete();
        return redirect()->route('equipos.index')->with('success', 'Equipo eliminado exitosamente.');
    }

    public function addMember(Request $request, Team $equipo)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'is_captain' => 'required|boolean',
        ]);

        DB::transaction(function() use ($request, $equipo) {
            if ($request->is_captain) {
                $equipo->members()->updateExistingPivot($equipo->captain_id, ['is_captain' => false]);
                $equipo->captain_id = $request->user_id;
                $equipo->save();
            }
            $equipo->members()->attach($request->user_id, ['is_captain' => $request->is_captain]);
        });

        return redirect()->route('equipos.show', $equipo)->with('success', 'Miembro añadido exitosamente.');
    }

    public function removeMember(Team $equipo, User $user)
    {
        $equipo->members()->detach($user->id);
        return redirect()->route('equipos.show', $equipo)->with('success', 'Miembro eliminado exitosamente.');
    }

    public function changeCaptain(Request $request, Team $equipo, User $miembro)
    {
        DB::transaction(function() use ($equipo, $miembro) {
            $equipo->members()->updateExistingPivot($equipo->captain_id, ['is_captain' => false]);
            $equipo->members()->updateExistingPivot($miembro->id, ['is_captain' => true]);
            $equipo->captain_id = $miembro->id;
            $equipo->save();
        });

        return redirect()->route('equipos.show', $equipo)->with('success', 'Capitán actualizado exitosamente.');
    }

    public function leaveTeam(Request $request, Team $equipo)
    {
        $userId = Auth::id();

        if ($equipo->captain_id == $userId) {
            return redirect()->route('equipos.show', $equipo)->with('error', 'El capitán no puede dejar el equipo sin transferir el mando.');
        }

        $equipo->members()->detach($userId);
        return redirect()->route('equipos.index')->with('success', 'Has dejado el equipo exitosamente.');
    }

    public function sendInvitation(Request $request, Team $equipo)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Evitar invitaciones duplicadas
        if (Invitation::where('team_id', $equipo->id)->where('user_id', $request->user_id)->exists()) {
            return redirect()->route('equipos.show', $equipo)->with('error', 'Este usuario ya tiene una invitación pendiente de este equipo.');
        }

        Invitation::create([
            'team_id' => $equipo->id,
            'user_id' => $request->user_id,
            'accepted' => null,
        ]);

        return redirect()->route('equipos.show', $equipo)->with('success', 'Invitación enviada exitosamente.');
    }

    public function acceptInvitation(Team $equipo)
    {
        $equipo->update(['status' => 'accepted']);
        return redirect()->back()->with('success', 'Cambios aceptados correctamente.');
    }
}
