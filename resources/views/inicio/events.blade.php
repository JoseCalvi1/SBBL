@extends('layouts.app')

@section('content')
    <div class="container nuevas-recetas">
        <h2 class="titulo-categoria text-uppercase mb-4 mt-4">Eventos realizados</h2>

        <div class="row">
                @foreach ($antiguos as $antiguo)
                        <div class="col-md-4 card m-2" style="padding:0px !important; max-width: 30%;">
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

@endsection
