<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use App\Models\TournamentResult;
use App\Models\Profile;
use App\Models\Region;
use App\Models\Versus;
use App\Models\Invitation;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['getFilterOptions', 'getBladers']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $regionId = $request->input('region');
        $isFreeAgent = $request->input('free_agent');

        $bladersQuery = Profile::with(['user.activeSubscription.plan', 'region', 'trophies'])
            ->withCount('trophies')
            ->when($regionId, fn($query) => $query->where('profiles.region_id', $regionId))
            ->when(isset($isFreeAgent) && $isFreeAgent !== '', function($query) use ($isFreeAgent) {
                $value = $isFreeAgent == '1' ? true : false;
                $query->where('profiles.free_agent', $value);
            })
            ->where(function ($query) {
                $query->where('profiles.points_x2', '<>', 0)
                    ->orWhere('profiles.points_s3', '<>', 0)
                    ->orWhereNotNull('profiles.imagen');
            })
            ->leftJoin('subscriptions', function($join) {
                $join->on('subscriptions.user_id', '=', 'profiles.user_id')
                    ->where('subscriptions.status', 'active')
                    ->where('subscriptions.ended_at', '>=', now());
            })
            ->leftJoin('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->orderByRaw("
                CASE
                    WHEN plans.slug = 'oro' THEN 1
                    WHEN plans.slug = 'plata' THEN 2
                    WHEN plans.slug = 'bronce' THEN 3
                    ELSE 4
                END, profiles.id ASC
            ");

        // ❌ Detectamos si hay filtros
        $hasFilter = $regionId || $isFreeAgent !== null;

        // Si hay filtros, traemos todos sin paginar
        $bladers = $hasFilter ? $bladersQuery->get() : $bladersQuery->paginate(100);

        $regiones = Region::all();
        $equipo = Team::where('captain_id', Auth::user()->id)->first();

        return view('profiles.index', compact('bladers', 'regiones', 'equipo'));
    }

    public function getFilterOptions()
    {
        $regiones = Region::all(['id', 'name']);

        $equipoId = null;
        $isAuthenticated = Auth::check();

        if ($isAuthenticated) {
            // Asume que solo el capitán puede enviar invitaciones.
            $equipo = Team::where('captain_id', Auth::id())->first(['id']);
            if ($equipo) {
                $equipoId = $equipo->id;
            }
        }

        return response()->json([
            'regiones' => $regiones,
            // Solo devolvemos el ID del equipo (o null)
            'equipo_id' => $equipoId,
            'is_authenticated' => $isAuthenticated,
        ]);
    }

    /**
     * Devuelve la lista de bladers filtrados y/o paginados.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBladers(Request $request)
    {
        $regionId = $request->input('region');
        // El input viene como string ('1' o '0') desde Vue.
        $isFreeAgent = $request->input('free_agent');
        $perPage = 100; // Valor fijo para paginación

        // --- 1. Construcción de la consulta (Tu lógica original) ---
        $bladersQuery = Profile::with(['user.activeSubscription.plan', 'region', 'trophies'])
            ->withCount('trophies')
            ->when($regionId, fn($query) => $query->where('profiles.region_id', $regionId))
            ->when(isset($isFreeAgent) && $isFreeAgent !== '', function($query) use ($isFreeAgent) {
                // Conversión de string '1'/'0' a booleano
                $value = $isFreeAgent == '1';
                $query->where('profiles.free_agent', $value);
            })
            // Lógica de visibilidad (points_x2, points_s3 o imagen)
            ->where(function ($query) {
                $query->where('profiles.points_x2', '<>', 0)
                      ->orWhere('profiles.points_s3', '<>', 0)
                      ->orWhereNotNull('profiles.imagen');
            })
            // Tu lógica de LEFT JOIN para la suscripción activa
            ->leftJoin('subscriptions', function($join) {
                $join->on('subscriptions.user_id', '=', 'profiles.user_id')
                     ->where('subscriptions.status', 'active')
                     // Usamos DB::raw('NOW()') para ser coherentes con el contexto de la base de datos
                     ->where('subscriptions.ended_at', '>=', DB::raw('NOW()'));
            })
            ->leftJoin('plans', 'subscriptions.plan_id', '=', 'plans.id')
            // Tu lógica de ordenación por nivel de suscripción
            ->orderByRaw("
                CASE
                    WHEN plans.slug = 'oro' THEN 1
                    WHEN plans.slug = 'plata' THEN 2
                    WHEN plans.slug = 'bronce' THEN 3
                    ELSE 4
                END, profiles.id ASC
            ")
            // Evitamos duplicados, crucial al usar LEFT JOIN
            ->select('profiles.*');

        // --- 2. Determinación de la paginación ---
        $hasFilter = $regionId || ($isFreeAgent !== null && $isFreeAgent !== '');

        if ($hasFilter) {
            // Si hay filtros activos, no paginamos (como en tu lógica original)
            $bladers = $bladersQuery->get()->unique('id'); // Aseguramos unicidad

            // Devolvemos una estructura simple sin metadata de paginación
            return response()->json([
                'data' => $bladers,
                'total' => $bladers->count(),
                'last_page' => 1, // Indicador para Vue de que no hay paginación
            ]);
        } else {
            // Si NO hay filtros, usamos la paginación estándar de Laravel
            $bladersPaginados = $bladersQuery->paginate($perPage);

            // Laravel ya formatea la respuesta de paginación con toda la metadata (links, current_page, last_page, data, etc.)
            return response()->json($bladersPaginados);
        }
    }

// Mostrar usuarios con roles especiales y estadísticas
    public function indexAdmin(Request $request)
    {
        // 1. Roles
        $profiles = Profile::whereHas('user', function ($query) {
            $query->has('roles');
        })->with('user.roles', 'region')->get();

        $allUsers = User::orderBy('name')->get();

        // 2. Suscripciones
        $subscriptions = Subscription::with('user', 'plan')
            ->where('status', 'active')
            ->orderBy('plan_id', 'desc')
            ->get();

        // 3. Historial Individual del Blader (NUEVO)
        $selectedBladerId = $request->input('blader_id');
        $selectedBlader = null;
        $bladerHistory = collect();

        if ($selectedBladerId) {
            $selectedBlader = User::find($selectedBladerId);

            if ($selectedBlader) {
                // Buscamos en la tabla de asistencias y cruzamos datos con eventos y regiones
                $bladerHistory = DB::table('assist_user_event')
                    ->where('assist_user_event.user_id', $selectedBladerId)
                    ->join('events', 'assist_user_event.event_id', '=', 'events.id')
                    ->leftJoin('regions', 'events.region_id', '=', 'regions.id')
                    ->select(
                        'assist_user_event.id as assist_id',
                        'events.name as event_name',
                        'events.date as event_date',
                        'regions.name as region_name',
                        'assist_user_event.puesto'
                    )
                    ->orderByDesc('events.date')
                    ->get();
            }
        }
        $plans = \App\Models\Plan::all();

        // 4. TESORERÍA: INGRESOS GRAN COPA
        // Traemos los eventos de la Gran Copa directamente
        $granCopaEvents = \App\Models\Event::where('beys', 'grancopa')
            ->where('date', '>=', '2025-09-01') // <-- FILTRO EN EL EVENTO
            ->orderByDesc('date')
            ->get();

        $totalParticipants = 0;
        $totalGross = 0;
        $totalFees = 0;
        $totalNet = 0;

        // Variables de configuración (Ajusta estos valores si tu tarifa de PayPal es distinta)
        $pricePerTicket = 5.00;
        $paypalPercentage = 0.034; // 3.4%
        $paypalFixed = 0.35; // 0.35€ fijos por transacción

        foreach ($granCopaEvents as $event) {
            // Contamos los participantes consultando la tabla exacta
            $participants = \Illuminate\Support\Facades\DB::table('assist_user_event')
                ->where('event_id', $event->id)
                ->count();

            $gross = $participants * $pricePerTicket;

            // La comisión se cobra por cada inscripción individual (transacción)
            // Si hay 0 participantes, la comisión es 0 para no restar fijos a lo tonto
            $fees = 0;
            if ($participants > 0) {
                $feePerTicket = ($pricePerTicket * $paypalPercentage) + $paypalFixed;
                $fees = $participants * $feePerTicket;
            }

            $net = $gross - $fees;

            // Guardamos los cálculos en el evento para pintarlos en la tabla
            $event->assists_count = $participants; // Pasamos el conteo manual
            $event->gross_revenue = $gross;
            $event->paypal_fees = $fees;
            $event->net_revenue = $net;

            // Sumamos al total global
            $totalParticipants += $participants;
            $totalGross += $gross;
            $totalFees += $fees;
            $totalNet += $net;
        }

        $treasuryStats = (object)[
            'participants' => $totalParticipants,
            'gross' => $totalGross,
            'fees' => $totalFees,
            'net' => $totalNet
        ];

        // 5. RADAR REGIONAL (Estadísticas de Staff y Participación)
        // Recogemos el filtro de la URL, por defecto el mes y año actuales
        $radarMonth = $request->input('radar_month', now()->month);
        $radarYear = $request->input('radar_year', now()->year);

        $regionalStats = \App\Models\Region::all()->map(function($region) use ($radarMonth, $radarYear) {

            // Contar Staff asignado a esta región (Jueces y Árbitros)
            $staffInRegion = \App\Models\Profile::where('region_id', $region->id)
                ->whereHas('user.roles', function($q) {
                    $q->whereIn('name', ['juez', 'arbitro']);
                })->count();

            // Preparamos la consulta de eventos en esa región
            $eventsQuery = \App\Models\Event::where('region_id', $region->id);

            // Aplicamos los filtros de fecha si no han seleccionado "Todos"
            if ($radarMonth !== 'all') {
                $eventsQuery->whereMonth('date', $radarMonth);
            }
            if ($radarYear !== 'all') {
                $eventsQuery->whereYear('date', $radarYear);
            }

            $eventsInPeriod = $eventsQuery->get();

            // Total de Bladers (inscripciones) en esos eventos filtrados
            $totalPlayersInPeriod = 0;
            foreach($eventsInPeriod as $event) {
                $totalPlayersInPeriod += \Illuminate\Support\Facades\DB::table('assist_user_event')
                    ->where('event_id', $event->id)
                    ->count();
            }

            // Cálculo de Medias
            $eventsCount = $eventsInPeriod->count();
            $avgPlayersPerEvent = $eventsCount > 0 ? round($totalPlayersInPeriod / $eventsCount, 1) : 0;

            // Ratio de carga de trabajo
            $workloadRatio = ($staffInRegion > 0 && $eventsCount > 0)
                ? round($avgPlayersPerEvent / $staffInRegion, 1)
                : $avgPlayersPerEvent;

            return (object) [
                'name' => $region->name,
                'staff_count' => $staffInRegion,
                'events_count' => $eventsCount,
                'total_players' => $totalPlayersInPeriod,
                'avg_players' => $avgPlayersPerEvent,
                'ratio' => $workloadRatio
            ];
        })->sortByDesc('total_players');

        return view('profiles.indexAdmin', compact(
            'profiles', 'allUsers', 'subscriptions', 'selectedBlader',
            'bladerHistory', 'selectedBladerId', 'granCopaEvents',
            'treasuryStats', 'plans', 'regionalStats', 'radarMonth', 'radarYear'
        ));
    }

    public function storeSubscription(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'plan_id' => 'required|exists:plans,id',
            'period' => 'required|in:monthly,annual',
            'status' => 'required',
        ]);

        \App\Models\Subscription::create([
            'user_id' => $request->user_id,
            'plan_id' => $request->plan_id,
            'period' => $request->period,
            'status' => $request->status,
            'paypal_subscription_id' => $request->paypal_subscription_id,
            'started_at' => $request->started_at ?? now(),
            'ended_at' => $request->ended_at,
        ]);

        return back()->with('success', 'Suscripción creada correctamente.');
    }

    public function updateSubscription(Request $request, $id)
    {
        $sub = \App\Models\Subscription::findOrFail($id);
        $sub->update($request->all());

        return back()->with('success', 'Suscripción actualizada correctamente.');
    }

    public function destroySubscription($id)
    {
        \App\Models\Subscription::findOrFail($id)->delete();
        return back()->with('success', 'Suscripción eliminada.');
    }

    // Eliminar un registro del historial de un Blader
    public function destroyHistory($id)
    {
        // Verificamos que el usuario logueado tenga permisos (opcional, pero recomendado)
        if (!\Illuminate\Support\Facades\Auth::user()->hasAnyRole(['admin', 'juez'])) {
            abort(403, 'No tienes permiso para eliminar registros.');
        }

        \Illuminate\Support\Facades\DB::table('assist_user_event')->where('id', $id)->delete();

        return back()->with('success', 'Registro de torneo eliminado correctamente.');
    }


    // Actualizar roles (Nuevo Sistema Nativo)
    public function updateRoles(Request $request, $userId)
    {
        if ($userId == 0) {
            $user = User::findOrFail($request->input('user_id'));
        } else {
            $user = User::findOrFail($userId);
        }

        // Recogemos los roles marcados en el formulario
        $rolesToAssign = [];
        if ($request->has('role_admin')) $rolesToAssign[] = 'admin';
        if ($request->has('role_juez')) $rolesToAssign[] = 'juez';
        if ($request->has('role_arbitro')) $rolesToAssign[] = 'arbitro';
        if ($request->has('role_editor')) $rolesToAssign[] = 'editor';
        if ($request->has('role_revisor')) $rolesToAssign[] = 'revisor';

        // Buscamos los IDs de esos roles en la BD
        $roleIds = \App\Models\Role::whereIn('name', $rolesToAssign)->pluck('id')->toArray();

        // Sync sincroniza la tabla intermedia: quita los que no estén en el array y añade los nuevos
        $user->roles()->sync($roleIds);

        return back()->with('success', 'Protocolo de roles de la SBBL actualizado correctamente.');
    }



    public function indexAdminX()
    {
        $profiles = Profile::orderBy('points_x2', 'DESC')->get();

        return view('profiles.indexAdminX', compact('profiles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Http\Response
     */
    public function show(Profile $profile, Request $request)
    {
        $invitacionesPendientes = Invitation::where('user_id', auth()->id())->get();

        $currentMonth = $request->input('month', Carbon::now()->month);
        $currentYear = $request->input('year', Carbon::now()->year);

        // Obtener los duelos del usuario en el mes seleccionado
        $versus = Versus::orderBy('id', 'DESC')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->where(function($query) use ($profile) {
                return $query->where('user_id_1', '=' , $profile->user_id)
                            ->orWhere('user_id_2', '=' , $profile->user_id);
            })
            ->get();

        // Obtener los eventos en los que ha participado el usuario en el mes seleccionado
        $eventos = Event::join('assist_user_event', 'events.id', '=', 'assist_user_event.event_id')
            ->where('assist_user_event.user_id', $profile->user_id)
            ->whereMonth('events.date', $currentMonth)
            ->whereYear('events.date', $currentYear)
            ->orderBy('events.date', 'DESC')
            ->get(['events.*']); // Seleccionamos todas las columnas de la tabla `events`

        $user = Auth::user();

        $subscription = $user->activeSubscription;

        return view('profiles.show', compact('profile', 'versus', 'eventos', 'invitacionesPendientes', 'currentMonth', 'currentYear', 'subscription'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Http\Response
     */
    public function edit(Profile $profile)
    {
        // Verificar permisos
        if (Auth::user()->profile->id != $profile->id && !Auth::user()->hasRole('admin')) {
             return redirect()->route('index'); // O mostrar un error 403
        }

        $regionT = Region::find($profile->region_id);
        $regions = Region::all();

        // =====================================================================
        // 1. CARGA DINÁMICA DE IMÁGENES DESDE CARPETAS
        // =====================================================================
        // Nota: El método auxiliar getImagesFromFolder se define más abajo.
        // Lee la carpeta public/upload-profiles/Base, public/upload-profiles/BRONCE, etc.

        // --- AVATARES ---
        $avatarOptions = $this->getImagesFromFolder('Base');
        $bronzeAvatars = $this->getImagesFromFolder('BRONCE');
        $silverAvatars = $this->getImagesFromFolder('PLATA');
        $goldAvatars   = $this->getImagesFromFolder('ORO');

        // Si tuvieras carpetas separadas para la Temporada 2 (S2) y quisieras unirlas
        // solo para suscriptores, descomenta las líneas siguientes:
        /*
        if (Auth::user()->activeSubscription) {
             $bronzeAvatars = array_merge($bronzeAvatars, $this->getImagesFromFolder('BRONCE_S2'));
             $silverAvatars = array_merge($silverAvatars, $this->getImagesFromFolder('PLATA_S2'));
             $goldAvatars   = array_merge($goldAvatars, $this->getImagesFromFolder('ORO_S2'));
        }
        */

        // --- MARCOS ---
        $marcoOptions = $this->getImagesFromFolder('Marcos'); // Marcos base
        $marcoBronce  = $this->getImagesFromFolder('Marcos/BRONCE');
        $marcoPlata   = $this->getImagesFromFolder('Marcos/PLATA');
        $marcoOro     = $this->getImagesFromFolder('Marcos/ORO');

        // --- FONDOS ---
        $fondoOptions = $this->getImagesFromFolder('Fondos');
        // Si tienes fondos S2 en otra carpeta:
        // if (Auth::user()->activeSubscription) {
        //    $fondoOptions = array_merge($fondoOptions, $this->getImagesFromFolder('Fondos_S2'));
        // }


        // =====================================================================
        // 2. LÓGICA DE EVENTOS ESPECIALES (Copas)
        // =====================================================================
        // Mantenemos tu lógica de BD para verificar si ganaron copas
        $copaLloros = DB::table('assist_user_event')
            ->join('events', 'assist_user_event.event_id', '=', 'events.id')
            ->where('assist_user_event.user_id', Auth::user()->id)
            ->whereRaw('LOWER(events.name) LIKE ?', ['%lloro%'])
            ->exists();

        $copaLetItRIP = DB::table('assist_user_event')
            ->join('events', 'assist_user_event.event_id', '=', 'events.id')
            ->where('assist_user_event.user_id', Auth::user()->id)
            ->whereRaw('LOWER(events.name) LIKE ?', ['%let it%'])
            ->exists();

        $avatars = []; // Array para los avatares especiales desbloqueados

        // Si tienen alguna copa, leemos la carpeta de exclusivos (ej: 'EXC')
        // Asegúrate de que la carpeta public/upload-profiles/EXC exista y tenga imágenes.
        if ($copaLloros || $copaLetItRIP) {
            $avatars = array_merge($avatars, $this->getImagesFromFolder('EXC'));
        }


        // =====================================================================
        // 3. DETERMINAR NIVEL DE SUSCRIPCIÓN (Tu lógica original)
        // =====================================================================
        $subscriptionLevel = '';
        // Prioridad: suscripción activa del usuario
        if (Auth::user()->activeSubscription) {
            $subscriptionLevel = 'SUSCRIPCIÓN '.strtoupper(Auth::user()->activeSubscription->plan->name); // Ej: 'SUSCRIPCIÓN ORO'
        } else {
            // Fallback: buscar en trofeos
            $subscriptionTrophy = Auth::user()->profile->trophies->first(function ($trophy) {
                return stripos($trophy->name, 'SUSCRIPCIÓN') !== false;
            });
            if ($subscriptionTrophy) {
                $subscriptionLevel = $subscriptionTrophy->name;
            }
        }

        return view('profiles.edit', compact(
            'profile', 'regions', 'regionT',
            'avatarOptions', 'bronzeAvatars', 'silverAvatars', 'goldAvatars',
            'marcoOptions', 'marcoBronce', 'marcoPlata', 'marcoOro',
            'fondoOptions',
            'avatars', // Iconos especiales de copa
            'subscriptionLevel'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Profile $profile)
    {
        $this->authorize('update', $profile);

        // Validar los datos, asegurándote de que 'custom_option' no sea obligatorio
        $data = request()->validate([
            'nombre' => 'required',
            'region_id' => 'required',
            'free_agent' => 'nullable|boolean',
            'fondo' => 'required',
            'marco' => 'required',
            'subtitulo' => 'nullable|string',  // No es obligatorio
        ]);

        $data['free_agent'] = $request->has('free_agent') ? 1 : 0;

        // Si el usuario sube una imagen
        if($request['imagen']) {
            $ruta_imagen = $request['imagen']->store('upload-profiles', 'public');
            $array_imagen = ['imagen' => $ruta_imagen];
        } elseif ($request['default_img']) {
            $array_imagen = ['imagen' => 'upload-profiles/'.$request['default_img']];
        }

        // Asignar nombre
        auth()->user()->name = $data['nombre'];
        auth()->user()->save();

        // Eliminar url y name de $data
        unset($data['nombre']);

        // Asignar biografía, imagen y la opción personalizada
        auth()->user()->profile()->update(array_merge(
            $data,
            $array_imagen ?? []
        ));

        // Redirigir al perfil actualizado
        return redirect()->action('App\Http\Controllers\ProfileController@show', $profile);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Http\Response
     */
    public function updatePoints(Request $request, Profile $profile)
    {

        // Validar
        $data = request()->validate([
            'points_s3' => 'required',
        ]);

        // Asignar los valores
        $profile->points_s3 = $data['points_s3'];

        $profile->save();

        $profiles = Profile::orderBy('id', 'ASC')->get();

        return view('profiles.indexAdmin', compact('profiles'));
    }

    public function updatePointsX(Request $request, Profile $profile)
    {

        // Validar
        $data = request()->validate([
            'points_x2' => 'required',
        ]);

        // Asignar los valores
        $profile->points_x2 = $data['points_x2'];

        $profile->save();

        $profiles = Profile::orderBy('id', 'ASC')->get();

        return view('profiles.indexAdminX', compact('profiles'));
    }

    public function updateAllPointsX(Request $request)
    {
        // Validar que todos los puntos sean numéricos y opcionales
        $data = $request->validate([
            'points_x2.*' => 'nullable|numeric',
        ]);

        // Iterar sobre los perfiles y actualizar los puntos
        foreach ($request->input('points_x2', []) as $profileId => $points) {
            $profile = Profile::find($profileId);
            if ($profile) {
                $profile->points_x2 = $points;
                $profile->save();
            }
        }

        // Redirigir con mensaje de éxito
        return redirect()->route('profiles.indexAdminX')
            ->with('success', 'Puntos actualizados correctamente');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Http\Response
     */
    public function destroy(Profile $profile)
    {
        //
    }

        public function ranking(Request $request)
        {
            ini_set('memory_limit', '1024M'); // Seguridad extra, por si acaso
            // Valores de filtros
            $limit = $request->input('limit', 25); // Valor por defecto: 25
            if ($limit !== 'all') {
                $limit = (int) $limit;
            }
            $region = $request->input('region', null);

            // Función para generar cada ranking
            $getBladersByPoints = function ($column) use ($limit, $region) {
                return Profile::with(['user.teams', 'region'])
                    ->when($region, function ($query) use ($region) {
                        return $query->whereHas('region', function ($q) use ($region) {
                            $q->where('name', $region);
                        });
                    })
                    ->where($column, '!=', 0)
                    ->orderBy($column, 'DESC')
                    ->orderBy('id', 'ASC')
                    ->when($limit !== 'all', function ($query) use ($limit) {
                        return $query->take($limit);
                    })
                    ->get()
                    ->map(function ($blader) use ($column) {
                        if ($blader->user->teams->isNotEmpty() && $blader->user->teams->first()->logo) {
                            $blader->team_logo = 'data:image/png;base64,' . $blader->user->teams->first()->logo;
                        } else {
                            $blader->team_logo = null;
                        }
                        return $blader;
                    });
            };

            // Rankings por temporada/sistema
            $bladers_points     = $getBladersByPoints('points');       // Season 1
            $bladers_points_s2  = $getBladersByPoints('points_s2');    // Season 2
            $bladers_points_s3  = $getBladersByPoints('points_s3');    // Season 3
            $bladers_points_x1  = $getBladersByPoints('points_x1');    // Beyblade X 1
            $bladers_points_x2  = $getBladersByPoints('points_x2');    // Beyblade X 2

            // Lista de regiones
            $regions = Region::all()->pluck('name');

            return view('profiles.ranking', [
                'bladers_points' => $bladers_points,
                'bladers_points_s2' => $bladers_points_s2,
                'bladers_points_s3' => $bladers_points_s3,
                'bladers_points_x1' => $bladers_points_x1,
                'bladers_points_x2' => $bladers_points_x2,
                'limit' => $limit,
                'region' => $region,
                'regions' => $regions,
            ]);
        }



    public function wrapped(Profile $profile)
    {
        $primerTorneo = DB::table('assist_user_event')
            ->where('user_id', $profile->id)
            ->where('event_id', '>=', 190)
            ->orderBy('id', 'asc')
            ->first();

        if ($primerTorneo) {
            $datosTorneo = Event::with('region')
                ->find($primerTorneo->event_id);
        } else {
            $datosTorneo = null;
        }

        $numeroTorneos = DB::table('assist_user_event')
            ->where('user_id', $profile->id)
            ->whereNotNull('puesto') // Asegura que 'puesto' no sea null
            ->where('puesto', '!=', 'nopresentado')
            ->where('event_id', '>=', 190)
            ->orderBy('id', 'asc')
            ->count();

        $torneosGanados = DB::table('assist_user_event')
            ->where('user_id', $profile->id)
            ->where('puesto', 'primero')
            ->where('event_id', '>=', 190)
            ->orderBy('id', 'asc')
            ->count();

        $mejorCombo = TournamentResult::where('user_id', $profile->id)
            ->select(DB::raw('blade, ratchet, bit, SUM(puntos_ganados) as total_puntos_ganados, SUM(puntos_perdidos) as total_puntos_perdidos'))
            ->groupBy('blade', 'ratchet', 'bit')
            ->orderBy(DB::raw('SUM(victorias)'), 'desc')
            ->first();

        $peorCombo = TournamentResult::where('user_id', $profile->id)
            ->select(DB::raw('blade, ratchet, bit, SUM(puntos_ganados) as total_puntos_ganados, SUM(puntos_perdidos) as total_puntos_perdidos'))
            ->groupBy('blade', 'ratchet', 'bit')
            ->orderBy(DB::raw('SUM(derrotas)'), 'desc')
            ->first();

        return view('profiles.wrapped', compact('profile', 'datosTorneo', 'numeroTorneos', 'torneosGanados', 'mejorCombo', 'peorCombo'));
    }

    public function rankingPorSplits()
    {
        $splits = [
            'Pretemporada' => ['2025-06-22', '2025-08-31'],
            'Split inicial' => ['2025-09-01', '2025-09-30'],
            'Split Let It R.I.P.' => ['2025-10-01', '2025-11-30'],
            'Split XMAS' => ['2025-12-01', '2026-01-31'],
            'Split Ligera Revival' => ['2026-02-01', '2026-03-31'],
            'Split 4' => ['2026-04-01', '2026-05-31'],
            'Split final' => ['2026-06-01', '2026-06-30'],
        ];

        $data = [];

        foreach ($splits as $nombre => [$start, $end]) {
            $data[$nombre] = DB::table('points_log')
                ->join('events', 'points_log.event_id', '=', 'events.id')
                ->join('users', 'points_log.user_id', '=', 'users.id')
                ->select('users.id', 'users.name', DB::raw('SUM(points_log.puntos) as total_puntos'))
                ->whereBetween('events.date', [$start, $end])
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('total_puntos')
                ->get();
        }

        return view('profiles.splits', compact('data', 'splits'));
    }


    public function getDetails($id)
    {
        $blader = Profile::with('user')
                        ->findOrFail($id);

        $user = $blader->user;

        $team = null;
        $teamName = 'Ninguno';
        $teamLogoData = null;
        $teamPoints = 0;

        // 🔹 Inicializamos las estadísticas
        $torneosJugados = 0;
        $primeros = 0;
        $segundos = 0;
        $terceros = 0;
        $torneosJugados_x1 = 0;
        $primeros_x1 = 0;
        $segundos_x1 = 0;
        $terceros_x1 = 0;
        $mostrarEstadisticas = false; // Por defecto, no mostrar

        if ($user) {
            // 🔸 Comprobamos si el usuario tiene una suscripción activa
            $suscrito = DB::table('subscriptions')
                ->where('user_id', Auth::user()->id)
                ->where('status', 'active')
                ->exists();

            if ($suscrito) {
                $mostrarEstadisticas = true;

                // 🔹 Contar los eventos después del 1 de septiembre de 2025
                $bladerData = DB::table('assist_user_event')
                    ->join('events', 'assist_user_event.event_id', '=', 'events.id')
                    ->where('assist_user_event.user_id', $user->id)
                    ->where('events.date', '>', '2025-09-01')
                    ->select('assist_user_event.puesto')
                    ->get();

                $torneosJugados = $bladerData->count();
                $primeros = $bladerData->where('puesto', 'primero')->count();
                $segundos = $bladerData->where('puesto', 'segundo')->count();
                $terceros = $bladerData->where('puesto', 'tercero')->count();

                $bladerData_x1 = DB::table('assist_user_event')
                    ->join('events', 'assist_user_event.event_id', '=', 'events.id')
                    ->where('assist_user_event.user_id', $user->id)
                    ->where('events.date', '>', '2024-06-01')
                    ->where('events.date', '<', '2025-06-23')
                    ->select('assist_user_event.puesto')
                    ->get();

                $torneosJugados_x1 = $bladerData_x1->count();
                $primeros_x1 = $bladerData_x1->where('puesto', 'primero')->count();
                $segundos_x1 = $bladerData_x1->where('puesto', 'segundo')->count();
                $terceros_x1 = $bladerData_x1->where('puesto', 'tercero')->count();
            }

            // 🔹 Datos del equipo
            $teamData = DB::table('team_user')
                        ->where('user_id', $user->id)
                        ->select('team_id')
                        ->first();

            if ($teamData) {
                $team = DB::table('teams')
                        ->where('id', $teamData->team_id)
                        ->first();

                if ($team) {
                    $teamName = $team->name;
                    $teamLogoData = $team->logo;
                    $teamPoints = $team->points_x2;
                }
            }
        }

        return response()->json([
            'nombre' => $blader->user->name,
            'region' => $blader->region->name ?? 'No definida',
            'puntos_x1' => $blader->points_x1 ?? 0,
            'puntos_x2' => $blader->points_x2 ?? 0,
            'subtitulo' => $blader->subtitulo,
            'imagen' => asset('storage/' . ($blader->imagen ?? 'upload-profiles/Base/DranDagger.webp')),
            'marco' => asset('storage/' . ($blader->marco ?? 'upload-profiles/Marcos/BaseBlue.png')),
            'free_agent' => $blader->free_agent ? 'Sí' : 'No',
            'equipo_nombre' => $teamName,
            'equipo_logo_b64' => $teamLogoData,
            'equipo_puntos' => $teamPoints,

            // 🔹 Solo si está suscrito
            'mostrar_estadisticas' => $mostrarEstadisticas,
            'torneos_jugados' => $torneosJugados,
            'primeros' => $primeros,
            'segundos' => $segundos,
            'terceros' => $terceros,
            'torneos_jugados_x1' => $torneosJugados_x1,
            'primeros_x1' => $primeros_x1,
            'segundos_x1' => $segundos_x1,
            'terceros_x1' => $terceros_x1,
            'is_subscribed' => Auth::user()->activeSubscription ? true : false,
        ]);
    }

    private function getImagesFromFolder($folderName)
    {
        // 1. Ruta específica de TU SERVIDOR (La que has confirmado que funciona)
        $pathServer = "/home/sbbleso/www/storage/upload-profiles/{$folderName}";

        // 2. Ruta estándar de Laravel (Para que te siga funcionando en Local/XAMPP)
        $pathLocal = public_path("storage/upload-profiles/{$folderName}");

        $pathFinal = null;
        $urlPrefix = '';

        // Lógica de decisión:
        if (File::exists($pathServer)) {
            // Estamos en el SERVIDOR
            $pathFinal = $pathServer;
            $urlPrefix = 'storage/upload-profiles/';
        } elseif (File::exists($pathLocal)) {
            // Estamos en LOCAL
            $pathFinal = $pathLocal;
            $urlPrefix = 'storage/upload-profiles/';
        } else {
            // Intento final: buscar en carpeta pública normal (sin storage)
            $pathFinal = public_path("upload-profiles/{$folderName}");
            if (File::exists($pathFinal)) {
                $urlPrefix = 'upload-profiles/';
            } else {
                return []; // No existe
            }
        }

        // Leemos los archivos
        $files = File::files($pathFinal);
        $options = [];

        foreach ($files as $file) {
            if (in_array(strtolower($file->getExtension()), ['webp', 'png', 'gif', 'jpg', 'jpeg'])) {

                $filename = $file->getFilename();

                // Key para BD
                $key = $folderName . '/' . $filename;

                // Value para el navegador (usando el prefijo correcto)
                $value = $urlPrefix . $folderName . '/' . $filename;

                $options[$key] = $value;
            }
        }

        ksort($options);

        return $options;
    }
public function showPublic($slug)
{
    // 1. Parsear Slug
    if (!preg_match('/^(.+)-(\d+)$/', $slug, $matches)) {
        abort(404);
    }

    $nameSlug = $matches[1];
    $userId   = (int) $matches[2];

    // 2. Carga de datos
    $user = User::with(array(
        'profile.region',
        'profile.trophies',
        'teams',
        'assistsEvents' => function ($q) {
            $q->whereNotNull('puesto')->orderBy('date', 'desc');
        }
    ))->findOrFail($userId);

    // 3. Validación de URL canónica
    $nameLower = mb_strtolower($user->name);
    $nameLower = preg_replace('/\s+/', '-', trim($nameLower));
    $nameLower = preg_replace('/[^a-z0-9\-_.]/', '', $nameLower);
    $expectedSlug = $nameLower;

    $canonicalSlug = $expectedSlug . '-' . str_pad($userId, 4, '0', STR_PAD_LEFT);

    if (strtolower($nameSlug) !== strtolower($expectedSlug)) {
        return redirect()->route('blader.public', array('slug' => $canonicalSlug), 301);
    }

    // --- CÁLCULO DE ESTADÍSTICAS GENERALES ---
    $eventos = $user->assistsEvents;
    $totalTorneos = $eventos->count();
    $totalVictorias = 0;

    foreach ($eventos as $e) {
        $p = mb_strtolower($e->pivot->puesto);
        if ($p == '1' || $p == 'primero') {
            $totalVictorias++;
        }
    }

    $winRate = $totalTorneos > 0 ? round(($totalVictorias / $totalTorneos) * 100, 1) : 0;

    // --- ESTADÍSTICAS POR TEMPORADA (Manual) ---
    $statsPorTemporada = array();
    foreach ($eventos as $e) {
        $fecha = \Carbon\Carbon::parse($e->date);
        $tempNombre = 'Otras';

        if ($fecha->between('2024-07-01', '2025-06-30')) {
            $tempNombre = 'Temporada 1';
        } elseif ($fecha->between('2025-09-01', '2026-05-31')) {
            $tempNombre = 'Temporada 2';
        }

        if (!isset($statsPorTemporada[$tempNombre])) {
            // Asignamos los puntos desde el perfil según la temporada
            $puntosBase = 0;
            if ($tempNombre == 'Temporada 1') {
                $puntosBase = $user->profile->points_x1; // O 'points' si es la antigua
            } elseif ($tempNombre == 'Temporada 2') {
                $puntosBase = $user->profile->points_x2;
            }

            $statsPorTemporada[$tempNombre] = array(
                'torneos'  => 0,
                'puntos'   => $puntosBase, // Cargamos el valor de la columna de la tabla profiles
                'primeros' => 0,
                'segundos' => 0,
                'terceros' => 0
            );
        }

        $statsPorTemporada[$tempNombre]['torneos']++;

        // Si quieres SUMAR los puntos del pivot ADEMÁS de los de la columna, deja la línea de abajo.
        // Si los puntos de la columna ya son el total, COMENTA la línea siguiente:
        // $statsPorTemporada[$tempNombre]['puntos'] += ($e->pivot->puntos ?? 0);

        $p = mb_strtolower($e->pivot->puesto);
        if ($p == '1' || $p == 'primero') $statsPorTemporada[$tempNombre]['primeros']++;
        if ($p == '2' || $p == 'segundo') $statsPorTemporada[$tempNombre]['segundos']++;
        if ($p == '3' || $p == 'tercero') $statsPorTemporada[$tempNombre]['terceros']++;
    }

    $statsPorTemporada = collect($statsPorTemporada);

    // --- EVOLUCIÓN DE PUNTOS (points_log) ---
    $puntosLog = \DB::table('points_log')
        ->join('events', 'points_log.event_id', '=', 'events.id')
        ->where('points_log.user_id', $userId)
        ->select('points_log.puntos', 'events.date', 'events.name')
        ->orderBy('events.date', 'desc')
        ->take(8)
        ->get();

    $graficaPuntos = array();
    $graficaLabels = array();
    $ultimoEvento = null;

    $puntosLogArray = array_reverse($puntosLog->toArray());

    foreach ($puntosLogArray as $log) {
        $graficaPuntos[] = $log->puntos;
        $graficaLabels[] = \Carbon\Carbon::parse($log->date)->format('d/m');
        $ultimoEvento = $log->name;
    }

// --- RENDIMIENTO POR TIPO (Basado en campo 'beys') ---
    $agrupadoPorTipo = array();

    // Definimos un mapeo para que los nombres luzcan bien en la vista
    $nombresFormateados = array(
        'ranking'     => 'Ranking',
        'rankingplus' => 'Ranking',
        'quedada'     => 'Quedada',
        'grancopa'    => 'Gran Copa'
    );

    foreach ($eventos as $e) {
        // Usamos el campo 'beys' como identificador de tipo
        $tipoRaw = mb_strtolower($e->beys);

        // Solo procesamos si es uno de los tipos que te interesan
        if (isset($nombresFormateados[$tipoRaw])) {
            $tipoVisible = $nombresFormateados[$tipoRaw];

            if (!isset($agrupadoPorTipo[$tipoVisible])) {
                $agrupadoPorTipo[$tipoVisible] = array('torneos' => 0, 'top4' => 0);
            }

            $agrupadoPorTipo[$tipoVisible]['torneos']++;

            $p = mb_strtolower($e->pivot->puesto);
            $esTop4 = in_array($p, array('1', '2', '3', '4', 'primero', 'segundo', 'tercero', 'cuarto'));

            if ($esTop4) {
                $agrupadoPorTipo[$tipoVisible]['top4']++;
            }
        }
    }

    $winRatePorTipo = array();
    foreach ($agrupadoPorTipo as $nombre => $data) {
        $winRatePorTipo[$nombre] = array(
            'torneos' => $data['torneos'],
            'tasa'    => round(($data['top4'] / $data['torneos']) * 100)
        );
    }

    // --- COMBOS (tournament_results) ---
    $topCombosRaw = \App\Models\TournamentResult::where('user_id', $userId)
        ->select(
            'blade', 'ratchet', 'bit',
            \DB::raw('SUM(victorias) as total_victorias'),
            \DB::raw('SUM(derrotas) as total_derrotas'),
            \DB::raw('COUNT(*) as cantidad_usado')
        )
        ->groupBy('blade', 'ratchet', 'bit')
        ->orderBy('cantidad_usado', 'desc')
        ->take(5)
        ->get();

    $topCombos = array();
    foreach ($topCombosRaw as $c) {
        $totalRondas = $c->total_victorias + $c->total_derrotas;
        $wr = $totalRondas > 0 ? round(($c->total_victorias / $totalRondas) * 100, 1) : 0;

        $obj = new \stdClass();
        $obj->combo_name = $c->blade . ' ' . $c->ratchet . ' ' . $c->bit;
        $obj->partidas   = $c->cantidad_usado;
        $obj->win_rate   = $wr;
        $topCombos[] = $obj;
    }

    // --- RESULTADO FINAL ---
    $primerosPuntos = 0;
    $primeraTemp = $statsPorTemporada->first();
    if ($primeraTemp) {
        $primerosPuntos = $primeraTemp['puntos'];
    }

    return view('profiles.public', array(
        'user' => $user,
        'statsPorTemporada' => $statsPorTemporada,
        'totalTorneos' => $totalTorneos,
        'totalVictorias' => $totalVictorias,
        'winRate' => $winRate,
        'temporadasActivas' => $statsPorTemporada->count(),
        'puntosTemporadaActual' => $primerosPuntos,
        'ultimoEvento' => $ultimoEvento,
        'graficaPuntos' => $graficaPuntos,
        'graficaLabels' => $graficaLabels,
        'topCombos' => $topCombos,
        'winRatePorTipo' => $winRatePorTipo,
        'trofeos' => $user->profile->trophies ? $user->profile->trophies : collect(array()),
        'historialEventos' => $eventos,
        'canonicalSlug' => $canonicalSlug
    ));
}
    // Helper: convierte "JoseCalvi1" → "josecalvi1"
    // Conserva números y letras, sustituye espacios por guión
    private function toSlug(string $name): string
    {
        $slug = mb_strtolower($name);
        $slug = preg_replace('/\s+/', '-', trim($slug));
        $slug = preg_replace('/[^a-z0-9\-_.]/', '', $slug);
        return $slug;
    }

}
