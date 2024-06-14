@extends('layouts.app')

@section('content')
<div class="py-4">
    <h2 class="text-center mb-2 text-white">Administra los duelos de equipos</h2>

    <div class="col-md-10 mx-auto bg-white p-3" style="color: white !important;background-color:transparent !important">
        <a href="{{ route('teams_versus.create') }}" class="btn btn-outline-primary mr-2 mb-4 text-uppercase font-weight-bold">
            Crear duelo de equipo
        </a>

        <table class="table" style="color: white !important;">
            <thead class="bg-primary text-light">
                <tr>
                    <th scope="col">Equipo 1</th>
                    <th scope="col">Puntuación 1</th>
                    <th scope="col">Equipo 2</th>
                    <th scope="col">Puntuación 2</th>
                    <th scope="col">Modalidad</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($versus as $duel)
                    <tr>
                        <td>{{ $duel->versus_1->name }}</td>
                        <td>{{ $duel->result_1 }}</td>
                        <td>{{ $duel->versus_2->name }}</td>
                        <td>{{ $duel->result_2 }}</td>
                        <td>{{ $duel->matchup }}</td>
                        <td>{{ $duel->created_at }}</td>
                        <td>
                            <a href="{{ route('teams_versus.edit', ['duel' => $duel->id]) }}" class="btn btn-dark mb-2 d-block">Editar</a>
                            @if ($duel->status == 'OPEN')
                                <form method="POST" action="{{ route('teams_versus.puntuarDuelo', ['duel' => $duel->id, 'mode' => $duel->matchup, 'winner' => $duel->team_id_1]) }}" style="display: contents; text-align: center;">
                                    @method('PUT')
                                    @csrf
                                    <button type="submit" class="btn btn-success mb-2 mt-2 d-block" style="width: 100%">CONFIRMAR</button>
                                </form>
                            @endif
                        </td>
                        <td></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
