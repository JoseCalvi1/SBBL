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
    @php
        $isEquityTabActive = request()->has('radar_month') || request()->has('radar_year');
        $isBladerTabActive = !$isEquityTabActive && request('blader_id');
        $isRolesTabActive = !$isEquityTabActive && !$isBladerTabActive;
    @endphp
    <ul class="nav nav-tabs" id="userTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ $isRolesTabActive ? 'active' : '' }}" id="roles-tab" data-bs-toggle="tab" href="#roles" role="tab"><span><i class="fas fa-id-card-alt me-2"></i> ROLES Y ACCESOS</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="subs-tab" data-bs-toggle="tab" href="#subs" role="tab"><span><i class="fas fa-gem me-2"></i> SUSCRIPCIONES</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $isBladerTabActive ? 'active' : '' }}" id="stats-tab" data-bs-toggle="tab" href="#stats" role="tab"><span><i class="fas fa-search me-2"></i> HISTORIAL DE BLADER</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="treasury-tab" data-bs-toggle="tab" href="#treasury" role="tab"><span><i class="fas fa-coins me-2"></i> TESORERÍA</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $isEquityTabActive ? 'active' : '' }}" id="regional-tab" data-bs-toggle="tab" href="#regional" role="tab"><span><i class="fas fa-balance-scale me-2"></i> EQUIDAD ARBITRAL</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="discord-tab" data-bs-toggle="tab" href="#discord" role="tab"><span><i class="fab fa-discord me-2" style="color: #5865F2;"></i> DISCORD</span></a>
        </li>
    </ul>

    <div class="tab-content" id="userTabsContent">

        {{-- =====================================
             PESTAÑA 1: ROLES
             ===================================== --}}
        <div class="tab-pane fade {{ $isRolesTabActive ? 'show active' : '' }}" id="roles" role="tabpanel" aria-labelledby="roles-tab">

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
                                <td class="fw-bold">{{ $sub->user->name ?? 'Desconocido' }}</td>
                                <td>
                                    <select form="edit-sub-{{ $sub->id }}" name="plan_id" class="form-control form-control-sm bg-dark text-white border-secondary">
                                        @foreach($plans as $plan)
                                            <option value="{{ $plan->id }}" {{ $sub->plan_id == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select form="edit-sub-{{ $sub->id }}" name="period" class="form-control form-control-sm bg-dark text-white border-secondary">
                                        <option value="monthly" {{ $sub->period == 'monthly' ? 'selected' : '' }}>Mensual</option>
                                        <option value="annual" {{ $sub->period == 'annual' ? 'selected' : '' }}>Anual</option>
                                    </select>
                                </td>
                                <td>
                                    <select form="edit-sub-{{ $sub->id }}" name="status" class="form-control form-control-sm bg-dark text-white border-secondary">
                                        <option value="active" {{ $sub->status == 'active' ? 'selected' : '' }}>Activa</option>
                                        <option value="pending" {{ $sub->status == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="canceled" {{ $sub->status == 'canceled' ? 'selected' : '' }}>Cancelada</option>
                                        <option value="expired" {{ $sub->status == 'expired' ? 'selected' : '' }}>Expirada</option>
                                    </select>
                                </td>

                                <td>
                                    <input form="edit-sub-{{ $sub->id }}" type="text" name="paypal_subscription_id" value="{{ $sub->paypal_subscription_id }}" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Sin ID">
                                </td>

                                <td>
                                    <input form="edit-sub-{{ $sub->id }}" type="date" name="ended_at" value="{{ $sub->ended_at ? $sub->ended_at->format('Y-m-d') : '' }}" class="form-control form-control-sm bg-dark text-white border-secondary">
                                </td>
                                <td class="text-end">
                                    {{-- FORMULARIOS OCULTOS EN LA CELDA DE ACCIONES --}}
                                    <form id="edit-sub-{{ $sub->id }}" action="{{ route('admin.subscriptions.update', $sub->id) }}" method="POST" style="display: none;">
                                        @csrf @method('PUT')
                                    </form>

                                    <form id="delete-sub-{{ $sub->id }}" action="{{ route('admin.subscriptions.destroy', $sub->id) }}" method="POST" style="display:none;">
                                        @csrf @method('DELETE')
                                    </form>

                                    <div class="btn-group">
                                        <button type="submit" form="edit-sub-{{ $sub->id }}" class="btn btn-sm btn-success" title="Guardar cambios"><i class="fas fa-save"></i></button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmDeleteSub('{{ $sub->id }}')" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
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
             PESTAÑA 5: EQUIDAD ARBITRAL
             ===================================== --}}
        <div class="tab-pane fade {{ $isEquityTabActive ? 'show active' : '' }}" id="regional" role="tabpanel" aria-labelledby="regional-tab">

            {{-- FILTROS --}}
            <div class="command-panel p-4 mb-4" style="background: rgba(0,0,0,0.5); border: 2px solid var(--shonen-cyan);">
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
                                <option value="all" {{ $radarYear === 'all' ? 'selected' : '' }}>-- HISTÓRICO DESDE HUB --</option>
                                @for ($y = now()->year; $y >= 2026; $y--)
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
                    <i class="fas fa-balance-scale me-2" style="color: var(--shonen-cyan);"></i>
                    EQUIDAD ARBITRAL Y PARTICIPACIÓN:
                    <span class="text-warning">
                        @if($radarMonth === 'all' && $radarYear === 'all')
                            DESDE ACTIVACIÓN HUB (OCT 2026)
                        @elseif($radarMonth === 'all')
                            AÑO {{ $radarYear }}
                        @elseif($radarYear === 'all')
                            MES DE {{ strtoupper(\Carbon\Carbon::create()->month($radarMonth)->translatedFormat('F')) }} (TODOS LOS AÑOS)
                        @else
                            {{ strtoupper(\Carbon\Carbon::create()->month($radarMonth)->translatedFormat('F')) }} {{ $radarYear }}
                        @endif
                    </span>
                </h4>
                <p class="text-white-50 small">Torneos de Ranking con arbitraje asignado desde el Hub (desde el 09/10/2026). Desglose mensual, por comunidad/provincia y torneo a torneo.</p>
            </div>

            {{-- RESUMEN GLOBAL --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="p-3 bg-black border border-info rounded text-center">
                        <div class="text-white-50 small text-uppercase">Torneos Ranking con Staff</div>
                        <div class="font-bangers fs-2 text-info">{{ $arbitrationSummary->events_count }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-3 bg-black border border-warning rounded text-center">
                        <div class="text-white-50 small text-uppercase">Bladers Únicos (Inscripciones)</div>
                        <div class="font-bangers fs-2 text-warning">
                            {{ $arbitrationSummary->unique_participants }}
                            <small class="fs-6 text-white-50">({{ $arbitrationSummary->total_inscriptions }})</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-3 bg-black border border-success rounded text-center">
                        <div class="text-white-50 small text-uppercase">Staff Oficial Apuntado</div>
                        <div class="font-bangers fs-2 text-success">{{ $arbitrationSummary->staff_count }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-3 bg-black border border-secondary rounded text-center">
                        <div class="text-white-50 small text-uppercase">Ayudantes (Revisor / Manual)</div>
                        <div class="font-bangers fs-2 text-white">{{ $arbitrationSummary->helpers_count }}</div>
                    </div>
                </div>
            </div>

            {{-- TABLA 1: COMUNIDADES (CON DESPLEGABLE DE PROVINCIAS SI HAY > 1 Y DESGLOSE MENSUAL) --}}
            <div class="mb-2 d-flex justify-content-between align-items-center">
                <h5 class="font-bangers text-white fs-4 m-0"><i class="fas fa-map-marked-alt me-2 text-info"></i> DESGLOSE POR COMUNIDAD Y MENSUAL</h5>
                <span class="text-white-50 small">Pulsa en una comunidad con varias provincias o en "Meses" para desplegar el detalle.</span>
            </div>
            <div class="table-responsive mb-5">
                <table class="table table-tactical align-middle">
                    <thead>
                        <tr>
                            <th>Comunidad / Provincia</th>
                            <th class="text-center">Torneos Ranking</th>
                            <th class="text-center">Bladers Únicos</th>
                            <th class="text-center">Staff Oficial</th>
                            <th class="text-center">Ayudantes</th>
                            <th class="text-center">Desglose Mensual</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($regionalStats as $reg)
                            <tr>
                                <td class="fw-bold fs-5">
                                    @if($reg->has_multiple_provinces)
                                        <button class="btn btn-sm btn-outline-info me-2 py-0 px-2" type="button" data-bs-toggle="collapse" data-bs-target="#prov-region-{{ $reg->id }}" aria-expanded="false" title="Ver provincias">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                    @endif
                                    {{ $reg->name }}
                                    @if($reg->has_multiple_provinces)
                                        <span class="badge bg-dark border border-secondary text-white-50 ms-1" style="font-size: 0.7rem;">{{ $reg->provinces_stats->count() }} prov.</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-dark border border-info px-3 fs-6">{{ $reg->events_count }}</span>
                                </td>
                                <td class="text-center fw-bold text-warning fs-5">
                                    {{ $reg->unique_participants }}
                                    @if($reg->total_inscriptions > $reg->unique_participants)
                                        <small class="text-white-50 fw-normal fs-6">({{ $reg->total_inscriptions }} insc.)</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success px-3 fs-6">{{ $reg->staff_count }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary px-3 fs-6">{{ $reg->helpers_count }}</span>
                                </td>
                                <td class="text-center">
                                    @if($reg->monthly_breakdown->isNotEmpty())
                                        <button class="btn btn-sm btn-outline-warning py-0 px-2" type="button" data-bs-toggle="collapse" data-bs-target="#monthly-region-{{ $reg->id }}">
                                            <i class="fas fa-calendar-alt me-1"></i> {{ $reg->monthly_breakdown->count() }} {{ $reg->monthly_breakdown->count() === 1 ? 'mes' : 'meses' }}
                                        </button>
                                    @else
                                        <span class="text-white-50 small">Sin actividad</span>
                                    @endif
                                </td>
                            </tr>

                            {{-- DESGLOSE MENSUAL DE LA COMUNIDAD --}}
                            @if($reg->monthly_breakdown->isNotEmpty())
                                <tr class="collapse" id="monthly-region-{{ $reg->id }}" style="background: rgba(255, 193, 7, 0.05);">
                                    <td colspan="6" class="p-3">
                                        <div class="small text-warning fw-bold mb-2"><i class="fas fa-calendar-check me-1"></i> EVOLUCIÓN MENSUAL EN {{ mb_strtoupper($reg->name) }}:</div>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($reg->monthly_breakdown as $mStat)
                                                <div class="p-2 bg-black border border-secondary rounded small">
                                                    <div class="fw-bold text-info">{{ $mStat->month_label }}</div>
                                                    <div class="text-white-50">
                                                        Torneos: <strong class="text-white">{{ $mStat->events_count }}</strong> |
                                                        Bladers: <strong class="text-warning">{{ $mStat->unique_participants }}</strong> |
                                                        Staff: <strong class="text-success">{{ $mStat->staff_count }}</strong> |
                                                        Ayud.: <strong class="text-white">{{ $mStat->helpers_count }}</strong>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endif

                            {{-- DESPLEGABLE DE PROVINCIAS (SOLO SI HAY MÁS DE 1 PROVINCIA) --}}
                            @if($reg->has_multiple_provinces)
                                @foreach($reg->provinces_stats as $prov)
                                    <tr class="collapse" id="prov-region-{{ $reg->id }}" style="background: rgba(0, 229, 255, 0.04);">
                                        <td class="ps-5 text-info">
                                            <i class="fas fa-level-up-alt fa-rotate-90 me-2 text-white-50"></i>
                                            {{ $prov->name }}
                                        </td>
                                        <td class="text-center">{{ $prov->events_count }}</td>
                                        <td class="text-center text-warning fw-bold">
                                            {{ $prov->unique_participants }}
                                            @if($prov->total_inscriptions > $prov->unique_participants)
                                                <small class="text-white-50 fw-normal">({{ $prov->total_inscriptions }})</small>
                                            @endif
                                        </td>
                                        <td class="text-center"><span class="badge bg-dark border border-success text-success">{{ $prov->staff_count }}</span></td>
                                        <td class="text-center"><span class="badge bg-dark border border-secondary text-white-50">{{ $prov->helpers_count }}</span></td>
                                        <td class="text-center">
                                            @if($prov->monthly_breakdown->isNotEmpty())
                                                <div class="d-flex flex-wrap justify-content-center gap-1">
                                                    @foreach($prov->monthly_breakdown as $pm)
                                                        <span class="badge bg-black border border-secondary text-white-50" style="font-size: 0.72rem;">
                                                            {{ $pm->month_label }}: <strong class="text-warning">{{ $pm->unique_participants }}</strong> bladers ({{ $pm->events_count }}T)
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-white-50 small">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- TABLA 2: DESGLOSE TORNEO A TORNEO --}}
            <div class="mb-2 d-flex justify-content-between align-items-center">
                <h5 class="font-bangers text-white fs-4 m-0"><i class="fas fa-trophy me-2 text-warning"></i> CONTABILIDAD TORNEO A TORNEO</h5>
                <span class="badge bg-dark border border-warning text-warning">{{ $tournamentBreakdown->count() }} torneos</span>
            </div>
            <div class="table-responsive">
                <table class="table table-tactical align-middle">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Torneo</th>
                            <th>Comunidad / Provincia</th>
                            <th class="text-center">Participantes</th>
                            <th class="text-center">Staff Oficial</th>
                            <th class="text-center">Ayudantes (Revisor / Manual)</th>
                            <th class="text-center">Total Arbitraje</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tournamentBreakdown as $t)
                            <tr>
                                <td class="text-nowrap">
                                    <div class="fw-bold text-white">{{ $t->date_formatted }}</div>
                                    <div class="text-white-50 small">{{ $t->month_label }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-warning">{{ $t->name }}</div>
                                    <span class="badge bg-dark border border-info text-info" style="font-size: 0.7rem;">
                                        {{ $t->beys === 'rankingplus' ? 'RANKING PLUS' : 'RANKING' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="text-white fw-bold">{{ $t->region_name }}</div>
                                    <div class="text-info small">{{ $t->province_name }}</div>
                                </td>
                                <td class="text-center fw-bold text-warning fs-5">{{ $t->participants_count }}</td>
                                <td class="text-center">
                                    <span class="badge bg-success px-3 fs-6">{{ $t->staff_count }}</span>
                                    @if(!empty($t->staff_names))
                                        <div class="text-white-50 small mt-1">{{ implode(', ', $t->staff_names) }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary px-3 fs-6">{{ $t->helpers_count }}</span>
                                    @if(!empty($t->helper_names))
                                        <div class="text-white-50 small mt-1">{{ implode(', ', $t->helper_names) }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info text-dark fw-bold px-3 fs-6">{{ $t->total_arbitration }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-white-50 p-5">
                                    No hay torneos de ranking con equipo arbitral apuntado en el periodo seleccionado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- LEYENDA TÉCNICA --}}
            <div class="mt-4 p-3 bg-black border border-secondary">
                <div class="row text-white-50 small">
                    <div class="col-md-4"><i class="fas fa-user-shield text-success me-1"></i> <strong>Staff Oficial:</strong> Usuarios apuntados en el Hub con rol Admin, Juez o Árbitro.</div>
                    <div class="col-md-4"><i class="fas fa-hands-helping text-info me-1"></i> <strong>Ayudantes:</strong> Usuarios con rol Revisor o añadidos manualmente al torneo.</div>
                    <div class="col-md-4"><i class="fas fa-users text-warning me-1"></i> <strong>Bladers Únicos:</strong> Participantes distintos en ese mes/comunidad (y total de inscripciones entre paréntesis).</div>
                </div>
            </div>
        </div>

        {{-- =====================================
             PESTAÑA 6: DISCORD VINCULADOS
             ===================================== --}}
        <div class="tab-pane fade" id="discord" role="tabpanel" aria-labelledby="discord-tab">
            <div class="mb-4 border-bottom border-secondary pb-3">
                <h4 class="font-bangers text-white m-0" style="font-size: 2rem;">
                    <i class="fab fa-discord me-2" style="color: #5865F2;"></i>
                    BLADERS VINCULADOS A DISCORD
                </h4>
                <p class="text-white-50 small m-0 mt-1">Sincronización en vivo. Los nombres se leen directamente del servidor de Discord sin ocupar espacio en la base de datos.</p>
            </div>

            <div class="table-responsive">
                <table class="table table-tactical">
                    <thead>
                        <tr>
                            <th>Nombre en la Web</th>
                            <th>Nombre en Discord</th>
                            <th>ID Discord</th>
                            <th>Email</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($discordUsers as $dUser)
                            <tr>
                                <td class="fw-bold fs-5 text-warning">{{ $dUser->name }}</td>
                                <td class="fw-bold fs-5 text-info">
                                    {{-- Aquí está la magia: Cruzamos el ID del usuario con la lista en memoria --}}
                                    @if(isset($discordNamesMap[$dUser->discord_id]))
                                        <i class="fab fa-discord me-1" style="color: #5865F2;"></i> {{ $discordNamesMap[$dUser->discord_id] }}
                                    @else
                                        <span class="text-white-50 fs-6 fw-normal"><i class="fas fa-ghost me-1"></i> No está en el servidor</span>
                                    @endif
                                </td>
                                <td>{{ $dUser->discord_id }}</td>
                                <td>{{ $dUser->email }}</td>
                                <td class="text-center">
                                    <span class="badge bg-success text-white border border-white px-3 py-2">
                                        <i class="fas fa-link me-1"></i> VINCULADO
                                    </span>
                                </td>
                            </tr>
                        @endforeach

                        @if($discordUsers->isEmpty())
                            <tr>
                                <td colspan="4" class="text-center text-white-50 p-5">
                                    <i class="fab fa-discord fs-1 mb-3 d-block" style="color: rgba(255,255,255,0.1);"></i>
                                    Aún no hay usuarios con su cuenta de Discord vinculada.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
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
