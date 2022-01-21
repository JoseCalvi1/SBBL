@extends('layouts.app')

@section('head')
    <meta name="description" content="Organización oficial de Beyblade España"/>
    <meta name="keywords" content="sbbl, beyblade, españa, torneo, liga, discord, app, web, evento, ranking, español, hasbro, takara, tomy, burst"/>
    <meta name="author" content="José A. Calvillo Olmedo" />
    <meta name="copyright" content="SBBL - José A. Calvillo Olmedo" />
@endsection

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" integrity="sha512-tS3S5qG0BlhnQROyJXvNjeEM4UpMXHrQfTGmbQ1gKmelCxlSEBUaxhRBj/EFTzpbP4RVSrpEikbmdJobCvhE3g==" crossorigin="anonymous" />
@endsection

@section('content')
    <div>
        <img src="../images/banner.jpg" class="w-100">
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-12 p-2">
            <div class="row m-0">
                <h2 class="titulo-categoria text-uppercase mb-4 mt-4">Próximos eventos</h2>

                <div class="owl-carousel owl-theme">
                        @foreach ($nuevos as $nuevo)
                                <div class="card">
                                    <img src="/storage/{{ $nuevo->imagen }}"  class="card-img-top">

                                    <div class="card-body">
                                        <h3>{{ $nuevo->name }}</h3>

                                        <p>{{ $nuevo->location }} <b>({{ $nuevo->region->name }})</b></p>
                                        <p><event-date fecha="{{ $nuevo->date }}"></event-date></p>

                                        <a href="{{ route('events.show', ['event' => $nuevo->id]) }}" class="btn btn-primary d-block font-weight-bold text-uppercase">Ver evento</a>
                                    </div>
                                </div>
                        @endforeach
                </div>
            </div>

            <div class="row m-0">
                <h2 class="titulo-categoria text-uppercase mb-4 mt-4">Eventos realizados</h2>

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
        <div class="col-md-12 text-center">
            <h3 class="titulo-categoria text-uppercase mb-4 mt-4">Top 5 SBBL</h3>
            <div class="row" style="align-items: center;display: flex;justify-content: center;">
                <div class="col-md-2 text-center top-1 ranking-card m-2">
                    <h2 class="font-weight-bold">1º</h2>
                    <h3>{{ $bladers[0]->user->name }}</h3>
                    <h4>{{ ($bladers[0]->region) ? $bladers[0]->region->name : 'No definida'}}</h4>
                    <h2>{{ $bladers[0]->points }}<span style="font-size:0.5em">pts</span></h2>
                </div>
                <div class="col-md-2 text-center top-2 ranking-card m-2">
                    <h2 class="font-weight-bold">2º</h2>
                    <h3>{{ $bladers[1]->user->name }}</h3>
                    <h4>{{ ($bladers[1]->region) ? $bladers[1]->region->name : 'No definida'}}</h4>
                    <h2>{{ $bladers[1]->points }}<span style="font-size:0.5em">pts</span></h2>
                </div>
                <div class="col-md-2 text-center top-3 ranking-card m-2">
                    <h2 class="font-weight-bold">3º</h2>
                    <h3>{{ $bladers[2]->user->name }}</h3>
                    <h4>{{ ($bladers[2]->region) ? $bladers[2]->region->name : 'No definida'}}</h4>
                    <h2>{{ $bladers[2]->points }}<span style="font-size:0.5em">pts</span></h2>
                </div>
                <div class="col-md-2 text-center ranking-card m-2">
                    <h2 class="font-weight-bold">4º</h2>
                    <h3>{{ $bladers[3]->user->name }}</h3>
                    <h4>{{ ($bladers[3]->region) ? $bladers[3]->region->name : 'No definida'}}</h4>
                    <h2>{{ $bladers[3]->points }}<span style="font-size:0.5em">pts</span></h2>
                </div>
                <div class="col-md-2 text-center ranking-card m-2">
                    <h2 class="font-weight-bold">5º</h2>
                    <h3>{{ $bladers[4]->user->name }}</h3>
                    <h4>{{ ($bladers[4]->region) ? $bladers[4]->region->name : 'No definida'}}</h4>
                    <h2>{{ $bladers[4]->points }}<span style="font-size:0.5em">pts</span></h2>
                </div>
            </div>

            <h3 class="titulo-categoria text-uppercase mb-4 mt-4">Categorías especiales</h3>
            <div class="row" style="align-items: center;display: flex;justify-content: center;">
                <div class="col-md-2 text-center top-1 ranking-card m-2">
                    <h2 class="font-weight-bold">Top Stamina</h2>
                    <h3>{{ $stamina->user->name }}</h3>
                    <h4>{{ ($stamina->region) ? $stamina->region->name : 'No definida'}}</h4>
                    <h2>2:43:08</span></h2>
                </div>
            </div>
        </div>
    </div>
</div>

    <div class="container-fluid nuevas-recetas my-2 pb-2" style="background-color: #a7a4a4">
        <div class="row">
            <div class="text-center">
                <div class="my-4 mx-5 text-white" style="font-size: 1.2em; font-weight:bold;"><h3><b>¡Hola bladers!</b></h3>

                    <br>Bienvenidos a la Spanish Beyblade Battling League, o más sencillo, la SBBL.

                    <br><br>La SBBL es una liga/organización creada con el objetivo de reunir a todos los bladers residentes en España dentro de una comunidad más grande y fuerte.

                    Con esto intentaríamos conseguir llevar a cabo mayor número de eventos, quedadas y torneos en todo nuestro país.

                    <br><br>Si eres blader, resides en España y buscas una liga donde competir y disfrutar del beyblade con otrxs como tú, este es tu sitio.

                    <br><br>!Únete a la SBBL!</p>
                </div>
                    <a style="display: inline-block; font-size:2em; font-weight: bold; text-decoration:none; color: white;" target="_blank" href="https://discord.gg/ve7dgpCF9x"><i class="fab fa-discord"></i> Discord SBBL</a>&nbsp;&nbsp;&nbsp;
                    <a style="display: inline-block; font-size:2em; font-weight: bold; text-decoration:none; color: white;" target="_blank" href="https://www.instagram.com/sbbl_oficial/"><i class="fab fa-instagram"></i>  Instagram SBBL</a>&nbsp;&nbsp;&nbsp;
                    <a style="display: inline-block; font-size:2em; font-weight: bold; text-decoration:none; color: white;" target="_blank" href=""><i class="fab fa-twitter"></i>  Twitter SBBL</a>
            </div>
        </div>
    </div>
@endsection
