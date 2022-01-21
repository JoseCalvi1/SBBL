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
    <div class="col-md-8 p-2">
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
<div class="col-md-1"></div>
<div class="col-md-3 nuevas-recetas">
    <h2 class="titulo-categoria text-uppercase mb-4 mt-4">Top 5 SBBL</h2>
    <div style="width: 100%;color:rgb(196, 196, 2);border:2px solid rgb(196, 196, 2);"><span style="font-weight:bold;border-right:2px solid rgb(196, 196, 2);padding:2px 4px">1º</span><span style="padding: 2px 4px;">{{ $bladers[0]->user->name }} <span style="float: right;padding-right:2px;">{{ $bladers[0]->points }} ptos</span></span></div>
    <div style="width: 100%;color:rgb(71, 71, 70);border:2px solid rgb(71, 71, 70);"><span style="font-weight:bold;border-right:1px solid rgb(71, 71, 70);padding:2px 4px">2º</span><span style="padding: 2px 4px;">{{ $bladers[1]->user->name }} <span style="float: right;padding-right:2px;">{{ $bladers[1]->points }} ptos</span></span></div>
    <div style="width: 100%;color:rgb(126, 77, 3);border:2px solid rgb(126, 77, 3);"><span style="font-weight:bold;border-right:1px solid rgb(126, 77, 3);padding:2px 4px">3º</span><span style="padding: 2px 4px;">{{ $bladers[2]->user->name }} <span style="float: right;padding-right:2px;">{{ $bladers[2]->points }} ptos</span></span></div>
    <div style="width: 100%;color:rgb(36, 35, 35);border:2px solid rgb(36, 35, 35);"><span style="font-weight:bold;border-right:1px solid rgb(36, 35, 35);padding:2px 4px">4º</span><span style="padding: 2px 4px;">{{ $bladers[3]->user->name }} <span style="float: right;padding-right:2px;">{{ $bladers[3]->points }} ptos</span></span></div>
    <div style="width: 100%;color:rgb(36, 35, 35);border:2px solid rgb(36, 35, 35);"><span style="font-weight:bold;border-right:1px solid rgb(36, 35, 35);padding:2px 4px">5º</span><span style="padding: 2px 4px;">{{ $bladers[4]->user->name }} <span style="float: right;padding-right:2px;">{{ $bladers[4]->points }} ptos</span></span></div>

    <h2 class="titulo-categoria text-uppercase mb-4 mt-4">Top Stamina</h2>
    <div style="width: 100%;color:rgb(196, 196, 2);border:2px solid rgb(196, 196, 2);"><span style="font-weight:bold;border-right:2px solid rgb(196, 196, 2);padding:2px 4px">1º</span><span style="padding: 2px 4px;">{{ $stamina->user->name }} <span style="float: right;padding-right:2px;">TIEMPO</span></span></div>
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
