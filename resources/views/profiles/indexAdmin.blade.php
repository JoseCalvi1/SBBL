@extends('layouts.app')

@section('title', 'Administración SBBL')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* ====================================================================
       ESTILOS ESPECÍFICOS: PANEL ADMIN
       ==================================================================== */

    .page-title {
        font-family: 'Oswald', sans-serif;
        font-size: 3.5rem;
        color: var(--sbbl-gold);
        text-shadow: 2px 2px 0px #000, 4px 4px 0px var(--shonen-red);
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    /* ── TABS (PESTAÑAS) ── */
    .nav-tabs {
        border-bottom: 4px solid #000;
        margin-bottom: 20px !important;
        gap: 5px;
    }
    .nav-tabs .nav-item { margin-bottom: -4px; }
    .nav-tabs .nav-link {
        background: #000;
        color: #fff;
        border: 3px solid #000;
        font-family: 'Oswald', sans-serif;
        font-size: 1.3rem;
        letter-spacing: 1px;
        border-radius: 0;
        transform: skewX(-5deg);
        transition: 0.2s;
        padding: 10px 25px;
        text-transform: uppercase;
    }
    .nav-tabs .nav-link > span { display: block; transform: skewX(5deg); }
    .nav-tabs .nav-link:hover {
        background: var(--sbbl-blue-3);
        color: var(--sbbl-gold);
    }
    .nav-tabs .nav-link.active {
        background: var(--sbbl-gold);
        color: #000;
        border-bottom-color: var(--sbbl-gold);
        box-shadow: 4px -4px 0 var(--shonen-red);
    }

    /* ── TABLAS ── */
    .table-tactical {
        border: 3px solid #000;
        box-shadow: 6px 6px 0 #000;
        background: var(--sbbl-blue-2);
        margin-bottom: 0;
    }
    .table-tactical thead {
        background: #000;
        border-bottom: 4px solid var(--sbbl-gold);
    }
    .table-tactical th {
        font-family: 'Oswald', sans-serif;
        color: var(--shonen-cyan);
        font-size: 1.2rem;
        letter-spacing: 1px;
        text-transform: uppercase;
        border: none;
        padding: 15px;
    }
    .table-tactical td {
        background: transparent;
        color: #fff;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        vertical-align: middle;
        padding: 15px;
    }
    .table-tactical tbody tr:hover {
        background: rgba(0, 0, 0, 0.4);
    }

    /* ── CHECKBOXES PERSONALIZADOS ── */
    .role-checkbox {
        display: flex; align-items: center; gap: 8px; margin-right: 15px; margin-bottom: 10px;
    }
    .role-checkbox input[type="checkbox"] {
        width: 20px; height: 20px;
        accent-color: var(--sbbl-gold);
        cursor: pointer;
    }
    .role-checkbox label {
        font-family: 'Oswald', sans-serif;
        font-size: 1.1rem;
        color: #fff;
        cursor: pointer;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    /* ── BADGES ROLES ── */
    .badge-role {
        font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 0.7rem;
        padding: 4px 8px; border: 1px solid #000; color: #000; text-transform: uppercase;
        margin-right: 4px; display: inline-block; margin-bottom: 4px;
    }
    .bg-admin { background: #ff4d4d; }
    .bg-juez { background: var(--sbbl-gold); }
    .bg-arbitro { background: var(--shonen-cyan); }
    .bg-editor { background: #b45309; color: #fff;}
    .bg-revisor { background: #a8c0ff; }
</style>
@endsection

@section('content')
<div class="container-fluid py-4 mb-5">

    <div class="text-center mb-5">
        <h2 class="page-title"><i class="fas fa-users-cog me-2 text-white" style="text-shadow:none;"></i> ADMINISTRACIÓN SBBL</h2>
        <p class="text-white fw-bold">Gestión de usuarios, roles, suscripciones y estadísticas de torneos.</p>
    </div>

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert alert-shonen alert-shonen-success mb-4 text-center"><div><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div></div>
    @endif

    {{-- NAVEGACIÓN PESTAÑAS --}}
    <ul class="nav nav-tabs" id="userTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ request('blader_id') ? '' : 'active' }}" id="roles-tab" data-bs-toggle="tab" href="#roles" role="tab"><span><i class="fas fa-id-card-alt me-2"></i> ROLES Y ACCESOS</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="subs-tab" data-bs-toggle="tab" href="#subs" role="tab"><span><i class="fas fa-gem me-2"></i> SUSCRIPCIONES</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('blader_id') ? 'active' : '' }}" id="stats-tab" data-bs-toggle="tab" href="#stats" role="tab"><span><i class="fas fa-search me-2"></i> HISTORIAL DE BLADER</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="treasury-tab" data-bs-toggle="tab" href="#treasury" role="tab"><span><i class="fas fa-coins me-2"></i> TESORERÍA</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="regional-tab" data-bs-toggle="tab" href="#regional" role="tab"><span><i class="fas fa-map-marked-alt me-2"></i> RADAR REGIONAL</span></a>
        </li>
    </ul>

    <div class="tab-content" id="userTabsContent">

        {{-- =====================================
             PESTAÑA 1: ROLES
             ===================================== --}}
        <div class="tab-pane fade {{ request('blader_id') ? '' : 'show active' }}" id="roles" role="tabpanel" aria-labelledby="roles-tab">

            {{-- PANEL: ASIGNAR NUEVO ROL --}}
            <div class="command-panel p-4 mb-5" style="background: rgba(0,0,0,0.5); border: 2px solid var(--sbbl-gold);">
                <h4 class="font-Oswald text-white mb-3 pb-2 border-bottom border-secondary" style="font-size: 1.8rem;"><i class="fas fa-user-plus me-2" style="color: var(--sbbl-gold);"></i> ASIGNAR ROLES A USUARIO</h4>
                <form action="{{ route('profiles.updateRoles', ['user' => 0]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row align-items-end g-3">
                        <div class="col-md-5">
                            <label class="text-white font-Oswald fs-5 mb-2" for="user_id">SELECCIONAR BLADER:</label>
                            <select id="user_id" name="user_id" class="form-control select2" required>
                                <option value="">-- Buscar blader --</option>
                                @foreach($allUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->email }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-5">
                            <label class="text-white font-Oswald fs-5 mb-2 d-block">SELECCIONAR ROLES:</label>
                            <div class="d-flex flex-wrap bg-black p-2 border border-secondary">
                                <div class="role-checkbox"><input type="checkbox" id="n_admin" name="role_admin" value="1"><label for="n_admin">Admin</label></div>
                                <div class="role-checkbox"><input type="checkbox" id="n_juez" name="role_juez" value="1"><label for="n_juez">Juez</label></div>
                                <div class="role-checkbox"><input type="checkbox" id="n_arbitro" name="role_arbitro" value="1"><label for="n_arbitro">Árbitro</label></div>
                                <div class="role-checkbox"><input type="checkbox" id="n_editor" name="role_editor" value="1"><label for="n_editor">Editor</label></div>
                                <div class="role-checkbox"><input type="checkbox" id="n_revisor" name="role_revisor" value="1"><label for="n_revisor">Revisor</label></div>
                            </div>
                        </div>

                        <div class="col-md-2 text-end">
                            <button type="submit" class="btn btn-warning font-Oswald fs-5 w-100 py-2" style="letter-spacing: 1px;">GUARDAR</button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- LISTADO DE PERSONAL --}}
            <h4 class="font-Oswald text-white mb-3" style="font-size: 2rem;"><i class="fas fa-users-cog me-2" style="color: var(--shonen-red);"></i> EQUIPO DE GESTIÓN</h4>
            <div class="table-responsive">
                <table class="table table-tactical">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Región</th>
                            <th>Roles Asignados</th>
                            <th class="text-end">Modificar Accesos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($profiles as $profile)
                            <tr>
                                <td class="fw-bold" style="font-size: 1.1rem;">{{ $profile->user->name }}</td>
                                <td class="text-white-50">{{ $profile->user->email }}</td>
                                <td><span class="badge bg-dark border border-secondary text-white">{{ $profile->region->name ?? 'NO ASIGNADA' }}</span></td>
                                <td>
                                    @if($profile->user->hasRole('admin')) <span class="badge-role bg-admin">Admin</span> @endif
                                    @if($profile->user->hasRole('juez')) <span class="badge-role bg-juez">Juez</span> @endif
                                    @if($profile->user->hasRole('arbitro')) <span class="badge-role bg-arbitro">Árbitro</span> @endif
                                    @if($profile->user->hasRole('editor')) <span class="badge-role bg-editor">Editor</span> @endif
                                    @if($profile->user->hasRole('revisor')) <span class="badge-role bg-revisor">Revisor</span> @endif
                                </td>
                                <td>
                                    <form action="{{ route('profiles.updateRoles', ['user' => $profile->user->id]) }}" method="POST" class="d-flex align-items-center justify-content-end gap-2">
                                        @csrf
                                        @method('PUT')
                                        <div class="d-flex bg-black p-1 border border-secondary">
                                            <div class="role-checkbox m-0 mx-1 px-1"><input type="checkbox" name="role_admin" title="Admin" value="1" {{ $profile->user->hasRole('admin') ? 'checked' : '' }}></div>
                                            <div class="role-checkbox m-0 mx-1 px-1"><input type="checkbox" name="role_juez" title="Juez" value="1" {{ $profile->user->hasRole('juez') ? 'checked' : '' }}></div>
                                            <div class="role-checkbox m-0 mx-1 px-1"><input type="checkbox" name="role_arbitro" title="Árbitro" value="1" {{ $profile->user->hasRole('arbitro') ? 'checked' : '' }}></div>
                                            <div class="role-checkbox m-0 mx-1 px-1"><input type="checkbox" name="role_editor" title="Editor" value="1" {{ $profile->user->hasRole('editor') ? 'checked' : '' }}></div>
                                            <div class="role-checkbox m-0 mx-1 px-1"><input type="checkbox" name="role_revisor" title="Revisor" value="1" {{ $profile->user->hasRole('revisor') ? 'checked' : '' }}></div>
                                        </div>
                                        <button type="submit" class="btn btn-warning btn-sm p-1 px-2" style="font-size: 1rem;" title="Actualizar"><i class="fas fa-save"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- =====================================
             PESTAÑA 2: SUSCRIPCIONES
             ===================================== --}}
        <div class="tab-pane fade" id="subs" role="tabpanel" aria-labelledby="subs-tab">

            {{-- PANEL: CREAR NUEVA SUSCRIPCIÓN --}}
            <div class="command-panel p-4 mb-5" style="background: rgba(0,0,0,0.5); border: 2px solid var(--shonen-cyan);">
                <h4 class="font-Oswald text-white mb-3 pb-2 border-bottom border-secondary" style="font-size: 1.8rem;"><i class="fas fa-plus-circle me-2" style="color: var(--shonen-cyan);"></i> ACTIVAR NUEVA SUSCRIPCIÓN (AURA)</h4>
                <form action="{{ route('admin.subscriptions.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="text-white font-Oswald fs-5 mb-2">BLADER</label>
                            <select name="user_id" class="form-control select2" required>
                                <option value="">-- Seleccionar --</option>
                                @foreach($allUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="text-white font-Oswald fs-5 mb-2">PLAN / AURA</label>
                            <select name="plan_id" class="form-control" required>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="text-white font-Oswald fs-5 mb-2">PERIODO</label>
                            <select name="period" class="form-control">
                                <option value="monthly">Mensual</option>
                                <option value="annual">Anual</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="text-white font-Oswald fs-5 mb-2">ESTADO</label>
                            <select name="status" class="form-control">
                                <option value="active">Activa</option>
                                <option value="pending">Pendiente</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="text-white font-Oswald fs-5 mb-2">ID PAYPAL (Opcional)</label>
                            <input type="text" name="paypal_subscription_id" class="form-control" placeholder="Ej: I-123456789">
                        </div>
                        <div class="col-md-3 text-end d-flex align-items-end">
                            <button type="submit" class="btn btn-info font-Oswald w-100 py-2 fs-5" style="letter-spacing: 1px;">ACTIVAR AURA</button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- TABLA DE SUSCRIPCIONES --}}
            <h4 class="font-Oswald text-white mb-3" style="font-size: 2rem;"><i class="fas fa-gem me-2" style="color: var(--shonen-cyan);"></i> GESTIÓN DE SUSCRIPCIONES</h4>
            <div class="table-responsive">
                <table class="table table-tactical">
                    <thead>
                        <tr>
                            <th>Blader</th>
                            <th>Plan / Aura</th>
                            <th>Periodo</th>
                            <th>Estado</th>
                            <th>ID PayPal</th> <th>Fin / Próximo Cobro</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($subscriptions as $sub)
                            <tr>
                                <form action="{{ route('admin.subscriptions.update', $sub->id) }}" method="POST">
                                    @csrf @method('PUT')
                                    <td class="fw-bold">{{ $sub->user->name ?? 'Desconocido' }}</td>
                                    <td>
                                        <select name="plan_id" class="form-control form-control-sm bg-dark text-white border-secondary">
                                            @foreach($plans as $plan)
                                                <option value="{{ $plan->id }}" {{ $sub->plan_id == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="period" class="form-control form-control-sm bg-dark text-white border-secondary">
                                            <option value="monthly" {{ $sub->period == 'monthly' ? 'selected' : '' }}>Mensual</option>
                                            <option value="annual" {{ $sub->period == 'annual' ? 'selected' : '' }}>Anual</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="status" class="form-control form-control-sm bg-dark text-white border-secondary">
                                            <option value="active" {{ $sub->status == 'active' ? 'selected' : '' }}>Activa</option>
                                            <option value="pending" {{ $sub->status == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                            <option value="canceled" {{ $sub->status == 'canceled' ? 'selected' : '' }}>Cancelada</option>
                                            <option value="expired" {{ $sub->status == 'expired' ? 'selected' : '' }}>Expirada</option>
                                        </select>
                                    </td>

                                    <td>
                                        <input type="text" name="paypal_subscription_id" value="{{ $sub->paypal_subscription_id }}" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Sin ID">
                                    </td>

                                    <td>
                                        <input type="date" name="ended_at" value="{{ $sub->ended_at ? $sub->ended_at->format('Y-m-d') : '' }}" class="form-control form-control-sm bg-dark text-white border-secondary">
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <button type="submit" class="btn btn-sm btn-success" title="Guardar cambios"><i class="fas fa-save"></i></button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmDeleteSub('{{ $sub->id }}')" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </td>
                                </form>
                                {{-- Formulario oculto para el borrado --}}
                                <form id="delete-sub-{{ $sub->id }}" action="{{ route('admin.subscriptions.destroy', $sub->id) }}" method="POST" style="display:none;">
                                    @csrf @method('DELETE')
                                </form>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- =====================================
             PESTAÑA 3: HISTORIAL DE BLADER
             ===================================== --}}
        <div class="tab-pane fade {{ request('blader_id') ? 'show active' : '' }}" id="stats" role="tabpanel" aria-labelledby="stats-tab">

            {{-- BUSCADOR --}}
            <div class="command-panel p-4 mb-5" style="background: rgba(0,0,0,0.5); border: 2px solid var(--shonen-cyan);">
                <h4 class="font-Oswald text-white mb-3 pb-2 border-bottom border-secondary" style="font-size: 1.8rem;"><i class="fas fa-search me-2" style="color: var(--shonen-cyan);"></i> CONSULTAR HISTORIAL</h4>
                <form action="{{ route('profiles.indexAdmin') }}" method="GET">
                    <div class="row align-items-end g-3">
                        <div class="col-md-9">
                            <label class="text-white font-Oswald fs-5 mb-2" for="blader_id">SELECCIONAR BLADER:</label>
                            <select id="blader_id" name="blader_id" class="form-control select2" required>
                                <option value="">-- Buscar blader --</option>
                                @foreach($allUsers as $user)
                                    <option value="{{ $user->id }}" {{ (isset($selectedBladerId) && $selectedBladerId == $user->id) ? 'selected' : '' }}>{{ $user->name }} - {{ $user->email }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 text-end">
                            <button type="submit" class="btn btn-info font-Oswald fs-5 w-100 py-2" style="letter-spacing: 1px;">VER HISTORIAL</button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- RESULTADOS DE LA BÚSQUEDA --}}
            @if(isset($selectedBlader) && $selectedBlader)
                <h4 class="font-Oswald text-white mb-3" style="font-size: 2rem;">
                    <i class="fas fa-trophy me-2" style="color: var(--sbbl-gold);"></i> HISTORIAL DE: <span class="text-warning">{{ strtoupper($selectedBlader->name) }}</span>
                </h4>
                <div class="table-responsive">
                    <table class="table table-tactical">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Torneo</th>
                                <th>Región</th>
                                <th class="text-center">Puesto Alcanzado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bladerHistory as $event)
                                <tr>
                                    <td>
                                        <i class="far fa-calendar-alt text-secondary me-2"></i>
                                        {{ $event->event_date ? \Carbon\Carbon::parse($event->event_date)->format('d/m/Y') : 'Sin fecha' }}
                                    </td>
                                    <td class="fw-bold" style="font-size: 1.1rem;">{{ $event->event_name }}</td>
                                    <td><span class="badge bg-dark border border-secondary text-white">{{ $event->region_name ?? 'Global' }}</span></td>
                                    <td class="text-center">
                                        @if($event->puesto == '1' || strtolower($event->puesto) == 'primero' || strtolower($event->puesto) == '1º')
                                            <span class="badge bg-warning text-dark fs-6 fw-bold"><i class="fas fa-medal me-1"></i> 1º Campeón</span>
                                        @elseif($event->puesto == '2' || strtolower($event->puesto) == 'segundo' || strtolower($event->puesto) == '2º')
                                            <span class="badge bg-secondary text-white fs-6 fw-bold">2º Subcampeón</span>
                                        @elseif($event->puesto == '3' || strtolower($event->puesto) == 'tercero' || strtolower($event->puesto) == '3º')
                                            <span class="badge text-white fs-6 fw-bold" style="background-color: #cd7f32;">3º Lugar</span>
                                        @else
                                            <span class="fw-bold fs-5">{{ $event->puesto ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <form action="{{ route('admin.history.destroy', $event->assist_id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este registro de torneo para este jugador? Esta acción no se puede deshacer.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar registro">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach

                            @if($bladerHistory->isEmpty())
                                <tr>
                                    <td colspan="5" class="text-center text-white-50 p-4">Este blader aún no tiene participaciones registradas en torneos.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            @endif

        </div>

        {{-- =====================================
             PESTAÑA 4: TESORERÍA (GRAN COPA)
             ===================================== --}}
        <div class="tab-pane fade" id="treasury" role="tabpanel" aria-labelledby="treasury-tab">

            {{-- TARJETAS DE RESUMEN GLOBAL --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="p-3 border border-secondary" style="background: rgba(0,0,0,0.5);">
                        <h6 class="text-white-50 font-bangers mb-1 fs-5">TOTAL PARTICIPANTES</h6>
                        <h3 class="text-white m-0 font-bangers fs-1">{{ $treasuryStats->participants }} <i class="fas fa-users fs-4 ms-2 text-secondary"></i></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border border-secondary" style="background: rgba(0,0,0,0.5);">
                        <h6 class="text-white-50 font-bangers mb-1 fs-5">INGRESOS BRUTOS</h6>
                        <h3 class="text-white m-0 font-bangers fs-1">{{ number_format($treasuryStats->gross, 2) }} €</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border border-danger" style="background: rgba(255, 42, 42, 0.1);">
                        <h6 class="text-white-50 font-bangers mb-1 fs-5">COMISIONES PAYPAL</h6>
                        <h3 class="text-danger m-0 font-bangers fs-1">-{{ number_format($treasuryStats->fees, 2) }} €</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border border-success" style="background: rgba(0, 255, 0, 0.1);">
                        <h6 class="text-white-50 font-bangers mb-1 fs-5">INGRESOS NETOS</h6>
                        <h3 class="text-success m-0 font-bangers fs-1">{{ number_format($treasuryStats->net, 2) }} € <i class="fas fa-check-circle fs-4 ms-2"></i></h3>
                    </div>
                </div>
            </div>

            {{-- TABLA DETALLADA POR EVENTO --}}
            <h4 class="font-bangers text-white mb-3" style="font-size: 2rem;"><i class="fas fa-list-alt me-2" style="color: var(--sbbl-gold);"></i> DESGLOSE POR EVENTO (GRAN COPA)</h4>
            <div class="table-responsive">
                <table class="table table-tactical">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Nombre del Torneo</th>
                            <th class="text-center">Bladers Inscritos</th>
                            <th class="text-end">Bruto (5€)</th>
                            <th class="text-end text-danger">Comisión PayPal</th>
                            <th class="text-end text-success">Ingreso Neto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($granCopaEvents as $event)
                            <tr>
                                <td>
                                    <i class="far fa-calendar-alt text-secondary me-2"></i>
                                    {{ \Carbon\Carbon::parse($event->date)->format('d/m/Y') }}
                                </td>
                                <td class="fw-bold" style="font-size: 1.1rem;">
                                    <a href="{{ route('events.show', $event->id) }}" class="text-decoration-none" target="_blank">
                                        {{ $event->name }} <i class="fas fa-external-link-alt ms-1 text-secondary" style="font-size: 0.8rem;"></i>
                                    </a>
                                </td>
                                <td class="text-center fw-bold fs-5">{{ $event->assists_count }}</td>
                                <td class="text-end fw-bold">{{ number_format($event->gross_revenue, 2) }} €</td>
                                <td class="text-end text-danger">-{{ number_format($event->paypal_fees, 2) }} €</td>
                                <td class="text-end text-success fw-bold fs-5">{{ number_format($event->net_revenue, 2) }} €</td>
                            </tr>
                        @endforeach

                        @if($granCopaEvents->isEmpty())
                            <tr>
                                <td colspan="6" class="text-center text-white-50 p-4">Aún no hay torneos de categoría "Gran Copa" registrados en el sistema.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        {{-- =====================================
             PESTAÑA 5: ESTADÍSTICAS REGIONALES
             ===================================== --}}
        <div class="tab-pane fade {{ request()->has('radar_month') ? 'show active' : '' }}" id="regional" role="tabpanel" aria-labelledby="regional-tab">

            {{-- FILTROS REGIONALES --}}
            <div class="command-panel p-4 mb-5" style="background: rgba(0,0,0,0.5); border: 2px solid var(--shonen-cyan);">
                <form method="GET" action="{{ route('profiles.indexAdmin') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="text-white font-bangers fs-5 mb-2">MES</label>
                            <select name="radar_month" class="form-control bg-dark text-white border-secondary">
                                <option value="all" {{ $radarMonth === 'all' ? 'selected' : '' }}>-- TODOS LOS MESES --</option>
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $radarMonth == $m ? 'selected' : '' }}>
                                        {{ strtoupper(\Carbon\Carbon::create()->month($m)->translatedFormat('F')) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="text-white font-bangers fs-5 mb-2">AÑO</label>
                            <select name="radar_year" class="form-control bg-dark text-white border-secondary">
                                <option value="all" {{ $radarYear === 'all' ? 'selected' : '' }}>-- HISTÓRICO TOTAL --</option>
                                @for ($y = now()->year; $y >= now()->year - 5; $y--)
                                    <option value="{{ $y }}" {{ $radarYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3 text-end">
                            <button type="submit" class="btn btn-info font-bangers fs-5 w-100 py-2" style="letter-spacing: 1px;">APLICAR FILTROS</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="text-center mb-4">
                <h4 class="font-bangers text-white" style="font-size: 2rem;">
                    <i class="fas fa-map-marked-alt me-2" style="color: var(--shonen-cyan);"></i>
                    ASISTENCIA POR REGIÓN:
                    <span class="text-warning">
                        @if($radarMonth === 'all' && $radarYear === 'all')
                            HISTÓRICO ABSOLUTO
                        @elseif($radarMonth === 'all')
                            AÑO {{ $radarYear }}
                        @elseif($radarYear === 'all')
                            MES DE {{ strtoupper(\Carbon\Carbon::create()->month($radarMonth)->translatedFormat('F')) }} (TODOS LOS AÑOS)
                        @else
                            {{ strtoupper(\Carbon\Carbon::create()->month($radarMonth)->translatedFormat('F')) }} {{ $radarYear }}
                        @endif
                    </span>
                </h4>
                <p class="text-white-50 small">Análisis de participación y distribución de Jueces/Árbitros por comunidad.</p>
            </div>

            <div class="table-responsive">
                <table class="table table-tactical">
                    <thead>
                        <tr>
                            <th>Región</th>
                            <th class="text-center">Jueces/Árbitros</th>
                            <th class="text-center">Torneos</th>
                            <th class="text-center">Bladers Totales</th>
                            <th class="text-center">Media x Torneo</th>
                            <th class="text-center">Carga x Árbitro</th>
                            <th class="text-center">Estado de la Región</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($regionalStats as $reg)
                            <tr>
                                <td class="fw-bold fs-5">{{ $reg->name }}</td>
                                <td class="text-center">
                                    <span class="badge bg-dark border border-info px-3">{{ $reg->staff_count }}</span>
                                </td>
                                <td class="text-center">{{ $reg->events_count }}</td>
                                <td class="text-center fw-bold text-warning fs-5">{{ $reg->total_players }}</td>
                                <td class="text-center">{{ $reg->avg_players }}</td>
                                <td class="text-center">
                                    <span class="fw-bold" style="color: {{ $reg->ratio > 20 ? 'var(--shonen-red)' : ($reg->ratio > 12 ? 'var(--sbbl-gold)' : 'var(--shonen-cyan)') }}">
                                        {{ $reg->ratio }} <small>Bladers/Árbitro</small>
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($reg->events_count > 0 && $reg->ratio > 20)
                                        <span class="badge bg-danger text-white border border-white"><i class="fas fa-exclamation-triangle me-1"></i> SATURADA</span>
                                    @elseif($reg->events_count > 0)
                                        <span class="badge bg-success text-white border border-white"><i class="fas fa-check-circle me-1"></i> ACTIVA</span>
                                    @else
                                        <span class="badge bg-secondary text-white-50">INACTIVA</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- LEYENDA TÉCNICA --}}
            <div class="mt-4 p-3 bg-black border border-secondary">
                <div class="row text-white-50 small">
                    <div class="col-md-4"><i class="fas fa-info-circle text-info me-1"></i> <strong>Carga x Árbitro:</strong> Cuántos Bladers debe supervisar cada miembro del staff de media por torneo.</div>
                    <div class="col-md-4"><i class="fas fa-signal text-success me-1"></i> <strong>Saturada (>20):</strong> Indica que se necesita reclutar más Jueces o Árbitros en esa región.</div>
                    <div class="col-md-4"><i class="fas fa-moon text-secondary me-1"></i> <strong>Inactiva:</strong> Regiones sin torneos registrados en el periodo seleccionado.</div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Inicializamos Select2
        $('select.select2').select2({
            placeholder: '-- Seleccionar --',
            width: '100%',
            dropdownCssClass: "bg-black border-warning text-white"
        });
    });

    // Función para confirmar la eliminación de la suscripción
    function confirmDeleteSub(id) {
        if (confirm('¿Estás seguro de que quieres eliminar esta suscripción? El usuario perderá sus beneficios de Aura inmediatamente.')) {
            document.getElementById('delete-sub-' + id).submit();
        }
    }
</script>
@endsection
