@extends('layouts.app')

{{--
|--------------------------------------------------------------------------
| ESTILOS (GAMIFICACIÓN / PUESTO DE MANDO)
|--------------------------------------------------------------------------
--}}
@section('styles')
<style>
    /* --- ESTRUCTURA DE PANELES (Command Center) --- */
    .command-panel {
        background-color: #1e1e2f; /* Tu color base original */
        background: linear-gradient(145deg, #1e1e2f 0%, #161625 100%);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        height: 100%;
        position: relative;
    }

    /* Cabeceras de las cajas */
    .panel-header {
        background: rgba(0, 0, 0, 0.2);
        padding: 12px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        color: #adb5bd;
        font-family: 'Courier New', Courier, monospace; /* Toque técnico */
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 1px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* --- STATS (Marcadores) --- */
    .stat-box {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        transition: transform 0.2s, border-color 0.2s;
    }
    .stat-box:hover {
        transform: translateY(-3px);
        border-color: rgba(255, 255, 255, 0.2);
        background: rgba(255, 255, 255, 0.06);
    }
    .stat-value {
        display: block;
        font-size: 1.8rem;
        font-weight: 800;
        line-height: 1.2;
    }
    .stat-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        color: #888;
        letter-spacing: 0.5px;
    }

    /* --- AVATAR Y MARCO (Ajustado para el panel) --- */
    .profile-avatar-container {
        position: relative;
        width: 160px;
        height: 160px;
        margin: -80px auto 15px auto; /* Margen negativo para subirlo sobre el fondo */
        z-index: 10;
    }
    .profile-img-base, .profile-img-frame {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        object-fit: cover;
    }

    /* --- ESTILOS DE SUSCRIPCIÓN --- */
    .suscripcion-nivel-3 { color: gold !important; }
    .suscripcion-nivel-2 { color: #c0e5fb !important; }
    .suscripcion-nivel-1 { color: #CD7F32 !important; }

    .sub-badge {
        font-size: 0.75rem;
        padding: 4px 8px;
        border-radius: 4px;
        border: 1px solid currentColor;
        background: rgba(0,0,0,0.2);
    }

    /* --- NAV TEMPORAL (Meses) --- */
    .time-nav {
        background: #101018;
        border: 1px solid #343a40;
        border-radius: 50px;
        padding: 5px 15px;
        display: inline-flex;
        align-items: center;
        gap: 15px;
    }
    .pilot-name {
        /* Tamaño dinámico: se hace pequeño en móviles (1rem) y crece hasta 1.6rem máximo */
        font-size: clamp(1rem, 5vw, 1.6rem);

        font-weight: 900;
        letter-spacing: -0.5px;

        /* AJUSTES PARA QUE NO SE CORTE */
        white-space: normal; /* Permite que baje de línea si no cabe */
        word-wrap: break-word; /* Rompe la palabra si es más ancha que la caja */
        line-height: 1; /* Altura de línea compacta por si ocupa dos renglones */

        /* Centrado y márgenes */
        text-align: center;
        width: 100%;
        display: block;
    }
</style>
@endsection

{{--
|--------------------------------------------------------------------------
| CONTENIDO PRINCIPAL
|--------------------------------------------------------------------------
--}}
@section('content')
@if (Auth::check() && (Auth::user()->profile->id == $profile->id || Auth::user()->is_admin))
<div class="container py-2">

    {{-- 1. MENSAJES DEL SISTEMA (Alertas) --}}
    @if(session('success') || session('error'))
        <div class="mb-4">
            @if(session('success'))
                <div class="alert alert-success bg-dark border-success text-success d-flex align-items-center shadow">
                    <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
                </div>
            @elseif(session('error'))
                <div class="alert alert-danger bg-dark border-danger text-danger d-flex align-items-center shadow">
                    <i class="fa fa-exclamation-triangle me-2"></i> {{ session('error') }}
                </div>
            @endif
        </div>
    @endif

    {{-- GRID PRINCIPAL --}}
    <div class="row g-4">

        {{-- COLUMNA IZQUIERDA: IDENTIFICACIÓN DEL PILOTO --}}
        <div class="col-lg-4">
            <div class="command-panel">
                {{-- Fondo de cabecera --}}
                <div style="background-image: url('{{ $profile->fondo_url }}');
                            height: 140px;
                            background-size: cover;
                            background-position: center;
                            opacity: 0.9;">
                </div>

                <div class="p-4 text-center">
                    {{-- Avatar --}}
                    <div class="profile-avatar-container">
                        @php
                            $imgPadding = strpos($profile->avatar_url, '.gif') !== false ? 'padding: 15px;' : '';
                        @endphp
                        <img src="{{ $profile->avatar_url }}" class="rounded-circle profile-img-base" style="{{ $imgPadding }}" loading="lazy">
                        <img src="{{ $profile->marco_url }}" class="rounded-circle profile-img-frame" loading="lazy">
                    </div>

                    {{-- Datos Usuario --}}
                    <h2 class="text-white pilot-name mb-1" title="{{ $profile->user->name }}">
                        {{ $profile->user->name }}
                    </h2>
                    <div class="mb-4">
                        <span class="badge bg-secondary text-uppercase" style="letter-spacing: 1px;">
                            {{ $profile->region->name ?? 'SIN ASIGNAR' }}
                        </span>
                    </div>

                    {{-- Botones de Acción --}}
                    @if (Auth::user()->profile->id == $profile->id)
                        <div class="d-grid gap-2">
                            <a href="{{ route('profiles.edit', ['profile' => Auth::user()->id]) }}" class="btn btn-outline-info text-uppercase fw-bold btn-sm">
                                <i class="fas fa-cog me-1"></i> Ajustes de Perfil
                            </a>
                            @if($subscription)
                                <a href="{{ route('collection.index') }}" class="btn btn-outline-warning text-uppercase fw-bold btn-sm">
                                    <i class="fas fa-box-open me-1"></i> Mi Colección
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA: DASHBOARD DE ESTADO --}}
        <div class="col-lg-8">
            <div class="row g-4 h-100">

                {{-- A. SECCIÓN DE RECURSOS (Métricas) --}}
                <div class="col-12">
                    <div class="command-panel p-0">
                        <div class="panel-header">
                            <span><i class="fas fa-chart-line me-2"></i>Métricas de Combate</span>
                            <span class="badge bg-success bg-opacity-25 text-success border border-success">ONLINE</span>
                        </div>

                        <div class="p-4">
                            @php
                                $coinCount = 0;
                                if (Auth::check()) {
                                    $trophy = DB::table('trophies')->where('name', 'SBBL Coin')->first();
                                    if ($trophy) {
                                        $coinCount = DB::table('profilestrophies')
                                            ->where('trophies_id', $trophy->id)
                                            ->where('profiles_id', Auth::user()->id)
                                            ->value('count') ?? 0;
                                    }
                                }
                            @endphp

                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="stat-box">
                                        <span class="stat-value text-warning">{{ number_format($coinCount) }}</span>
                                        <span class="stat-label">Lagartos (Coins) 🦎</span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="stat-box">
                                        <span class="stat-value text-info">{{ $profile->points_x2 }}</span>
                                        <span class="stat-label">Puntos de Rango</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- B. LICENCIA Y COMUNICACIONES --}}
                <div class="col-12">
                    <div class="command-panel p-0">
                        <div class="panel-header">
                            <span><i class="fas fa-id-card me-2"></i>Estado & Comunicaciones</span>
                        </div>

                        <div class="p-4">
                            {{-- Suscripción --}}
                            @if($subscription)
                                @php
                                    $planName = strtolower($subscription->plan->slug);

                                    // Valor por defecto (equivalente al 'default' del match)
                                    $claseNivel = 'text-white';

                                    // Lógica tradicional compatible con todas las versiones de PHP
                                    if ($planName == 'oro') {
                                        $claseNivel = 'suscripcion-nivel-3';
                                    } elseif ($planName == 'plata') {
                                        $claseNivel = 'suscripcion-nivel-2';
                                    } elseif ($planName == 'bronce') {
                                        $claseNivel = 'suscripcion-nivel-1';
                                    }

                                    $periodo = $subscription->period === 'monthly' ? 'Mensual' : 'Anual';
                                @endphp
                                <div class="d-flex flex-wrap align-items-center justify-content-between p-3 rounded mb-3" style="background: rgba(255,255,255,0.03); border: 1px dashed rgba(255,255,255,0.1);">
                                    <div>
                                        <h5 class="mb-1 text-uppercase fw-bold {{ $claseNivel }}">
                                            Licencia {{ ucfirst($planName) }}
                                        </h5>
                                        <small class="text-white">
                                            Válida hasta: <span class="text-white">{{ $subscription->ended_at->format('d/m/Y') }}</span>
                                        </small>
                                    </div>
                                    <span class="sub-badge {{ $claseNivel }}">{{ $periodo }}</span>
                                </div>
                            @else
                                <div class="alert alert-dark border-secondary text-center mb-3">
                                    <small class="text-white">No se detecta licencia activa.</small>
                                </div>
                            @endif

                            {{-- Invitaciones Pendientes --}}
                            @if(!$invitacionesPendientes->isEmpty())
                                <div class="mt-4">
                                    <h6 class="text-danger text-uppercase fs-6 mb-3 border-bottom border-danger pb-2" style="font-family: monospace;">
                                        ⚠ Solicitudes de Reclutamiento
                                    </h6>
                                    @foreach ($invitacionesPendientes as $invitacion)
                                        <div class="d-flex justify-content-between align-items-center bg-dark p-2 rounded border border-secondary mb-2">
                                            <span class="text-white small">Equipo: <strong>{{ $invitacion->team->name }}</strong></span>
                                            <div>
                                                <form action="{{ route('invitations.accept', $invitacion) }}" method="POST" class="d-inline">
                                                    @csrf <button class="btn btn-success btn-sm py-0 px-2">✓</button>
                                                </form>
                                                <form action="{{ route('invitations.reject', $invitacion) }}" method="POST" class="d-inline">
                                                    @csrf <button class="btn btn-danger btn-sm py-0 px-2">✕</button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div> {{-- Fin Grid Principal --}}

    {{-- 3. SECCIÓN INFERIOR: HISTORIAL DE MISIONES (Eventos) --}}
    <div class="mt-5">
        <div class="command-panel p-4">

            {{-- Header con Navegación --}}
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary">
                <h3 class="h5 text-white text-uppercase mb-3 mb-md-0" style="font-family: monospace;">
                    <i class="fas fa-calendar-alt me-2"></i>Registro de Eventos
                </h3>

                {{-- Navegación Meses --}}
                @php
                    $prevMonthUrl = route('profiles.show', ['profile' => $profile->id, 'month' => $currentMonth == 1 ? 12 : $currentMonth - 1, 'year' => $currentMonth == 1 ? $currentYear - 1 : $currentYear]);
                    $nextMonthUrl = route('profiles.show', ['profile' => $profile->id, 'month' => $currentMonth == 12 ? 1 : $currentMonth + 1, 'year' => $currentMonth == 12 ? $currentYear + 1 : $currentYear]);
                    $monthName = \Carbon\Carbon::create($currentYear, $currentMonth)->translatedFormat('F Y');
                @endphp
                <div class="time-nav">
                    <a href="{{ $prevMonthUrl }}" class="text-secondary text-decoration-none fs-5 hover-white"><i class="fas fa-chevron-left"></i></a>
                    <span class="text-info fw-bold text-uppercase" style="min-width: 120px; text-align: center;">{{ $monthName }}</span>
                    <a href="{{ $nextMonthUrl }}" class="text-secondary text-decoration-none fs-5 hover-white"><i class="fas fa-chevron-right"></i></a>
                </div>
            </div>

            {{-- Grid de Eventos --}}
            <div class="row">
                @forelse ($eventos as $evento)
                    <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                        <div class="card h-100 bg-dark text-white border border-secondary shadow-sm" style="transition: transform 0.2s;">
                            {{-- Imagen Evento --}}
                            @php
                                $eventImageUrl = $evento->image_mod ? 'data:image/png;base64,' . $evento->image_mod : "/storage/{$evento->imagen}";
                            @endphp
                            <div style="height: 150px; background: url('{{ $eventImageUrl }}') center center no-repeat; background-size: cover; border-bottom: 1px solid rgba(255,255,255,0.1);"></div>

                            <div class="card-body d-flex flex-column p-3">
                                <h6 class="fw-bold text-truncate mb-1">{{ $evento->name }}</h6>
                                <p class="small text-secondary mb-2">{{ $evento->region->name }}</p>
                                <div class="mt-auto">
                                    <p class="small text-info mb-2"><event-date fecha="{{ $evento->date }}"></event-date></p>
                                    <a href="{{ route('events.show', ['event' => $evento->id]) }}" class="btn btn-outline-light btn-sm w-100 text-uppercase fw-bold" style="font-size: 0.7rem;">
                                        Ver Informe
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 py-5 text-center">
                        <div class="text-white" style="font-family: monospace;">
                            -- NO HAY ACTIVIDAD REGISTRADA EN ESTE SECTOR TEMPORAL --
                        </div>
                    </div>
                @endforelse
            </div>

        </div>
    </div>

</div>
@endif
@endsection
