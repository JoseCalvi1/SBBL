@extends('layouts.app')

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
</style>
<head>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-mini-map/dist/Control.MiniMap.css" />
    <script src="https://unpkg.com/leaflet-mini-map/dist/Control.MiniMap.js"></script>
</head>
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
    <div class="navbar navbar-expand-lg" style="background-color: #283b63;">
        <button class="navbar-toggler d-flex justify-content-center align-items-center w-100 h-100 d-lg-none"
        type="button" data-toggle="collapse" data-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation"
        style="background: none; border: none;">
    <i class="fas fa-cog fa-2x text-white"></i>
</button>

        <div class="collapse navbar-collapse" id="navbarNav" style="padding: unset;">
            <ul class="navbar-nav m-auto flex-column flex-lg-row">
                <!-- Usamos flex-column en móviles (pantallas pequeñas) para hacer el menú vertical -->
                <li class="nav-item">
                    <a class="nav-link ml-2 mr-2" style="color: white; font-weight: bold; font-size:1.2em;" href="{{ route('inicio.events') }}">
                        {{ 'EVENTOS' }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link ml-2 mr-2" style="color: white; font-weight: bold; font-size:1.2em;" href="{{ route('versus.all') }}">
                        {{ 'DUELOS' }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link ml-2 mr-2" style="color: white; font-weight: bold; font-size:1.2em;" href="{{ route('profiles.index') }}">
                        {{ 'BLADERS' }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link ml-2 mr-2" style="color: white; font-weight: bold; font-size:1.2em;" href="{{ route('profiles.ranking') }}">
                        {{ 'RANKING' }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link ml-2 mr-2" style="color: white; font-weight: bold; font-size:1.2em;" href="{{ route('inicio.stats') }}">
                        {{ 'STATS' }}
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <!--<div>
        <img src="../images/bannersbbl2.png" class="w-100">
    </div>-->
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
                <h3 class="titulo-categoria text-uppercase mb-4 mt-4" style="color:white">Top 5 SBBL X</h3>
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
                        <h2 style="color: white">{{ $bladers[0]->points_x1 }}<span style="font-size:0.5em">pts</span></h2>
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
                        <h2 style="color: white">{{ $bladers[1]->points_x1 }}<span style="font-size:0.5em">pts</span></h2>
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
                        <h2 style="color: white">{{ $bladers[2]->points_x1 }}<span style="font-size:0.5em">pts</span></h2>
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
                        <h2 style="color: white">{{ $bladers[3]->points_x1 }}<span style="font-size:0.5em">pts</span></h2>
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
                        <h2 style="color: white">{{ $bladers[4]->points_x1 }}<span style="font-size:0.5em">pts</span></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4" id="nacional">
            <div class="col-md-12 text-white text-center p-4" style="border: 1px solid #1e2a47; border-radius: 5px; background:#1e2a47; box-shadow: 0px 0px 10px rgba(255, 255, 255, 0.5)">
                <h2 class="text-lg font-bold mb-2">OBJETIVO NACIONAL SBBL</h2>
                <div class="relative w-full h-6 rounded-full" style="height: 20px; border: 2px solid">
                    <div class=" bg-white h-full rounded-full" style="width: 45%; height: 18px"></div>
                </div>
                <p class="text-sm mt-2">1370€ / 3000€</p>

                <div class="mt-2">
                    <p><strong>¿Aún no tienes una suscripción?</strong> Consíguela fácilmente haciendo clic en
                        <a href="https://sbbl.es/subscriptions" style="color: #007bff; font-weight: bold;">este enlace</a>.</p>

                    <p>También puedes <strong>regalar una suscripción</strong> a un amigo, indicando su nombre de usuario y el nivel que deseas obsequiar.</p>

                    <p>Si prefieres <strong>apoyar esta iniciativa</strong>, puedes contribuir directamente
                        <a href="https://www.paypal.com/paypalme/sbbloficial" style="color: #28a745; font-weight: bold;">aquí</a>.</p>
                </div>
                <div class="mt-4">
                    <h3 class="text-md font-semibold">Aclaraciones</h3>
                    <div class="w-full h-24 bg-gray-100 flex items-center justify-center text-gray-500">
                        <p>El objetivo es para crear el nacional en las instalaciones de Movistar KOI en Madrid con todo tipo de ayudas y herramientas, premios sorprendentes y mucho mas con lo que se ha recaudado íntegramente de las suscripciones a la web, el merchandising y otras cosas que iremos anunciando.</p>
                        <p>En el caso de no llegar al objetivo se barajarían diferentes opciones para el desarrollo del torneo pudiendo incluso no ser en Madrid ya que tenemos otras ofertas más asequibles pero también muy buenas.</p>
                        <p>Con todo esto lo que queremos hacer es crear el mayor torneo que llevamos hasta la fecha y que sea una experiencia inolvidable para todos.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="container" style="background-color: #1e2a47; color: white;">

        <div class="row border-bottom border-white" style="background-color: #283b63;">
            <div class="col-md-12 text-center pt-2">
                <h2 style="font-size: 1.5em; font-weight: bold;">¿Cómo participo en la liga?</h2>
            </div>
            <div class="col-md-4">
                <div class="rrss text-center p-4">
                    <a style="display: inline-block; font-size: 1.2em; font-weight: bold; text-decoration: none; color: rgb(173, 159, 7);" target="_blank" href="{{ route('inicio.events') }}">
                        <i class="fa fa-sitemap" style="font-size: 4em;"></i><br>Participa en<br><b>Torneos</b>
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="rrss text-center p-4">
                    <a style="display: inline-block; font-size: 1.2em; font-weight: bold; text-decoration: none; color: rgb(160, 0, 0);" target="_blank" href="{{ route('versus.all') }}">
                        <i class="fa fa-trophy" style="font-size: 4em;"></i><br>Compite en<br><b>Duelos</b>
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="rrss text-center p-4">
                    <a style="display: inline-block; font-size: 1.2em; font-weight: bold; text-decoration: none; color: rgb(166, 1, 207);" target="_blank" href="#">
                        <i class="fa fa-star" style="font-size: 4em;"></i><br>Torneos<br><b>Especiales</b>
                    </a>
                </div>
            </div>
        </div>

        <div id="bladerofthemonth" class="row border-bottom border-white" style="background-color: rgb(59, 79, 148);">
            <div class="col-md-9 text-white text-center p-4" style="font-size: 1.2em; font-weight: bold; line-height: 1">
                <h2 style="font-size: 1.5em; font-weight: bold;">
                    BLADER DEL MES {{ $lastMonthName ?? '' }} {{ $lastYear ?? '' }}
                </h2>
                <p>
                    ¡El mes pasado el blader con la mayor cantidad de puntos obtenidos fue {{ $bestUserProfile->name ?? '' }} de {{ $bestUserProfile->profile->region->name ?? '' }}!
                </p>
                <p>
                    Nada más y nada menos que con un total de {{ $bestUser->total_puntos ?? '' }}
                </p>
                <p>
                    Su mejor combo fue {{ $bestUserRecord->blade ?? '' }} {{ $bestUserRecord->ratchet ?? '' }} {{ $bestUserRecord->bit ?? '' }}
                </p>
                <p>
                    Con el que consiguió un total de {{ $bestUserRecord->puntos_ganados ?? '' }} puntos en {{ $bestUserRecord->victorias ?? '' }} victorias
                </p>
            </div>
            <div class="col-md-3 text-center text-white p-4 d-none d-sm-block" style="border: unset;">
                <div style="position: relative">
                    @if ($bestUserProfile->profile->imagen)
                        <img src="/storage/{{ $bestUserProfile->profile->imagen }}" class="rounded-circle" width="180" style="position: absolute; top: 0; left: 0;{{ strpos($bestUserProfile->profile->imagen, '.gif') !== false ? 'padding: 20px;' : '' }}">
                    @else
                        <img src="/storage/upload-profiles/Base/DranDagger.webp" class="rounded-circle" width="180" style="position: absolute; top: 0; left: 0;">
                    @endif
                    @if ($bestUserProfile->profile->marco)
                        <img src="/storage/{{ $bestUserProfile->profile->marco }}" class="rounded-circle" width="180" style="position: absolute; top: 0; left: 0;">
                    @else
                        <img src="/storage/upload-profiles/Marcos/BaseBlue.png" class="rounded-circle" width="180" style="position: absolute; top: 0; left: 0;">
                    @endif
                </div>
            </div>
        </div>

        <div class="row" style="background-color: #283b63;">
            <div class="col-md-3">
                <div class="rrss text-center p-4">
                    <a style="display: inline-block; font-size: 1.2em; font-weight: bold; text-decoration: none; color: rgb(66, 31, 243);" target="_blank" href="https://discord.gg/JCtAHfJ8Ht">
                        <i class="fab fa-discord" style="font-size: 4em;"></i><br>Únete a nuestro<br><b>Discord</b>
                    </a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="rrss text-center p-4">
                    <a style="display: inline-block; font-size: 1.2em; font-weight: bold; text-decoration: none; color: rgb(160, 0, 112);" target="_blank" href="https://www.instagram.com/sbbl_oficial/">
                        <i class="fab fa-instagram" style="font-size: 4em;"></i><br>Síguenos en<br><b>Instagram</b>
                    </a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="rrss text-center p-4">
                    <a style="display: inline-block; font-size: 1.2em; font-weight: bold; text-decoration: none; color: rgb(207, 1, 1);" target="_blank" href="https://www.youtube.com/@sbbl_oficial">
                        <i class="fab fa-youtube" style="font-size: 4em;"></i><br> Suscríbete en <br><b>YouTube</b>
                    </a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="rrss text-center p-4">
                    <a style="display: inline-block; font-size: 1.2em; font-weight: bold; text-decoration: none; color: rgb(66, 31, 243);" target="_blank" href="https://x.com/SBBLOficial">
                        <i class="fab fa-twitter" style="font-size: 4em;"></i><br>Síguenos en<br><b>Twitter</b>
                    </a>
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
            <div class="row m-0">
                <h2 id="heat-map" class="titulo-categoria text-uppercase mb-4 mt-4" style="color:white">Mapa de calor</h2>

                <div id="map" style="width: 100%; height: 500px;"></div>
            </div>
    </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Crear el mapa centrado en España y ajustado para incluir todas las regiones
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
</script>
@endsection
