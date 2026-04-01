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
    /* ====================================================================
       ESTILOS ESPECÍFICOS DE LA PÁGINA DE INICIO (El resto hereda del layout)
       ==================================================================== */

    .section-title {
        font-family: 'Bangers', cursive;
        color: var(--sbbl-gold);
        text-shadow: 2px 2px 0px #000, 4px 4px 0px var(--sbbl-blue-3);
        margin-bottom: 25px;
        text-transform: uppercase;
        font-size: 2.5rem;
        letter-spacing: 2px;
    }

    /* --- BARRA DE PROGRESO DE TEMPORADA --- */
    .season-tracker { background: #000; border: 2px solid #fff; border-radius: 0; transform: skewX(-15deg); padding: 2px; margin-bottom: 10px; }
    .combined-bar { border-radius: 0; display: flex; width: 100%; height: 12px; }
    .segment { height: 100%; transition: width 1.5s ease-in-out; }
    .preseason { background: var(--sbbl-blue-3); border-right: 2px solid #000; }
    .season { background: var(--sbbl-gold); }
    .timeline-dates { font-family: 'Bangers', cursive; font-size: 1.1rem; color: #fff; display: flex; justify-content: space-between; letter-spacing: 1px; }

    /* --- ACCESOS RÁPIDOS --- */
    .quick-action-btn {
        background: var(--sbbl-blue-2); border: 2px solid #000; color: #fff;
        padding: 15px; border-radius: 0; transform: skewX(-5deg);
        box-shadow: 4px 4px 0px #000; text-align: center; text-decoration: none; display: block; transition: all 0.2s;
    }
    .quick-action-btn > * { transform: skewX(5deg); display: block; }
    .quick-action-btn:hover { background: var(--sbbl-gold); color: #000; border-color: #000; box-shadow: 4px 4px 0px var(--shonen-red); transform: skewX(-5deg) translate(-2px, -2px); }
    .quick-action-btn:hover i { color: #000 !important; }

    /* --- MVP CARD --- */
    .mvp-card {
        background: var(--sbbl-blue-3); border: 4px solid #000; border-radius: 0;
        box-shadow: 8px 8px 0px var(--sbbl-gold);
        background-image: repeating-linear-gradient(45deg, rgba(0,0,0,0.1) 0px, rgba(0,0,0,0.1) 10px, transparent 10px, transparent 20px);
    }
    .mvp-card h2, .mvp-card h4 { color: #fff; font-family: 'Bangers', cursive; letter-spacing: 1px; text-shadow: 2px 2px 0px #000; }
    .mvp-stats span { background: #000; color: var(--sbbl-gold) !important; border: 2px solid #fff; font-family: 'Bangers', cursive; font-size: 1.1rem; padding: 5px 10px; border-radius: 0; }

    /* --- RANKINGS WIDGET --- */
    .ranking-podium { display: flex; align-items: flex-end; justify-content: center; gap: 15px; margin-bottom: 30px; }
    .podium-place { text-align: center; position: relative; }
    .podium-1 { order: 2; transform: scale(1.1); z-index: 10; }
    .podium-2 { order: 1; }
    .podium-3 { order: 3; }
    .avatar-frame-container { position: relative; width: 80px; height: 80px; margin: 0 auto 10px; border-radius: 50%; }
    .podium-1 .avatar-frame-container { width: 100px; height: 100px; }

    /* Avatares 100% redondos (CORREGIDO) */
    .avatar-frame-container img.rounded-circle {
        border-radius: 50% !important;
        clip-path: none !important;
        border: 2px solid var(--sbbl-gold);
        object-fit: cover;
    }

    .rank-number { position: absolute; bottom: -10px; left: 50%; transform: translateX(-50%); background: #000; color: #fff; padding: 2px 8px; font-weight: bold; font-size: 1rem; border: 2px solid #fff; font-family: 'Bangers', cursive; }
    .podium-1 .rank-number { border-color: var(--sbbl-gold); color: var(--sbbl-gold); }

    .team-row { display: flex; align-items: center; justify-content: space-between; background: rgba(0,0,0,0.5); padding: 10px; margin-bottom: 8px; border: 2px solid #000; transform: skewX(-5deg); transition: 0.2s; }
    .team-row > * { transform: skewX(5deg); }
    .team-row:hover { background: #000; border-color: var(--sbbl-gold); box-shadow: 4px 4px 0 var(--shonen-red); }
    .team-rank-badge { font-family: 'Bangers', cursive; font-size: 1.4rem; color: var(--sbbl-gold); width: 30px; text-shadow: 1px 1px 0 #000; }

    /* --- ARTÍCULOS Y EVENTOS --- */
    .news-card, .event-hover-effect { background: var(--sbbl-blue-2); border: 3px solid #000 !important; border-radius: 0 !important; box-shadow: 5px 5px 0px #000 !important; transition: 0.2s; }
    .news-card img, .event-hover-effect img { border-radius: 0 !important; border-bottom: 3px solid #000; }
    .news-card .btn, .event-hover-effect .btn { border-radius: 0; border: 2px solid #000; font-weight: 900; font-family: 'Bangers', cursive; font-size: 1.1rem; background: var(--sbbl-blue-3); color: #fff;}
    .news-card .btn:hover, .event-hover-effect .btn:hover { background: var(--sbbl-gold); color: #000; box-shadow: 3px 3px 0 var(--shonen-red); }
    .news-card:hover, .event-hover-effect:hover { transform: translate(-2px, -2px) !important; box-shadow: 7px 7px 0px var(--sbbl-gold) !important; border-color: var(--sbbl-gold) !important; }

    /* --- EXTRAS (Conquista, Banner Evento Nacional, Redes) --- */
    .conquest-card { border: 3px solid #000; border-radius: 0; box-shadow: 6px 6px 0px var(--shonen-red); background: var(--sbbl-blue-3); background-image: repeating-linear-gradient(45deg, rgba(0,0,0,0.1) 0px, rgba(0,0,0,0.1) 10px, transparent 10px, transparent 20px); text-decoration: none; display: block; position: relative; transition: all 0.2s; }
    .conquest-card:hover { transform: translate(-2px, -2px); box-shadow: 8px 8px 0px var(--sbbl-gold); border-color: var(--sbbl-gold); }
    .conquest-content { position: relative; z-index: 1; }
    .conquest-icon-box { width: 70px; height: 70px; border-radius: 0; border: 3px solid #000; background: var(--sbbl-gold); color: #000; display: flex; align-items: center; justify-content: center; box-shadow: 3px 3px 0 var(--shonen-red); }

    .evento-banner { background: #000; border: 3px solid var(--sbbl-gold); border-radius: 0; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; text-decoration: none; transition: 0.2s; margin-top: 15px; box-shadow: 4px 4px 0px #000; }
    .evento-banner:hover { transform: translate(-2px, -2px); box-shadow: 6px 6px 0px var(--shonen-red); border-color: var(--shonen-red); }
    .evento-banner-text { color: #fff; font-family: 'Bangers', cursive; font-size: 1.4rem; letter-spacing: 1px; margin: 0; }
    .evento-banner-btn { background: var(--sbbl-gold); color: #000; padding: 5px 15px; font-family: 'Bangers', cursive; font-size: 1.2rem; border: 2px solid #000; transition: 0.2s; box-shadow: 2px 2px 0 var(--shonen-red); }

    .social-hub a { color: #fff; font-size: 1.5rem; margin: 0 10px; transition: all 0.2s; }
    .social-hub a:hover { color: var(--sbbl-gold); transform: scale(1.2) rotate(5deg); }
    .live-dot { width: 10px; height: 10px; background: #fff; border: 2px solid #000; display: inline-block; margin-right: 6px; animation: pulse-red 1s infinite; }
</style>
@include('database.partials.mainmenu-styles')
@endsection

@section('content')
<div class="main-wrapper" style="background: transparent;"> {{-- El fondo general ya lo pone el layout --}}
    <div class="container-fluid content-layer pb-5 pt-3">

        {{-- 1. ALERTAS DEL SISTEMA --}}
        @if ((Auth::user() && !Auth::user()->profile->region))
            <div class="alert alert-shonen alert-shonen-danger text-center rounded-0 mb-4 mx-3">
                <div><i class="fas fa-exclamation-triangle"></i> ATENCIÓN: REGIÓN NO ASIGNADA. <a href="{{ route('profiles.edit', ['profile' => Auth::user()->id]) }}" class="text-white text-decoration-underline">CONFIGURAR AHORA</a></div>
            </div>
        @endif

        {{-- BANNER DE EVENTO NACIONAL --}}
        <div class="container">
            <a href="{{ route('inicio.nacional') }}" class="evento-banner">
                <div class="d-flex align-items-center">
                    <i class="fas fa-lock text-danger me-3 fs-3" style="animation: pulse-red 1s infinite;"></i>
                    <p class="evento-banner-text">
                        Descubre los detalles del <span style="color: var(--shonen-red);">Gran Nacional SBBL'26</span>
                    </p>
                </div>
                <span class="evento-banner-btn">Ver Detalles <i class="fas fa-chevron-right ms-1"></i></span>
            </a>
        </div>

        {{-- 2. HERO DASHBOARD --}}
        <div class="container mt-4">
            <div class="row g-4">

                {{-- Panel Izquierdo: Status --}}
                <div class="col-lg-7">
                    <div class="command-panel h-100 d-flex flex-column justify-content-center p-4">
                        <div class="d-flex align-items-center mb-3">
                            <h2 class="mb-0 text-white font-bangers" style="line-height: 1.1; font-size: 2rem;">
                                <span style="color: var(--sbbl-gold); text-shadow: 2px 2px 0 #000;">SBBL</span> <br>ESTATUS DE TEMPORADA
                            </h2>
                            <span class="badge bg-white text-dark border border-2 border-dark ms-3 font-bangers fs-5">ONLINE</span>
                        </div>

                        <p id="season-status" class="text-white mb-3 fw-bold fs-5 font-bangers" style="letter-spacing: 1px;"></p>

                        <div class="season-tracker mb-2">
                            <div class="combined-bar">
                                <div id="pre-fill" class="segment preseason"></div>
                                <div id="season-fill" class="segment season"></div>
                            </div>
                        </div>
                        <div class="timeline-dates">
                            <span>22 JUN 2025</span>
                            <span class="text-center" style="color: var(--sbbl-gold);">TEMP. 2</span>
                            <span>31 MAY 2026</span>
                        </div>

                        <hr class="border-dark my-4" style="border-width: 3px; opacity: 1;">

                        <div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-white font-bangers fs-5">Canales de Comunicación</span>
                                <div class="social-hub">
                                    <a href="https://discord.gg/JCtAHfJ8Ht" target="_blank" title="Discord"><i class="fab fa-discord"></i></a>
                                    <a href="https://www.youtube.com/@sbbl_oficial" target="_blank" title="YouTube"><i class="fab fa-youtube"></i></a>
                                    <a href="https://www.twitch.tv/sbbl_oficial" target="_blank" title="Twitch"><i class="fab fa-twitch"></i></a>
                                    <a href="https://www.instagram.com/sbbl_oficial/" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
                                    <a href="https://x.com/SBBLOficial" target="_blank" title="Twitter"><i class="fab fa-twitter"></i></a>
                                </div>
                            </div>
                            <div class="mt-2 text-end">
                                <small style="color: #aaa; font-size: 0.8rem; font-weight: bold;">
                                    * Nota: Ningún grupo en WhatsApp/Telegram es oficial.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Panel Derecho: Video Destacado --}}
                <div class="col-lg-5">
                    <div class="command-panel p-0 h-100" style="border: 3px solid #000;">
                        <div class="ratio ratio-16x9 h-100">
                            <iframe src="https://www.youtube.com/embed/wkFnz8kPs3M?si=Dot6IDj6hHy7WqLc" title="SBBL Video" allowfullscreen></iframe>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BOTÓN DE DESPLIEGUE: MODO CONQUISTA --}}
            <div class="container mt-4 px-0">
                <div class="row">
                    <div class="col-12">
                        <a target="_blank" href="{{ route('conquest.index') }}" class="conquest-card p-4 d-flex flex-column flex-md-row align-items-center justify-content-between">
                            <div class="conquest-content d-flex align-items-center mb-3 mb-md-0">
                                <div class="conquest-icon-box me-4 d-none d-sm-flex flex-shrink-0">
                                    <i class="fas fa-map-marked-alt fa-2x"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center mb-1">
                                        <h3 class="text-white m-0 me-3 font-bangers fs-2" style="text-shadow: 2px 2px 0 #000;">MODO CONQUISTA</h3>
                                        <span class="badge bg-dark border border-2 border-white text-white font-bangers fs-6">
                                            <span class="live-dot"></span> GUERRA ACTIVA
                                        </span>
                                    </div>
                                    <p class="text-white mb-2 fw-bold" style="max-width: 550px; line-height: 1.4; text-shadow: 1px 1px 0 #000;">
                                        El mapa de España está en disputa. Únete a una facción, compite en torneos para ganar influencia y capturar territorios reales.
                                    </p>
                                </div>
                            </div>
                            <div class="conquest-content text-end">
                                <span class="btn px-4 py-2" style="color: var(--sbbl-gold)">
                                    Ver Mapa Táctico <i class="fas fa-chevron-right ms-2"></i>
                                </span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            {{-- 3. ACCESOS RÁPIDOS --}}
            <div class="row mt-3 g-3">
                <div class="col-6 col-md-3">
                    <a href="{{ route('inicio.halloffame') }}" class="quick-action-btn">
                        <i class="fas fa-trophy fa-2x mb-2" style="color: var(--sbbl-gold);"></i><br>
                        <span class="fw-bold" style="font-size: 1.1rem;">SALÓN DE LA FAMA</span>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ route('inicio.resumen_semanal') }}" class="quick-action-btn">
                        <i class="fas fa-bullseye fa-2x mb-2" style="color: var(--shonen-red);"></i><br>
                        <span class="fw-bold" style="font-size: 1.1rem;">RESUMEN SEMANAL</span>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ route('events.index') }}" class="quick-action-btn">
                        <i class="fas fa-calendar-alt fa-2x mb-2" style="color: #fff;"></i><br>
                        <span class="fw-bold" style="font-size: 1.1rem;">CALENDARIO</span>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ route('profiles.ranking') }}" class="quick-action-btn">
                        <i class="fas fa-chart-line fa-2x mb-2" style="color: #00ffcc;"></i><br>
                        <span class="fw-bold" style="font-size: 1.1rem;">RANKINGS</span>
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
                        <div class="card h-100 event-hover-effect">
                            <div class="ratio ratio-16x9">
                                @if ($evento->image_mod)
                                    <img src="data:image/png;base64,{{ $evento->image_mod }}" class="object-fit-cover">
                                @else
                                    <img src="/storage/{{ $evento->imagen }}" class="object-fit-cover">
                                @endif
                                <div class="position-absolute top-0 end-0 m-2">
                                    <span class="badge bg-black border border-white font-bangers fs-6">{{ $evento->region->name }}</span>
                                </div>
                            </div>
                            <div class="card-body d-flex flex-column p-3">
                                <h4 class="font-bangers text-truncate text-white">{{ $evento->name }}</h4>
                                <div class="text-white mb-3 fw-bold">
                                    <i class="far fa-clock me-1 text-warning"></i> <event-date fecha="{{ $evento->date }}"></event-date>
                                </div>
                                <a href="{{ route('events.show', ['event' => $evento->id]) }}" class="btn w-100 mt-auto text-uppercase">
                                    Ver Detalles
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5 border border-3 border-dark bg-black text-white" style="border-radius: 0; box-shadow: 6px 6px 0 var(--sbbl-blue-3);">
                        <h4 class="font-bangers fs-1"><i class="fas fa-radar text-danger"></i> Sin actividad detectada</h4>
                        <p class="fw-bold">No hay eventos próximos programados.</p>
                    </div>
                @endforelse
            </div>

            @if ($totalEventos > 3)
                <div class="text-center mt-4">
                    <button id="show-more-events" class="btn w-50" style="border-radius: 0; border: 3px solid #000; font-family: 'Bangers', cursive; font-size: 1.3rem; background: var(--sbbl-gold); color: #000; box-shadow: 4px 4px 0 #000; transform: skewX(-5deg);" onclick="toggleMoreEvents()" data-showing="false">
                        <span style="transform: skewX(5deg); display: block;">⬇ Mostrar más ({{ $totalEventos - 3 }})</span>
                    </button>
                </div>
            @endif
        </div>

        {{-- 5. RANKINGS & EQUIPOS --}}
        <div class="container mt-5">
            <div class="row g-5">

                <div class="col-lg-6">
                    <div class="command-panel p-4 h-100">
                        <div class="panel-header mb-4 bg-transparent border-0 px-0">
                            <span><i class="fas fa-crown text-warning me-2"></i>Top Bladers</span>
                            <a href="{{ route('profiles.ranking') }}" class="text-decoration-none text-white font-bangers fs-5">VER TODO ></a>
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
                                        <img src="{{ $blader->marco_url }}" class="w-100 h-100 position-absolute top-0 start-0" style="border-radius: 50% !important;" loading="lazy">
                                        <div class="rank-number">#{{ $pos }}</div>
                                    </div>
                                    <div class="text-white fw-bold text-truncate" style="max-width: 100px;">{{ $blader->user->name }}</div>
                                    <div class="font-bangers" style="color: var(--sbbl-gold); font-size: 1.2rem;">{{ $blader->points_x2 }} pts</div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            @foreach ($bladers->slice(3, 2) as $index => $blader)
                                <div class="d-flex align-items-center justify-content-between p-2 border-bottom border-dark" style="border-width: 2px !important;">
                                    <div class="d-flex align-items-center">
                                        <span class="text-white fw-bold me-3 font-bangers" style="font-size: 1.5rem;">#{{ $index + 1 }}</span>
                                        <img src="{{ $blader->avatar_url }}" class="me-2" width="35" height="35" style="border: 2px solid var(--sbbl-gold); border-radius: 50%; object-fit: cover;">
                                        <span class="text-white fw-bold">{{ $blader->user->name }}</span>
                                    </div>
                                    <span class="font-bangers fs-5" style="color: var(--sbbl-gold); text-shadow: 1px 1px 0 #000;">{{ $blader->points_x2 }} pts</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="command-panel p-4 h-100">
                        <div class="panel-header mb-4 bg-transparent border-0 px-0">
                            <span><i class="fas fa-users text-info me-2"></i> Equipos</span>
                        </div>
                        <div class="team-list">
                            @foreach($teams->take(5) as $key => $team)
                            <div class="team-row">
                                <div class="d-flex align-items-center">
                                    <div class="team-rank-badge">#{{ $key + 1 }}</div>
                                    <div class="me-3" style="width: 40px; height: 40px; background: #000; border: 2px solid #444;">
                                        <img class="lazy-logo w-100 h-100 object-fit-contain"
                                             src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=="
                                             data-src="@if($team->logo) data:image/png;base64,{{ $team->logo }} @else /images/logo_new.png @endif">
                                    </div>
                                    <span class="fw-bold text-white">{{ $team->name }}</span>
                                </div>
                                <span class="badge bg-black text-white border border-secondary font-bangers fs-6" style="border-radius: 0;">{{ $team->points_x2 }} pts</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- 6. BLADER DEL MES (MVP) --}}
        <div class="container mt-5">
            <div class="mvp-card p-4 p-md-5 position-relative overflow-hidden">
                <div class="row align-items-center position-relative" style="z-index: 1;">
                    <div class="col-md-8">
                        <div class="d-inline-block bg-black text-white px-3 py-1 mb-3 font-bangers fs-4" style="border: 2px solid #fff; box-shadow: 4px 4px 0 #000; transform: skewX(-10deg);">
                            <span style="display:block; transform: skewX(10deg);">🌟 RENDIMIENTO DESTACADO</span>
                        </div>
                        <h2 class="display-5 mb-1" style="font-size: 3rem;">Blader del Mes</h2>
                        <h4 class="mb-4" style="color: #ccc;">{{ $lastMonthName ?? '' }} {{ $lastYear ?? '' }}</h4>

                        <div class="d-flex align-items-center mb-4">
                            <h3 class="mb-0 me-3 font-bangers" style="font-size: 2.5rem; text-shadow: 2px 2px 0 #000; color: var(--sbbl-gold);">{{ $bestUserProfile->name ?? 'N/A' }}</h3>
                            <span class="badge bg-black text-white border border-2 border-white fw-bold py-2" style="border-radius: 0;">{{ $bestUserProfile->profile->region->name ?? '' }}</span>
                        </div>

                        <div class="mvp-stats d-flex flex-wrap gap-3">
                            <span><i class="fas fa-star"></i> {{ $bestUser->total_puntos ?? '0' }} Pts</span>
                            <span><i class="fas fa-trophy text-white"></i> <span class="text-white">{{ $bestUserRecord->total_victorias_combo ?? '0' }} Win</span></span>
                            <span>
                                <i class="fas fa-cog"></i>
                                @if($bestUserRecord)
                                    {{ $bestUserRecord->blade }}/{{ $bestUserRecord->ratchet }}/{{ $bestUserRecord->bit }}
                                @else
                                    Sin datos
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="col-md-4 text-center mt-4 mt-md-0">
                         @php
                            $profileMVP = $bestUserProfile->profile;
                            $gifStyle = strpos($profileMVP->avatar_url, '.gif') !== false ? 'padding: 15px;' : '';
                        @endphp
                        <div class="position-relative d-inline-block" style="width: 200px; height: 200px;">
                            <img src="{{ $profileMVP->avatar_url }}" class="w-100 h-100 position-absolute top-0 start-0" style="{{ $gifStyle }} border-radius: 50%; border: 4px solid var(--sbbl-gold); object-fit: cover;" loading="lazy">
                            <img src="{{ $profileMVP->marco_url }}" class="w-100 h-100 position-absolute top-0 start-0" style="border-radius: 50%; z-index: 2;" loading="lazy">
                            <div style="position: absolute; inset: -15px; border: 4px dashed #fff; border-radius: 50%; animation: spin 10s linear infinite; opacity: 0.5;"></div>
                        </div>
                    </div>
                    <style>@keyframes spin { 100% { transform: rotate(360deg); } }</style>
                </div>
            </div>
        </div>

        {{-- 7. NOTICIAS --}}
        <div class="container mt-5">
            <h3 class="section-title">Artículos Recientes</h3>
            <div class="row g-4">
                @forelse ($articles as $article)
                    <div class="col-md-6 col-lg-4">
                        <div class="card news-card h-100">
                            @if ($article->image)
                                <img src="data:image/png;base64,{{ $article->image }}" alt="Cover" class="object-fit-cover" style="height: 180px;">
                            @else
                                <div class="bg-black d-flex align-items-center justify-content-center" style="height: 180px; border-bottom: 3px solid #000;">
                                    <i class="fas fa-newspaper fa-3x text-secondary"></i>
                                </div>
                            @endif
                            <div class="card-body d-flex flex-column p-4">
                                <h4 class="font-bangers text-white mb-3" style="font-size: 1.5rem; letter-spacing: 1px;">{{ $article->title }}</h4>
                                <a href="{{ route('blog.show', $article->custom_url) }}" class="btn mt-auto text-uppercase">Leer Informe</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12"><p class="text-white text-center fw-bold">No hay artículos recientes.</p></div>
                @endforelse
            </div>
        </div>

        {{-- 8. QUIÉNES SOMOS & STAFF --}}
        <div class="container mt-5 mb-5">
            <div class="command-panel p-5 text-center">
                <h2 class="mb-4 font-bangers text-white" style="font-size: 3rem;">La Iniciativa SBBL</h2>
                <div class="row justify-content-center text-center">
                    <div class="col-md-8 text-white fw-bold">
                        <p class="mb-3" style="font-size: 1.1rem;">La <strong style="color: var(--sbbl-gold); font-size: 1.3rem; font-family: 'Bangers', cursive;">SBBL (Spanish BeyBattle League)</strong> es una organización sin ánimo de lucro operada por la comunidad. Nuestro objetivo es estandarizar y profesionalizar el Beyblade competitivo en España.</p>
                        <p class="mb-4" style="color: #ccc;">Gestionamos rankings, organizamos torneos nacionales y desarrollamos tecnología para conectar a los Bladers de todas las regiones.</p>
                    </div>
                </div>

                <div class="d-flex justify-content-center gap-3 mt-3">
                    <a href="https://paypal.me/AsocSBBL" target="_blank" class="btn" style="border-radius: 0; border: 3px solid #000; font-family: 'Bangers', cursive; font-size: 1.5rem; background: var(--sbbl-gold); color: #000; box-shadow: 4px 4px 0 #000; transform: skewX(-5deg);">
                        <span style="display: block; transform: skewX(5deg);"><i class="fab fa-paypal me-2"></i> Apoyar Proyecto</span>
                    </a>
                </div>

                <div class="row justify-content-center mt-5 g-4">
                    @foreach ($usuarios as $usuario)
                        @php $profile = $usuario->profile; @endphp
                        <div class="col-6 col-md-3">
                            <div class="d-flex flex-column align-items-center">
                                <div class="position-relative mb-3" style="width: 80px; height: 80px;">
                                    <img src="{{ $profile->avatar_url }}" class="rounded-circle w-100 h-100" style="object-fit: cover; border: 2px solid var(--sbbl-gold);">
                                    <img src="{{ $profile->marco_url }}" class="position-absolute top-0 start-0 w-100 h-100" style="border-radius:50%;">
                                </div>
                                <h5 class="mb-0 text-white font-bangers fs-4" style="letter-spacing: 1px;">{{ $usuario->name }}</h5>
                                <span class="badge bg-black text-white mt-1 border border-dark" style="font-family: 'Bangers', cursive; border-radius: 0;">{{ $usuario->titulo ?? 'STAFF' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- 9. HISTORIAL --}}
        <div class="container pb-5">
            <h3 class="section-title">Torneos pasados</h3>
            <div class="owl-carousel owl-theme">
                @foreach ($antiguos as $antiguo)
                    <div class="card bg-black border-0 overflow-hidden" style="border: 3px solid #333 !important; border-radius: 0; transition: 0.2s;">
                        <div style="height: 180px; background: url('{{ $antiguo->image_mod ? "data:image/png;base64,".$antiguo->image_mod : "/storage/".$antiguo->imagen }}') center/cover; opacity: 0.7; transition: 0.3s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.7"></div>
                        <div class="card-body p-3" style="background: var(--sbbl-blue-2);">
                            <h5 class="text-truncate text-white font-bangers fs-4" style="letter-spacing: 1px;">{{ $antiguo->name }}</h5>
                            <small class="text-white d-block fw-bold text-uppercase">{{ $antiguo->location }}</small>
                            <small style="color: var(--sbbl-gold); font-family: 'Bangers', cursive; font-size: 1.1rem;"><event-date fecha="{{ $antiguo->date }}"></event-date></small>
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
            button.querySelector('span').innerHTML = '⬆ Ocultar';
            button.dataset.showing = 'true';
        } else {
            document.querySelectorAll('.more-event-item').forEach(item => {
                if (parseInt(item.dataset.index) > 3) item.classList.add('d-none');
            });
            button.querySelector('span').innerHTML = `⬇ Mostrar más (${totalHiddenCount})`;
            button.dataset.showing = 'false';
        }
    }

    // Lazy Loading & Season Progress
    document.addEventListener("DOMContentLoaded", function () {
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
        const endSeason = new Date("2026-05-31");

        const totalDuration = (endSeason - startPre) / (1000 * 60 * 60 * 24);
        const elapsedDays = (today - startPre) / (1000 * 60 * 60 * 24);

        let prePercent = 0, seasonPercent = 0, statusText = "";

        if (today < startPre) {
            statusText = "⏳ ESPERANDO INICIO...";
        } else if (today >= startPre && today < startSeason) {
            prePercent = (elapsedDays / totalDuration) * 100;
            statusText = "🔧 PRE-TEMPORADA";
        } else if (today >= startSeason && today <= endSeason) {
            prePercent = ((startSeason - startPre) / (endSeason - startPre)) * 100;
            const totalProgress = (elapsedDays / totalDuration) * 100;
            seasonPercent = totalProgress - prePercent;
            statusText = "🔥 TEMPORADA 2 ACTIVA";
        } else {
            prePercent = 20; seasonPercent = 80;
            statusText = "✅ CICLO COMPLETADO";
        }

        document.getElementById("pre-fill").style.width = `${prePercent}%`;
        document.getElementById("season-fill").style.width = `${seasonPercent}%`;
        document.getElementById("season-status").innerHTML = statusText;
    });
</script>
@endsection
