@extends('layouts.app')

@section('head')
    <meta name="description" content="Organización de BeyBattle España"/>
    <meta name="keywords" content="sbbl, beyblade, españa, torneo, liga, discord, app, web, evento, ranking, español, hasbro, takara, tomy, burst"/>
    <meta name="author" content="José A. Calvillo Olmedo" />
    <meta name="copyright" content="SBBL - José A. Calvillo Olmedo" />
@endsection

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" integrity="sha512-tS3S5qG0BlhnQROyJXvNjeEM4UpMXHrQfTGmbQ1gKmelCxlSEBUaxhRBj/EFTzpbP4RVSrpEikbmdJobCvhE3g==" crossorigin="anonymous" />
@endsection

@section('content')
<div class="container-fluid" style="background-image: url('../images/webTile2.png'); background-size: 20%; background-repeat: repeat; background-position: center; padding: 0px;">
    <div class="container-fluid" style="background: darkblue">

@if ((Auth::user() && !Auth::user()->profile->region))
<div class="row text-center" style="background-color: red; color: white; padding: 20px;">
   <p class="text-center" style="margin-bottom: 0;">TODAVÍA NO HAS SELECCIONADO TU COMUNIDAD AUTÓNOMA. HAZLO EN <a style="color: yellow; font-weight: bold;" href="{{ route('profiles.edit', ['profile' => Auth::user()->id]) }}"> ESTE ENLACE</a></p>
</div>
@endif

        <div class="row">
            <ul class="navbar-nav m-auto" style="flex-direction: row;">
                <li class="nav-item">
                    <a class="nav-link ml-2 mr-2" style="color: white; font-weight: bold; font-size:1.2em;" href="{{ route('inicio.index') }}">
                        {{ 'INICIO' }}
                    </a>
                </li>
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
                    <a class="nav-link ml-2 mr-2" style="color: white; font-weight: bold; font-size:1.2em;" href="{{ route('mercado.index') }}">
                        {{ 'MERCADO' }}
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
                <div class="card">
                    @if ($evento->image_mod)
                    <span style="width: 100%; min-height: 180px; background: url('data:image/png;base64,{{ $evento->image_mod }}') bottom center no-repeat;background-size: cover;"></span>
                    @else
                    <span style="width: 100%; min-height: 180px; background: url('/storage/{{ $evento->imagen }}') bottom center no-repeat;background-size: cover;"></span>
                    @endif
                    <div class="card-body">
                        <h3 style="font-weight: bold;">{{ $evento->name }}</h3>
                        <h3>{{ $evento->region->name }}</h3>
                        <p><event-date fecha="{{ $evento->date }}"></event-date></p>
                    </div>
                    <a href="{{ route('events.show', ['event' => $evento->id]) }}" class="d-block font-weight-bold text-uppercase pt-2 pb-2" style="text-decoration: none; color:white;width: 100%; background-color:rgb(87, 170, 244);">Ver evento</a>
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
                                <img src="/storage/{{ $bladers[0]->imagen }}" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw;">
                            @else
                                <img src="/storage/upload-profiles/DranDaggerBase.png" class="rounded-circle" width="100" style="position: absolute; : 0; left: 1.2vw;">
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
                                <img src="/storage/{{ $bladers[1]->imagen }}" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw;">
                            @else
                                <img src="/storage/upload-profiles/DranDaggerBase.png" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw;">
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
                                <img src="/storage/{{ $bladers[2]->imagen }}" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw;">
                            @else
                                <img src="/storage/upload-profiles/DranDaggerBase.png" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw;">
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
                                <img src="/storage/{{ $bladers[3]->imagen }}" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw;">
                            @else
                                <img src="/storage/upload-profiles/DranDaggerBase.png" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw;">
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
                                <img src="/storage/{{ $bladers[4]->imagen }}" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw;">
                            @else
                                <img src="/storage/upload-profiles/DranDaggerBase.png" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 1.2vw;">
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
    </div>
            <div class="container">

                <div class="row border-bottom" style="background-color: whitesmoke">
                    <div class="col-md-12 text-center pt-2"><h2 style="font-size: 2em; font-weight:bold;">¿Cómo participo en la liga?</h2></div>
                    <div class="col-md-4">
                        <div class="rrss text-center p-4">
                            <a style="display: inline-block; font-size:1.2em; font-weight: bold; text-decoration:none; color: rgb(173, 159, 7);" target="_blank" href="{{ route('inicio.events') }}"><i class="fa fa-sitemap" style="font-size:4em;"></i> <br>Participa en<br><b>Torneos</b></a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="rrss text-center p-4">
                            <a style="display: inline-block; font-size:1.2em; font-weight: bold; text-decoration:none; color: rgb(160, 0, 0);" target="_blank" href="{{ route('versus.all') }}"><i class="fa fa-trophy" style="font-size:4em;"></i> <br>Compite en<br><b>Duelos</b></a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="rrss text-center p-4">
                            <a style="display: inline-block; font-size:1.2em; font-weight: bold; text-decoration:none; color: rgb(166, 1, 207);" target="_blank" href="#"><i class="fa fa-star" style="font-size:4em;"></i> <br>Torneos<br><b>Especiales</b></a>
                        </div>
                    </div>
                </div>

                <div id="bladerofthemonth" class="row" style="background-color: rgb(205, 127, 50)">
                    <div class="col-md-9 text-white text-center p-4" style="font-size: 1.2em; font-weight:bold; line-height: 1">
                        <!--<h2 style="font-size: 2em; font-weight:bold;">ENTREVISTAS A NUESTROS CAMPEONES</h2>
                        <p>Con la inminente llegada de las nuevas temporadas de Beyblade X y Burst hemos querido preparar una cosa</p>
                        <p>Así que para despedirnos de la segunda temporada de Burst hemos hecho dos entrevistas con los campeones de cada temporada</p>
                        <p>Podéis leer sus opiniones en <a style="text-decoration: none; color: black" href="{{ route('inicio.entrevistas') }}">el siguiente apartado</a></p>-->
                        <h2 style="font-size: 2em; font-weight:bold;">
                            BLADER DEL MES {{ $lastMonthName ?? '' }} {{ $lastYear ?? '' }}
                        </h2>
                        <p>
                            ¡El mes pasado el blader con la mayor cantidad de puntos obtenidos fue {{ $bestUserProfile->name ?? '' }}
                            de {{ $bestUserProfile->profile->region->name ?? '' }}!
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
                    <div class="col-md-3 text-center text-white p-4" style="border: unset;">
                        <div style="position: relative">
                            @if ($bestUserProfile->profile->imagen)
                                    <img src="/storage/{{ $bestUserProfile->profile->imagen }}" class="rounded-circle" width="180" style="position: absolute; top: 0; left: 0;">
                                @else
                                    <img src="/storage/upload-profiles/DranDaggerBase.png" class="rounded-circle" width="180" style="position: absolute; top: 0; left: 0;">
                                @endif
                                @if ($bestUserProfile->profile->marco)
                                    <img src="/storage/{{ $bestUserProfile->profile->marco }}" class="rounded-circle" width="180" style="position: absolute; top: 0; left: 0;">
                                @else
                                    <img src="/storage/upload-profiles/Marcos/BaseBlue.png" class="rounded-circle" width="180" style="position: absolute; top: 0; left: 0;">
                                @endif
                        </div>
                    </div>
                </div>

                <div class="row border-bottom" style="background-color: whitesmoke">
                    <div class="col-md-4">
                        <div class="rrss text-center p-4">
                            <a style="display: inline-block; font-size:1.2em; font-weight: bold; text-decoration:none; color: rgb(66, 31, 243);" target="_blank" href="https://discord.gg/JCtAHfJ8Ht"><i class="fab fa-discord" style="font-size:4em;"></i> <br>Únete a nuestro<br><b>Discord</b></a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="rrss text-center p-4">
                            <a style="display: inline-block; font-size:1.2em; font-weight: bold; text-decoration:none; color: rgb(160, 0, 112);" target="_blank" href="https://www.instagram.com/sbbl_oficial/"><i class="fab fa-instagram" style="font-size:4em;"></i> <br>Síguenos en<br><b>Instagram</b></a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="rrss text-center p-4">
                            <a style="display: inline-block; font-size:1.2em; font-weight: bold; text-decoration:none; color: rgb(207, 1, 1);" target="_blank" href="https://www.youtube.com/channel/UCMXJL2jR3ev0CNbhPrfSOwQ"><i class="fab fa-youtube" style="font-size:4em;"></i> <br> Suscríbete en <br><b>YouTube</b></a>
                        </div>
                    </div>
                </div>
            </div>

    <div class="container mt-2">

            <div class="row m-0">
                <h2 class="titulo-categoria text-uppercase mb-4 mt-4" style="color:white">Eventos realizados</h2>

                <div class="owl-carousel owl-theme">
                        @foreach ($antiguos as $antiguo)
                                <div class="card">
                                    <img src="/storage/{{ $antiguo->imagen }}"  class="card-img-top">

                                    <div class="card-body">
                                        <h3>{{ $antiguo->name }}</h3>

                                        <p>{{ $antiguo->location }}</p>
                                        <p><event-date fecha="{{ $antiguo->date }}"></event-date></p>

                                        <a href="{{ route('events.show', ['event' => $antiguo->id]) }}" class="btn btn-primary d-block font-weight-bold text-uppercase">Ver evento</a>
                                    </div>
                                </div>
                        @endforeach
                </div>
            </div>
    </div>
    </div>
</div>

@endsection
