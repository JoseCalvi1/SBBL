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
    padding: 25px;
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
    <div class="container-fluid" style="background: darkblue">

@if ((Auth::user() && !Auth::user()->profile->region))
<div class="row text-center" style="background-color: red; color: white; padding: 20px;">
   <p class="text-center" style="margin-bottom: 0;">TODAVÍA NO HAS SELECCIONADO TU COMUNIDAD AUTÓNOMA. HAZLO EN <a style="color: yellow; font-weight: bold;" href="{{ route('profiles.edit', ['profile' => Auth::user()->id]) }}"> ESTE ENLACE</a></p>
</div>
@endif

@if (1 == 2)
<div class="row text-center" style="background: linear-gradient(135deg, #28a745, #218838); color: white; padding: 20px; border-radius: 8px;">
    <p class="text-center" style="margin-bottom: 0; font-size: 1.2em; font-weight: bold;">
       🎉 ¡DESCUBRE TU <a style="color: #ffc107; text-decoration: underline;" href="{{ route('profiles.wrapped', ['profile' => Auth::user()->id]) }}">SBBL WRAPPED DE MITAD DE TEMPORADA</a>! 🎯
    </p>
 </div>
@endif


    </div>


    <a href="{{ route('inicio.nacional') }}" style="text-decoration: none; color: inherit;">
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
    </a>

    <div class="season-wrapper">
        <h2>📅 Progreso de la Temporada</h2>
        <p id="season-status"></p>

        <div class="combined-bar">
            <div id="pre-fill" class="segment preseason"></div>
            <div id="season-fill" class="segment season"></div>
        </div>

        <div class="bar-labels-proportional">
            <span style="left: 0%">22 Junio 2025</span>
            <span style="left: 19%">1 Septiembre 2025</span>
            <span style="left: 94%">30 Junio 2026</span>
        </div>

        <div class="social-links">
            <a href="https://discord.gg/JCtAHfJ8Ht" target="_blank" title="Discord"><i class="fab fa-discord"></i></a>
            <a href="https://www.youtube.com/@sbbl_oficial" target="_blank" title="YouTube"><i class="fab fa-youtube"></i></a>
            <a href="https://www.twitch.tv/sbbl_oficial" target="_blank" title="Twitch"><i class="fab fa-twitch"></i></a>
            <a href="https://www.instagram.com/sbbl_oficial/" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="https://x.com/SBBLOficial" target="_blank" title="Twitter"><i class="fab fa-twitter"></i></a>
            <a href="https://bsky.app/profile/sbbloficial.bsky.social" target="_blank" title="Bluesky"><i class="fa-solid fa-cloud"></i></a>
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
        <h3 class="titulo-categoria text-uppercase mb-4 mt-4" style="color:white">Próximos eventos</h3>
        <div class="row m-0">
        @foreach ($nuevos as $evento)
            <div class="col-md-4 pb-2">
                <div class="card" style="background-color: #283b63; color: white; border: 2px solid #ffffff;">
                    @if ($evento->image_mod)
                    <span style="width: 100%; min-height: 200px; background: url('data:image/png;base64,{{ $evento->image_mod }}') bottom center no-repeat; background-size: cover;"></span>
                    @else
                    <span style="width: 100%; min-height: 200px; background: url('/storage/{{ $evento->imagen }}') bottom center no-repeat; background-size: cover;"></span>
                    @endif
                    <div class="card-body">
                        <h3 style="font-weight: bold;">{{ $evento->name }}</h3>
                        <h3>{{ $evento->region->name }}</h3>
                        <p><event-date fecha="{{ $evento->date }}"></event-date></p>
                    </div>
                    <a href="{{ route('events.show', ['event' => $evento->id]) }}" class="d-block font-weight-bold text-uppercase pt-2 pb-2" style="text-decoration: none; color: white; width: 100%; background-color: #1e2a47; border-color: #ffffff;">Ver evento</a>
                </div>
            </div>
        @endforeach

        @if ($nuevos->isEmpty())
            <div class="col-md-4 pb-2">
                <div class="card">
                    <div class="card-body">
                        <h3 class="border-bottom pt-4 pb-5">Prepárate para los nuevos eventos</h3>
                        <h3 class="pt-5">Próximamente</h3>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center d-none d-sm-block">
                <h3 class="titulo-categoria text-uppercase mb-4 mt-4" style="color:white">Top individual</h3>
                <div class="row" style="align-items: center;display: flex;justify-content: center;">
                    <div class="col-md-2 text-center m-2">
                        <h4 class="font-weight-bold top-1 ranking-card mb-3">1er Puesto</h4>
                        <div style="position: relative">
                        @if ($bladers[0]->imagen)
                                <img src="/storage/{{ $bladers[0]->imagen }}" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw; {{ strpos($bladers[0]->imagen, '.gif') !== false ? 'padding: 10px;' : '' }}">
                            @else
                                <img src="/storage/upload-profiles/Base/DranDagger.webp" class="rounded-circle" width="100" style="position: absolute; : 0; left: 1.2vw;">
                            @endif
                            @if ($bladers[0]->marco)
                                <img src="/storage/{{ $bladers[0]->marco }}" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw;">
                            @else
                                <img src="/storage/upload-profiles/Marcos/BaseBlue.png" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw;">
                            @endif
                        </div>
                        <h3 style="color: white; margin-top: 120px">{{ $bladers[0]->user->name }}</h3>
                        <h2 style="color: white">{{ $bladers[0]->points_x2 }}<span style="font-size:0.5em">pts</span></h2>
                    </div>
                    <div class="col-md-2 text-center m-2">
                        <h4 class="font-weight-bold top-2 ranking-card mb-3">2º Puesto</h4>
                        <div style="position: relative">
                        @if ($bladers[1]->imagen)
                                <img src="/storage/{{ $bladers[1]->imagen }}" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw; {{ strpos($bladers[1]->imagen, '.gif') !== false ? 'padding: 10px;' : '' }}">
                            @else
                                <img src="/storage/upload-profiles/Base/DranDagger.webp" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw;">
                            @endif
                            @if ($bladers[1]->marco)
                                <img src="/storage/{{ $bladers[1]->marco }}" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw;">
                            @else
                                <img src="/storage/upload-profiles/Marcos/BaseBlue.png" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw;">
                            @endif
                        </div>
                        <h3 style="color: white; margin-top: 120px">{{ $bladers[1]->user->name }}</h3>
                        <h2 style="color: white">{{ $bladers[1]->points_x2 }}<span style="font-size:0.5em">pts</span></h2>
                    </div>
                    <div class="col-md-2 text-center m-2">
                        <h4 class="font-weight-bold top-3 ranking-card mb-3">3º Puesto</h4>
                        <div style="position: relative">
                        @if ($bladers[2]->imagen)
                                <img src="/storage/{{ $bladers[2]->imagen }}" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw; {{ strpos($bladers[2]->imagen, '.gif') !== false ? 'padding: 10px;' : '' }}">
                            @else
                                <img src="/storage/upload-profiles/Base/DranDagger.webp" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw;">
                            @endif
                            @if ($bladers[2]->marco)
                                <img src="/storage/{{ $bladers[2]->marco }}" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw;">
                            @else
                                <img src="/storage/upload-profiles/Marcos/BaseBlue.png" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw;">
                            @endif
                    </div>
                        <h3 style="color: white; margin-top: 120px">{{ $bladers[2]->user->name }}</h3>
                        <h2 style="color: white">{{ $bladers[2]->points_x2 }}<span style="font-size:0.5em">pts</span></h2>
                    </div>
                    <div class="col-md-2 text-center m-2">
                        <h4 class="font-weight-bold ranking-card mb-3">4º Puesto</h4>
                        <div style="position: relative">
                        @if ($bladers[3]->imagen)
                                <img src="/storage/{{ $bladers[3]->imagen }}" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw; {{ strpos($bladers[3]->imagen, '.gif') !== false ? 'padding: 10px;' : '' }}">
                            @else
                                <img src="/storage/upload-profiles/Base/DranDagger.webp" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw;">
                            @endif
                            @if ($bladers[3]->marco)
                                <img src="/storage/{{ $bladers[3]->marco }}" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw;">
                            @else
                                <img src="/storage/upload-profiles/Marcos/BaseBlue.png" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw;">
                            @endif
                        </div>
                        <h3 style="color: white; margin-top: 120px">{{ $bladers[3]->user->name }}</h3>
                        <h2 style="color: white">{{ $bladers[3]->points_x2 }}<span style="font-size:0.5em">pts</span></h2>
                    </div>
                    <div class="col-md-2 text-center m-2">
                        <h4 class="font-weight-bold ranking-card mb-3">5º Puesto</h4>
                        <div style="position: relative">
                        @if ($bladers[4]->imagen)
                                <img src="/storage/{{ $bladers[4]->imagen }}" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw; {{ strpos($bladers[4]->imagen, '.gif') !== false ? 'padding: 10px;' : '' }}">
                            @else
                                <img src="/storage/upload-profiles/Base/DranDagger.webp" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw;">
                            @endif
                            @if ($bladers[4]->marco)
                                <img src="/storage/{{ $bladers[4]->marco }}" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw;">
                            @else
                                <img src="/storage/upload-profiles/Marcos/BaseBlue.png" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw;">
                            @endif
                        </div>
                        <h3 style="color: white; margin-top: 120px">{{ $bladers[4]->user->name }}</h3>
                        <h2 style="color: white">{{ $bladers[4]->points_x2 }}<span style="font-size:0.5em">pts</span></h2>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="container ranking-container">
        <h3 class="titulo-categoria text-uppercase mb-4 mt-4" style="color:white">Top Equipos</h3>

        @foreach($teams as $key => $team)
        <div class="team-card" style="background-image: url(data:image/png;base64,{{ $team->image }});">
            <div class="team-overlay"></div>
            <div class="team-entry">
                <div class="team-rank">#{{ $key + 1 }}</div>
                <img class="team-logo" src="@if($team->logo) data:image/png;base64,{{ $team->logo }} @else /images/logo_new.png @endif" alt="Logo de {{ $team->name }}">
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
                <!-- Imagen del perfil -->
                <img
                    src="/storage/{{ $bestUserProfile->profile->imagen ?? 'upload-profiles/Base/DranDagger.webp' }}"
                    class="rounded-circle position-absolute top-0 start-0 w-100 h-100"
                    style="{{ isset($bestUserProfile->profile->imagen) && strpos($bestUserProfile->profile->imagen, '.gif') !== false ? 'padding: 20px;' : '' }}"
                >

                <!-- Marco -->
                <img
                    src="/storage/{{ $bestUserProfile->profile->marco ?? 'upload-profiles/Marcos/BaseBlue.png' }}"
                    class="rounded-circle position-absolute top-0 start-0 w-100 h-100"
                >
            </div>
        </div>
    </div>
</div>



    <!-- Quiénes somos -->
    <div class="container my-5">
        <h3 class="text-center text-uppercase mb-4" style="color: #fff; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);">¿Quiénes Somos?</h3>
        <div class="row justify-content-center">
            <p class="text-center text-white mb-5" style="font-size: 1.1rem; color: #ccc; line-height: 1.8;">
                La SBBL (Spanish BeyBattle League) es una plataforma web no remunerada creada por un grupo de entusiastas de la comunidad de Beyblade España.
                Nuestra misión es hacer más fácil para los bladers encontrar otros jugadores cerca de su zona de residencia y participar en un ranking nacional donde puedan poner a prueba sus habilidades.
                Todo esto en un ambiente colaborativo, sin fines de lucro, enfocado en fortalecer la comunidad Beyblade en nuestro país.
            </p>
            @foreach ($usuarios as $usuario)
            <div class="col-md-4 text-center p-2">
                <div class="position-relative d-flex flex-column align-items-center user-card" style="background: #333; border-radius: 10px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3); transition: transform 0.3s ease;">
                    <div class="position-relative">
                        @if ($usuario->profile->marco)
                            <img src="/storage/{{ $usuario->profile->marco }}" class="rounded-circle" width="110" height="110" style="position: relative; top: -5px; left: -5px; z-index: 2">
                        @else
                            <img src="/storage/upload-profiles/Marcos/BaseBlue.png" class="rounded-circle" width="110" height="110" style="position: relative; top: -5px; left: -5px; z-index: 2">
                        @endif
                        @if ($usuario->profile->imagen)
                            <img src="/storage/{{ $usuario->profile->imagen }}" class="rounded-circle" width="100" height="100" style="position: absolute; top: 0; left: 0; z-index: 1;{{ strpos($usuario->profile->imagen, '.gif') !== false ? 'padding: 10px;' : '' }}">
                        @else
                            <img src="/storage/upload-profiles/Base/DranDagger.webp" class="rounded-circle" width="100" height="100" style="position: absolute; top: 0; left: 0; z-index: 1;">
                        @endif
                    </div>
                    <h3 class="user-name" style="color: white; margin-top: 10px; font-size: 1.5rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);">{{ $usuario->name }}</h3>
                    <!-- Subtítulo personalizado -->
                    <p class="user-title" style="color: #ccc; font-size: 1rem; margin-top: 5px; font-style: italic;">{{ $usuario->titulo ?? 'Jugador de élite' }}</p>
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
            <!--<div class="row m-0">
                <h2 id="heat-map" class="titulo-categoria text-uppercase mb-4 mt-4" style="color:white">Mapa de calor</h2>

                <div id="map" style="width: 100%; height: 500px;"></div>
            </div>-->
    </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        /* Crear el mapa centrado en España y ajustado para incluir todas las regiones
        var map = L.map('map', {
            center: [40.5, -3], // Centrado en España
            zoom: 6, // Aumentar el zoom para un detalle más cercano
            zoomControl: false, // Ocultar controles de zoom
            dragging: false, // Desactivar arrastre
            scrollWheelZoom: false, // Desactivar zoom con la rueda del ratón
            doubleClickZoom: false, // Desactivar zoom con doble clic
            touchZoom: false, // Desactivar zoom táctil en móviles
            attributionControl: false // Ocultar créditos de OpenStreetMap
        });

        // Cargar el GeoJSON con los límites de las comunidades autónomas (incluye Canarias, Ceuta y Melilla)
        fetch("https://raw.githubusercontent.com/codeforamerica/click_that_hood/master/public/data/spain-communities.geojson")
            .then(response => response.json())
            .then(geojsonData => {
                L.geoJSON(geojsonData, {
                    style: function () {
                        return {
                            color: "#000", // Borde negro
                            weight: 1, // Grosor de las líneas
                            fillColor: "#ccc", // Color de fondo
                            fillOpacity: 0.5 // Transparencia del fondo
                        };
                    }
                }).addTo(map);

                // Ajustar el mapa a los límites de las comunidades autónomas
                var bounds = L.geoJSON(geojsonData).getBounds();
                map.fitBounds(bounds); // Ajusta la vista para que incluya todo
            });

        // Coordenadas de cada comunidad autónoma
        var coordenadas = {
            "Andalucía": [37.3891, -5.9845],
            "Aragón": [41.6519, -0.8773],
            "Asturias": [43.3614, -5.8593],
            "Baleares": [39.6953, 3.0176],
            "Canarias": [28.2916, -16.6291], // Posición real de Canarias
            "Cantabria": [43.4623, -3.8099],
            "Castilla La Mancha": [39.00, -3.2],
            "Castilla y León": [41.8357, -4.3976],
            "Catalunya": [41.5912, 1.5209],
            "Extremadura": [39.4833, -6.3723],
            "Galicia": [42.5751, -8.1339],
            "Madrid": [40.4165, -3.70256],
            "Murcia": [37.9922, -1.1307],
            "Navarra": [42.6954, -1.6761],
            "La Rioja": [42.2871, -2.5396],
            "País Vasco": [43.0853, -2.4937],
            "Valencia": [39.4699, -0.3763],
            "Ceuta": [35.8894, -5.3199],
            "Melilla": [35.2923, -2.9381]
        };

        // Obtener los datos desde Laravel
        var datos = @json($usuariosPorComunidad);

        // Agregar etiquetas con la cantidad de usuarios
        datos.forEach(function (dato) {
            var coord = coordenadas[dato.comunidad_autonoma];
            if (coord) {
                L.marker(coord, { icon: L.divIcon({
                        className: 'custom-label',
                        html: `<b>${dato.total}</b>`,
                        iconSize: [30, 30]
                    })
                }).addTo(map);
            }
        });

        // Mini mapa que muestra Canarias en la esquina inferior izquierda
        var miniMap = new L.Control.MiniMap(
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }),
            {
                position: 'bottomleft', // Coloca el mini mapa en la esquina inferior izquierda
                width: 150,
                height: 150,
                zoomLevelOffset: -5, // Ajusta el zoom del mini mapa para mostrar toda España
            }
        ).addTo(map);
    });
    */

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
