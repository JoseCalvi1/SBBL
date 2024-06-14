@extends('layouts.app')

@section('content')
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
