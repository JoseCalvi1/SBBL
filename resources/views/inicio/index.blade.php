@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" integrity="sha512-tS3S5qG0BlhnQROyJXvNjeEM4UpMXHrQfTGmbQ1gKmelCxlSEBUaxhRBj/EFTzpbP4RVSrpEikbmdJobCvhE3g==" crossorigin="anonymous" />
@endsection

@section('content')
    <div>
        <img src="../images/banner.jpg" class="w-100">
    </div>

    <div class="container nuevas-recetas">
        <h2 class="titulo-categoria text-uppercase mb-4 mt-4">Próximos eventos</h2>

        <div class="owl-carousel owl-theme">
                @foreach ($nuevos as $nuevo)
                        <div class="card">
                            <img src="/storage/{{ $nuevo->imagen }}"  class="card-img-top">

                            <div class="card-body">
                                <h3>{{ $nuevo->name }}</h3>

                                <p>{{ $nuevo->location }}</p>
                                <p><event-date fecha="{{ $nuevo->date }}"></event-date></p>

                                <a href="{{ route('events.show', ['event' => $nuevo->id]) }}" class="btn btn-primary d-block font-weight-bold text-uppercase">Ver evento</a>
                            </div>
                        </div>
                @endforeach
        </div>
    </div>

    <div class="container nuevas-recetas">
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

    <div class="container-fluid nuevas-recetas" style="background-color: #a7a4a4">
        <div class="row">
            <div class="col-md-6">
                <h2 class="titulo-categoria text-uppercase my-4 mx-5 text-white" style="border-bottom: 0px !important; font-weight:bold;">Sobre nosotros</h2>
                <p class="my-4 mx-5 text-white" style="font-size: 1.2em; font-weight:bold;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse a elit vel tortor condimentum imperdiet. Quisque sodales convallis nisl, at egestas sem finibus a. Sed venenatis mauris massa, et auctor orci lobortis quis.
                    Curabitur consequat tempus blandit. Morbi id sem a magna dapibus consectetur. Nunc a eleifend neque, et placerat massa. Nulla ut porta dui, non sagittis diam. Q
                    uisque suscipit enim in ipsum mattis, quis efficitur urna viverra. Vestibulum varius eget nulla egestas finibus. Morbi sodales vel nulla at eleifend. Quisque molestie euismod libero, id laoreet nunc dictum at.</p>
            </div>
            <div class="col-md-6">
                <img src="../images/sbbl.png" class="w-75">
            </div>
        </div>
    </div>
@endsection
