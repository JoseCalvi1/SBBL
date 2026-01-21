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
<!-- Estilos adicionales -->
<style>
    .user-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
    }

    .user-name {
        color: #fff;
        font-size: 1.5rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .user-title {
        color: #bbb;
        font-size: 1rem;
        margin-top: 5px;
    }
    .custom-label {
        background: rgba(255, 255, 255, 0.7);
        padding: 5px;
        border-radius: 5px;
        text-align: center;
    }
.season-wrapper {
    background: #121212;
    color: #eee;
    padding: 25px 10px;
    font-family: 'Segoe UI', sans-serif;
    box-shadow: 0 0 20px rgba(0, 255, 255, 0.1);
  }

  .combined-bar {
    display: flex;
    height: 30px;
    width: 100%;
    border-radius: 8px;
    overflow: hidden;
    background: #2c2c2c;
    margin: 10px 0;
    box-shadow: inset 0 0 6px #000;
  }

  .segment {
    height: 100%;
    transition: width 1s ease-in-out;
  }

  .preseason {
    background: linear-gradient(to right, #00c3ff, #007aff);
  }

  .season {
    background: linear-gradient(to right, #ffc107, #ff5722);
  }

    .bar-labels-proportional {
    position: relative;
    height: 20px;
    margin-top: 5px;
    }

    .bar-labels-proportional span {
    position: absolute;
    font-size: 0.8em;
    color: #aaa;
    }
    .social-links {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
    gap: 1rem;
    font-size: 1.8rem;
}

.social-links a {
    color: #ffffff;
    transition: transform 0.2s ease, color 0.2s ease;
}

.social-links a:hover {
    transform: scale(1.2);
    color: #11e6c6; /* Azul claro de tu diseño */
}

    .ranking-title {
        font-size: 2rem;
        font-weight: bold;
        text-transform: uppercase;
        color: #ffc107;
        text-align: center;
        margin-bottom: 30px;
    }

    .team-card {
        display: flex;
        align-items: center;
        background-size: cover;
        background-position: center;
        position: relative;
        border-radius: 12px;
        margin-bottom: 20px;
        padding: 15px 20px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.4);
    }

    .team-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: 12px;
        background: rgba(0, 0, 0, 0.6);
        z-index: 1;
    }

    .team-logo {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        z-index: 2;
        margin-right: 20px;
    }

    .team-info {
        z-index: 2;
        color: white;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .team-name {
        font-size: 1.4em;
        font-weight: bold;
    }

    .team-points {
        font-size: 1.2em;
        color: #ffc107;
    }

    .team-rank {
        z-index: 2;
        font-size: 2em;
        font-weight: bold;
        color: white;
        margin-right: 20px;
        width: 50px;
        text-align: center;
    }

    .team-entry {
        display: flex;
        align-items: center;
        width: 100%;
    }
.bg-blader-month {
    background: linear-gradient(135deg, #1e2a47, #3b4f94);
}
.social-links a svg {
        width: 1em;
        height: 1em;
        fill: white;
        transition: fill 0.3s ease;
    }

    .social-links a:hover svg {
        fill: #11e6c6;
    }

</style>

<head>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-mini-map/dist/Control.MiniMap.css" />
    <script src="https://unpkg.com/leaflet-mini-map/dist/Control.MiniMap.js"></script>
</head>
@include('database.partials.mainmenu-styles')
@endsection

@section('content')
<div class="container-fluid" style="background-image: url('../images/webTile2.png'); background-size: 20%; background-repeat: repeat; background-position: center; padding: 0px;">
    <div class="container-fluid">

@if ((Auth::user() && !Auth::user()->profile->region))
<div class="row text-center" style="background-color: red; color: white; padding: 20px;">
   <p class="text-center" style="margin-bottom: 0;">TODAVÍA NO HAS SELECCIONADO TU COMUNIDAD AUTÓNOMA. HAZLO EN <a style="color: yellow; font-weight: bold;" href="{{ route('profiles.edit', ['profile' => Auth::user()->id]) }}"> ESTE ENLACE</a></p>
</div>
@endif

@if (Auth::user() && 1==2)
<div class="row text-center" style="background: linear-gradient(135deg, #28a745, #218838); color: white; padding: 20px;">
    <p class="text-center" style="margin-bottom: 0; font-size: 1.2em; font-weight: bold;">
       🎉 ¡DESCUBRE TU <a style="color: #ffc107; text-decoration: underline;" href="{{ route('profiles.wrapped', ['profile' => Auth::user()->id]) }}">SBBL WRAPPED DE FINAL DE TEMPORADA</a>! 🎯
    </p>
 </div>
@endif


    </div>


<!--    <a href="{{ route('inicio.nacional') }}" style="text-decoration: none; color: inherit;">
        <div style="position: relative; text-align: center;">
            <img src="../images/banner_nacional.webp" class="w-100">

            <div class="relative w-full h-6 rounded-full bg-blue" style="height: 20px; border: 2px solid;">
                <div class=" bg-white h-full rounded-full" style="width: 100%; height: 18px"><span class="text-sm mt-2" style="color: black">3000 / 3000 €</span></div>
            </div>
            <div class="text-white" style="
                width: 100%;
                background: rgba(0, 0, 0, 0.7); /* Fondo oscuro con opacidad */
                color: white;
                text-align: center;
                padding: 10px;
                position: absolute;
                bottom: 20px;
                left: 0;
            ">
                PULSA AQUÍ PARA VER TODA LA INFORMACIÓN
            </div>
        </div>
    </a> -->

    <div class="season-wrapper">
        <div class="row">
        <div class="col-md-7">
            <h2>📅 Progreso de la Temporada</h2>
                <p id="season-status"></p>

                <div class="combined-bar">
                    <div id="pre-fill" class="segment preseason"></div>
                    <div id="season-fill" class="segment season"></div>
                </div>

                <div class="bar-labels-proportional">
                    <span style="left: 0%">22 Junio 2025</span>
                    <span style="left: 19%">1 Septiembre 2025</span>
                    <span style="left: 86%">30 Junio 2026</span>
                </div>


                <p class="mt-2">Si tienes alguna sugerencia que hacer no dudes en <a href="{{ route('inicio.contact') }}">escribirnos un correo</a> o <a href="https://forms.gle/g9eWmD5KwgjoXWeu5">mandar tu sugerencia</a></p>

                <div class="social-links">
                    <a href="https://discord.gg/JCtAHfJ8Ht" target="_blank" title="Discord"><i class="fab fa-discord"></i></a>
                    <a href="https://www.youtube.com/@sbbl_oficial" target="_blank" title="YouTube"><i class="fab fa-youtube"></i></a>
                    <a href="https://www.twitch.tv/sbbl_oficial" target="_blank" title="Twitch"><i class="fab fa-twitch"></i></a>
                    <a href="https://www.instagram.com/sbbl_oficial/" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="https://x.com/SBBLOficial" target="_blank" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="https://bsky.app/profile/sbbloficial.bsky.social" target="_blank" title="Bluesky" aria-label="Bluesky">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600">
                            <path d="m135.72 44.03c66.496 49.921 138.02 151.14 164.28 205.46 26.262-54.316 97.782-155.54 164.28-205.46 47.98-36.021 125.72-63.892 125.72 24.795 0 17.712-10.155 148.79-16.111 170.07-20.703 73.984-96.144 92.854-163.25 81.433 117.3 19.964 147.14 86.092 82.697 152.22-122.39 125.59-175.91-31.511-189.63-71.766-2.514-7.3797-3.6904-10.832-3.7077-7.8964-0.0174-2.9357-1.1937 0.51669-3.7077 7.8964-13.714 40.255-67.233 197.36-189.63 71.766-64.444-66.128-34.605-132.26 82.697-152.22-67.108 11.421-142.55-7.4491-163.25-81.433-5.9562-21.282-16.111-152.36-16.111-170.07 0-88.687 77.742-60.816 125.72-24.795z"/>
                        </svg>
                    </a>
                </div>
        </div>
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden" data-aos="fade-up">
                <div class="ratio ratio-16x9">
                    <iframe width="560" height="315" src="https://www.youtube.com/embed/wkFnz8kPs3M?si=Dot6IDj6hHy7WqLc" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
            </div>
        </div>
        </div>

    </div>

    <div class="menu-container-wrapper">
        <button class="menu-toggle">☰ Especial</button>
        <div class="menu-row">
            <a href="{{ route('inicio.halloffame') }}" class="menu-button" style="width: 250px">🏆 Salón de la Fama</a>
            <a href="{{ route('inicio.resumen_semanal') }}" class="menu-button" style="width: 250px">🎯 Resumen semanal</a>
        </div>
    </div>





<div class="col-md-12 p-4 text-center">
    <div class="events-box-container bg-dark p-4 p-md-5 rounded-3 shadow-2xl">
    <!-- Título de la sección -->
    <h3 class="titulo-categoria text-uppercase mb-4 text-white">Próximos eventos</h3>

    {{-- Inicialización de variables para la lógica de visualización --}}
    @php
        $totalEventos = $nuevos->count();
    @endphp

    {{-- Contenedor de la cuadrícula de eventos --}}
    <div class="row g-3" id="event-cards-grid">

        @forelse ($nuevos as $evento)
            @php
                // Determina si el evento debe estar oculto inicialmente (si no es uno de los 3 primeros)
                $isHidden = $loop->iteration > 3;
            @endphp

            {{-- Añadimos la clase 'more-event-item' para identificarlos con JS
                 y la clase 'd-none' de Bootstrap si debe estar oculto inicialmente --}}
            <div class="col-md-4 more-event-item @if($isHidden) d-none @endif" data-index="{{ $loop->iteration }}">
                <div class="card h-100 shadow-lg border-1 rounded-3 overflow-hidden">
                    <div class="ratio ratio-16x9">
                        {{-- Lógica para mostrar imagen codificada o desde storage --}}
                        @if ($evento->image_mod)
                            <img src="data:image/png;base64,{{ $evento->image_mod }}" class="card-img-top object-fit-cover" alt="{{ $evento->name }}">
                        @else
                            <img src="/storage/{{ $evento->imagen }}" class="card-img-top object-fit-cover" alt="{{ $evento->name }}">
                        @endif
                    </div>
                    <div class="card-body d-flex flex-column bg-dark text-white">
                        <h5 class="card-title fw-bold mb-1">{{ $evento->name }}</h5>
                        <p class="mb-1 text-info">{{ $evento->region->name }}</p>
                        {{-- Componente para la fecha del evento --}}
                        <p class="mb-3"><event-date fecha="{{ $evento->date }}"></event-date></p>
                        <a href="{{ route('events.show', ['event' => $evento->id]) }}"
                        class="btn btn-outline-info mt-auto w-100 text-uppercase fw-bold">
                            Ver evento
                        </a>
                    </div>
                </div>
            </div>
        @empty
            {{-- Mensaje si no hay eventos próximos --}}
            <div class="col-12 text-center py-5">
                <h4 class="fw-bold text-white">Prepárate para los nuevos eventos</h4>
                <p class="text-muted">Próximamente</p>
            </div>
        @endforelse
    </div>

    {{-- Botón "Mostrar más" integrado en la parte inferior del contenedor.
         Solo se muestra si hay más de 3 eventos. --}}
    @if ($totalEventos > 3)
        <div class="text-center mt-4 pt-4 border-top border-secondary">
            <button id="show-more-events"
                    class="btn btn-info btn-lg w-100 fw-bold text-uppercase"
                    onclick="toggleMoreEvents()"
                    data-showing="false">
                Mostrar más ({{ $totalEventos - 3 }} restantes)
            </button>
        </div>
    @endif
</div>


    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center d-none d-sm-block">
                <h3 class="titulo-categoria text-uppercase mb-4 mt-4" style="color:white">Top individual</h3>
                <div class="row" style="align-items: center; display: flex; justify-content: center;">
                {{-- Tomamos solo los primeros 5 bladers y hacemos un bucle --}}
                @foreach ($bladers->take(5) as $index => $blader)
                    @php
                        $posicion = $index + 1;

                        // Lógica para textos y clases según la posición
                        $textos = [1 => '1er Puesto', 2 => '2º Puesto', 3 => '3er Puesto', 4 => '4º Puesto', 5 => '5º Puesto'];
                        $titulo = $textos[$posicion] ?? "{$posicion}º Puesto";

                        // Clase CSS especial solo para los 3 primeros (top-1, top-2, top-3)
                        $claseTop = $posicion <= 3 ? "top-{$posicion}" : "";

                        // Padding para GIF
                        $gifStyle = strpos($blader->avatar_url, '.gif') !== false ? 'padding: 10px;' : '';
                    @endphp

                    <div class="col-md-2 text-center m-2">
                        {{-- Título del Ranking --}}
                        <h4 class="font-weight-bold ranking-card {{ $claseTop }} mb-3">
                            {{ $titulo }}
                        </h4>

                        {{-- Contenedor de Avatar (Centrado con margin auto en lugar de left vw) --}}
                        <div style="position: relative; width: 100px; height: 100px; margin: 0 auto;">

                            {{-- AVATAR --}}
                            <img src="{{ $blader->avatar_url }}"
                                class="rounded-circle"
                                width="100" height="100"
                                style="position: absolute; top: 0; left: 0; object-fit: cover; {{ $gifStyle }}"
                                loading="lazy"
                                alt="Avatar de {{ $blader->user->name }}">

                            {{-- MARCO --}}
                            <img src="{{ $blader->marco_url }}"
                                class="rounded-circle"
                                width="100" height="100"
                                style="position: absolute; top: 0; left: 0;"
                                loading="lazy"
                                alt="Marco">
                        </div>

                        {{-- Nombre y Puntos --}}
                        <h3 class="text-white mt-4">{{ $blader->user->name }}</h3>
                        <h2 class="text-white">
                            {{ $blader->points_x2 }}<span style="font-size:0.5em">pts</span>
                        </h2>
                    </div>
                @endforeach
            </div>
            </div>
        </div>

    </div>

    <div class="container ranking-container">
        <h3 class="titulo-categoria text-uppercase mb-4 mt-4" style="color:white">Top Equipos</h3>

        @foreach($teams as $key => $team)
        <div class="team-card lazy-bg" data-bg="data:image/png;base64,{{ $team->image }}">
            <div class="team-overlay"></div>
            <div class="team-entry">
                <div class="team-rank">#{{ $key + 1 }}</div>
                <img class="team-logo lazy-logo"
                    src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==" {{-- placeholder transparente --}}
                    data-src="@if($team->logo) data:image/png;base64,{{ $team->logo }} @else /images/logo_new.png @endif"
                    alt="Logo de {{ $team->name }}">
                <div class="team-info">
                    <span class="team-name">{{ $team->name }}</span>
                    <span class="team-points">{{ $team->points_x2 }} pts</span>
                </div>
            </div>
        </div>
        @endforeach

    </div>

<div class="container text-white my-4 rounded shadow bg-blader-month">
    <div class="row align-items-center py-4 px-2">
        <!-- Texto -->
        <div class="col-md-8 text-center">
            <h2 class="fw-bold mb-3 text-uppercase" style="font-size: 1.5em;">
                🏆 Blader del Mes {{ $lastMonthName ?? '' }} {{ $lastYear ?? '' }}
            </h2>
            <p class="mb-1">
                El blader con más puntos fue <strong>{{ $bestUserProfile->name ?? 'Desconocido' }}</strong>
                de <strong>{{ $bestUserProfile->profile->region->name ?? 'Región desconocida' }}</strong>
            </p>
            <p class="mb-1">
                Total de puntos: <span class="text-warning fw-bold">{{ $bestUser->total_puntos ?? '0' }}</span>
            </p>
            <p class="mb-1">
                Mejor combo: <strong>{{ $bestUserRecord->blade ?? '?' }} {{ $bestUserRecord->ratchet ?? '?' }} {{ $bestUserRecord->bit ?? '?' }}</strong>
            </p>
            <p class="mb-0">
                Logró <strong>{{ $bestUserRecord->victorias ?? '0' }}</strong> victorias sumando
                <strong class="text-success">{{ $bestUserRecord->puntos_ganados ?? '0' }} puntos</strong>
            </p>
        </div>

        <!-- Imagen -->
        <div class="col-md-3 text-center d-none d-md-block">
            <div class="position-relative d-inline-block" style="width: 180px; height: 180px;">

                @php
                    // Creamos un atajo para no escribir tanto código dentro del HTML
                    $profile = $bestUserProfile->profile;

                    // Calculamos el estilo del GIF usando la nueva URL
                    $gifStyle = strpos($profile->avatar_url, '.gif') !== false ? 'padding: 20px;' : '';
                @endphp

                <img src="{{ $profile->avatar_url }}"
                    class="rounded-circle position-absolute top-0 start-0 w-100 h-100"
                    style="{{ $gifStyle }}"
                    loading="lazy"
                    alt="Avatar">

                <img src="{{ $profile->marco_url }}"
                    class="rounded-circle position-absolute top-0 start-0 w-100 h-100"
                    loading="lazy"
                    alt="Marco">

            </div>
        </div>
    </div>
</div>

<div class="container">
        <div class="py-4">
            <h2 class="text-center mb-4 text-white">Últimos Artículos</h2>
            <div class="row">
                @forelse ($articles as $article)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            @if ($article->image)
                                <div class="text-center">
                                    <img src="data:image/png;base64,{{ $article->image }}" alt="Imagen del artículo" class="img-fluid rounded mb-4" style="height: 120px">
                                </div>
                            @endif
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">{{ $article->title }}</h5>
                                <a href="{{ route('blog.show', $article->custom_url) }}" class="btn btn-primary mt-auto">Leer más</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-white">No hay artículos publicados todavía.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quiénes somos -->
    <div class="container my-5">

        <div class="container my-5">
        <div class="card shadow-lg border-0 rounded-4" style="background: linear-gradient(135deg, #111, #222); color: #fff;">
            <div class="card-body text-center px-4 py-5">

            <h3 class="text-uppercase mb-4 fw-bold" style="color: #ffcc00; text-shadow: 0 0 10px rgba(255, 204, 0, 0.4);">
                ¿Quiénes Somos?
            </h3>

            <p class="mb-4" style="font-size: 1.1rem; line-height: 1.8;">
                La <strong>SBBL (Spanish BeyBattle League)</strong> es una <strong>asociación sin ánimo de lucro</strong> fundada por y para los fans de Beyblade en España.
                Nuestro objetivo es <strong>fortalecer la comunidad</strong> mediante la organización de torneos, ligas y actividades que fomenten el compañerismo, la deportividad y la diversión.
            </p>

            <p class="mb-4" style="font-size: 1.1rem; line-height: 1.8;">
                A través de esta plataforma gestionamos <strong>eventos, rankings nacionales, estadísticas y colecciones</strong> para que cada jugador pueda seguir su progreso
                y compartir su pasión con otros. Todo el trabajo detrás de la SBBL —desarrollo web, organización de torneos, diseño y mantenimiento— es realizado por voluntarios de la comunidad.
            </p>

            <p class="mb-5" style="font-size: 1.1rem; line-height: 1.8;">
                Gracias a las <strong>aportaciones y suscripciones</strong> de nuestros miembros y colaboradores, podemos mantener viva esta iniciativa, cubrir gastos de dominio, premios y eventos presenciales en todo el país.
            </p>

            <div class="p-4 rounded-4 mx-auto" style="max-width: 700px; background: linear-gradient(90deg, #2a2a2a, #3d2c00); border: 1px solid rgba(255,255,255,0.1);">
                <h5 class="fw-bold mb-2 text-warning">
                💛 ¿Quieres apoyar más a la comunidad?
                </h5>
                <p class="mb-3" style="font-size: 1rem;">
                Si deseas realizar una contribución mayor para ayudarnos a seguir organizando torneos y mantener viva la SBBL,
                puedes hacerlo desde el siguiente enlace:
                </p>
                <a href="https://paypal.me/AsocSBBL" target="_blank" class="btn btn-warning fw-bold px-4 rounded-pill shadow">
                <i class="fab fa-paypal me-2"></i> Contribuir vía PayPal
                </a>
            </div>

            </div>
        </div>
        </div>


    <!-- Equipo -->
    <div class="row justify-content-center mt-5">
        @foreach ($usuarios as $usuario)
            @php
                // Creamos un atajo al perfil
                $profile = $usuario->profile;

                // Calculamos estilo si es GIF
                $gifStyle = strpos($profile->avatar_url, '.gif') !== false ? 'padding: 10px;' : '';
            @endphp

            <div class="col-md-4 text-center p-2">
                <div class="position-relative d-flex flex-column align-items-center user-card"
                    style="background: #333; border-radius: 10px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3); transition: transform 0.3s ease;">

                    {{-- Contenedor de Avatar + Marco (Definimos tamaño fijo para centrado perfecto) --}}
                    <div class="position-relative" style="width: 100px; height: 100px;">

                        {{-- AVATAR (Z-Index 1: Fondo) --}}
                        <img src="{{ $profile->avatar_url }}"
                            class="rounded-circle"
                            width="100" height="100"
                            style="position: absolute; top: 0; left: 0; z-index: 1; object-fit: cover; {{ $gifStyle }}"
                            loading="lazy"
                            alt="Avatar">

                        {{-- MARCO (Z-Index 2: Frente, ligeramente más grande y desplazado) --}}
                        <img src="{{ $profile->marco_url }}"
                            class="rounded-circle"
                            width="110" height="110"
                            style="position: absolute; top: -5px; left: -5px; z-index: 2;"
                            loading="lazy"
                            alt="Marco">
                    </div>

                    {{-- Textos --}}
                    <h3 class="user-name"
                        style="color: white; margin-top: 15px; font-size: 1.5rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);">
                        {{ $usuario->name }}
                    </h3>

                    <p class="user-title"
                    style="color: #ccc; font-size: 1rem; margin-top: 5px; font-style: italic;">
                        {{ $usuario->titulo ?? 'Miembro del equipo SBBL' }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>
</div>


    <div class="container mt-2">

            <div class="row m-0">
                <h2 class="titulo-categoria text-uppercase mb-4 mt-4" style="color:white">Eventos realizados</h2>

                <div class="owl-carousel owl-theme">
                        @foreach ($antiguos as $antiguo)
                            <div class="card" style="background-color: #283b63; color: white; border: 2px solid #ffffff;">
                                @if ($antiguo->image_mod)
                                <span style="width: 100%; min-height: 200px; background: url('data:image/png;base64,{{ $antiguo->image_mod }}') bottom center no-repeat; background-size: cover;"></span>
                                @else
                                <span style="width: 100%; min-height: 200px; background: url('/storage/{{ $antiguo->imagen }}') bottom center no-repeat; background-size: cover;"></span>
                                @endif

                                <div class="card-body">
                                    <h3>{{ $antiguo->name }}</h3>
                                    <p>{{ $antiguo->location }}</p>
                                    <p><event-date fecha="{{ $antiguo->date }}"></event-date></p>

                                    <a href="{{ route('events.show', ['event' => $antiguo->id]) }}" class="btn btn-outline-light d-block font-weight-bold text-uppercase" style="background-color: #1e2a47; border-color: #ffffff;">Ver evento</a>
                                </div>
                            </div>
                        @endforeach
                </div>
            </div>
    </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function toggleMoreEvents() {
        const button = document.getElementById('show-more-events');
        const hiddenEvents = document.querySelectorAll('.more-event-item.d-none');
        // Lee el estado actual del botón
        const isShowing = button.dataset.showing === 'true';

        // El número de eventos ocultos inicialmente (para el texto del botón al cerrar)
        const totalHiddenCount = parseInt({{ $totalEventos }} - 3);

        if (!isShowing) {
            // Caso 1: Mostrar eventos
            hiddenEvents.forEach(item => {
                item.classList.remove('d-none');
            });
            button.textContent = 'Mostrar menos';
            button.dataset.showing = 'true';
        } else {
            // Caso 2: Ocultar eventos
            document.querySelectorAll('.more-event-item').forEach(item => {
                const index = parseInt(item.getAttribute('data-index'));
                // Ocultar todos los que no son los primeros 3
                if (index > 3) {
                    item.classList.add('d-none');
                }
            });
            // Restaurar el texto original con el contador
            button.textContent = `Mostrar más (${totalHiddenCount} restantes)`;
            button.dataset.showing = 'false';
        }
    }
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {

        // Lazy loading de background-image
    const lazyBackgrounds = document.querySelectorAll(".lazy-bg");

    const bgObserver = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const bg = entry.target.getAttribute("data-bg");
                if (bg) {
                    entry.target.style.backgroundImage = `url(${bg})`;
                    entry.target.removeAttribute("data-bg");
                }
                bgObserver.unobserve(entry.target);
            }
        });
    });

    lazyBackgrounds.forEach(bg => bgObserver.observe(bg));

    // Lazy loading de imágenes <img>
    const lazyImages = document.querySelectorAll("img.lazy-logo");

    const imgObserver = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                const realSrc = img.getAttribute("data-src");
                if (realSrc) {
                    img.src = realSrc;
                    img.removeAttribute("data-src");
                }
                imgObserver.unobserve(img);
            }
        });
    });

    lazyImages.forEach(img => imgObserver.observe(img));

        const today = new Date();

        const startPre = new Date("2025-06-22");
        const startSeason = new Date("2025-09-01");
        const endSeason = new Date("2026-06-30");

        const totalDuration = (endSeason - startPre) / (1000 * 60 * 60 * 24); // total días
        const preDuration = (startSeason - startPre) / (1000 * 60 * 60 * 24); // días de pretemporada
        const seasonDuration = (endSeason - startSeason) / (1000 * 60 * 60 * 24);

        const elapsedDays = (today - startPre) / (1000 * 60 * 60 * 24);

        let prePercent = 0, seasonPercent = 0;
        let statusText = "";

        if (today < startPre) {
            prePercent = 0;
            seasonPercent = 0;
            statusText = "⏳ La pretemporada aún no ha comenzado.";
        } else if (today >= startPre && today < startSeason) {
            const progress = elapsedDays / totalDuration;
            prePercent = (elapsedDays / totalDuration) * 100;
            seasonPercent = 0;
            statusText = "🔧 Estamos en <strong>pretemporada</strong>. ¡Calienta motores!";
        } else if (today >= startSeason && today <= endSeason) {
            prePercent = (preDuration / totalDuration) * 100;
            const seasonDays = (today - startSeason) / (1000 * 60 * 60 * 24);
            seasonPercent = (seasonDays / totalDuration) * 100;
            statusText = "🔥 <strong>Temporada 2 en curso</strong>. ¡A luchar!";
        } else {
            prePercent = (preDuration / totalDuration) * 100;
            seasonPercent = (seasonDuration / totalDuration) * 100;
            statusText = "✅ La Temporada 2 ha finalizado. ¡Nos vemos en la próxima!";
        }

        document.getElementById("pre-fill").style.width = `${prePercent}%`;
        document.getElementById("season-fill").style.width = `${seasonPercent}%`;
        document.getElementById("season-status").innerHTML = statusText;
  });
</script>
@endsection
