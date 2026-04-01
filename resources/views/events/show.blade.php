@extends('layouts.app')

@section('title', $event->name)

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
    /* ====================================================================
       ESTILOS ESPECÍFICOS: DETALLE DE EVENTO (El resto hereda del layout)
       ==================================================================== */

    /* --- TÍTULO Y ESTADO --- */
    .event-title {
        font-family: 'Bangers', cursive;
        font-size: 3.5rem;
        color: var(--sbbl-gold);
        text-shadow: 3px 3px 0 #000, 6px 6px 0 var(--shonen-blue);
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 1rem;
        line-height: 1.1;
    }

    .status-badge {
        font-family: 'Bangers', cursive;
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
    .info-list .info-value { font-family: 'Bangers', cursive; font-size: 1.3rem; letter-spacing: 1px; }

    /* --- SECCIÓN PARTICIPANTES --- */
    .participant-card {
        background: var(--sbbl-blue-2);
        border: 3px solid #000;
        box-shadow: 6px 6px 0 var(--sbbl-gold);
        border-radius: 0;
        margin-bottom: 2rem;
    }

    .participant-item {
        background: rgba(0,0,0,0.3);
        border: 2px solid #000;
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
    .participant-item.reserve { border-color: var(--shonen-red); background: rgba(255, 42, 42, 0.1); }

    /* --- ARREGLO DE SELECT2 (Modo Oscuro / Shonen) --- */
    .select2-container { width: 100% !important; }
    .select2-dropdown { z-index: 999999 !important; background-color: #000 !important; border: 2px solid var(--sbbl-gold) !important; border-radius: 0 !important; }
    .select2-container--bootstrap-5 .select2-selection { background-color: #111 !important; border: 2px solid #000 !important; border-radius: 0 !important; color: #fff !important; font-weight: 900; }
    .select2-container--bootstrap-5 .select2-selection__rendered { color: #fff !important; }
    .select2-search__field { background-color: #222 !important; color: #fff !important; border: 1px solid #444 !important; }
    .select2-results__option { color: #fff !important; background-color: #000 !important; font-weight: bold; }
    .select2-container--bootstrap-5 .select2-results__option--highlighted { background-color: var(--sbbl-gold) !important; color: #000 !important; }

    /* --- ARREGLO MODAL --- */
    .modal-backdrop { z-index: 1050 !important; opacity: 0.85 !important; background-color: #000 !important; }
    .modal { z-index: 1060 !important; }
    .modal-content { background-color: transparent !important; border: none !important; box-shadow: none !important; border-radius: 0 !important; }
    .btn-close-white { filter: invert(1) grayscale(100%) brightness(200%); }

    /* Chat Evento */
    .chat-header {
        font-family: 'Bangers', cursive; font-size: 1.8rem; letter-spacing: 1px;
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
    @endphp

    {{-- CABECERA DEL EVENTO --}}
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="event-title">{{ $event->name }}</h1>
            <div class="status-badge">
                <span>
                    @switch($event->status)
                        @case('OPEN') <span class="text-white">🟢 ABIERTO</span> @break
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
        {{-- COLUMNA IZQUIERDA: INFO DEL EVENTO Y CHAT --}}
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
                        <span class="badge bg-black text-white border border-2 border-white" style="font-family: 'Bangers', cursive; font-size: 1.2rem; transform: skewX(-5deg);"><span style="display:block; transform:skewX(5deg);">{{ $event->region->name }}</span></span>
                    </div>
                    <div class="position-absolute bottom-0 start-0 w-100 p-3 z-2" style="background: linear-gradient(to top, rgba(0,0,0,0.9), transparent); pointer-events: none;">
                        <h5 class="text-white mb-0 font-bangers fs-3" style="text-shadow: 2px 2px 0 #000;"><i class="fas fa-map-marker-alt me-2" style="color: var(--shonen-red);"></i>{{ $event->city }}</h5>
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
                        <li class="flex-column align-items-start">
                            <span class="info-label mb-2"><i class="fas fa-map-pin me-2 text-white"></i>Ubicación</span>
                            <div class="text-white bg-black p-3 border border-secondary w-100 fw-bold">{!! $event->location !!}</div>
                        </li>
                        @if($event->note)
                        <li class="flex-column align-items-start">
                            <span class="info-label text-warning mb-2"><i class="fas fa-sticky-note me-2 text-white"></i>Notas</span>
                            <div class="text-white fst-italic fw-bold border-start border-3 border-warning ps-2">{!! $event->note !!}</div>
                        </li>
                        @endif
                    </ul>

                    <div class="d-grid gap-3 mt-4">
                        @if(in_array($event->beys, ['ranking', 'rankingplus']))
                            <a href="{{ route('inicio.rules') }}" target="_blank" class="btn-shonen btn-shonen-info text-center w-100" style="padding: 10px;">
                                <span><i class="fas fa-book me-1"></i> REGLAMENTO OFICIAL</span>
                            </a>
                        @endif
                        @if ($event->iframe)
                            <a href="{{ $event->iframe }}" target="_blank" class="btn-shonen text-center w-100" style="background: var(--shonen-red); color: #fff; padding: 10px;">
                                <span><i class="fab fa-youtube me-1"></i> VER VIDEO</span>
                            </a>
                        @endif
                        @if ($event->challonge)
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

        {{-- COLUMNA DERECHA: PARTICIPANTES Y ESTADO --}}
        <div class="col-lg-8 col-md-7">

            @if (session('success'))
                <div class="alert alert-shonen alert-shonen-success mb-4 text-center"><div><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div></div>
            @endif
            @if (session('error'))
                <div class="alert alert-shonen alert-shonen-danger mb-4 text-center"><div><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}</div></div>
            @endif

            {{-- REVISIONES Y JUECES --}}
            @if (in_array($event->beys, ['ranking', 'rankingplus']) && (in_array($event->status, ['INVALID', 'CLOSE']) || (Auth::check() && Auth::user()->is_admin)))
                <div class="mb-4">
                    <button class="btn-shonen btn-shonen-warning w-100 text-center" type="button"
                            data-bs-toggle="collapse" data-bs-target="#reviewCollapse{{ $event->id }}"
                            aria-expanded="false" aria-controls="reviewCollapse{{ $event->id }}" style="padding: 10px;">
                        <span><i class="fas fa-clipboard-check me-2"></i> MOSTRAR VALIDADORES Y COMENTARIOS</span>
                    </button>

                    <div class="collapse mt-3" id="reviewCollapse{{ $event->id }}">
                        <div class="command-panel p-4">

                            {{-- LÓGICA PRINCIPAL: JUEZ vs ÁRBITROS --}}
                            @if($event->judgeReview)
                                {{-- CASO 1: HAY REVISIÓN DE JUEZ (SOLO SE MUESTRA ESTA) --}}
                                <h4 class="font-bangers text-white mb-3"><i class="fas fa-gavel me-2" style="color: var(--sbbl-gold);"></i>Resolución del Juez</h4>
                                <div class="p-3 border border-warning" style="background-color: rgba(255, 193, 7, 0.1);">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong class="text-white">Juez Principal</strong>
                                        <span class="badge {{ $event->judgeReview->status == 'approved' ? 'bg-success' : ($event->judgeReview->status == 'rejected' ? 'bg-danger' : 'bg-secondary') }} font-bangers fs-6 border border-white">
                                            {{ strtoupper($event->judgeReview->status) }}
                                        </span>
                                    </div>
                                    <div class="text-white fst-italic fw-bold">
                                        "{{ $event->judgeReview->comment }}"
                                    </div>
                                </div>

                            @else
                                {{-- CASO 2: NO HAY JUEZ, MOSTRAMOS ÁRBITROS --}}
                                <h4 class="font-bangers text-white mb-3"><i class="fas fa-users me-2" style="color: var(--shonen-cyan);"></i>Historial de Revisiones</h4>

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
                                                    @if(Auth::check() && (Auth::user()->is_admin || Auth::user()->name == $review->referee->name))
                                                        {{ $review->referee->name ?? 'Árbitro' }}
                                                    @else
                                                        Árbitro {{ $loop->iteration }}
                                                    @endif
                                                </strong>
                                                <span class="badge {{ $review->status == 'approved' ? 'bg-success' : ($review->status == 'rejected' ? 'bg-danger' : 'bg-secondary') }} font-bangers fs-6 border border-white">
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
                            {{-- FIN LÓGICA --}}
                        </div>
                    </div>
                </div>
            @endif


            <div class="participant-card">
                <div class="card-body p-4 bg-transparent">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                        <h2 class="font-bangers text-white m-0" style="font-size: 2.5rem; letter-spacing: 1px;">
                            <i class="fas fa-users me-2" style="color: var(--sbbl-gold);"></i> PARTICIPANTES <span class="badge bg-white text-dark ms-2 border border-2 border-dark" style="font-size: 1.5rem;">{{ $assists->count() }}</span>
                        </h2>

                        <div class="d-flex flex-wrap gap-2">
                            @if (($event->status == "OPEN") && (Auth::user()->is_admin || Auth::user()->is_referee) && ($diffInHours <= 24 && $diffInHours >= 0))
                                <form method="POST" action="{{ route('events.estado', ['event' => $event->id, 'estado' => 'inscripcion']) }}">
                                    @csrf @method('PUT')
                                    <button type="submit" class="btn btn-secondary fw-bold border-2 border-dark">CERRAR INSC.</button>
                                </form>
                            @endif

                            @if (($event->status != "CLOSE" && $event->status != "INVALID") && Auth::user()->is_referee || ($event->status == "OPEN" && $event->created_by == Auth::user()->id))
                                <a href="{{ route('events.edit', ['event' => $event->id]) }}" class="btn btn-light fw-bold border-2 border-dark"><i class="fas fa-edit me-1"></i> EDITAR</a>
                            @endif

                            @if ($isUserInscribed && $event->status != "INVALID")
                                <button type="button" class="btn-shonen btn-shonen-info text-center" data-bs-toggle="modal" data-bs-target="#formModal" style="padding: 5px 15px;">
                                    <span><i class="fas fa-layer-group me-1"></i> MI DECK</span>
                                </button>
                            @endif
                        </div>
                    @if(isset($equiposAsistentes) && $equiposAsistentes->isNotEmpty())
                        <div class="w-100 mt-2">
                            <button type="button" class="btn-shonen w-100 text-center" style="background: transparent; color: #fff; border-color: #fff; padding: 10px;" data-bs-toggle="modal" data-bs-target="#teamsModal">
                                <span><i class="fas fa-shield-alt me-2" style="color: var(--shonen-cyan);"></i> VER EQUIPOS REPRESENTADOS ({{ $equiposAsistentes->count() }})</span>
                            </button>
                        </div>
                    @endif
                    </div>

                    <div class="p-4 mb-4 border border-dark" style="background-color: #000;">
                    @auth
                        @if($event->status == "OPEN" && $event->date > $hoy)

                        {{-- INICIO BLOQUE LIMITES DE ESTADIO --}}
                        @if($event->has_stadium_limit)
                            @php
                                $limit = 9999;
                                $limitText = "AFORO ILIMITADO";
                                $alertColor = "success";
                                $currentCount = $assists->count();

                                if ($event->stadiums == 1) {
                                    $limit = 20;
                                    $limitText = "AFORO ASEGURADO: 20 BLADERS (1 ESTADIO)";
                                    $alertColor = ($currentCount >= $limit) ? 'danger' : 'success';
                                } elseif ($event->stadiums == 2) {
                                    $limit = 30;
                                    $limitText = "AFORO ASEGURADO: 30 BLADERS (2 ESTADIOS)";
                                    $alertColor = ($currentCount >= $limit) ? 'danger' : 'info';
                                } elseif ($event->stadiums == 4) {
                                    $limit = 45;
                                    $limitText = "AFORO ASEGURADO: 45 BLADERS (4 ESTADIOS)";
                                    $alertColor = ($currentCount >= $limit) ? 'danger' : 'info';
                                }

                                $shonenColor = $alertColor == 'success' ? '#00ff00' : ($alertColor == 'danger' ? 'var(--shonen-red)' : 'var(--shonen-cyan)');
                            @endphp

                            <div class="text-center mb-4 p-3 border" style="border-color: {{ $shonenColor }} !important; background: rgba(255,255,255,0.05);">
                                <h4 class="font-bangers mb-2" style="color: {{ $shonenColor }}; font-size: 2rem; letter-spacing: 1px;">
                                    🏟️ {{ $event->stadiums }} ESTADIOS DETECTADOS
                                </h4>
                                <p class="text-white fw-bold mb-3">{{ $limitText }}</p>

                                <div class="progress rounded-0 border border-secondary" style="height: 25px; background-color: #111;">
                                    <div class="progress-bar fw-bold text-dark font-bangers fs-5" role="progressbar"
                                         style="background-color: {{ $shonenColor }}; width: {{ min(($currentCount / ($limit > 1000 ? 50 : $limit)) * 100, 100) }}%;">
                                        {{ $currentCount }} / {{ ($limit > 1000) ? '∞' : $limit }}
                                    </div>
                                </div>

                                @if($currentCount >= $limit && $limit < 1000)
                                    <hr class="border-secondary mt-4">
                                    <p class="mb-0 fw-bold text-white" style="font-size: 1.1rem;">
                                        <i class="fas fa-exclamation-triangle text-danger me-2"></i> AFORO GARANTIZADO COMPLETO. LAS NUEVAS INSCRIPCIONES ENTRAN EN LISTA DE ESPERA.
                                    </p>
                                @endif
                            </div>
                        @endif
                        {{-- FIN BLOQUE LIMITES DE ESTADIO --}}

                        @if (!$isUserInscribed)
                            {{-- MENSAJE DE AVISO --}}
                            @if(isset($isRegistered) && $isRegistered && in_array($event->beys, ['ranking', 'rankingplus']))
                                <div class="alert alert-shonen alert-shonen-warning text-center">
                                    <div><i class="fas fa-exclamation-triangle me-2"></i> YA ESTÁS INSCRITO EN OTRO TORNEO DE RANKING ESTA SEMANA.</div>
                                </div>
                            @endif

                            @php $creator = \App\Models\User::find($event->created_by); @endphp
                            @if ($creator && !($creator->is_jury || $creator->is_referee || $creator->is_reviewer))
                                <div class="alert alert-shonen alert-shonen-info text-center" style="border-color: #fff !important; color: #fff !important; box-shadow: 4px 4px 0 #fff;">
                                    <div>EVENTO ORGANIZADO POR LA COMUNIDAD. MATERIAL NO GARANTIZADO POR SBBL.</div>
                                </div>
                            @endif

                            @if($event->beys === "grancopa" || $event->beys === "copapaypal")
                                <div class="text-center p-4 border border-secondary" style="background: #111;">
                                    <p class="text-white font-bangers fs-3 mb-3">COSTE DE INSCRIPCIÓN: <span style="color: var(--sbbl-gold);">{{ $event->beys === "grancopa" ? '5€' : '2€' }}</span></p>
                                    <div id="paypal-button-container" class="d-flex justify-content-center"></div>
                                </div>
                            @else
                                <form method="POST" action="{{ route('events.assist', ['event' => $event->id]) }}">
                                    @csrf
                                    <button type="submit" class="btn-shonen w-100 text-center" style="background: #00ff00; color: #000; border-color: #000; padding: 15px; font-size: 1.8rem;"
                                        @if(in_array($event->beys, ['ranking', 'rankingplus']) &&
                                        ((isset($rankingTournamentsLeft) && $rankingTournamentsLeft == 0) || (isset($isRegistered) && $isRegistered)))
                                        disabled style="background: #555 !important; color: #888 !important; border-color: #333 !important; cursor: not-allowed; box-shadow: none;"
                                        @endif>
                                        <span><i class="fas fa-fist-raised me-2"></i> INSCRIBIRSE EN LA BATALLA
                                        @if(in_array($event->beys, ['ranking', 'rankingplus']) && isset($rankingTournamentsLeft))
                                            <span class="badge bg-black text-white ms-3 border border-white" style="font-size: 1rem; vertical-align: middle;">Restantes: {{ $rankingTournamentsLeft }}</span>
                                        @endif
                                        </span>
                                    </button>
                                </form>
                            @endif

                        @else
                            <div class="text-center">
                                <button type="button" class="btn-shonen text-center" style="background: var(--shonen-red); color: #fff; padding: 10px 20px;" data-bs-toggle="modal" data-bs-target="#confirmModal">
                                    <span><i class="fas fa-times-circle me-2"></i> CANCELAR ASISTENCIA</span>
                                </button>
                            </div>
                        @endif

                    @elseif($event->status != "OPEN")
                        <div class="text-center text-white font-bangers fs-3">LAS INSCRIPCIONES ESTÁN CERRADAS.</div>
                    @else
                        <div class="text-center text-white font-bangers fs-3">EL EVENTO HA FINALIZADO.</div>
                    @endif
                    @else
                        <div class="text-center font-bangers fs-3" style="color: var(--sbbl-gold);">INICIA SESIÓN PARA INSCRIBIRTE AL COMBATE.</div>
                    @endauth
                    </div>

                    {{-- UTILIDADES --}}
                    <div class="d-flex flex-wrap gap-3 mb-4">
                        <button id="copyButton" class="btn btn-dark fw-bold border-2 border-secondary flex-fill text-white">
                            <i class="fas fa-copy me-2" style="color: var(--sbbl-gold);"></i> Copiar Nombres
                        </button>
                        @if (Auth::user() && Auth::user()->is_jury)
                            <button id="copyButtonEmail" class="btn btn-dark fw-bold border-2 border-secondary flex-fill text-white">
                                <i class="fas fa-envelope me-2" style="color: var(--shonen-cyan);"></i> Copiar Emails
                            </button>
                        @endif
                    </div>

                    @if (Auth::user() && Auth::user()->is_jury)
                        <div class="mb-4 bg-black p-3 border border-secondary">
                            <h6 class="text-white fw-bold mb-2">Añadir Participante Manual:</h6>
                            <form method="POST" action="{{ route('events.addAssist', ['event' => $event->id]) }}" class="input-group">
                                @csrf
                                <select name="participante_id" class="form-select form-select-sm select2">
                                    <option value="">Buscar blader...</option>
                                    @foreach($participantes as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-success fw-bold px-3 border-2 border-dark"><i class="fas fa-plus"></i></button>
                            </form>
                        </div>
                    @endif

                    {{-- LISTA DE PARTICIPANTES --}}
                    @if(count($assists) > 0)
                        <form method="POST" action="{{ route('events.updatePuestos', ['event' => $event->id]) }}">
                            @csrf @method('PUT')

                            @php
                                $limit = 9999;
                                if ($event->has_stadium_limit) {
                                    if ($event->stadiums == 1) $limit = 20;
                                    elseif ($event->stadiums == 2) $limit = 30;
                                    elseif ($event->stadiums == 4) $limit = 45;
                                }
                            @endphp

                            @foreach ($assists as $assist)
                                @php $isReserve = ($loop->iteration > $limit); @endphp

                                <div class="participant-item p-3 {{ $isReserve ? 'reserve' : '' }}">
                                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">

                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold border border-2 border-dark" style="width: 45px; height: 45px; font-size: 1.2rem; background: var(--sbbl-gold); color: #000;">
                                                {{ substr($assist->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-bangers fs-4 text-white d-flex align-items-center gap-2 flex-wrap" style="letter-spacing: 1px;">
                                                    {{ $assist->name }}

                                                    {{-- ETIQUETA DE EQUIPO DEL USUARIO --}}
                                                    @if($assist->active_team)
                                                        <span class="badge font-sans fw-bold" style="background-color: {{ $assist->active_team->color ?? '#475569' }}; font-size: 0.75rem; border: 2px solid #000; letter-spacing: 0;">
                                                            <i class="fas fa-shield-alt me-1"></i>{{ $assist->active_team->name }}
                                                        </span>
                                                    @endif

                                                    {{-- ETIQUETA DE RESERVA --}}
                                                    @if($isReserve)
                                                        <span class="badge bg-danger text-white border border-white font-sans" style="font-size: 0.75rem; letter-spacing: 0;">RESERVA #{{ $loop->iteration - $limit }}</span>
                                                    @else
                                                        <span class="badge bg-dark text-white border border-secondary font-sans" style="font-size: 0.75rem; letter-spacing: 0;">#{{ $loop->iteration }}</span>
                                                    @endif
                                                </div>

                                                @if(!empty($assist->pivot->puesto) && $assist->pivot->puesto !== 'participante')
                                                    <span class="badge bg-white text-dark border border-dark mt-1 fw-bold">{{ strtoupper($assist->pivot->puesto) }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                                            @if (($event->status != "CLOSE" && $event->status != "INVALID") && Auth::user()->is_referee || ($event->status == "OPEN" && $event->created_by == Auth::user()->id))
                                                <input type="hidden" name="participantes[{{ $assist->id }}][id]" value="{{ $assist->id }}">
                                                <select class="form-select fw-bold bg-black text-white border-2 border-secondary" style="width: 140px;" name="participantes[{{ $assist->id }}][puesto]">
                                                    <option value="participante" {{ $assist->pivot->puesto == 'participante' ? 'selected' : '' }}>Participante</option>
                                                    @php $totalParticipantes = $assists->count(); @endphp
                                                    @if($totalParticipantes >= 4) <option value="primero" {{ $assist->pivot->puesto == 'primero' ? 'selected' : '' }}>1º Lugar</option> @endif
                                                    @if($totalParticipantes >= 6) <option value="segundo" {{ $assist->pivot->puesto == 'segundo' ? 'selected' : '' }}>2º Lugar</option> @endif
                                                    @if($totalParticipantes >= 9) <option value="tercero" {{ $assist->pivot->puesto == 'tercero' ? 'selected' : '' }}>3º Lugar</option> @endif
                                                    @if($totalParticipantes >= 17) <option value="cuarto" {{ $assist->pivot->puesto == 'cuarto' ? 'selected' : '' }}>4º Lugar</option> @endif
                                                    @if($totalParticipantes >= 25) <option value="quinto" {{ $assist->pivot->puesto == 'quinto' ? 'selected' : '' }}>5º Lugar</option> @endif
                                                    @if($totalParticipantes >= 33) <option value="septimo" {{ $assist->pivot->puesto == 'septimo' ? 'selected' : '' }}>7º Lugar</option> @endif
                                                    <option value="nopresentado" {{ $assist->pivot->puesto == 'nopresentado' ? 'selected' : '' }}>No Pres.</option>
                                                </select>
                                            @endif

                                            @php $hasRes = isset($resultsByParticipant[$assist->id]) && count($resultsByParticipant[$assist->id]) > 0; @endphp
                                            @if($hasRes)
                                                <button type="button" class="btn btn-outline-light fw-bold border-2" data-bs-toggle="collapse" data-bs-target="#res-{{ $assist->id }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="collapse mt-3" id="res-{{ $assist->id }}">
                                        <div class="bg-black p-3 border border-secondary text-white fw-bold">
                                            @if($hasRes)
                                                @foreach($resultsByParticipant[$assist->id] as $res)
                                                    <div class="d-flex flex-wrap gap-2 border-bottom border-dark pb-2 mb-2">
                                                        <span class="text-white font-bangers fs-5" style="letter-spacing: 1px; color: var(--sbbl-gold) !important;">{{ $res->blade }}</span>
                                                        <span><i class="fas fa-plus text-secondary mx-1" style="font-size: 0.7rem;"></i> {{ $res->assist_blade }} <span class="mx-2 text-secondary">|</span> {{ $res->ratchet }} <span class="text-secondary mx-1">·</span> {{ $res->bit }}</span>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @if (($event->status != "CLOSE" && $event->status != "INVALID") && Auth::user()->is_referee || ($event->status == "OPEN" && $event->created_by == Auth::user()->id))
                                <div class="mt-4 p-4 border border-dark" style="background: #000;">
                                    <h5 class="font-bangers text-white fs-3 mb-3"><i class="fas fa-lock me-2" style="color: var(--shonen-red);"></i>CIERRE DE TORNEO</h5>

                                    <input type="url" name="iframe" autocomplete="off" class="form-control fw-bold border-2 border-secondary bg-dark text-white mb-3 p-2" placeholder="Link YouTube del Torneo" value="{{ old('iframe', $event->iframe) }}" required>

                                    <input type="url" name="challonge" autocomplete="off" class="form-control fw-bold border-2 border-secondary bg-dark text-white mb-3 p-2" placeholder="Link del Bracket (Challonge/Tonamel)" value="{{ old('challonge', $event->challonge) }}" required>

                                    <div class="form-check mb-4 bg-dark p-3 border border-warning">
                                        <input class="form-check-input ms-1" type="checkbox" id="podioCheck" required style="transform: scale(1.5);">
                                        <label class="form-check-label text-white fw-bold ms-3" for="podioCheck">
                                            CONFIRMO QUE EL PODIO HA SIDO ASIGNADO CORRECTAMENTE A LOS PARTICIPANTES.
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
                            <h4 class="font-bangers text-white fs-3">LA ARENA ESTÁ VACÍA.</h4>
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
                <h5 class="modal-title font-bangers fs-3 text-white"><i class="fas fa-exclamation-triangle me-2" style="color: var(--shonen-red);"></i> ALERTA DE SISTEMA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4" style="background: var(--sbbl-blue-2);">
                <p class="text-white fw-bold fs-5 mb-0">¿REALMENTE QUIERES CANCELAR TU ASISTENCIA A LA MISIÓN?</p>
            </div>
            <div class="modal-footer border-top border-dark d-flex justify-content-center gap-3" style="background: #000;">
                <button type="button" class="btn fw-bold bg-dark text-white border border-secondary" data-bs-dismiss="modal">MANTENER POSICIÓN</button>
                <form method="POST" action="{{ route('events.noassist', ['event' => $event->id]) }}">
                    @method('DELETE') @csrf
                    <button type="submit" class="btn fw-bold" style="background: var(--shonen-red); color: #fff; border: 2px solid #000;">SÍ, ABORTAR MISIÓN</button>
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
                <h5 class="modal-title font-bangers fs-3 text-white"><i class="fas fa-shield-alt me-2" style="color: var(--shonen-cyan);"></i> EQUIPOS REPRESENTADOS</h5>
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
                                            <strong class="font-bangers fs-4 text-white text-truncate" style="letter-spacing: 1px;">{{ $equipo['name'] }}</strong>
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
    jQuery(document).ready(function ($) {

        // 1. Inicializar Select2
        $('.select2').not('.select2-modal').select2({ theme: 'bootstrap-5', width: '100%' });

        $('#formModal').on('shown.bs.modal', function () {
            $('.select2-modal').select2({ theme: 'bootstrap-5', width: '100%', dropdownParent: $('#formModal') });
        });

        // --- COLORES DEL CHAT ---
        function stringToColor(str) {
            let hash = 0;
            for (let i = 0; i < str.length; i++) hash = str.charCodeAt(i) + ((hash << 5) - hash);
            const h = Math.abs(hash) % 360;
            return `hsl(${h}, 85%, 70%)`;
        }

        function applyColors(elements) {
            $(elements).each(function() {
                const text = $(this).text();
                if(text.includes(':') || text.length < 20) {
                    const name = text.replace(':', '').trim();
                    const color = stringToColor(name);
                    $(this).attr('style', `color: ${color} !important;`);
                }
            });
        }

        const chatObserver = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    const newElements = $(mutation.addedNodes).find('strong, b, .fw-bold');
                    applyColors(newElements);
                    applyColors($('#app strong, #app b').filter(function() { return !$(this).attr('style'); }));
                }
            });
        });

        const chatContainer = document.getElementById('chat-container-wrapper');
        if (chatContainer) {
            chatObserver.observe(chatContainer, { childList: true, subtree: true });
        }

        setTimeout(() => { applyColors($('#app strong, #app b, #app .fw-bold')); }, 1000);

        // --- PAYPAL CONFIGURACIÓN ---
        @if(in_array($event->beys, ["grancopa", "copapaypal"]))
            const amount = "{{ $event->beys === 'grancopa' ? '5.00' : '2.00' }}";

            paypal.Buttons({
                style: { layout: 'vertical', color: 'gold', shape: 'rect', label: 'pay' },

                onClick: function(data, actions) { return actions.resolve(); },

                createOrder: (data, actions) => {
                    return actions.order.create({
                        purchase_units: [{
                            description: "Inscripción Misión: {{ $event->name }}",
                            amount: { value: amount }
                        }]
                    });
                },

                onApprove: (data, actions) => {
                    return actions.order.capture().then(function(details) {
                        return fetch("{{ route('events.assist', ['event' => $event->id]) }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                "Accept": "application/json"
                            },
                            body: JSON.stringify({
                                paypal_order_id: data.orderID,
                                paypal_details: details
                            })
                        }).then(response => {
                            if (!response.ok) throw new Error('Error al registrar en la base de datos');
                            return response.json();
                        }).then(data => {
                            alert('¡INSCRIPCIÓN COMPLETADA CON ÉXITO!');
                            window.location.reload();
                        }).catch(err => {
                            console.error(err);
                            alert('El pago se realizó pero hubo un error de sincronización. Contacta con el Alto Mando. ID: ' + data.orderID);
                        });
                    });
                },

                onError: (err) => {
                    console.error('Paypal Error:', err);
                    alert('ERROR EN LA TRANSMISIÓN DE FONDOS. NO SE HA REALIZADO CARGO.');
                }
            }).render("#paypal-button-container");
        @endif

        // --- BOTONES DE COPIAR ---
        $('#copyButton').on('click', function() {
            const names = {!! json_encode($assists->pluck('name')->values()->toArray()) !!}.join('\n');
            navigator.clipboard ? navigator.clipboard.writeText(names).then(() => alert('NOMBRES DE BLADERS COPIADOS AL PORTAPAPELES.')) : alert('Navegador no compatible');
        });

        $('#copyButtonEmail').on('click', function() {
            const emails = {!! json_encode($assists->pluck('email')->values()->toArray()) !!}.join('; ');
            navigator.clipboard ? navigator.clipboard.writeText(emails).then(() => alert('CORREOS COPIADOS AL PORTAPAPELES (;).')) : alert('Navegador no compatible');
        });
    });
</script>
@endsection
