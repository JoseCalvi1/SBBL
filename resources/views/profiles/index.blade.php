@extends('layouts.app')

@section('content')

    <div class="container">
        <h2 class="titulo-categoria text-uppercase mb-4 mt-4">Bladers</h2>

        <div class="row">
                @foreach ($bladers as $blader)
                        <div class="card col-md-3 py-2 m-2">
                            @if ($blader->imagen)
                                <img src="/storage/{{ $blader->imagen }}"  class="card-img-top">
                            @else
                                <img src="../images/default_user.jpg"  class="card-img-top">
                            @endif

                            <div class="card-body">
                                <h3><b>{{ $blader->user->name }}</b></h3>
                                <h4>Región: {{ ($blader->region) ? $blader->region->name : 'No definida'}}</h4>
                            </div>
                        </div>
                @endforeach
        </div>
    </div>

@endsection
