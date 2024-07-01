@extends('layouts.app')

@section('content')
<div class="container-fluid" style="background: darkblue">

            <div class="row">
                <ul class="navbar-nav m-auto" style="flex-direction: row;">
                    <li class="nav-item">
                        <a class="nav-link ml-2 mr-2" style="color: white; font-weight: bold; font-size:1.2em;" href="{{ route('equipos.index') }}">
                            {{ 'INICIO' }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link ml-2 mr-2" style="color: white; font-weight: bold; font-size:1.2em;" href="{{ route('teams_versus.all') }}">
                            {{ 'DUELOS' }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link ml-2 mr-2" style="color: white; font-weight: bold; font-size:1.2em;" href="{{ route('equipos.ranking') }}">
                            {{ 'RANKING' }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
<div class="container">
    <h2 class="text-center mt-2 mb-4" style="color: white">Listado de equipos
        @if (Auth::user() && Auth::user()->teams->isEmpty())
            <a href="{{ route('equipos.create') }}" class="btn btn-outline-warning mb-2 text-uppercase font-weight-bold">
                Crear equipo
            </a>
        @endif
    </h2>

    <div class="row row-cols-1 row-cols-md-3">
        @forelse($equipos as $equipo)
            <div class="col mb-4">
                <div class="card bg-dark text-white">
                    <div class="card-body p-0">
                        @if($equipo->image)
                        <div class="mb-2" style="position: relative; padding-top: 56.25%; background-image: url(data:image/png;base64,{{ $equipo->image }}); background-size: cover; background-position: center; background-repeat: no-repeat; text-align: center;">
                            <img src="data:image/png;base64,{{ $equipo->logo }}" alt="Logo del Equipo" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); max-width: 80%; max-height: 80%;">
                        </div>

                        @endif
                        <h5 class="card-title ml-1"><strong>{{ $equipo->name }}</strong></h5>
                        <p class="card-text ml-1">{{ Illuminate\Support\Str::limit($equipo->description, 100) }}</p>
                        <a href="{{ route('equipos.show', $equipo) }}" class="btn btn-primary">Ver equipo</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col">
                <p class="text-center" style="color: white">No hay equipos disponibles.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
