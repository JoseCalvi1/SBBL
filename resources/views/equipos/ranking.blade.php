@extends('layouts.app')

@section('content')
<div class="container-fluid" style="background: #283b63">

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
<div class="py-4">
    <div class="container">
        <h2 class="text-center mb-4 text-white">Ranking de Equipos</h2>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="text-white">
                    <div>
                        <div class="table-responsive">
                            <table class="table text-white">
                                <thead class="bg-dark">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Equipo</th>
                                        <th scope="col">Puntuación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($teams as $key => $team)
                                    <tr>
                                        <th scope="row">{{ $key + 1 }}</th>
                                        <td>{{ $team->name }}</td>
                                        <td>{{ $team->points_x1 }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
