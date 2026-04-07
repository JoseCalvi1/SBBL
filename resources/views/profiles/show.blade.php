@extends('layouts.app')

@section('title', 'Perfil de Piloto - SBBL')

{{--
|--------------------------------------------------------------------------
| ESTILOS ESPECÍFICOS DEL PERFIL
|--------------------------------------------------------------------------
--}}
@section('styles')
<style>
    /* --- AVATAR Y MARCO (100% Circulares y Limpios) --- */
    .profile-avatar-container {
        position: relative;
        width: 160px;
        height: 160px;
        margin: -80px auto 15px auto;
        z-index: 10;
        border-radius: 50%;
    }
    .profile-img-base {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        object-fit: cover;
        border-radius: 50% !important;
        border: 4px solid var(--sbbl-gold);
        background: var(--sbbl-blue-1); /* Fondo base por si es un gif transparente */
        box-shadow: 0 4px 15px rgba(0,0,0,0.8);
    }
    .profile-img-frame {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        object-fit: cover;
        border-radius: 50% !important;
        z-index: 2;
    }

    /* --- ETIQUETAS DE SUSCRIPCIÓN --- */
    .sub-badge {
        font-family: 'Oswald', cursive;
        font-size: 1.2rem;
        padding: 5px 15px;
        border-radius: 0;
        border: 2px solid #000;
        background: #000;
        letter-spacing: 1px;
        box-shadow: 3px 3px 0 var(--sbbl-blue-3);
        transform: skewX(-5deg);
        display: inline-block;
    }
    .sub-badge > span { display: block; transform: skewX(5deg); }

    /* --- NAV TEMPORAL (Meses) --- */
    .time-nav {
        background: #000;
        border: 3px solid var(--sbbl-gold);
        border-radius: 0;
        padding: 5px 15px;
        display: inline-flex;
        align-items: center;
        gap: 15px;
        box-shadow: 4px 4px 0 #000;
        transform: skewX(-5deg);
    }
    .time-nav > * { transform: skewX(5deg); }
    .time-nav a { transition: 0.2s; color: #fff; }
    .time-nav a:hover { color: var(--shonen-cyan) !important; transform: scale(1.2) skewX(5deg); }

    /* --- NOMBRE DEL PILOTO --- */
    .pilot-name {
        font-family: 'Oswald', cursive;
        font-size: clamp(2.5rem, 5vw, 3.5rem);
        color: var(--sbbl-gold);
        text-shadow: 3px 3px 0px #000, 6px 6px 0px var(--sbbl-blue-3);
        letter-spacing: 2px;
        white-space: normal;
        word-wrap: break-word;
        line-height: 1;
        text-align: center;
        width: 100%;
        display: block;
    }

    /* --- TARJETAS DE EVENTOS (Historial) --- */
    .event-card-shonen {
        background: var(--sbbl-blue-2);
        border: 3px solid #000;
        border-radius: 0;
        transition: 0.2s;
        box-shadow: 5px 5px 0 #000;
    }
    .event-card-shonen:hover {
        border-color: var(--sbbl-gold);
        box-shadow: 6px 6px 0 var(--shonen-red);
        transform: translate(-3px, -3px);
    }
    .event-card-shonen img { border-bottom: 3px solid #000; }
</style>
@endsection

{{--
|--------------------------------------------------------------------------
| CONTENIDO PRINCIPAL
|--------------------------------------------------------------------------
--}}
@section('content')
@if (Auth::check() && (Auth::user()->profile->id == $profile->id || Auth::user()->hasRole('admin')))
<div class="container py-4">

    {{-- 1. MENSAJES DEL SISTEMA (Alertas Globales Heredadas) --}}
    @if(session('success') || session('error'))
        <div class="mb-4">
            @if(session('success'))
                <div class="alert alert-shonen alert-shonen-success d-flex align-items-center">
                    <div><i class="fa fa-check-circle me-2"></i> {{ session('success') }}</div>
                </div>
            @elseif(session('error'))
                <div class="alert alert-shonen alert-shonen-danger d-flex align-items-center">
                    <div><i class="fa fa-exclamation-triangle me-2"></i> {{ session('error') }}</div>
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
                            border-bottom: 4px solid #000;
                            filter: contrast(1.1) brightness(0.8);">
                </div>

                <div class="p-4 text-center">
                    {{-- Avatar Circular --}}
                    <div class="profile-avatar-container">
                        @php
                            $imgPadding = strpos($profile->avatar_url, '.gif') !== false ? 'padding: 15px;' : '';
                        @endphp
                        <img src="{{ $profile->avatar_url }}" class="profile-img-base" style="{{ $imgPadding }}" loading="lazy">
                        @if($profile->marco_url)
                            <img src="{{ $profile->marco_url }}" class="profile-img-frame" loading="lazy">
                        @endif
                    </div>

                    {{-- Datos Usuario --}}
                    <h2 class="pilot-name mb-2" title="{{ $profile->user->name }}">
                        {{ $profile->user->name }}
                        <span style="font-size:0.4em; color: #fff; text-shadow: 1px 1px 0 #000; font-family: 'Montserrat', sans-serif; font-weight: 900; display: block; margin-top: 10px;">
                            #{{ str_pad($profile->user->id, 4, '0', STR_PAD_LEFT) }}
                        </span>
                    </h2>

                    <div class="mb-4 mt-3">
                        <span class="badge bg-black border border-white text-uppercase py-2 px-3 text-white" style="font-family: 'Oswald', cursive; font-size: 1.2rem; letter-spacing: 1px; box-shadow: 3px 3px 0 var(--sbbl-blue-3); transform: skewX(-5deg); display: inline-block;">
                            <span style="transform: skewX(5deg); display: block;">{{ $profile->region->name ?? 'ZONA DESCONOCIDA' }}</span>
                        </span>
                    </div>

                    {{-- Botones de Acción --}}
                    @if (Auth::user()->profile->id == $profile->id)
                        <div class="d-grid gap-3 mt-4">
                            <a href="{{ route('profiles.edit', ['profile' => Auth::user()->id]) }}" class="btn-shonen btn-shonen-info text-center w-100">
                                <span><i class="fas fa-cog me-1"></i> CALIBRAR ARMADURA</span>
                            </a>
                            @if($subscription)
                                <a href="{{ route('collection.index') }}" class="btn-shonen btn-shonen-warning text-center w-100">
                                    <span><i class="fas fa-box-open me-1"></i> ARSENAL (COLECCIÓN)</span>
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
                            <span><i class="fas fa-chart-line me-2" style="color: var(--sbbl-gold);"></i> Nivel de Poder</span>
                            <span class="badge bg-white text-dark border border-dark" style="font-family: 'Oswald', cursive; font-size: 1.1rem; transform: skewX(-5deg);"><span style="display:block; transform:skewX(5deg);">ONLINE</span></span>
                        </div>

                        <div class="p-4" style="background: var(--sbbl-blue-1);">
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
                                    <div class="stat-box border-secondary">
                                        <span class="stat-value" style="color: var(--sbbl-gold);">{{ number_format($coinCount) }}</span>
                                        <span class="stat-label text-white">Lagartos (Coins) 🦎</span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="stat-box border-secondary">
                                        <span class="stat-value" style="color: var(--shonen-cyan);">{{ $profile->points_x2 }}</span>
                                        <span class="stat-label text-white">Puntos de Rango</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- B. LICENCIA Y COMUNICACIONES --}}
                <div class="col-12">
                    <div class="command-panel p-0">
                        <div class="panel-header" style="border-bottom-color: var(--shonen-red);">
                            <span><i class="fas fa-id-card me-2" style="color: var(--shonen-red);"></i> Estado & Reclutamiento</span>
                        </div>

                        <div class="p-4" style="background: var(--sbbl-blue-1);">
                            {{-- Suscripción --}}
                            @if($subscription)
                                @php
                                    $planName = strtolower($subscription->plan->slug);
                                    $claseNivel = 'text-white';
                                    if ($planName == 'oro') $claseNivel = 'suscripcion-nivel-3';
                                    elseif ($planName == 'plata') $claseNivel = 'suscripcion-nivel-2';
                                    elseif ($planName == 'bronce') $claseNivel = 'suscripcion-nivel-1';
                                    $periodo = $subscription->period === 'monthly' ? 'Mensual' : 'Anual';
                                @endphp
                                <div class="d-flex flex-wrap align-items-center justify-content-between p-3 mb-3 bg-black border border-secondary" style="transform: skewX(-2deg);">
                                    <div style="transform: skewX(2deg);">
                                        <h5 class="mb-1 text-uppercase font-Oswald fs-3 {{ $claseNivel }}">
                                            Licencia {{ ucfirst($planName) }}
                                        </h5>
                                        <small class="text-white fw-bold">
                                            Válida hasta: <span style="color: var(--shonen-cyan);">{{ $subscription->ended_at->format('d/m/Y') }}</span>
                                        </small>
                                    </div>
                                    <span class="sub-badge {{ $claseNivel }}"><span>{{ $periodo }}</span></span>
                                </div>
                            @else
                                <div class="alert bg-black border border-secondary text-center mb-3" style="border-radius: 0;">
                                    <span class="text-white fw-bold font-Oswald fs-4">NO SE DETECTA AURA ACTIVA.</span>
                                </div>
                            @endif

                            {{-- Invitaciones Pendientes --}}
                            @if(!$invitacionesPendientes->isEmpty())
                                <div class="mt-4 p-3" style="background: rgba(255, 42, 42, 0.1); border: 2px solid var(--shonen-red);">
                                    <h6 class="text-danger text-uppercase fs-4 mb-3 font-Oswald" style="text-shadow: 1px 1px 0 #000;">
                                        <i class="fas fa-exclamation-triangle me-2"></i> ¡SOLICITUDES DE FACCIÓN!
                                    </h6>
                                    @foreach ($invitacionesPendientes as $invitacion)
                                        <div class="d-flex justify-content-between align-items-center bg-black p-2 border border-danger mb-2" style="transform: skewX(-5deg);">
                                            <span class="text-white fw-bold small ms-2" style="transform: skewX(5deg); font-size: 1rem;">Sindicato: <strong style="color: var(--sbbl-gold); font-family: 'Oswald', cursive; font-size: 1.3rem; letter-spacing: 1px;">{{ $invitacion->team->name }}</strong></span>
                                            <div style="transform: skewX(5deg);">
                                                <form action="{{ route('invitations.accept', $invitacion) }}" method="POST" class="d-inline">
                                                    @csrf <button class="btn btn-success btn-sm rounded-0 border-dark fw-bold px-3" style="box-shadow: 2px 2px 0 #000;">ACEPTAR</button>
                                                </form>
                                                <form action="{{ route('invitations.reject', $invitacion) }}" method="POST" class="d-inline">
                                                    @csrf <button class="btn btn-danger btn-sm rounded-0 border-dark fw-bold px-3" style="box-shadow: 2px 2px 0 #000;">RECHAZAR</button>
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
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 pb-3 border-bottom border-dark" style="border-width: 4px !important;">
                <h3 class="font-Oswald text-white text-uppercase mb-3 mb-md-0" style="font-size: 2.5rem; text-shadow: 2px 2px 0 #000;">
                    <i class="fas fa-calendar-alt me-2" style="color: var(--sbbl-gold);"></i> Registro de Batallas
                </h3>

                {{-- Navegación Meses --}}
                @php
                    $prevMonthUrl = route('profiles.show', ['profile' => $profile->id, 'month' => $currentMonth == 1 ? 12 : $currentMonth - 1, 'year' => $currentMonth == 1 ? $currentYear - 1 : $currentYear]);
                    $nextMonthUrl = route('profiles.show', ['profile' => $profile->id, 'month' => $currentMonth == 12 ? 1 : $currentMonth + 1, 'year' => $currentMonth == 12 ? $currentYear + 1 : $currentYear]);
                    $monthName = \Carbon\Carbon::create($currentYear, $currentMonth)->translatedFormat('F Y');
                @endphp
                <div class="time-nav">
                    <a href="{{ $prevMonthUrl }}" class="text-white text-decoration-none fs-5"><span><i class="fas fa-chevron-left"></i></span></a>
                    <span class="fw-bold text-uppercase font-Oswald fs-4 text-white" style="min-width: 120px; text-align: center;"><span>{{ $monthName }}</span></span>
                    <a href="{{ $nextMonthUrl }}" class="text-white text-decoration-none fs-5"><span><i class="fas fa-chevron-right"></i></span></a>
                </div>
            </div>

            {{-- Grid de Eventos --}}
            <div class="row">
                @forelse ($eventos as $evento)
                    <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                        <div class="card h-100 event-card-shonen">
                            {{-- Imagen Evento --}}
                            @php
                                $eventImageUrl = $evento->image_mod ? 'data:image/png;base64,' . $evento->image_mod : "/storage/{$evento->imagen}";
                            @endphp
                            <div style="height: 150px; background: url('{{ $eventImageUrl }}') center center no-repeat; background-size: cover; border-radius: 0;"></div>

                            <div class="card-body d-flex flex-column p-3 bg-transparent">
                                <h5 class="font-Oswald text-white text-truncate mb-1 fs-4" style="letter-spacing: 1px;">{{ $evento->name }}</h5>
                                <p class="small text-white mb-2 fw-bold text-uppercase opacity-75">{{ $evento->region->name }}</p>
                                <div class="mt-auto">
                                    <p class="small mb-3 font-Oswald text-white fs-5" style="text-shadow: 1px 1px 0 #000;"><event-date fecha="{{ $evento->date }}"></event-date></p>
                                    <a href="{{ route('events.show', ['event' => $evento->id]) }}" class="btn-shonen btn-shonen-info w-100 text-center" style="padding: 5px;">
                                        <span>REVISAR LOG</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 py-5 text-center bg-black" style="border: 3px solid #333;">
                        <div class="font-Oswald text-white opacity-50" style="font-size: 2rem;">
                            <i class="fas fa-search d-block mb-3" style="font-size: 3rem;"></i>
                            NO SE DETECTA ACTIVIDAD EN ESTE PERIODO
                        </div>
                    </div>
                @endforelse
            </div>

        </div>
    </div>

</div>
@endif
@endsection
