@extends('layouts.app')


@section('content')
<div class="py-4">
    <h2 class="text-center mb-2">Administra los duelos</h2>

    <div class="col-md-10 mx-auto bg-white p-3">
        <a href="{{ route('versus.create') }}" class="btn btn-outline-primary mr-2 mb-4 text-uppercase font-weight-bold">
            Crear duelo
        </a>

        <table class="table">
            <thead class="bg-primary text-light">
                <tr>
                    <th scole="col">Jugador 1 (VICTORIA)</th>
                    <th scole="col">Puntuación 1</th>
                    <th scole="col">Jugador 2 (DERROTA)</th>
                    <th scole="col">Puntuación 2</th>
                    <th scole="col">Modalidad</th>
                    <th scole="col">Fecha</th>
                    <th scole="col">Acciones</th>
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
                        <td><a href="{{ route('versus.edit', ['duel' => $duel->id]) }}" class="btn btn-dark mb-2 d-block">Editar</a>
                            @if ($duel->status == 'OPEN')
                                <form method="POST" action="{{ route('versus.puntuarDuelo', ['duel' => $duel->id, 'mode' => $duel->matchup, 'winner' => $duel->user_id_1]) }}" style="display: contents; text-align: center;">
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
