@extends('layouts.app')

@section('title', 'SBBL - Inicio')

@section('head')
    <meta name="description" content="Organización de BeyBattle España"/>
    <meta name="keywords" content="sbbl, beyblade, españa, torneo, liga, discord, app, web, evento, ranking, español, hasbro, takara, tomy, burst, x, beyblade x, beyblade españa"/>
    <meta name="author" content="José A. Calvillo Olmedo" />
    <meta name="copyright" content="SBBL - José A. Calvillo Olmedo" />
@endsection

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" integrity="sha512-tS3S5qG0BlhnQROyJXvNjeEM4UpMXHrQfTGmbQ1gKmelCxlSEBUaxhRBj/EFTzpbP4RVSrpEikbmdJobCvhE3g==" crossorigin="anonymous" />
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-mini-map/dist/Control.MiniMap.css" />

<style>
    /* --- ESTILO GENERAL DEL DASHBOARD --- */
    :root {
        --neon-blue: #0dcaf0;
        --neon-gold: #ffc107;
        --dark-bg: #121212;
        --panel-bg: rgba(30, 30, 47, 0.95);
    }

    .main-wrapper {
        background-image: url('../images/webTile2.png');
        background-size: 300px;
        background-repeat: repeat;
        background-position: center;
        padding: 0px;
        min-height: 100vh;
        position: relative;
    }

    /* Overlay para oscurecer el fondo y que se lea mejor */
    .main-wrapper::before {
        content: '';
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(10, 10, 15, 0.85);
        z-index: 0;
    }

    .content-layer {
        position: relative;
        z-index: 1;
    }

    /* --- TARJETAS TIPO PUESTO DE MANDO --- */
    .command-card {
        background: var(--panel-bg);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
        overflow: hidden;
        margin-bottom: 2rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .command-header {
        background: rgba(255, 255, 255, 0.05);
        padding: 10px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        font-family: monospace;
        text-transform: uppercase;
        color: #adb5bd;
        font-weight: bold;
        letter-spacing: 1px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* --- BARRA DE PROGRESO DE TEMPORADA --- */
    .season-tracker {
        background: #000;
        border-radius: 50px;
        padding: 5px;
        border: 1px solid #333;
        box-shadow: inset 0 0 10px #000;
    }
    .combined-bar {
        display: flex;
        height: 12px;
        width: 100%;
        border-radius: 10px;
        overflow: hidden;
        background: #1a1a1a;
    }
    .segment { height: 100%; transition: width 1.5s ease-in-out; }
    .preseason { background: linear-gradient(to right, #00c3ff, #007aff); box-shadow: 0 0 10px #007aff; }
    .season { background: linear-gradient(to right, #ffc107, #ff5722); box-shadow: 0 0 10px #ff5722; }

    .timeline-dates {
        display: flex; justify-content: space-between;
        font-size: 0.7rem; color: #666; font-family: monospace; margin-top: 5px;
    }

    /* --- SOCIAL LINKS --- */
    .social-hub a {
        color: #aaa;
        font-size: 1.5rem;
        margin: 0 10px;
        transition: all 0.3s;
    }
    .social-hub a:hover { color: var(--neon-blue); transform: scale(1.2); text-shadow: 0 0 10px var(--neon-blue); }
    .social-hub svg { width: 1em; height: 1em; fill: currentColor; }

    /* --- ACCESOS RÁPIDOS (Botones grandes) --- */
    .quick-action-btn {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: white;
        padding: 15px;
        border-radius: 8px;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s;
        display: block;
        height: 100%;
    }
    .quick-action-btn:hover {
        background: rgba(13, 202, 240, 0.1);
        border-color: var(--neon-blue);
        color: white;
        transform: translateY(-3px);
    }

    /* --- RANKINGS --- */
    .ranking-podium {
        display: flex;
        align-items: flex-end;
        justify-content: center;
        gap: 15px;
        margin-bottom: 30px;
    }
    .podium-place { text-align: center; position: relative; }
    .podium-1 { order: 2; transform: scale(1.1); z-index: 10; }
    .podium-2 { order: 1; }
    .podium-3 { order: 3; }

    .avatar-frame-container {
        position: relative; width: 80px; height: 80px; margin: 0 auto 10px;
    }
    .podium-1 .avatar-frame-container { width: 100px; height: 100px; }

    .rank-number {
        position: absolute; bottom: -10px; left: 50%; transform: translateX(-50%);
        background: #000; color: white; padding: 2px 8px; border-radius: 4px;
        font-weight: bold; font-size: 0.8rem; border: 1px solid #444;
    }
    .podium-1 .rank-number { border-color: gold; color: gold; }

    /* Teams List */
    .team-row {
        display: flex; align-items: center; justify-content: space-between;
        background: rgba(0,0,0,0.3); padding: 10px; margin-bottom: 8px;
        border-radius: 6px; border-left: 3px solid transparent;
        transition: 0.2s;
    }
    .team-row:hover { background: rgba(255,255,255,0.05); border-left-color: var(--neon-blue); }
    .team-rank-badge { font-family: monospace; font-weight: bold; width: 30px; color: #888; }

    /* --- BLADER DEL MES (MVP CARD) --- */
    .mvp-card {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        border: 1px solid var(--neon-gold);
        box-shadow: 0 0 20px rgba(255, 193, 7, 0.15);
    }
    .mvp-stats span {
        background: rgba(0,0,0,0.4); padding: 5px 10px; border-radius: 4px;
        font-size: 0.85rem; border: 1px solid rgba(255,255,255,0.1);
    }

    /* --- ARTÍCULOS --- */
    .news-card img { height: 160px; object-fit: cover; width: 100%; border-radius: 8px 8px 0 0; }
    .news-card { background: #1a1a1a; border: none; height: 100%; border-radius: 8px; overflow: hidden; transition: 0.3s; }
    .news-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.5); }

    /* --- MISC --- */
    .section-title {
        color: white; text-transform: uppercase; font-weight: 800; letter-spacing: 1px;
        margin-bottom: 25px; border-left: 4px solid var(--neon-blue); padding-left: 15px;
    }

    .event-hover-effect:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(13, 202, 240, 0.2) !important;
        border-color: var(--neon-blue) !important;
    }
/* --- TARJETA DE CONQUISTA --- */
.conquest-card {
    display: block;
    position: relative;
    /* Fondo rojo oscuro degradado */
    background: linear-gradient(90deg, #1a0505 0%, #380a0a 100%);
    border: 1px solid #5c0e0e;
    border-radius: 12px;
    overflow: hidden;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
}

/* Efecto Hover */
.conquest-card:hover {
    border-color: #ff3333;
    box-shadow: 0 0 25px rgba(220, 20, 60, 0.4);
    transform: translateY(-3px);
}

/* Patrón de fondo tipo mapa táctico */
.conquest-grid-bg {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    /* Pequeños puntos para simular grid */
    background-image: radial-gradient(#5c0e0e 1px, transparent 1px);
    background-size: 20px 20px;
    opacity: 0.5;
    z-index: 0;
}

.conquest-content {
    position: relative;
    z-index: 1;
}

/* Icono grande */
.conquest-icon-box {
    width: 70px; height: 70px;
    background: rgba(0, 0, 0, 0.4);
    border: 1px solid #ff3333;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    color: #ff3333;
    box-shadow: 0 0 10px rgba(255, 51, 51, 0.2);
}

/* Animación de "En vivo" */
.live-dot {
    width: 8px; height: 8px; background: #ff3333; border-radius: 50%;
    display: inline-block; margin-right: 6px;
    box-shadow: 0 0 0 0 rgba(255, 51, 51, 0.7);
    animation: pulse-red 2s infinite;
}

@keyframes pulse-red {
    0% { box-shadow: 0 0 0 0 rgba(255, 51, 51, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(255, 51, 51, 0); }
    100% { box-shadow: 0 0 0 0 rgba(255, 51, 51, 0); }
}

.feature-tag {
    font-size: 0.75rem;
    background: rgba(255,255,255,0.05);
    padding: 3px 8px;
    border-radius: 4px;
    color: #bbb;
    margin-right: 5px;
    font-family: monospace;
}
</style>
@include('database.partials.mainmenu-styles')
@endsection

@section('content')
<div class="main-wrapper">
    <div class="container-fluid content-layer pb-5">

        {{-- 1. ALERTAS DEL SISTEMA --}}
        @if ((Auth::user() && !Auth::user()->profile->region))
            <div class="alert alert-danger bg-dark border border-danger text-danger text-center font-monospace rounded-0 mb-0">
                <i class="fas fa-exclamation-triangle"></i> ATENCIÓN: REGIÓN NO ASIGNADA. <a href="{{ route('profiles.edit', ['profile' => Auth::user()->id]) }}" class="text-white fw-bold text-decoration-underline">CONFIGURAR AHORA</a>
            </div>
        @endif

        {{-- 2. HERO DASHBOARD: Estado de Temporada + Video Destacado --}}
        <div class="container mt-4">
            <div class="row g-4">

                {{-- Panel Izquierdo: Status --}}
                <div class="col-lg-7">
                    <div class="command-card h-100 d-flex flex-column justify-content-center p-4">
                        <div class="d-flex align-items-center mb-3">
                            <h2 class="h4 text-white fw-bold mb-0 text-uppercase">
                                <span class="text-info">SBBL</span> <br>ESTATUS DE TEMPORADA
                            </h2>
                            <span class="badge bg-success bg-opacity-25 text-success border border-success ms-3 animate-pulse">ONLINE</span>
                        </div>

                        <p id="season-status" class="text-light mb-3 font-monospace"></p>

                        <div class="season-tracker mb-2">
                            <div class="combined-bar">
                                <div id="pre-fill" class="segment preseason"></div>
                                <div id="season-fill" class="segment season"></div>
                            </div>
                        </div>
                        <div class="timeline-dates">
                            <span>22 JUN 2025</span>
                            <span class="text-center">TEMP. 2</span>
                            <span>30 JUN 2026</span>
                        </div>

                        <hr class="border-secondary my-4">

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-white text-uppercase small" style="letter-spacing: 1px;">Canales de Comunicación</span>
                            <div class="social-hub">
                                <a href="https://discord.gg/JCtAHfJ8Ht" target="_blank" title="Discord"><i class="fab fa-discord"></i></a>
                                <a href="https://www.youtube.com/@sbbl_oficial" target="_blank" title="YouTube"><i class="fab fa-youtube"></i></a>
                                <a href="https://www.twitch.tv/sbbl_oficial" target="_blank" title="Twitch"><i class="fab fa-twitch"></i></a>
                                <a href="https://www.instagram.com/sbbl_oficial/" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
                                <a href="https://x.com/SBBLOficial" target="_blank" title="Twitter"><i class="fab fa-twitter"></i></a>
                                <!--<a href="https://bsky.app/profile/sbbloficial.bsky.social" target="_blank" title="Bluesky">
                                     <svg viewBox="0 0 600 600"><path d="m135.72 44.03c66.496 49.921 138.02 151.14 164.28 205.46 26.262-54.316 97.782-155.54 164.28-205.46 47.98-36.021 125.72-63.892 125.72 24.795 0 17.712-10.155 148.79-16.111 170.07-20.703 73.984-96.144 92.854-163.25 81.433 117.3 19.964 147.14 86.092 82.697 152.22-122.39 125.59-175.91-31.511-189.63-71.766-2.514-7.3797-3.6904-10.832-3.7077-7.8964-0.0174-2.9357-1.1937 0.51669-3.7077 7.8964-13.714 40.255-67.233 197.36-189.63 71.766-64.444-66.128-34.605-132.26 82.697-152.22-67.108 11.421-142.55-7.4491-163.25-81.433-5.9562-21.282-16.111-152.36-16.111-170.07 0-88.687 77.742-60.816 125.72-24.795z"/></svg>
                                </a>-->
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Panel Derecho: Video Destacado --}}
                <div class="col-lg-5">
                    <div class="command-card p-0 border-0 h-100">
                        <div class="ratio ratio-16x9 h-100">
                            <iframe src="https://www.youtube.com/embed/wkFnz8kPs3M?si=Dot6IDj6hHy7WqLc" title="SBBL Video" allowfullscreen style="border-radius: 12px;"></iframe>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BOTÓN DE DESPLIEGUE: MODO CONQUISTA --}}
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <a target="_blank" href="{{ route('conquest.index') }}" class="conquest-card p-4 d-flex flex-column flex-md-row align-items-center justify-content-between">

                {{-- Fondo Táctico --}}
                <div class="conquest-grid-bg"></div>

                {{-- Izquierda: Info Principal --}}
                <div class="conquest-content d-flex align-items-center mb-3 mb-md-0">
                    {{-- Icono Grande --}}
                    <div class="conquest-icon-box me-4 d-none d-sm-flex flex-shrink-0">
                        <i class="fas fa-map-marked-alt fa-2x"></i>
                    </div>

                    {{-- Textos Explicativos --}}
                    <div>
                        <div class="d-flex align-items-center mb-1">
                            <h3 class="fw-bold text-white text-uppercase m-0 me-3" style="letter-spacing: 1px;">MODO CONQUISTA</h3>
                            <span class="badge bg-danger bg-opacity-75 border border-danger text-white">
                                <span class="live-dot"></span> GUERRA ACTIVA
                            </span>
                        </div>

                        <p class="text-white-50 mb-2" style="max-width: 550px; line-height: 1.4;">
                            El mapa de España está en disputa. Únete a una facción, compite en torneos para ganar influencia y <strong>captura territorios reales</strong> para tu equipo.
                        </p>

                        {{-- Tags de características para que entiendan la mecánica --}}
                        <div class="d-flex flex-wrap gap-2">
                            <span class="feature-tag"><i class="fas fa-users me-1"></i> Facciones</span>
                            <span class="feature-tag"><i class="fas fa-chess-board me-1"></i> Estrategia</span>
                            <span class="feature-tag"><i class="fas fa-flag me-1"></i> Control de Territorio</span>
                        </div>
                    </div>
                </div>

                {{-- Derecha: Botón de Acción --}}
                <div class="conquest-content text-end">
                    <span class="btn btn-outline-danger fw-bold text-uppercase px-4 py-2" style="border-width: 2px;">
                        Ver Mapa Táctico <i class="fas fa-chevron-right ms-2"></i>
                    </span>
                </div>

            </a>
        </div>
    </div>
</div>

            {{-- 3. ACCESOS RÁPIDOS (Sustituye al menú flotante) --}}
            <div class="row mt-3 g-3">
                <div class="col-6 col-md-3">
                    <a href="{{ route('inicio.halloffame') }}" class="quick-action-btn">
                        <i class="fas fa-trophy fa-lg mb-2 text-warning"></i><br>
                        <span class="fw-bold text-uppercase">Salón de la Fama</span>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ route('inicio.resumen_semanal') }}" class="quick-action-btn">
                        <i class="fas fa-bullseye fa-lg mb-2 text-danger"></i><br>
                        <span class="fw-bold text-uppercase">Resumen Semanal</span>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ route('events.index') }}" class="quick-action-btn">
                        <i class="fas fa-calendar-alt fa-lg mb-2 text-info"></i><br>
                        <span class="fw-bold text-uppercase">Calendario</span>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ route('profiles.ranking') }}" class="quick-action-btn">
                        <i class="fas fa-chart-line fa-lg mb-2 text-success"></i><br>
                        <span class="fw-bold text-uppercase">Rankings</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- 4. PRÓXIMOS EVENTOS --}}
        <div class="container mt-5">
            <h3 class="section-title">Próximos Eventos</h3>

            <div class="row g-4" id="event-cards-grid">
                @php $totalEventos = $nuevos->count(); @endphp
                @forelse ($nuevos as $evento)
                    @php $isHidden = $loop->iteration > 3; @endphp
                    <div class="col-md-4 more-event-item @if($isHidden) d-none @endif" data-index="{{ $loop->iteration }}">
                        <div class="card h-100 bg-dark text-white border-secondary event-hover-effect" style="border-radius: 12px; overflow: hidden; transition: 0.3s;">
                            <div class="ratio ratio-16x9">
                                @if ($evento->image_mod)
                                    <img src="data:image/png;base64,{{ $evento->image_mod }}" class="object-fit-cover">
                                @else
                                    <img src="/storage/{{ $evento->imagen }}" class="object-fit-cover">
                                @endif
                                <div class="position-absolute top-0 end-0 m-2">
                                    <span class="badge bg-black bg-opacity-75 border border-white">{{ $evento->region->name }}</span>
                                </div>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="fw-bold text-truncate">{{ $evento->name }}</h5>
                                <div class="text-info small mb-3">
                                    <i class="far fa-clock me-1"></i> <event-date fecha="{{ $evento->date }}"></event-date>
                                </div>
                                <a href="{{ route('events.show', ['event' => $evento->id]) }}" class="btn btn-outline-light btn-sm w-100 mt-auto text-uppercase fw-bold">
                                    Ver Detalles
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5 border border-secondary border-dashed rounded text-white">
                        <h4><i class="fas fa-radar"></i> Sin actividad detectada</h4>
                        <p>No hay eventos próximos programados.</p>
                    </div>
                @endforelse
            </div>

            @if ($totalEventos > 3)
                <div class="text-center mt-4">
                    <button id="show-more-events" class="btn btn-dark border-secondary w-50 text-uppercase text-white" onclick="toggleMoreEvents()" data-showing="false">
                        ⬇ Mostrar más ({{ $totalEventos - 3 }})
                    </button>
                </div>
            @endif
        </div>

        {{-- 5. RANKINGS & BLADER DEL MES --}}
        <div class="container mt-5">
            <div class="row g-5">

                {{-- TOP INDIVIDUAL (PODIO) --}}
                <div class="col-lg-6">
                    <div class="command-card p-4 h-100">
                        <div class="command-header mb-4">
                            <span><i class="fas fa-crown text-warning me-2"></i>Top Bladers</span>
                            <a href="{{ route('profiles.ranking') }}" class="text-decoration-none small text-white">VER TODO ></a>
                        </div>

                        <div class="ranking-podium">
                            @foreach ($bladers->take(3) as $index => $blader)
                                @php
                                    $pos = $index + 1;
                                    $gifStyle = strpos($blader->avatar_url, '.gif') !== false ? 'padding: 5px;' : '';
                                @endphp
                                <div class="podium-place podium-{{ $pos }}">
                                    <div class="avatar-frame-container">
                                        <img src="{{ $blader->avatar_url }}" class="rounded-circle w-100 h-100 object-fit-cover position-absolute top-0 start-0" style="{{ $gifStyle }}" loading="lazy">
                                        <img src="{{ $blader->marco_url }}" class="w-100 h-100 position-absolute top-0 start-0" loading="lazy">
                                        <div class="rank-number">#{{ $pos }}</div>
                                    </div>
                                    <div class="text-white fw-bold text-truncate" style="max-width: 100px;">{{ $blader->user->name }}</div>
                                    <div class="text-info small fw-bold">{{ $blader->points_x2 }} pts</div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Lista del 4º y 5º --}}
                        <div class="mt-3">
                            @foreach ($bladers->slice(3, 2) as $index => $blader)
                                <div class="d-flex align-items-center justify-content-between p-2 border-bottom border-secondary">
                                    <div class="d-flex align-items-center">
                                        <span class="text-white fw-bold me-3">#{{ $index + 4 }}</span>
                                        <img src="{{ $blader->avatar_url }}" class="rounded-circle me-2" width="30" height="30">
                                        <span class="text-white">{{ $blader->user->name }}</span>
                                    </div>
                                    <span class="text-info small">{{ $blader->points_x2 }} pts</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- TOP EQUIPOS (LISTA TÁCTICA) --}}
                <div class="col-lg-6">
                    <div class="command-card p-4 h-100">
                        <div class="command-header mb-4">
                            <span><i class="fas fa-users text-info me-2"></i>Clasificación Equipos</span>
                        </div>

                        <div class="team-list">
                            @foreach($teams->take(5) as $key => $team)
                            <div class="team-row">
                                <div class="d-flex align-items-center">
                                    <div class="team-rank-badge">#{{ $key + 1 }}</div>
                                    <div class="me-3" style="width: 40px; height: 40px;">
                                        <img class="lazy-logo w-100 h-100 object-fit-contain"
                                             src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=="
                                             data-src="@if($team->logo) data:image/png;base64,{{ $team->logo }} @else /images/logo_new.png @endif">
                                    </div>
                                    <span class="fw-bold text-white">{{ $team->name }}</span>
                                </div>
                                <span class="badge bg-dark border border-secondary text-warning">{{ $team->points_x2 }} pts</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- 6. BLADER DEL MES (MVP) --}}
        <div class="container mt-5">
            <div class="mvp-card rounded-4 p-4 p-md-5 text-white position-relative overflow-hidden">
                {{-- Efecto de fondo --}}
                <div style="position: absolute; top: 0; right: 0; width: 50%; height: 100%; background: radial-gradient(circle, rgba(255,193,7,0.1) 0%, rgba(0,0,0,0) 70%); z-index: 0;"></div>

                <div class="row align-items-center position-relative" style="z-index: 1;">
                    <div class="col-md-8">
                        <div class="d-inline-block bg-warning text-dark fw-bold px-2 py-1 rounded mb-2 text-uppercase" style="font-size: 0.8rem;">
                            🌟 Rendimiento Destacado
                        </div>
                        <h2 class="display-6 fw-bold text-uppercase mb-1">Blader del Mes</h2>
                        <h4 class="text-white mb-4 text-uppercase">{{ $lastMonthName ?? '' }} {{ $lastYear ?? '' }}</h4>

                        <div class="d-flex align-items-center mb-4">
                            <h3 class="text-white mb-0 me-3">{{ $bestUserProfile->name ?? 'N/A' }}</h3>
                            <span class="badge bg-secondary">{{ $bestUserProfile->profile->region->name ?? '' }}</span>
                        </div>

                        <div class="mvp-stats d-flex flex-wrap gap-2 text-info font-monospace">
                            <span><i class="fas fa-star text-warning"></i> {{ $bestUser->total_puntos ?? '0' }} Pts</span>
                            <span><i class="fas fa-trophy"></i> {{ $bestUserRecord->victorias ?? '0' }} Victorias</span>
                            <span><i class="fas fa-cog"></i> {{ $bestUserRecord->blade ?? '?' }}/{{ $bestUserRecord->ratchet ?? '?' }}/{{ $bestUserRecord->bit ?? '?' }}</span>
                        </div>
                    </div>

                    <div class="col-md-4 text-center mt-4 mt-md-0">
                         @php
                            $profileMVP = $bestUserProfile->profile;
                            $gifStyle = strpos($profileMVP->avatar_url, '.gif') !== false ? 'padding: 15px;' : '';
                        @endphp
                        <div class="position-relative d-inline-block" style="width: 200px; height: 200px;">
                            <img src="{{ $profileMVP->avatar_url }}" class="rounded-circle w-100 h-100 position-absolute top-0 start-0" style="{{ $gifStyle }}" loading="lazy">
                            <img src="{{ $profileMVP->marco_url }}" class="w-100 h-100 position-absolute top-0 start-0" loading="lazy">
                            {{-- Brillo detrás --}}
                            <div style="position: absolute; inset: -20px; background: var(--neon-gold); filter: blur(40px); opacity: 0.3; z-index: -1; border-radius: 50%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 7. NOTICIAS --}}
        <div class="container mt-5">
            <h3 class="section-title">Artículos Recientes</h3>
            <div class="row g-4">
                @forelse ($articles as $article)
                    <div class="col-md-6 col-lg-4">
                        <div class="card news-card text-white">
                            @if ($article->image)
                                <img src="data:image/png;base64,{{ $article->image }}" alt="Cover">
                            @else
                                <div class="bg-secondary d-flex align-items-center justify-content-center" style="height: 160px;">
                                    <i class="fas fa-newspaper fa-3x text-dark"></i>
                                </div>
                            @endif
                            <div class="card-body d-flex flex-column p-4">
                                <h5 class="fw-bold mb-3">{{ $article->title }}</h5>
                                <a href="{{ route('blog.show', $article->custom_url) }}" class="btn btn-outline-info btn-sm mt-auto text-uppercase">Leer Informe</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-white text-center">No hay artículos recientes.</p>
                @endforelse
            </div>
        </div>

        {{-- 8. QUIÉNES SOMOS & STAFF --}}
        <div class="container mt-5 mb-5">
            <div class="command-card p-5 text-center">
                <h3 class="text-white text-uppercase fw-bold mb-4" style="text-shadow: 0 0 10px rgba(255,255,255,0.3);">La Iniciativa SBBL</h3>

                <div class="row justify-content-center text-start text-secondary">
                    <div class="col-md-8">
                        <p class="mb-3">La <strong>SBBL (Spanish BeyBattle League)</strong> es una organización sin ánimo de lucro operada por la comunidad. Nuestro objetivo es estandarizar y profesionalizar el Beyblade competitivo en España.</p>
                        <p class="mb-4">Gestionamos rankings, organizamos torneos nacionales y desarrollamos tecnología para conectar a los Bladers de todas las regiones.</p>
                    </div>
                </div>

                <div class="d-flex justify-content-center gap-3 mt-3">
                    <a href="https://paypal.me/AsocSBBL" target="_blank" class="btn btn-warning fw-bold text-dark rounded-pill shadow-lg">
                        <i class="fab fa-paypal me-2"></i> Apoyar Proyecto
                    </a>
                </div>

                {{-- STAFF GRID --}}
                <div class="row justify-content-center mt-5 g-4">
                    @foreach ($usuarios as $usuario)
                        @php $profile = $usuario->profile; @endphp
                        <div class="col-6 col-md-3">
                            <div class="d-flex flex-column align-items-center">
                                <div class="position-relative mb-3" style="width: 80px; height: 80px;">
                                    <img src="{{ $profile->avatar_url }}" class="rounded-circle w-100 h-100" style="object-fit: cover;">
                                    <img src="{{ $profile->marco_url }}" class="position-absolute top-0 start-0 w-100 h-100">
                                </div>
                                <h6 class="text-white fw-bold mb-0 text-uppercase">{{ $usuario->name }}</h6>
                                <small class="text-white" style="font-size: 0.7rem;">{{ $usuario->titulo ?? 'STAFF' }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- 9. HISTORIAL (Owl Carousel) --}}
        <div class="container pb-5">
            <h3 class="section-title">Torneos pasados</h3>
            <div class="owl-carousel owl-theme">
                @foreach ($antiguos as $antiguo)
                    <div class="card bg-dark text-white border-0 rounded overflow-hidden">
                        <div style="height: 180px; background: url('{{ $antiguo->image_mod ? "data:image/png;base64,".$antiguo->image_mod : "/storage/".$antiguo->imagen }}') center/cover;"></div>
                        <div class="card-body p-3">
                            <h6 class="text-truncate">{{ $antiguo->name }}</h6>
                            <small class="text-white d-block">{{ $antiguo->location }}</small>
                            <small class="text-info"><event-date fecha="{{ $antiguo->date }}"></event-date></small>
                            <a href="{{ route('events.show', ['event' => $antiguo->id]) }}" class="stretched-link"></a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js" integrity="sha512-bPs7Ae6pVvhOSiIcyUClR7/q2OAsRiovw4vAkX+zJbw3ShAeeqezq50RIIcIURq7Oa20rW2n2q+fyXBNcU9lrw==" crossorigin="anonymous"></script>

<script>
    // Configuración Carrusel
    $(document).ready(function(){
        $(".owl-carousel").owlCarousel({
            loop:false, margin:15, nav:false, dots:true,
            responsive:{ 0:{items:1}, 600:{items:2}, 1000:{items:4} }
        });
    });

    // Toggle Eventos
    function toggleMoreEvents() {
        const button = document.getElementById('show-more-events');
        const hiddenEvents = document.querySelectorAll('.more-event-item.d-none');
        const isShowing = button.dataset.showing === 'true';
        const totalHiddenCount = parseInt({{ $totalEventos }} - 3);

        if (!isShowing) {
            document.querySelectorAll('.more-event-item').forEach(i => i.classList.remove('d-none'));
            button.innerHTML = '⬆ Ocultar';
            button.dataset.showing = 'true';
        } else {
            document.querySelectorAll('.more-event-item').forEach(item => {
                if (parseInt(item.dataset.index) > 3) item.classList.add('d-none');
            });
            button.innerHTML = `⬇ Mostrar más (${totalHiddenCount})`;
            button.dataset.showing = 'false';
        }
    }

    // Lazy Loading & Season Progress
    document.addEventListener("DOMContentLoaded", function () {
        // Lazy Logo
        const imgObserver = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    const realSrc = img.getAttribute("data-src");
                    if (realSrc) { img.src = realSrc; img.removeAttribute("data-src"); }
                    imgObserver.unobserve(img);
                }
            });
        });
        document.querySelectorAll("img.lazy-logo").forEach(img => imgObserver.observe(img));

        // Season Logic
        const today = new Date();
        const startPre = new Date("2025-06-22");
        const startSeason = new Date("2025-09-01");
        const endSeason = new Date("2026-06-30");

        const totalDuration = (endSeason - startPre) / (1000 * 60 * 60 * 24);
        const elapsedDays = (today - startPre) / (1000 * 60 * 60 * 24);

        let prePercent = 0, seasonPercent = 0, statusText = "";

        if (today < startPre) {
            statusText = "⏳ ESPERANDO INICIO DE SECUENCIA...";
        } else if (today >= startPre && today < startSeason) {
            prePercent = (elapsedDays / totalDuration) * 100;
            statusText = "🔧 PRE-TEMPORADA EN CURSO";
        } else if (today >= startSeason && today <= endSeason) {
            prePercent = ((startSeason - startPre) / (endSeason - startPre)) * 100; // Fijo pre
            seasonPercent = ((today - startSeason) / (endSeason - startSeason)) * 100; // Variable season
            // Ajuste visual simple para la barra combinada
             const totalProgress = (elapsedDays / totalDuration) * 100;
             seasonPercent = totalProgress - prePercent; // Ajuste relativo
            statusText = "🔥 TEMPORADA 2 ACTIVA";
        } else {
            prePercent = 20; seasonPercent = 80; // Full bars
            statusText = "✅ CICLO COMPLETADO";
        }

        document.getElementById("pre-fill").style.width = `${prePercent}%`;
        document.getElementById("season-fill").style.width = `${seasonPercent}%`;
        document.getElementById("season-status").innerHTML = statusText;
    });
</script>
@endsection
