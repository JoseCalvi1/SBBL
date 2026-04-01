@extends('layouts.app')

@section('meta')
    <link rel="canonical" href="{{ url('/blader/' . $canonicalSlug) }}">
    <meta property="og:title" content="{{ $user->name }} — Perfil SBBL">
    <meta property="og:description" content="{{ $totalTorneos }} torneos · {{ $totalVictorias }} victorias · {{ $winRate }}% win rate">
    <meta property="og:url" content="{{ url('/blader/' . $canonicalSlug) }}">
    <meta name="robots" content="index, follow">
@endsection

@section('styles')
<style>
    /* --- ESTILO GENERAL: ANIME / SHONEN --- */
    @import url('https://fonts.googleapis.com/css2?family=Bangers&family=Montserrat:ital,wght@0,700;0,900;1,900&display=swap');

    :root {
        --shonen-red: #ff2a2a;
        --shonen-yellow: #ffd700;
        --shonen-blue: #2a75ff;
        --dark-bg: #111;
        --panel-bg: #1a1a1a;
        --text-main: #fff;
    }

    body {
        font-family: 'Montserrat', sans-serif;
        background-color: var(--dark-bg);
        color: var(--text-main);
        /* Patrón de puntos gruesos tipo manga */
        background-image: radial-gradient(rgba(255, 255, 255, 0.15) 2px, transparent 2px);
        background-size: 20px 20px;
    }

    h1, h2, h3, h4, h5, .font-bangers {
        font-family: 'Bangers', cursive;
        letter-spacing: 1px;
    }

    /* ── ESTRUCTURA BASE ── */
    .command-panel {
        background: var(--panel-bg);
        border: 3px solid #000;
        border-radius: 0 15px 0 15px;
        box-shadow: 6px 6px 0px var(--shonen-blue);
        overflow: hidden;
        position: relative;
        transition: all 0.2s;
    }
    .command-panel:hover {
        transform: translate(-2px, -2px);
        box-shadow: 8px 8px 0px var(--shonen-red);
    }

    .panel-header {
        background: #000;
        padding: 10px 20px;
        border-bottom: 3px solid var(--shonen-yellow);
        color: #fff;
        font-family: 'Bangers', cursive;
        font-size: 1.3rem;
        letter-spacing: 1px;
        text-transform: uppercase;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* ── HERO ── */
    .profile-hero-bg {
        height: 160px;
        background-size: cover;
        background-position: center;
        position: relative;
        border-bottom: 3px solid #000;
        /* Un pequeño filtro para que el texto resalte más si la imagen es clara */
        filter: contrast(1.1) brightness(0.8);
    }
    .profile-hero-bg::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(to bottom, transparent 40%, var(--panel-bg) 100%);
    }
    .profile-avatar-wrap {
        position: relative;
        width: 120px;
        height: 120px;
        margin: -60px auto 12px auto;
        z-index: 10;
    }
    .profile-avatar-wrap img {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        object-fit: cover;
        border-radius: 0; /* Lo hacemos cuadrado/poligonal */
        clip-path: polygon(15px 0, 100% 0, 100% calc(100% - 15px), calc(100% - 15px) 100%, 0 100%, 0 15px);
        border: 3px solid #000;
        box-shadow: 4px 4px 0px var(--shonen-yellow);
    }
    .pilot-name {
        font-family: 'Bangers', cursive;
        font-size: clamp(2rem, 5vw, 3rem);
        color: var(--shonen-yellow);
        text-shadow: 2px 2px 0px #000, 4px 4px 0px var(--shonen-red);
        word-break: break-word;
        text-align: center;
        letter-spacing: 2px;
        line-height: 1;
    }
    .division-pill {
        font-family: 'Bangers', cursive;
        font-size: 1rem;
        padding: 4px 12px;
        border-radius: 0;
        border: 2px solid #000;
        text-transform: uppercase;
        transform: skewX(-10deg);
        display: inline-block;
    }
    .division-pill > * { transform: skewX(10deg); display: block; } /* Des-inclinar texto */
    .division-oro    { color: #000; background: var(--shonen-yellow); box-shadow: 3px 3px 0 #000; }
    .division-plata  { color: #000; background: #e2e8f0; box-shadow: 3px 3px 0 #000; }
    .division-bronce { color: #fff; background: #b45309; box-shadow: 3px 3px 0 #000; }

    /* ── SEASON TABS ── */
    .season-tabs {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }
    .season-tab {
        font-family: 'Bangers', cursive;
        font-size: 1.1rem;
        padding: 5px 15px;
        border-radius: 0;
        border: 2px solid #fff;
        color: #fff;
        background: #000;
        cursor: pointer;
        transition: all 0.2s;
        transform: skewX(-5deg);
    }
    .season-tab.active,
    .season-tab:hover {
        background: var(--shonen-yellow);
        color: #000;
        border-color: #000;
        box-shadow: 3px 3px 0 var(--shonen-red);
    }

    /* ── PODIO ── */
    .podium-box {
        background: #000;
        border: 2px solid #333;
        border-radius: 0;
        padding: 10px 6px;
        text-align: center;
        flex: 1;
        transform: skewX(-5deg);
        box-shadow: 4px 4px 0 var(--shonen-blue);
        transition: 0.2s;
    }
    .podium-box > * { transform: skewX(5deg); }
    .podium-box:hover { background: #111; box-shadow: 4px 4px 0 var(--shonen-red); }
    .podium-num { font-family: 'Bangers', cursive; font-size: 2.2rem; line-height: 1; }
    .podium-lbl { font-size: 0.7rem; font-weight: 900; text-transform: uppercase; margin-top: 5px; }

    /* ── BARRAS WIN RATE ── */
    .wr-bar-bg {
        background: #000;
        border: 2px solid #333;
        border-radius: 0;
        height: 12px;
        overflow: hidden;
        margin-top: 4px;
        transform: skewX(-15deg);
    }
    .wr-bar-fill {
        height: 100%;
        background: var(--shonen-blue);
        transition: width 0.6s ease;
    }
    .wr-bar-fill.amber { background: var(--shonen-yellow); }

    /* ── COMBOS ── */
    .combo-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 10px;
        margin-bottom: 8px;
        background: rgba(0,0,0,0.5);
        border: 2px solid #333;
        transform: skewX(-5deg);
        transition: 0.2s;
    }
    .combo-row > * { transform: skewX(5deg); }
    .combo-row:hover { background: #000; border-color: var(--shonen-yellow); }
    .combo-rank { font-family: 'Bangers', cursive; font-size: 1.4rem; color: var(--shonen-red); min-width: 25px; text-align: center; }
    .combo-wr   { font-family: 'Bangers', cursive; font-size: 1.2rem; color: var(--shonen-yellow); text-shadow: 1px 1px 0 #000; }

    /* ── BADGES / LOGROS ── */
    .badge-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }
    @media (max-width: 576px) {
        .badge-grid { grid-template-columns: repeat(3, 1fr); }
    }
    .badge-item {
        background: #000;
        border: 2px solid #333;
        border-radius: 0;
        padding: 15px 8px;
        text-align: center;
        box-shadow: 4px 4px 0 var(--shonen-blue);
        transition: 0.2s;
    }
    .badge-item:hover { border-color: var(--shonen-yellow); box-shadow: 4px 4px 0 var(--shonen-red); transform: translateY(-2px); }
    .badge-item.locked { opacity: 0.4; filter: grayscale(1); box-shadow: none; }
    .badge-item.locked:hover { transform: none; border-color: #333; }
    .badge-icon {
        width: 45px; height: 45px;
        border-radius: 50%;
        margin: 0 auto 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px;
        border: 2px solid var(--shonen-yellow);
        background: #111 !important;
    }
    .badge-name { font-size: 0.75rem; font-weight: 900; text-transform: uppercase; line-height: 1.2; }

    /* ── HISTORIAL EVENTOS ── */
    .event-row {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px 10px;
        margin-bottom: 8px;
        background: rgba(0,0,0,0.5);
        border: 2px solid #333;
        transition: 0.2s;
    }
    .event-row:hover { background: #000; border-color: var(--shonen-blue); }
    .event-date { font-family: 'Bangers', cursive; font-size: 1.1rem; color: var(--shonen-yellow); min-width: 55px; line-height: 1; text-align: center;}
    .event-name { font-size: 0.95rem; font-weight: 900; text-transform: uppercase; }
    .event-meta { font-size: 0.75rem; font-weight: 700; color: #888; }

    .result-pill {
        font-family: 'Bangers', cursive;
        font-size: 1rem;
        padding: 4px 10px;
        border-radius: 0;
        border: 2px solid #000;
        transform: skewX(-10deg);
        display: inline-block;
        white-space: nowrap;
    }
    .result-pill > * { transform: skewX(10deg); display: block; }
    .result-1st  { background: var(--shonen-yellow); color: #000; box-shadow: 2px 2px 0 #000; }
    .result-2nd  { background: #e2e8f0; color: #000; box-shadow: 2px 2px 0 #000; }
    .result-3rd  { background: #b45309; color: #fff; box-shadow: 2px 2px 0 #000; }
    .result-top  { background: var(--shonen-blue); color: #fff; box-shadow: 2px 2px 0 #000; }
    .result-part { background: #333; color: #fff; box-shadow: 2px 2px 0 #000; }

    /* ── GRÁFICA DE PUNTOS ── */
    .pts-chart-wrap { height: 100px; display: flex; align-items: flex-end; gap: 4px; padding-bottom: 5px; border-bottom: 2px solid #333;}
    .pts-bar {
        flex: 1;
        border-radius: 0;
        min-height: 4px;
        background: var(--shonen-blue);
        border: 1px solid #000;
        transition: 0.2s;
    }
    .pts-bar:hover { background: var(--shonen-red); transform: scaleY(1.05); transform-origin: bottom; }
    .pts-bar.highlight { background: var(--shonen-yellow); }
    .pts-labels { display: flex; gap: 4px; margin-top: 8px; }
    .pts-labels span { flex: 1; font-family: 'Bangers', cursive; font-size: 0.9rem; color: #fff; text-align: center; }

    /* ── SHARE BUTTON Y QR ── */
    .btn-share {
        background: #000;
        border: 2px solid #fff;
        color: #fff;
        border-radius: 0;
        font-family: 'Bangers', cursive;
        font-size: 1.1rem;
        padding: 8px 20px;
        cursor: pointer;
        transition: 0.2s;
        transform: skewX(-5deg);
        display: inline-block;
        box-shadow: 4px 4px 0 var(--shonen-blue);
    }
    .btn-share > * { transform: skewX(5deg); display: block; }
    .btn-share:hover {
        background: var(--shonen-yellow);
        color: #000;
        border-color: #000;
        box-shadow: 4px 4px 0 var(--shonen-red);
    }

    .qr-container { margin-top: 20px; }
    .qr-wrapper {
        background: #000;
        padding: 10px;
        display: inline-block;
        border: 3px solid #333;
        box-shadow: 6px 6px 0 var(--shonen-blue);
        transition: 0.2s;
    }
    .qr-wrapper:hover { border-color: var(--shonen-yellow); box-shadow: 6px 6px 0 var(--shonen-red); transform: translate(-2px, -2px); }

    /* ── META MINI-ROW ── */
    .mini-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 2px dashed #333;
        font-weight: 700;
    }
    .mini-row:last-child { border-bottom: none; }
    .mini-lbl { color: #aaa; text-transform: uppercase; }
    .mini-val { color: #fff; font-family: 'Bangers', cursive; font-size: 1.2rem; letter-spacing: 1px;}
    .mini-val.text-warning { color: var(--shonen-yellow) !important; text-shadow: 1px 1px 0 #000;}
</style>
@endsection

@section('content')
<div class="container py-4">

    <div class="row g-4 mb-4">
        {{-- Columna izquierda: identidad --}}
        <div class="col-lg-4">
            <div class="command-panel">
                <div class="profile-hero-bg" style="background-image: url('{{ $user->profile->fondo_url }}')"></div>

                <div class="px-4 pb-4 text-center">
                    <div class="profile-avatar-wrap">
                        <img src="{{ $user->profile->avatar_url }}" alt="{{ $user->name }}" loading="lazy">
                        @if($user->profile->marco)
                            <img src="{{ $user->profile->marco_url }}" alt="" loading="lazy" style="border: none; box-shadow: none;">
                        @endif
                    </div>

                    <h1 class="pilot-name mb-2">
                        {{ $user->name }}
                        <span style="font-size:0.5em; color: #fff; text-shadow: none; font-family: 'Montserrat', sans-serif; font-weight: 900;">
                            #{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}
                        </span>
                    </h1>

                    @if($user->profile->subtitulo)
                        <p class="mb-3 fw-bold" style="font-size:0.9rem; color: #ddd; background: #000; display: inline-block; padding: 5px 10px; border-left: 4px solid var(--shonen-yellow);">
                            "{{ $user->profile->subtitulo }}"
                        </p>
                    @endif

                    <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
                        @php
                            $division = mb_strtolower($user->profile->division ?? '');
                            $divClass = 'text-white border-white bg-dark';
                            if ($division === 'oro') $divClass = 'division-oro';
                            elseif ($division === 'plata') $divClass = 'division-plata';
                            elseif ($division === 'bronce') $divClass = 'division-bronce';
                        @endphp
                        @if($division)
                            <span class="division-pill {{ $divClass }}"><span>{{ ucfirst($division) }}</span></span>
                        @endif

                        <span class="division-pill text-white border-white" style="background: var(--shonen-blue); box-shadow: 3px 3px 0 #000;">
                            <span>{{ $user->profile->region->name ?? 'Sin región' }}</span>
                        </span>

                        @if($user->teams->isNotEmpty())
                            @php $equipo = $user->teams->first(); @endphp
                            <a href="{{ route('equipos.show', $equipo) }}" class="division-pill text-decoration-none" style="background: #000; color: var(--shonen-yellow); border-color: var(--shonen-yellow); box-shadow: 3px 3px 0 var(--shonen-red);">
                                <span>⚡ {{ $equipo->name }}</span>
                            </a>
                        @endif
                    </div>

                    <button class="btn-share" onclick="copyProfile()">
                        <span><i class="fas fa-share-alt me-1"></i> Compartir perfil</span>
                    </button>

                    {{-- Código QR --}}
                    <div class="qr-container">
                        @php
                            $urlPerfil = url('/blader/' . $canonicalSlug);
                            $qrApi = "https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=" . urlencode($urlPerfil) . "&color=ffd700&bgcolor=000000";
                        @endphp
                        <div class="qr-wrapper">
                            <img src="{{ $qrApi }}" alt="QR Perfil" style="width: 100px; height: 100px; image-rendering: pixelated;">
                        </div>
                        <p class="font-bangers mt-2 mb-0" style="font-size: 1.1rem; color: var(--shonen-yellow); letter-spacing: 1px;">SCANNER ID</p>
                    </div>
                </div>

                {{-- Stats rápidas --}}
                <div class="row g-0 border-top border-dark" style="border-width: 3px !important; background: #000;">
                    <div class="col-3 text-center py-3 border-end border-dark" style="border-width: 3px !important;">
                        <div class="font-bangers text-white" style="font-size:1.8rem; line-height: 1;">{{ $totalTorneos }}</div>
                        <div style="font-size:0.65rem; color:var(--shonen-yellow); font-weight: 900; text-transform:uppercase;">Torneos</div>
                    </div>
                    <div class="col-3 text-center py-3 border-end border-dark" style="border-width: 3px !important;">
                        <div class="font-bangers text-white" style="font-size:1.8rem; line-height: 1;">{{ $totalVictorias }}</div>
                        <div style="font-size:0.65rem; color:var(--shonen-yellow); font-weight: 900; text-transform:uppercase;">1.º Puesto</div>
                    </div>
                    <div class="col-3 text-center py-3 border-end border-dark" style="border-width: 3px !important;">
                        <div class="font-bangers text-white" style="font-size:1.8rem; line-height: 1;">{{ $winRate }}%</div>
                        <div style="font-size:0.65rem; color:var(--shonen-yellow); font-weight: 900; text-transform:uppercase;">Win Rate</div>
                    </div>
                    <div class="col-3 text-center py-3">
                        <div class="font-bangers text-white" style="font-size:1.8rem; line-height: 1;">{{ $temporadasActivas }}</div>
                        <div style="font-size:0.65rem; color:var(--shonen-yellow); font-weight: 900; text-transform:uppercase;">Temporadas</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Columna derecha: stats por temporada + gráfica --}}
        <div class="col-lg-8">
            <div class="row g-4">
                <div class="col-12">
                    <div class="command-panel p-0">
                        <div class="panel-header"><span><i class="fas fa-trophy me-2" style="color: var(--shonen-yellow);"></i> Registro de Temporada</span></div>
                        <div class="p-4">
                            @if($statsPorTemporada->isNotEmpty())
                                <div class="season-tabs">
                                    @foreach($statsPorTemporada as $temporada => $stats)
                                        <button class="season-tab {{ $loop->first ? 'active' : '' }}" onclick="switchSeason('{{ $temporada }}', this)">{{ $temporada }}</button>
                                    @endforeach
                                </div>
                                @foreach($statsPorTemporada as $temporada => $stats)
                                    <div class="season-data" id="season-{{ str_replace(' ', '-', mb_strtolower($temporada)) }}" style="{{ $loop->first ? '' : 'display:none' }}">
                                        <div class="d-flex gap-3 mb-4">
                                            <div class="podium-box"><div class="podium-num" style="color: var(--shonen-yellow);">{{ $stats['primeros'] }}</div><div class="podium-lbl text-white">🥇 Primeros</div></div>
                                            <div class="podium-box"><div class="podium-num" style="color:#e2e8f0">{{ $stats['segundos'] }}</div><div class="podium-lbl text-white">🥈 Segundos</div></div>
                                            <div class="podium-box"><div class="podium-num" style="color:#b45309">{{ $stats['terceros'] }}</div><div class="podium-lbl text-white">🥉 Terceros</div></div>
                                        </div>
                                        <div class="mini-row"><span class="mini-lbl">Torneos jugados</span><span class="mini-val">{{ $stats['torneos'] }}</span></div>
                                        <div class="mini-row"><span class="mini-lbl">Puntos acumulados</span><span class="mini-val text-warning">{{ $stats['puntos'] }} pts</span></div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-secondary text-center py-3 font-bangers" style="font-size: 1.5rem;">-- SIN DATOS DE TEMPORADA --</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="command-panel p-0">
                        <div class="panel-header">
                            <span><i class="fas fa-chart-line me-2" style="color: var(--shonen-yellow);"></i> Evolución de Poder</span>
                            <span class="badge bg-white text-dark border border-dark" style="font-family: 'Bangers'; font-size: 1rem;">{{ $puntosTemporadaActual }} PTS ACTUALES</span>
                        </div>
                        <div class="p-4">
                            <div class="pts-chart-wrap" id="pts-chart"></div>
                            <div class="pts-labels" id="pts-labels"></div>
                            <div class="mini-row mt-3"><span class="mini-lbl">Última Batalla</span><span class="mini-val" style="color: var(--shonen-yellow);">{{ $ultimoEvento ?? '—' }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Combos y Rendimiento --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="command-panel p-0 h-100">
                <div class="panel-header"><span><i class="fas fa-hammer me-2" style="color: var(--shonen-blue);"></i> Combos Favoritos</span></div>
                <div class="p-4">
                    @forelse($topCombos as $i => $combo)
                        <div class="combo-row">
                            <span class="combo-rank">#{{ $i + 1 }}</span>
                            <div style="flex:1; min-width:0">
                                <div class="text-white text-truncate font-bangers" style="font-size:1.3rem; letter-spacing: 1px;">{{ $combo->combo_name }}</div>
                                <div class="fw-bold" style="font-size:0.75rem; color:#aaa">{{ $combo->partidas }} partidas jugadas</div>
                            </div>
                            <span class="combo-wr">{{ $combo->win_rate }}% WR</span>
                        </div>
                    @empty
                        <p class="text-secondary text-center py-3 font-bangers" style="font-size: 1.5rem;">-- SIN DATOS DE COMBATE --</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="command-panel p-0 h-100">
                <div class="panel-header">
                    <span><i class="fas fa-crosshairs me-2" style="color: var(--shonen-red);"></i> Rendimiento Táctico</span>
                </div>
                <div class="p-4">
                    @forelse($winRatePorTipo as $tipo => $data)
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-end mb-1">
                                <span class="font-bangers" style="color:#fff; font-size: 1.2rem; letter-spacing: 1px;">{{ $tipo }}</span>
                                <span style="color:var(--shonen-yellow); font-weight: 900; font-size: 0.8rem;">
                                    {{ $data['torneos'] }} {{ $data['torneos'] == 1 ? 'Batalla' : 'Batallas' }}
                                    <span class="mx-1 text-white">|</span>
                                    {{ $data['tasa'] }}% WR
                                </span>
                            </div>
                            <div class="wr-bar-bg" title="Win Rate en {{ $tipo }}: {{ $data['tasa'] }}%">
                                <div class="wr-bar-fill {{ $data['tasa'] >= 70 ? 'amber' : '' }}" style="width:{{ $data['tasa'] }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="fas fa-ghost d-block mb-3" style="font-size: 3rem; color: #333;"></i>
                            <p class="text-secondary font-bangers" style="font-size:1.5rem;">-- SIN ASISTENCIAS REGISTRADAS --</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Logros --}}
    @if($trofeos->isNotEmpty())
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="command-panel p-0">
                <div class="panel-header">
                    <span><i class="fas fa-medal me-2" style="color: var(--shonen-yellow);"></i> Galería de Trofeos</span>
                    <span class="badge bg-white text-dark border border-dark" style="font-family: 'Bangers'; font-size: 1rem;">{{ $trofeos->count() }} OBTENIDOS</span>
                </div>
                <div class="p-4">
                    <div class="badge-grid">
                        @foreach($trofeos as $trofeo)
                            <div class="badge-item {{ ($trofeo->pivot->count ?? 0) == 0 ? 'locked' : '' }}">
                                <div class="badge-icon">
                                    @if($trofeo->image) <img src="{{ asset('storage/' . $trofeo->image) }}" style="width:30px;height:30px;object-fit:contain">
                                    @else 🏆 @endif
                                </div>
                                <div class="badge-name">{{ $trofeo->name }}</div>
                                @if(($trofeo->pivot->count ?? 0) > 1)
                                    <div class="font-bangers mt-1" style="font-size:1.2rem; color:var(--shonen-yellow);">x{{ $trofeo->pivot->count }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Historial --}}
    <div class="row g-4">
        <div class="col-12">
            <div class="command-panel p-0">
                <div class="panel-header"><span><i class="fas fa-calendar-alt me-2" style="color: #fff;"></i> Historial de Combate</span></div>
                <div class="p-4">
                    @forelse($historialEventos as $evento)
                        @php
                            $p = mb_strtolower($evento->pivot->puesto ?? '');
                            $pillClass = 'result-part'; $pillText = 'Participó';
                            if ($p == '1' || $p == 'primero') { $pillClass = 'result-1st'; $pillText = '1.º'; }
                            elseif ($p == '2' || $p == 'segundo') { $pillClass = 'result-2nd'; $pillText = '2.º'; }
                            elseif ($p == '3' || $p == 'tercero') { $pillClass = 'result-3rd'; $pillText = '3.º'; }
                            elseif ($p == '4' || $p == 'cuarto') { $pillClass = 'result-top'; $pillText = '4.º'; }
                        @endphp
                        <div class="event-row">
                            <div class="event-date">{{ \Carbon\Carbon::parse($evento->date)->format('d M y') }}</div>
                            <div style="flex:1; min-width:0">
                                <div class="event-name text-truncate text-white">{{ $evento->name }}</div>
                                <div class="event-meta">{{ $evento->region->name ?? '—' }}</div>
                            </div>
                            <span class="result-pill {{ $pillClass }}"><span>{{ $pillText }}</span></span>
                            <a href="{{ route('events.show', $evento->id) }}" class="btn btn-light rounded-0 border-dark fw-bold px-3 py-1 text-uppercase" style="font-size:0.7rem; border-width: 2px;">Ver</a>
                        </div>
                    @empty
                        <p class="text-secondary text-center py-4 font-bangers" style="font-size: 1.5rem;">-- SIN HISTORIAL REGISTRADO --</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Gráfica
    const ptsData = @json($graficaPuntos);
    const ptsLabels = @json($graficaLabels);
    const chart = document.getElementById('pts-chart');
    const labels = document.getElementById('pts-labels');

    if (chart && ptsData.length) {
        const max = Math.max(...ptsData, 1);
        ptsData.forEach((v, i) => {
            const bar = document.createElement('div');
            bar.className = 'pts-bar' + (i === ptsData.length - 1 ? ' highlight' : '');
            bar.style.height = Math.max(4, Math.round((v / max) * 96)) + 'px';
            bar.title = (ptsLabels[i] ?? '') + ': ' + v + ' pts';
            chart.appendChild(bar);
            const lbl = document.createElement('span');
            lbl.textContent = ptsLabels[i] ?? '';
            labels.appendChild(lbl);
        });
    }

    function switchSeason(temporada, btn) {
        document.querySelectorAll('.season-data').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.season-tab').forEach(el => el.classList.remove('active'));
        const slug = temporada.toString().toLowerCase().replace(/\s+/g, '-').replace(/[^\w\-]+/g, '');
        const target = document.getElementById('season-' + slug);
        if (target) target.style.display = '';
        btn.classList.add('active');
    }

    function copyProfile() {
        const url = '{{ url('/blader/' . $canonicalSlug) }}';
        navigator.clipboard.writeText(url).then(() => {
            const btn = event.currentTarget;
            const span = btn.querySelector('span');
            const orig = span.innerHTML;
            span.innerHTML = '<i class="fas fa-check me-1"></i> Copiado!';
            btn.style.background = 'var(--shonen-yellow)';
            btn.style.color = '#000';
            setTimeout(() => {
                span.innerHTML = orig;
                btn.style.background = '';
                btn.style.color = '';
            }, 2000);
        });
    }
</script>
@endsection
