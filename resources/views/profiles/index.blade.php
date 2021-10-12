@extends('layouts.app')

@section('content')

    <div class="container">
        <h2 class="titulo-categoria text-uppercase mb-4 mt-4">Bladers</h2>

        <div class="row">
            @php
                $supremo = 0;
            @endphp
                @foreach ($bladers as $blader)
                        <div class="card col-md-3 py-2 mb-2" style="{{ ($supremo < 4) ? 'border:2px solid #FFDC00;' : '' }}">
                            @if ($blader->imagen)
                                <img src="/storage/{{ $blader->imagen }}"  class="card-img-top">
                            @else
                                <img src="../images/default_user.jpg"  class="card-img-top">
                            @endif

                            <div class="card-body">
                                <h3 class="text-center"><b style="color:#FFDC00;padding">{{ ($supremo < 4) ? '4 SUPREMOS' : '' }}</b></h3>
                                <h3><b>{{ $blader->user->name }}</b></h3>
                                <h4>Región: {{ ($blader->region) ? $blader->region->name : 'No definida'}}</h4>
                                <h4>Puntos: {{ $blader->points }}</h4>
                            </div>
                        </div>
                        @php
                            $supremo += 1;
                        @endphp
                @endforeach
        </div>
    </div>

@endsection
