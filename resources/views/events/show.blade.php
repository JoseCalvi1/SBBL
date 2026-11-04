@extends('layouts.app')

@section('title', 'Detalles del evento')

@section('styles')
<style>
    /* ====================================================================
       ESTILOS ESPECÍFICOS: DETALLE DE EVENTO (El resto hereda del layout)
       ==================================================================== */

    /* --- TÍTULO Y ESTADO --- */
    .event-title {
        font-family: 'Oswald', cursive;
        font-size: 3.5rem;
        color: var(--sbbl-gold);
        text-shadow: 3px 3px 0 #000, 6px 6px 0 var(--shonen-blue);
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 1rem;
        line-height: 1.1;
    }

    .status-badge {
        font-family: 'Oswald', cursive;
        font-size: 1.5rem;
        letter-spacing: 1px;
        padding: 5px 20px;
        border: 3px solid #000;
        transform: skewX(-10deg);
        box-shadow: 4px 4px 0 #000;
        display: inline-block;
        background: #111;
    }
    .status-badge > span { display: block; transform: skewX(10deg); }

    /* --- IMAGEN Y LUPA --- */
    .event-image-card {
        border: 4px solid #000;
        border-radius: 0;
        box-shadow: 8px 8px 0 var(--sbbl-blue-3);
        background: var(--sbbl-blue-2);
        margin-bottom: 2rem;
        overflow: hidden;
    }

    .img-zoom-container {
        position: relative;
        overflow: hidden;
        cursor: pointer;
        border-bottom: 4px solid #000;
    }
    .img-zoom-container:hover .img-zoom-overlay { opacity: 1; }
    .img-zoom-overlay {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.6);
        display: flex; justify-content: center; align-items: center;
        opacity: 0; transition: opacity 0.3s ease; z-index: 10;
    }
    .img-zoom-icon { font-size: 3rem; color: var(--sbbl-gold); text-shadow: 2px 2px 0 #000; }

    /* --- LISTAS DE INFORMACIÓN --- */
    .info-list li {
        padding: 10px 0;
        border-bottom: 2px dashed #000;
        display: flex; justify-content: space-between; align-items: center;
        color: #fff;
    }
    .info-list li:last-child { border-bottom: none; }
    .info-list .info-label { font-weight: 900; color: var(--shonen-cyan); text-transform: uppercase; }
    .info-list .info-value { font-family: 'Oswald', cursive; font-size: 1.3rem; letter-spacing: 1px; }

    /* --- SECCIÓN PARTICIPANTES --- */
    .participant-card {
        background: var(--sbbl-blue-2);
        border: 3px solid #000;
        box-shadow: 6px 6px 0 var(--sbbl-gold);
        border-radius: 0;
        margin-bottom: 2rem;
    }

    .participant-item {
        background: rgba(0,0,0,0.35);
        border: 2px solid #000;
        border-left: 6px solid #10b981;
        margin-bottom: 10px;
        transform: skewX(-2deg);
        transition: 0.2s;
    }
    .participant-item > * { transform: skewX(2deg); }
    .participant-item:hover {
        background: #000;
        border-color: var(--sbbl-gold);
        transform: translate(-2px, -2px) skewX(-2deg);
        box-shadow: 4px 4px 0 var(--shonen-red);
    }
    .participant-item.reserve {
        border-color: #eab308;
        border-left: 6px solid #eab308;
        background: rgba(234, 179, 8, 0.12);
    }
    .participant-item.dropped-row {
        border-color: #7f1d1d;
        border-left: 6px solid #ef4444;
        background: rgba(127, 29, 29, 0.18);
        opacity: 0.65;
    }

    /* --- ARREGLO DE SELECT2 (Modo Oscuro / Shonen) --- */
    .select2-container { width: 100% !important; }
    .select2-dropdown { z-index: 1070 !important; background-color: #000 !important; border: 2px solid var(--sbbl-gold) !important; border-radius: 0 !important; }
    .select2-container--bootstrap-5 .select2-selection { background-color: #111 !important; border: 2px solid #000 !important; border-radius: 0 !important; color: #fff !important; font-weight: 900; }
    .select2-container--bootstrap-5 .select2-selection__rendered { color: #fff !important; }
    .select2-search__field { background-color: #222 !important; color: #fff !important; border: 1px solid #444 !important; }
    .select2-results__option { color: #fff !important; background-color: #000 !important; font-weight: bold; }
    .select2-container--bootstrap-5 .select2-results__option--highlighted { background-color: var(--sbbl-gold) !important; color: #000 !important; }
    .select2-results__options { max-height: 300px !important; overflow-y: auto !important; }
    .select2-search--dropdown .select2-search__field { display: block !important; }

    /* --- ARREGLO MODAL --- */
    .modal-backdrop { z-index: 1050 !important; opacity: 0.85 !important; background-color: #000 !important; }
    .modal { z-index: 1060 !important; }
    .modal-content { background-color: transparent !important; border: none !important; box-shadow: none !important; border-radius: 0 !important; }
    .btn-close-white { filter: invert(1) grayscale(100%) brightness(200%); }

    /* Chat Evento */
    .chat-header {
        font-family: 'Oswald', cursive; font-size: 1.8rem; letter-spacing: 1px;
        background: #000; color: var(--sbbl-gold); border-bottom: 4px solid var(--shonen-red);
        padding: 10px 20px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">

    @php
        $isUserInscribed = Auth::check() && $assists->contains('id', Auth::id());
        $hoy = \Carbon\Carbon::now()->format('Y-m-d');
        $diffInHours = \Carbon\Carbon::parse($event->date)->diffInHours(\Carbon\Carbon::now(), false);

        // Permisos de Juez / Staff sobre el evento
        $isUserAdminOrJudgeRole = Auth::check() && Auth::user()->hasAnyRole(['admin', 'juez']);
        $isUserAssignedJudge = Auth::check() && $event->judges->contains('id', Auth::id());
        $canManageTournament = Auth::check() && (
            Auth::user()->hasAnyRole(['admin', 'juez', 'arbitro']) ||
            $isUserAssignedJudge ||
            $event->created_by == Auth::id()
        );

        // Cálculo de límite de aforo real
        $limit = 9999;
        if ($event->has_stadium_limit || !empty($event->max_players)) {
            if ($event->stadiums == 1) $limit = 20;
            elseif ($event->stadiums == 2) $limit = 35;
            elseif ($event->stadiums == 3) $limit = 45;
            elseif ($event->stadiums == 4) $limit = 55;
            elseif ($event->stadiums >= 5) $limit = 64;

            if (!empty($event->max_players)) {
                $limit = $event->max_players;
            }
        }

        // Recuento exacto descontando a los 'dropped'
        $activeParticipantsCount = $assists->filter(fn($a) => $a->pivot->puesto !== 'dropped')->count();
        $droppedParticipantsCount = $assists->filter(fn($a) => $a->pivot->puesto === 'dropped')->count();
        $entranCount = min($activeParticipantsCount, $limit);
        $reservasCount = max(0, $activeParticipantsCount - $limit);
    @endphp

    {{-- CABECERA DEL EVENTO --}}
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="event-title">{{ $event->name }}</h1>
            <div class="status-badge">
                <span>
                    @switch($event->status)
                        @case('OPEN') <span class="text-white">🟢 ABIERTO</span> @break
                        @case('LIVE') <span class="text-warning">🔥 EN CURSO</span> @break
                        @case('INSCRIPCION') <span style="color: var(--sbbl-gold);">🔒 INSCRIPCIÓN CERRADA</span> @break
                        @case('PENDING') <span style="color: var(--sbbl-gold);">⚠️ PENDIENTE DE CALIFICAR</span> @break
                        @case('REVIEW') <span style="color: var(--shonen-cyan);">🔎 EN REVISIÓN</span> @break
                        @case('INVALID') <span class="text-secondary">❌ INVÁLIDO</span> @break
                        @default <span style="color: var(--shonen-red);">🔴 CERRADO</span>
                    @endswitch
                </span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- COLUMNA IZQUIERDA: INFO DEL EVENTO, JUECES Y CHAT --}}
        <div class="col-lg-4 col-md-5">

            <div class="event-image-card">
                <div class="position-relative img-zoom-container" data-bs-toggle="modal" data-bs-target="#imageModal">
                    @php
                        $imageSrc = $event->image_mod ? "data:image/png;base64,".$event->image_mod : "/storage/".$event->imagen;
                    @endphp
                    <img src="{{ $imageSrc }}" class="w-100 object-fit-cover" style="height: 280px;" alt="Banner Evento">

                    <div class="img-zoom-overlay">
                        <i class="fas fa-search-plus img-zoom-icon"></i>
                    </div>

                    <div class="position-absolute top-0 end-0 m-2 z-2">
                        <span class="badge bg-black text-white border border-2 border-white" style="font-family: 'Oswald', cursive; font-size: 1.2rem; transform: skewX(-5deg);"><span style="display:block; transform:skewX(5deg);">{{ $event->region->name }}</span></span>
                    </div>
                    <div class="position-absolute bottom-0 start-0 w-100 p-3 z-2" style="background: linear-gradient(to top, rgba(0,0,0,0.9), transparent); pointer-events: none;">
                        <h5 class="text-white mb-0 font-Oswald fs-3" style="text-shadow: 2px 2px 0 #000;"><i class="fas fa-map-marker-alt me-2" style="color: var(--shonen-red);"></i>{{ $event->city }}</h5>
                    </div>
                </div>

                <div class="card-body p-4 bg-transparent">
                    <ul class="list-unstyled info-list mb-0">
                        <li>
                            <span class="info-label"><i class="fas fa-gamepad me-2 text-white"></i>Modalidad</span>
                            <span class="info-value text-white">{{ ($event->mode == 'beybladex') ? 'BEYBLADE X' : 'BURST' }} <span style="font-size: 0.8rem; color:#aaa;">({{ strtoupper($event->beys) }})</span></span>
                        </li>
                        <li>
                            <span class="info-label"><i class="fas fa-calendar me-2 text-white"></i>Fecha</span>
                            <span class="info-value text-white"><event-date fecha="{{ $event->date }}"></event-date></span>
                        </li>
                        <li>
                            <span class="info-label"><i class="fas fa-clock me-2 text-white"></i>Hora</span>
                            <span class="info-value text-white">{{ $event->time }}</span>
                        </li>
                        <li>
                            <span class="info-label"><i class="fas fa-map me-2 text-white"></i>Provincia</span>
                            <span class="info-value text-white">{{ $event->province->name ?? 'No especificada' }}</span>
                        </li>
                        <li class="flex-column align-items-start">
                            <span class="info-label mb-2"><i class="fas fa-map-pin me-2 text-white"></i>Ubicación</span>
                            <div class="text-white bg-black p-3 border border-secondary w-100 fw-bold">{!! $event->location !!}</div>
                        </li>
                        @if($event->note)
                        <li class="flex-column align-items-start">
                            <span class="info-label text-warning mb-2"><i class="fas fa-sticky-note me-2 text-white"></i>Notas</span>
                            <div class="text-white fst-italic fw-bold border-start border-3 border-warning ps-2 w-100" style="width: 100% !important;">{!! $event->note !!}</div>
                        </li>
                        @endif
                    </ul>

                    {{-- BLOQUE NUEVO: STAFF OFICIAL / JUECES DEL TORNEO --}}
                    <div class="mt-4 p-3 border border-2 border-warning" style="background: #000; box-shadow: 4px 4px 0 rgba(250, 204, 21, 0.3);">
                        <div class="d-flex justify-content-between align-items-center mb-2 border-bottom border-secondary pb-2">
                            <span class="font-Oswald fs-5 text-warning">
                                <i class="fas fa-gavel me-2"></i>JUECES ASIGNADOS
                            </span>
                            <span class="badge bg-warning text-dark fw-bold">{{ $event->judges->count() }}</span>
                        </div>

                        @if($event->judges->isNotEmpty())
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @foreach($event->judges as $juez)
                                    <span class="badge bg-dark border border-warning text-white px-2 py-1 d-inline-flex align-items-center gap-2" style="font-size: 0.85rem;">
                                        <i class="fas fa-user-shield text-warning"></i> {{ $juez->name }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-white-50 small fw-bold mb-3">Aún no hay jueces asignados a este evento.</p>
                        @endif

                        {{-- Botón Apuntarse / Salir de Arbitrar (Exclusivo Admin y Juez) --}}
                        @if($isUserAdminOrJudgeRole && !in_array($event->status, ['CLOSE', 'COMPLETED', 'INVALID']))
                            <button type="button" onclick="toggleJuezWeb({{ $event->id }})" id="btnToggleJuezWeb"
                                class="btn w-100 fw-bold font-Oswald fs-5 border-2 border-dark {{ $isUserAssignedJudge ? 'btn-danger text-white' : 'btn-warning text-dark' }}">
                                @if($isUserAssignedJudge)
                                    <i class="fas fa-user-minus me-1"></i> SALIR DEL ARBITRAJE
                                @else
                                    <i class="fas fa-gavel me-1"></i> APUNTARSE COMO JUEZ
                                @endif
                            </button>
                        @endif
                    </div>

                    <div class="d-grid gap-3 mt-4">
                        @if(in_array($event->beys, ['ranking', 'rankingplus']))
                            <a href="https://sbbl.es/blog/reglamento-s3-v1" target="_blank" class="btn-shonen btn-shonen-info text-center w-100" style="padding: 10px;">
                                <span><i class="fas fa-book me-1"></i> REGLAMENTO OFICIAL</span>
                            </a>
                        @endif
                        @if ($event->iframe)
                            <a href="{{ $event->iframe }}" target="_blank" class="btn-shonen text-center w-100" style="background: var(--shonen-red); color: #fff; padding: 10px;">
                                <span><i class="fab fa-youtube me-1"></i> VER VIDEO</span>
                            </a>
                        @endif

                        {{-- Si tiene Cuadro del Motor SBBL o enlace a Challonge --}}
                        @if (!empty($hasEngineBracket))
                            <a href="{{ route('torneo.bracket', $event->id) }}" target="_blank" class="btn-shonen btn-shonen-warning text-center w-100" style="padding: 10px;">
                                <span><i class="fas fa-sitemap me-1"></i> ABRIR CUADRO EN VIVO (PANTALLA COMPLETA)</span>
                            </a>
                        @elseif ($event->challonge)
                            <a href="{{ $event->challonge }}" target="_blank" class="btn-shonen btn-shonen-warning text-center w-100" style="padding: 10px;">
                                <span><i class="fas fa-trophy me-1"></i> VER BRACKET</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- CHAT DEL EVENTO --}}
            <div class="command-panel mb-4 p-0">
                <div class="chat-header">
                    <i class="fas fa-comments me-2 text-white"></i> CHAT DEL EVENTO
                </div>
                <div class="card-body p-0 bg-black" id="chat-container-wrapper">
                    <div id="app">
                        <chat-component :event-id="{{$event->id}}" />
                    </div>
                </div>
            </div>

        </div>

        {{-- COLUMNA DERECHA: CUADRO EN DIRECTO, PARTICIPANTES Y ESTADO --}}
        <div class="col-lg-8 col-md-7">

            @if (session('success'))
                <div class="alert alert-shonen alert-shonen-success mb-4 text-center text-white"><div><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div></div>
            @endif
            @if (session('error'))
                <div class="alert alert-shonen alert-shonen-danger mb-4 text-center text-white"><div><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}</div></div>
            @endif

            {{-- NUEVO: BLOQUE DEL CUADRO Y CLASIFICACIÓN EN DIRECTO EN LA WEB --}}
            @if(!empty($hasEngineMatches))
                <div class="participant-card mb-4" style="border-color: #000; box-shadow: 6px 6px 0 var(--shonen-cyan);">
                    <div class="card-body p-4 bg-transparent">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3 border-bottom border-dark pb-3">
                            <h3 class="font-Oswald text-white m-0 d-flex align-items-center gap-2" style="font-size: 2rem;">
                                <i class="fas fa-satellite-dish text-info"></i>
                                <span>SEGUIMIENTO EN DIRECTO</span>
                            </h3>
                            <div class="d-flex flex-wrap gap-2">
                                @if(!empty($hasEngineBracket))
                                    <button type="button" onclick="switchLiveWebTab('cuadro')" id="btnWebTabCuadro" class="btn btn-warning fw-bold border-2 border-dark">
                                        <i class="fas fa-sitemap me-1"></i> CUADRO ELIMINATORIO
                                    </button>
                                @endif
                                <button type="button" onclick="switchLiveWebTab('tabla')" id="btnWebTabTabla" class="btn {{ !empty($hasEngineBracket) ? 'btn-dark text-white' : 'btn-info text-dark' }} fw-bold border-2 border-dark">
                                    <i class="fas fa-list-ol me-1"></i> CLASIFICACIÓN
                                </button>
                                <button type="button" onclick="recargarSeguimientoWeb()" class="btn btn-outline-light fw-bold border-2" title="Actualizar en vivo">
                                    <i id="iconSyncWebLive" class="fas fa-sync-alt"></i>
                                </button>
                                @if(!empty($hasEngineBracket))
                                    <a href="{{ route('torneo.bracket', $event->id) }}" target="_blank" class="btn btn-info fw-bold border-2 border-dark text-dark" title="Abrir Cuadro Interactivo a Pantalla Completa">
                                        <i class="fas fa-expand-arrows-alt me-1"></i> PANTALLA COMPLETA
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Contenedor del Cuadro en Vivo (Iframe fluido al visor interactivo del Hub) --}}
                        @if(!empty($hasEngineBracket))
                            <div id="webLiveCuadroContainer" class="border border-2 border-dark bg-black position-relative" style="height: 520px; overflow: hidden;">
                                <iframe id="iframeLiveBracketWeb" src="{{ route('torneo.bracket', $event->id) }}" class="w-100 h-100 border-0"></iframe>
                            </div>
                        @endif

                        {{-- Contenedor de la Tabla de Clasificación en Vivo --}}
                        <div id="webLiveTablaContainer" class="{{ !empty($hasEngineBracket) ? 'd-none' : '' }} bg-black border border-2 border-dark p-3">
                            <div id="webLiveTablaContent" class="table-responsive">
                                <div class="text-center text-white-50 py-4 fw-bold">Cargando clasificación en directo...</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- REVISIONES Y JUECES --}}
            @if (1 == 2 && in_array($event->beys, ['ranking', 'rankingplus']) && (in_array($event->status, ['INVALID', 'CLOSE']) || (Auth::check() && Auth::user()->hasRole('admin'))))
                <div class="mb-4">
                    <button class="btn-shonen btn-shonen-warning w-100 text-center" type="button"
                            data-bs-toggle="collapse" data-bs-target="#reviewCollapse{{ $event->id }}"
                            aria-expanded="false" aria-controls="reviewCollapse{{ $event->id }}" style="padding: 10px;">
                        <span><i class="fas fa-clipboard-check me-2"></i> MOSTRAR VALIDADORES Y COMENTARIOS</span>
                    </button>

                    <div class="collapse mt-3" id="reviewCollapse{{ $event->id }}">
                        <div class="command-panel p-4">
                            @if($event->judgeReview)
                                <h4 class="font-Oswald text-white mb-3"><i class="fas fa-gavel me-2" style="color: var(--sbbl-gold);"></i>Resolución del Juez</h4>
                                <div class="p-3 border border-warning" style="background-color: rgba(255, 193, 7, 0.1);">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong class="text-white">Juez Principal</strong>
                                        <span class="badge {{ $event->judgeReview->status == 'approved' ? 'bg-success' : ($event->judgeReview->status == 'rejected' ? 'bg-danger' : 'bg-secondary') }} font-Oswald fs-6 border border-white">
                                            {{ strtoupper($event->judgeReview->status) }}
                                        </span>
                                    </div>
                                    <div class="text-white fst-italic fw-bold">
                                        "{{ $event->judgeReview->comment }}"
                                    </div>
                                </div>
                            @else
                                <h4 class="font-Oswald text-white mb-3"><i class="fas fa-users me-2" style="color: var(--shonen-cyan);"></i>Historial de Revisiones</h4>
                                @if($event->reviews->isEmpty())
                                    <div class="text-center text-white fw-bold bg-black p-3 border border-secondary">
                                        No hay revisiones aún.
                                    </div>
                                @else
                                    <ul class="list-unstyled mb-0 vstack gap-2" style="max-height: 250px; overflow-y: auto;">
                                        @foreach($event->reviews as $review)
                                        <li class="p-3 border border-secondary" style="background-color: #000;">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong class="text-white">
                                                    @if(Auth::check() && (Auth::user()->hasRole('admin') || Auth::user()->name == $review->referee->name))
                                                        {{ $review->referee->name ?? 'Árbitro' }}
                                                    @else
                                                        Árbitro {{ $loop->iteration }}
                                                    @endif
                                                </strong>
                                                <span class="badge {{ $review->status == 'approved' ? 'bg-success' : ($review->status == 'rejected' ? 'bg-danger' : 'bg-secondary') }} font-Oswald fs-6 border border-white">
                                                    {{ strtoupper($review->status) }}
                                                </span>
                                            </div>
                                            <div class="text-white fst-italic fw-bold border-start border-3 border-secondary ps-3">
                                                "{{ $review->comment }}"
                                            </div>
                                        </li>
                                        @endforeach
                                    </ul>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <div class="participant-card">
                <div class="card-body p-4 bg-transparent">
                    <div class="mb-4">
                        {{-- FILA 1: CABECERA Y BOTONES DE ACCIÓN --}}
                        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-3">

                            <h2 class="font-Oswald text-white m-0 d-flex flex-wrap align-items-center gap-2 gap-sm-3"
                                style="font-size: clamp(1.5rem, 4vw, 2.5rem); letter-spacing: 1px;">
                                <i class="fas fa-users" style="color: var(--sbbl-gold);"></i>
                                <span>PARTICIPANTES</span>
                                <span class="badge bg-white text-dark border border-2 border-dark shadow-sm align-middle"
                                    style="font-size: clamp(1rem, 2.5vw, 1.5rem);">
                                    {{ $activeParticipantsCount }} {{ $limit < 1000 ? '/ ' . $limit : '' }}
                                </span>
                            </h2>

                            <div class="d-flex flex-wrap align-items-center gap-2">
                                @if (in_array($event->beys, ['ranking', 'rankingplus']))
                                    @if ($event->status == "OPEN" && $canManageTournament && $event->date->format('Y-m-d') == $hoy)
                                        <form method="POST" action="{{ route('events.estado', ['event' => $event->id, 'estado' => 'comenzar']) }}" class="m-0">
                                            @csrf @method('PUT')
                                            <button type="button" id="comenzarTorneoBtn" class="btn btn-success fw-bold border-2 border-dark py-2">
                                                <i class="fas fa-play me-1"></i> COMENZAR TORNEO
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    @if (($event->status == "OPEN") && $canManageTournament && ($diffInHours <= 24 && $diffInHours >= 0))
                                        <form method="POST" action="{{ route('events.estado', ['event' => $event->id, 'estado' => 'inscripcion']) }}" class="m-0">
                                            @csrf @method('PUT')
                                            <button type="submit" class="btn btn-secondary fw-bold border-2 border-dark py-2">
                                                <i class="fas fa-lock me-1"></i> CERRAR INSC.
                                            </button>
                                        </form>
                                    @endif
                                @endif

                                @if (!in_array($event->status, ["CLOSE", "INVALID"]) && $canManageTournament)
                                    <a href="{{ route('events.edit', ['event' => $event->id]) }}" class="btn btn-light fw-bold border-2 border-dark py-2">
                                        <i class="fas fa-edit me-1"></i> EDITAR
                                    </a>
                                @endif

                                @if ($isUserInscribed && !in_array($event->status, ["INVALID", "LIVE", "CLOSE", "COMPLETED"]))
                                    <button type="button" class="btn-shonen btn-shonen-info text-center py-2 px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#formModal">
                                        <span><i class="fas fa-layer-group me-1"></i> MI DECK</span>
                                    </button>
                                @endif
                            </div>

                        </div>

                        {{-- FILA 2: BOTÓN DE EQUIPOS --}}
                        @if(isset($equiposAsistentes) && $equiposAsistentes->isNotEmpty())
                            <div class="w-100 mt-2">
                                <button type="button" class="btn-shonen w-100 text-center text-uppercase" style="background: rgba(255, 255, 255, 0.05); color: #fff; border: 2px dashed #fff; padding: 12px; transition: all 0.3s;" data-bs-toggle="modal" data-bs-target="#teamsModal">
                                    <span><i class="fas fa-shield-alt me-2" style="color: var(--shonen-cyan);"></i> VER EQUIPOS REPRESENTADOS ({{ $equiposAsistentes->count() }})</span>
                                </button>
                            </div>
                        @endif
                    </div>

                    {{-- TARJETAS RESUMEN DE ESTADO DEL CUADRO (IGUAL QUE EN EL HUB) --}}
                    <div class="row g-2 mb-4 text-center">
                        <div class="col-4">
                            <div class="p-2 border border-2 border-success" style="background: rgba(16, 185, 129, 0.15);">
                                <div class="text-success fw-bold small text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">
                                    <i class="fas fa-check-circle me-1"></i> ENTRAN AL CUADRO
                                </div>
                                <div class="font-Oswald text-white fs-2 lh-1 mt-1">{{ $entranCount }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 border border-2 border-warning" style="background: rgba(234, 179, 8, 0.15);">
                                <div class="text-warning fw-bold small text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">
                                    <i class="fas fa-clock me-1"></i> EN RESERVA
                                </div>
                                <div class="font-Oswald text-warning fs-2 lh-1 mt-1">{{ $reservasCount }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 border border-2 border-danger" style="background: rgba(239, 68, 68, 0.15);">
                                <div class="text-danger fw-bold small text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">
                                    <i class="fas fa-user-slash me-1"></i> EXCLUIDOS (DROP)
                                </div>
                                <div class="font-Oswald text-danger fs-2 lh-1 mt-1">{{ $droppedParticipantsCount }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 mb-4 border border-dark" style="background-color: #000;">
                    @auth
                        @if($event->status == "OPEN" && $event->date >= $hoy)

                        @if($event->has_stadium_limit || !empty($event->max_players))
                            @php
                                $limitText = !empty($event->max_players)
                                    ? "AFORO PERSONALIZADO: " . $limit . " BLADERS"
                                    : "AFORO ASEGURADO: " . $limit . " BLADERS (" . $event->stadiums . " ESTADIOS)";

                                $alertColor = ($activeParticipantsCount >= $limit) ? 'danger' : 'success';
                                $shonenColor = $alertColor == 'success' ? '#00ff00' : 'var(--shonen-red)';

                                if ($activeParticipantsCount >= ($limit * 0.8) && $activeParticipantsCount < $limit) {
                                    $alertColor = 'warning';
                                    $shonenColor = 'var(--sbbl-gold)';
                                }
                            @endphp

                            <div class="text-center mb-4 p-3 border" style="border-color: {{ $shonenColor }} !important; background: rgba(255,255,255,0.05);">
                                <h4 class="font-Oswald mb-2" style="color: {{ $shonenColor }}; font-size: 2rem; letter-spacing: 1px;">
                                    🏟️ {{ $event->stadiums }} ESTADIOS DETECTADOS
                                </h4>
                                <p class="text-white fw-bold mb-3">{{ $limitText }}</p>

                                <div class="progress rounded-0 border border-secondary" style="height: 25px; background-color: #111;">
                                    <div class="progress-bar fw-bold text-dark font-Oswald fs-5" role="progressbar"
                                         style="background-color: {{ $shonenColor }}; width: {{ min(($activeParticipantsCount / ($limit > 1000 ? 50 : $limit)) * 100, 100) }}%;">
                                        {{ $activeParticipantsCount }} / {{ ($limit > 1000) ? '∞' : $limit }}
                                    </div>
                                </div>

                                @if($activeParticipantsCount >= $limit && $limit < 1000)
                                    <hr class="border-secondary mt-4">
                                    <p class="mb-0 fw-bold text-white" style="font-size: 1.1rem;">
                                        <i class="fas fa-exclamation-triangle text-danger me-2"></i> AFORO GARANTIZADO COMPLETO. LAS NUEVAS INSCRIPCIONES ENTRAN EN LISTA DE ESPERA.
                                    </p>
                                @endif
                            </div>
                        @endif

                        @if (!$isUserInscribed)
                            @if(isset($isRegistered) && $isRegistered && in_array($event->beys, ['ranking', 'rankingplus']))
                                <div class="alert alert-shonen alert-shonen-warning text-center">
                                    <div class="text-white"><i class="fas fa-exclamation-triangle me-2"></i> YA ESTÁS INSCRITO EN OTRO TORNEO DE RANKING ESTA SEMANA.</div>
                                </div>
                            @endif

                            @php $creator = \App\Models\User::find($event->created_by); @endphp
                            @if ($creator && !$creator->hasAnyRole(['admin', 'juez']))
                                <div class="alert alert-shonen alert-shonen-info text-center" style="border-color: #fff !important; color: #fff !important; box-shadow: 4px 4px 0 #fff;">
                                    <div>EVENTO ORGANIZADO POR LA COMUNIDAD. MATERIAL NO GARANTIZADO POR SBBL.</div>
                                </div>
                            @endif

                            @if($event->beys === "copapaypal")
                                <div class="text-center p-4 border border-secondary" style="background: #111;">
                                    <p class="text-white font-Oswald fs-3 mb-3">COSTE DE INSCRIPCIÓN: <span style="color: var(--sbbl-gold);">{{ $event->beys === "grancopa" ? '5€' : '2€' }}</span></p>
                                    <div id="paypal-button-container" class="d-flex justify-content-center"></div>
                                </div>
                            @else
                                <button type="button" class="btn-shonen w-100 text-center" data-bs-toggle="modal" data-bs-target="#formModal" style="background: #00ff00; color: #000; border-color: #000; padding: 15px; font-size: 1.8rem;"
                                    @if(in_array($event->beys, ['ranking', 'rankingplus']) &&
                                    ((isset($rankingTournamentsLeft) && $rankingTournamentsLeft == 0) || (isset($isRegistered) && $isRegistered)))
                                    disabled style="background: #555 !important; color: #888 !important; border-color: #333 !important; cursor: not-allowed; box-shadow: none;"
                                    @endif>
                                    <span><i class="fas fa-fist-raised me-2"></i> INSCRIBIRSE EN EL EVENTO
                                    @if(in_array($event->beys, ['ranking', 'rankingplus']) && isset($rankingTournamentsLeft))
                                        <span class="badge bg-black text-white ms-3 border border-white" style="font-size: 1rem; vertical-align: middle;">Restantes: {{ $rankingTournamentsLeft }}</span>
                                    @endif
                                    </span>
                                </button>
                            @endif

                        @else
                            <div class="text-center">
                                <button type="button" class="btn-shonen text-center" style="background: var(--shonen-red); color: #fff; padding: 10px 20px;" data-bs-toggle="modal" data-bs-target="#confirmModal" @if($event->status == 'LIVE') disabled style="background: #555 !important; opacity:0.5;" @endif>
                                    <span><i class="fas fa-times-circle me-2"></i> CANCELAR ASISTENCIA</span>
                                </button>
                            </div>
                        @endif

                    @elseif($event->status == "LIVE")
                        <div class="text-center text-warning font-Oswald fs-3"><i class="fas fa-stadium text-warning me-2"></i> EL TORNEO ESTÁ EN CURSO. INSCRIPCIONES Y BAJAS BLOQUEADAS.</div>
                    @elseif($event->status != "OPEN")
                        <div class="text-center text-white font-Oswald fs-3">LAS INSCRIPCIONES ESTÁN CERRADAS.</div>
                    @else
                        <div class="text-center text-white font-Oswald fs-3">EL EVENTO HA FINALIZADO.</div>
                    @endif
                    @else
                        <div class="text-center font-Oswald fs-3" style="color: var(--sbbl-gold);">INICIA SESIÓN PARA INSCRIBIRTE AL COMBATE.</div>
                    @endauth
                    </div>

                    {{-- UTILIDADES --}}
                    <div class="d-flex flex-wrap gap-3 mb-4">
                        <button id="copyButton" class="btn btn-dark fw-bold border-2 border-secondary flex-fill text-white">
                            <i class="fas fa-copy me-2" style="color: var(--sbbl-gold);"></i> Copiar Titulares (Sin Drops)
                        </button>
                        @if ($canManageTournament)
                            <button id="copyButtonEmail" class="btn btn-dark fw-bold border-2 border-secondary flex-fill text-white">
                                <i class="fas fa-envelope me-2" style="color: var(--shonen-cyan);"></i> Copiar Emails
                            </button>
                        @endif
                    </div>

                    @if ($canManageTournament && !in_array($event->status, ['CLOSE', 'INVALID']))
                        <div class="mb-4 bg-black p-3 border border-secondary">
                            <h6 class="text-white fw-bold mb-2"><i class="fas fa-user-plus text-info me-1"></i> Añadir Participante Manual (Modo Juez):</h6>
                            <form method="POST" action="{{ route('events.addAssist', ['event' => $event->id]) }}" class="input-group">
                                @csrf
                                <select name="participante_id" class="form-select form-select-sm select2" required>
                                    <option value="">Buscar blader...</option>
                                    @foreach($participantes as $p)
                                        <option value="{{ $p->id }}">
                                            {{ $p->name }} #{{ str_pad($p->id, 4, '0', STR_PAD_LEFT) }} ({{ $p->email }})
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-success fw-bold px-3 border-2 border-dark"><i class="fas fa-plus me-1"></i> AÑADIR</button>
                            </form>
                        </div>
                    @endif

                    {{-- LISTA DE PARTICIPANTES CON RECUENTO INTELIGENTE DE DROPS Y RESERVAS --}}
                    @if(count($assists) > 0)
                        <form method="POST" action="{{ route('events.updatePuestos', ['event' => $event->id]) }}">
                            @csrf @method('PUT')

                            @php
                                $activeCounter = 0;
                                $requiredBeysCheck = ($event->deck === '1on1' || in_array($event->beys, ['crew_battle', 'equipos'])) ? 1 : 3;
                            @endphp

                            @foreach ($assists as $assist)
                                @php
                                    $isDropped = ($assist->pivot->puesto === 'dropped');
                                    $isReserve = false;
                                    $activeSlotNumber = null;
                                    $reserveSlotNumber = null;

                                    if (!$isDropped) {
                                        $activeCounter++;
                                        $activeSlotNumber = $activeCounter;
                                        if ($activeCounter > $limit) {
                                            $isReserve = true;
                                            $reserveSlotNumber = $activeCounter - $limit;
                                        }
                                    }

                                    $playerCombos = $resultsByParticipant[$assist->id] ?? collect();
                                    $hasCompleteDeck = count($playerCombos) >= $requiredBeysCheck;
                                @endphp

                                <div class="participant-item p-3 {{ $isDropped ? 'dropped-row' : ($isReserve ? 'reserve' : '') }}">
                                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">

                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold border border-2 border-dark"
                                                 style="width: 45px; height: 45px; font-size: 1.2rem; background: {{ $isDropped ? '#ef4444' : ($isReserve ? '#eab308' : 'var(--sbbl-gold)') }}; color: #000;">
                                                {{ substr($assist->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-Oswald fs-4 text-white d-flex align-items-center gap-2 flex-wrap" style="letter-spacing: 1px;">
                                                    <span class="{{ $isDropped ? 'text-decoration-line-through text-white-50' : '' }}">{{ $assist->name }}</span>
                                                    <span class="text-white-50" style="font-size: 1.1rem;">#{{ str_pad($assist->id, 4, '0', STR_PAD_LEFT) }}</span>

                                                    {{-- ETIQUETA DE EQUIPO DEL USUARIO --}}
                                                    @if($assist->active_team)
                                                        <span class="badge font-sans fw-bold" style="background-color: {{ $assist->active_team->color ?? '#475569' }}; font-size: 0.75rem; border: 2px solid #000; letter-spacing: 0;">
                                                            <i class="fas fa-shield-alt me-1"></i>{{ $assist->active_team->name }}
                                                        </span>
                                                    @endif

                                                    {{-- ETIQUETA DE EARLY BIRD (Copas) --}}
                                                    @if(!$isDropped && in_array($event->beys, ['copaespecial1', 'copaespecial2', 'copaespecial3', 'copaespecial4', 'grancopa']))
                                                        @php
                                                            $fechaInscripcion = \Carbon\Carbon::parse($assist->pivot->created_at);
                                                            $fechaEvento = \Carbon\Carbon::parse($event->date);
                                                        @endphp
                                                        @if($fechaInscripcion->diffInDays($fechaEvento, false) >= 7)
                                                            <span class="badge bg-success text-white border border-dark font-sans shadow" style="font-size: 0.75rem;">
                                                                <i class="fas fa-star text-warning me-1"></i> EARLY BIRD
                                                            </span>
                                                        @endif
                                                    @endif

                                                    {{-- ETIQUETA ESTADO DEL DECK --}}
                                                    @if(!$isDropped)
                                                        @if($hasCompleteDeck)
                                                            <span class="badge bg-success text-white border border-dark font-sans shadow" style="font-size: 0.75rem;">
                                                                <i class="fas fa-layer-group me-1"></i> DECK OK
                                                            </span>
                                                        @else
                                                            <span class="badge bg-warning text-dark border border-dark font-sans shadow" style="font-size: 0.75rem;">
                                                                ⚠️ FALTA DECK
                                                            </span>
                                                        @endif
                                                    @endif

                                                    {{-- ETIQUETA DE ESTADO EN EL CUADRO (TITULAR / RESERVA / DROP) --}}
                                                    @if($isDropped)
                                                        <span class="badge bg-danger text-white border border-white font-sans" style="font-size: 0.75rem; letter-spacing: 0;">
                                                            <i class="fas fa-skull me-1"></i> DROP / NO CUENTA
                                                        </span>
                                                    @elseif($isReserve)
                                                        <span class="badge bg-warning text-dark border border-dark font-sans fw-bold" style="font-size: 0.75rem; letter-spacing: 0;">
                                                            <i class="fas fa-clock me-1"></i> RESERVA #{{ $reserveSlotNumber }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-success text-white border border-dark font-sans fw-bold" style="font-size: 0.75rem; letter-spacing: 0;">
                                                            <i class="fas fa-check me-1"></i> #{{ $activeSlotNumber }} ENTRA
                                                        </span>
                                                    @endif
                                                </div>

                                                @if(!empty($assist->pivot->puesto) && !in_array($assist->pivot->puesto, ['participante', 'active', 'dropped']))
                                                    <span class="badge bg-white text-dark border border-dark mt-1 fw-bold"><i class="fas fa-medal text-warning me-1"></i>{{ strtoupper($assist->pivot->puesto) }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center flex-wrap gap-2">
                                            {{-- Selector de Puesto Manual / Automático --}}
                                            @if (!in_array($event->status, ["CLOSE", "INVALID"]) && $canManageTournament)
                                                <input type="hidden" name="participantes[{{ $assist->id }}][id]" value="{{ $assist->id }}">
                                                <select class="form-select form-select-sm fw-bold bg-black text-white border-2 border-secondary" style="width: 145px;" name="participantes[{{ $assist->id }}][puesto]">
                                                    <option value="participante" {{ in_array($assist->pivot->puesto, ['participante', 'active', null, '']) ? 'selected' : '' }}>Participante</option>
                                                    <option value="primero" {{ $assist->pivot->puesto == 'primero' ? 'selected' : '' }}>🥇 1º Lugar</option>
                                                    <option value="segundo" {{ $assist->pivot->puesto == 'segundo' ? 'selected' : '' }}>🥈 2º Lugar</option>
                                                    <option value="tercero" {{ $assist->pivot->puesto == 'tercero' ? 'selected' : '' }}>🥉 3º Lugar</option>
                                                    <option value="cuarto" {{ $assist->pivot->puesto == 'cuarto' ? 'selected' : '' }}>4º Lugar</option>
                                                    <option value="quinto" {{ $assist->pivot->puesto == 'quinto' ? 'selected' : '' }}>5º Lugar</option>
                                                    <option value="sexto" {{ $assist->pivot->puesto == 'sexto' ? 'selected' : '' }}>6º Lugar</option>
                                                    <option value="septimo" {{ $assist->pivot->puesto == 'septimo' ? 'selected' : '' }}>7º Lugar</option>
                                                    <option value="octavo" {{ $assist->pivot->puesto == 'octavo' ? 'selected' : '' }}>8º Lugar</option>
                                                    <option value="noveno" {{ $assist->pivot->puesto == 'noveno' ? 'selected' : '' }}>9º Lugar</option>
                                                    <option value="decimo" {{ $assist->pivot->puesto == 'decimo' ? 'selected' : '' }}>10º Lugar</option>
                                                    <option value="undecimo" {{ $assist->pivot->puesto == 'undecimo' ? 'selected' : '' }}>11º Lugar</option>
                                                    <option value="duodecimo" {{ $assist->pivot->puesto == 'duodecimo' ? 'selected' : '' }}>12º Lugar</option>
                                                    <option value="decimotercero" {{ in_array($assist->pivot->puesto, ['decimotercero', 'treceavo']) ? 'selected' : '' }}>13º Lugar</option>
                                                    <option value="decimocuarto" {{ $assist->pivot->puesto == 'decimocuarto' ? 'selected' : '' }}>14º Lugar</option>
                                                    <option value="decimoquinto" {{ $assist->pivot->puesto == 'decimoquinto' ? 'selected' : '' }}>15º Lugar</option>
                                                    <option value="decimosexto" {{ $assist->pivot->puesto == 'decimosexto' ? 'selected' : '' }}>16º Lugar</option>
                                                    <option value="decimoseptimo" {{ in_array($assist->pivot->puesto, ['decimoseptimo', 'diecisieteavo']) ? 'selected' : '' }}>17º Lugar</option>
                                                    <option value="dropped" {{ $assist->pivot->puesto == 'dropped' ? 'selected' : '' }}>🚫 Drop / Retirado</option>
                                                    <option value="nopresentado" {{ $assist->pivot->puesto == 'nopresentado' ? 'selected' : '' }}>❌ No Pres.</option>
                                                </select>
                                            @endif

                                            {{-- Botones de Staff: Ver Deck, Editar Deck del jugador y Drop rápido --}}
                                            @php
                                                $hasRes = count($playerCombos) > 0;
                                                $canViewDeck = $hasRes && (in_array($event->status, ['CLOSE', 'COMPLETED', 'PENDING', 'REVIEW']) || $canManageTournament);
                                            @endphp

                                            @if($canViewDeck)
                                                <button type="button" class="btn btn-sm btn-outline-light fw-bold border-2" data-bs-toggle="collapse" data-bs-target="#res-{{ $assist->id }}" title="Ver Deck">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @endif

                                            @if($canManageTournament && !in_array($event->status, ['CLOSE', 'INVALID']))
                                                <button type="button" onclick="abrirModalDeckParticipanteWeb({{ $assist->id }}, '{{ addslashes($assist->name) }}')" class="btn btn-sm btn-info text-dark fw-bold border-2 border-dark" title="Meter / Editar Deck de {{ $assist->name }}">
                                                    <i class="fas fa-layer-group"></i>
                                                </button>
                                                @if(!$isDropped)
                                                    <button type="button" onclick="ejecutarDropWeb({{ $event->id }}, {{ $assist->id }}, '{{ addslashes($assist->name) }}')" class="btn btn-sm btn-danger fw-bold border-2 border-dark" title="Dar de baja (Drop) - No contará para el cuadro">
                                                        <i class="fas fa-user-slash"></i>
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </div>

                                    <div class="collapse mt-3" id="res-{{ $assist->id }}">
                                        <div class="bg-black p-3 border border-secondary text-white fw-bold">
                                            @if($hasRes)
                                                @foreach($playerCombos as $res)
                                                    <div class="d-flex flex-wrap gap-2 border-bottom border-dark pb-2 mb-2">
                                                        <span class="text-white font-Oswald fs-5" style="letter-spacing: 1px; color: var(--sbbl-gold) !important;">{{ $res->blade }}</span>
                                                        <span>
                                                            <i class="fas fa-plus text-secondary mx-1" style="font-size: 0.7rem;"></i>
                                                            {{ $res->over_blade ?? '-' }}
                                                            <span class="mx-2 text-secondary">|</span>
                                                            {{ $res->assist_blade ?? '-' }}
                                                            <span class="mx-2 text-secondary">|</span>
                                                            {{ $res->ratchet }}
                                                            <span class="text-secondary mx-1">·</span>
                                                            {{ $res->bit }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @if (!in_array($event->status, ["CLOSE", "INVALID"]) && $canManageTournament)
                                <div class="mt-4 p-4 border border-dark" style="background: #000;">
                                    <h5 class="font-Oswald text-white fs-3 mb-3"><i class="fas fa-lock me-2" style="color: var(--shonen-red);"></i>CIERRE DE TORNEO</h5>

                                    <label class="form-label text-white-50 small fw-bold text-uppercase">Link YouTube del Torneo (Opcional)</label>
                                    <input type="url" name="iframe" autocomplete="off" class="form-control fw-bold border-2 border-secondary bg-darkblue text-white mb-3 p-2" placeholder="https://youtube.com/... (Opcional)" value="{{ old('iframe', $event->iframe) }}">

                                    <label class="form-label text-white-50 small fw-bold text-uppercase">Link del Cuadro / Challonge (Opcional — Se rellena solo si usas el motor SBBL)</label>
                                    <input type="url" name="challonge" autocomplete="off" class="form-control fw-bold border-2 border-secondary bg-darkblue text-white mb-3 p-2" placeholder="Link del Cuadro o Challonge (Opcional)" value="{{ old('challonge', $event->challonge ?: (!empty($hasEngineBracket) ? route('torneo.bracket', $event->id) : '')) }}">

                                    <div class="form-check mb-4 bg-dark p-3 border border-warning">
                                        <input class="form-check-input ms-1" type="checkbox" id="podioCheck" required style="transform: scale(1.5);">
                                        <label class="form-check-label text-white fw-bold ms-3" for="podioCheck">
                                            CONFIRMO QUE LAS POSICIONES HAN SIDO ASIGNADAS CORRECTAMENTE A LOS PARTICIPANTES.
                                        </label>
                                    </div>

                                    <button type="submit" onclick="if(!document.getElementById('podioCheck').checked) { document.getElementById('podioCheck').reportValidity(); return false; } return confirm('¿ESTÁS SEGURO DE GUARDAR LOS RESULTADOS DEFINITIVOS?');" class="btn-shonen w-100 text-center" style="background: var(--shonen-cyan); color: #000; border-color: #000; padding: 15px; font-size: 1.5rem;">
                                        <span><i class="fas fa-save me-2"></i> GUARDAR RESULTADOS OFICIALES</span>
                                    </button>
                                </div>
                            @endif
                        </form>
                    @else
                        <div class="p-5 text-center border border-secondary" style="background: #000;">
                            <i class="fas fa-users-slash fa-4x mb-3 text-secondary"></i>
                            <h4 class="font-Oswald text-white fs-3">LA ARENA ESTÁ VACÍA.</h4>
                            <p class="text-white fw-bold">Aún no hay bladers inscritos a este evento.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>
</div>
@endsection

@section('scripts')
{{-- MODAL CONFIRMAR CANCELACIÓN --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-color: var(--shonen-red);">
            <div class="modal-header border-bottom border-dark" style="background: #000;">
                <h5 class="modal-title font-Oswald fs-3 text-white"><i class="fas fa-exclamation-triangle me-2" style="color: var(--shonen-red);"></i> ALERTA DE SISTEMA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4" style="background: var(--sbbl-blue-2);">
                <p class="text-white fw-bold fs-5 mb-0">¿REALMENTE QUIERES CANCELAR TU ASISTENCIA AL EVENTO?</p>
            </div>
            <div class="modal-footer border-top border-dark d-flex justify-content-center gap-3" style="background: #000;">
                <button type="button" class="btn fw-bold bg-dark text-white border border-secondary" data-bs-dismiss="modal">MANTENER POSICIÓN</button>
                <form method="POST" action="{{ route('events.noassist', ['event' => $event->id]) }}">
                    @method('DELETE') @csrf
                    <button type="submit" class="btn fw-bold" style="background: var(--shonen-red); color: #fff; border: 2px solid #000;">SÍ, SALIR DEL EVENTO</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- MODAL IMAGEN BANNER --}}
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content" style="background: transparent !important; border: none !important;">
            <div class="modal-body p-0 position-relative text-center">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-3 bg-dark rounded-circle p-3 shadow" style="border: 2px solid #fff;" data-bs-dismiss="modal" aria-label="Close"></button>
                <img src="{{ $imageSrc }}" class="img-fluid border border-4 border-dark shadow-lg" style="max-height: 90vh; object-fit: contain; background: #000;">
            </div>
        </div>
    </div>
</div>

{{-- MODAL EQUIPOS --}}
<div class="modal fade" id="teamsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom border-dark">
                <h5 class="modal-title font-Oswald fs-3 text-white"><i class="fas fa-shield-alt me-2" style="color: var(--shonen-cyan);"></i> EQUIPOS REPRESENTADOS</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="background: var(--sbbl-blue-1);">
                <div class="row g-4">
                    @if(isset($equiposAsistentes))
                        @foreach($equiposAsistentes as $equipo)
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 border-0" style="background-color: var(--sbbl-blue-2); border-top: 5px solid {{ $equipo['color'] }} !important; box-shadow: 4px 4px 0 #000; border-radius: 0;">
                                    <div class="card-header border-0 pb-0 bg-transparent">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong class="font-Oswald fs-4 text-white text-truncate" style="letter-spacing: 1px;">{{ $equipo['name'] }}</strong>
                                            <span class="badge bg-white text-dark rounded-circle fs-6 border border-dark">{{ $equipo['count'] }}</span>
                                        </div>
                                        <hr class="border-secondary mt-2 mb-2" style="border-width: 2px;">
                                    </div>
                                    <div class="card-body pt-0">
                                        <ul class="list-unstyled mb-0">
                                            @foreach($equipo['members'] as $member)
                                                <li class="py-1 text-white fw-bold d-flex align-items-center gap-2" style="font-size: 0.9rem;">
                                                    <i class="fas fa-user-circle" style="color: {{ $equipo['color'] }};"></i>
                                                    {{ $member }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="modal-footer border-top border-dark" style="background: #000;">
                <button type="button" class="btn fw-bold text-white bg-dark border border-secondary" data-bs-dismiss="modal">CERRAR</button>
            </div>
        </div>
    </div>
</div>

@include('events.partials.deck_modal')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

@php
    $paypalClientId = config('paypal.mode') === 'sandbox' ? config('paypal.sandbox.client_id') : config('paypal.live.client_id');
@endphp
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency=EUR"></script>

<script>
    // 1. Apuntarse / Salir como Juez desde la Web (Reutiliza el endpoint del Hub)
    async function toggleJuezWeb(eventId) {
        const btn = document.getElementById('btnToggleJuezWeb');
        if (btn) btn.disabled = true;
        try {
            const res = await fetch(`/api/torneos/${eventId}/juez`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (res.ok) {
                window.location.reload();
            } else {
                alert(data.message || 'No tienes permisos para arbitrar este evento.');
                if (btn) btn.disabled = false;
            }
        } catch (e) {
            alert('Error de conexión al actualizar el arbitraje.');
            if (btn) btn.disabled = false;
        }
    }

    // 2. Ejecutar Drop a un participante desde la Web
    async function ejecutarDropWeb(eventId, userId, userName) {
        if (!confirm(`¿Seguro que quieres dar de baja (DROP) a ${userName}?\n\nSu plaza pasará al siguiente jugador en reserva y no entrará en los emparejamientos.`)) return;
        try {
            const res = await fetch(`/api/torneos/${eventId}/jugador/${userId}/drop`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'No se pudo retirar al jugador.');
            }
        } catch (e) {
            alert('Error de conexión al ejecutar Drop.');
        }
    }

    // 3. Pestañas de Seguimiento en Directo (Cuadro y Clasificación)
    function switchLiveWebTab(tab) {
        const cuadroCont = document.getElementById('webLiveCuadroContainer');
        const tablaCont = document.getElementById('webLiveTablaContainer');
        const btnCuadro = document.getElementById('btnWebTabCuadro');
        const btnTabla = document.getElementById('btnWebTabTabla');

        if (tab === 'cuadro' && cuadroCont) {
            cuadroCont.classList.remove('d-none');
            if (tablaCont) tablaCont.classList.add('d-none');
            if (btnCuadro) btnCuadro.className = 'btn btn-warning fw-bold border-2 border-dark';
            if (btnTabla) btnTabla.className = 'btn btn-dark text-white fw-bold border-2 border-dark';
        } else if (tablaCont) {
            if (cuadroCont) cuadroCont.classList.add('d-none');
            tablaCont.classList.remove('d-none');
            if (btnCuadro) btnCuadro.className = 'btn btn-dark text-white fw-bold border-2 border-dark';
            if (btnTabla) btnTabla.className = 'btn btn-info text-dark fw-bold border-2 border-dark';
            cargarTablaEnVivoWeb();
        }
    }

    async function cargarTablaEnVivoWeb() {
        const container = document.getElementById('webLiveTablaContent');
        if (!container) return;
        try {
            const res = await fetch(`/api/torneos/{{ $event->id }}/clasificacion`);
            const data = await res.json();
            const jugadores = data.clasificacion || [];
            if (jugadores.length === 0) {
                container.innerHTML = '<div class="text-center text-white-50 py-3 fw-bold">Aún no hay rondas completadas.</div>';
                return;
            }

            let html = `
                <table class="table table-dark table-striped table-bordered mb-0 align-middle text-center">
                    <thead>
                        <tr class="text-info font-Oswald fs-5">
                            <th style="width: 60px;">#</th>
                            <th class="text-start">BLADER</th>
                            <th>PTS SUIZO</th>
                            <th>BUCHHOLZ</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            jugadores.forEach((p, idx) => {
                const pos = idx + 1;
                const badge = pos === 1 ? '🥇 1º' : (pos === 2 ? '🥈 2º' : (pos === 3 ? '🥉 3º' : `#${pos}`));
                html += `
                    <tr class="${pos <= 4 ? 'table-active fw-bold' : ''}">
                        <td class="font-Oswald fs-5 text-warning">${badge}</td>
                        <td class="text-start fw-bold text-white text-uppercase">${p.name}</td>
                        <td class="font-Oswald fs-5 text-info">${p.points}</td>
                        <td class="text-white-50 fw-bold">${p.buchholz}</td>
                    </tr>
                `;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        } catch (e) {
            container.innerHTML = '<div class="text-center text-danger py-3 fw-bold">Error al sincronizar la clasificación.</div>';
        }
    }

    function recargarSeguimientoWeb() {
        const icon = document.getElementById('iconSyncWebLive');
        if (icon) icon.classList.add('fa-spin');
        const iframe = document.getElementById('iframeLiveBracketWeb');
        if (iframe && !document.getElementById('webLiveCuadroContainer')?.classList.contains('d-none')) {
            iframe.contentWindow.location.reload();
        }
        cargarTablaEnVivoWeb();
        setTimeout(() => { if (icon) icon.classList.remove('fa-spin'); }, 600);
    }

    jQuery(document).ready(function ($) {
        $('.select2').not('.select2-modal').select2({ theme: 'bootstrap-5', width: '100%' });
        $.fn.modal.Constructor.Default.focus = false;

        $('#formModal').on('shown.bs.modal', function () {
            $('.select2-modal').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#formModal .modal-content')
            });
        });

        @if(!empty($hasEngineMatches) && empty($hasEngineBracket))
            cargarTablaEnVivoWeb();
        @endif

        // Solo copiamos a los titulares activos (excluyendo 'dropped' y reservas fuera del límite)
        @php
            $activeTitularNames = $assists
                ->filter(fn($a) => $a->pivot->puesto !== 'dropped')
                ->take($limit)
                ->map(fn($a) => $a->name . '#' . str_pad($a->id, 4, '0', STR_PAD_LEFT))
                ->values()
                ->toArray();
        @endphp
        const titularNamesList = {!! json_encode($activeTitularNames) !!}.join('\n');

        $('#comenzarTorneoBtn').on('click', function(e) {
            e.preventDefault();
            const button = $(this);
            if (navigator.clipboard) {
                navigator.clipboard.writeText(titularNamesList).then(() => {
                    alert('🚀 ¡TORNEO EN LÍNEA!\nLos nombres de los titulares activos (sin Drops) se han copiado al portapapeles.');
                    button.closest('form').submit();
                }).catch(() => button.closest('form').submit());
            } else {
                button.closest('form').submit();
            }
        });

        $('#copyButton').on('click', function() {
            navigator.clipboard ? navigator.clipboard.writeText(titularNamesList).then(() => alert('TITULARES ACTIVOS COPIADOS AL PORTAPAPELES.')) : alert('Navegador no compatible');
        });

        $('#copyButtonEmail').on('click', function() {
            const emails = {!! json_encode($assists->pluck('email')->values()->toArray()) !!}.join('; ');
            navigator.clipboard ? navigator.clipboard.writeText(emails).then(() => alert('CORREOS COPIADOS AL PORTAPAPELES (;).')) : alert('Navegador no compatible');
        });
    });
</script>
@endsection