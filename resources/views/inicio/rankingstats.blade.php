@extends('layouts.app')

@section('content')
<div class="container">
    <a href="{{ route('inicio.stats') }}" class="btn btn-outline-primary mr-2 text-uppercase font-weight-bold m-4">
        Volver
    </a>
    <h2 class="ranking-title">Ranking de usuarios del mes pasado</h2>
    <table class="table-ranking">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Puntos Ganados</th>
                <th>Puntos Perdidos</th>
                <th>% Puntos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ranking as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->total_puntos_ganados }}</td>
                    <td>{{ $user->total_puntos_perdidos }}</td>
                    <td>{{ number_format($user->porcentaje_ganados, 2) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@section('styles')
<style>
    body {
        background-color: #121212;
        color: #e0e0e0;
    }

    .ranking-title {
        text-align: center;
        margin-bottom: 20px;
        color: #ffffff;
        font-size: 1.8em;
        font-weight: 500;
    }

    .table-ranking {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    .table-ranking thead {
        background-color: #333;
        color: #ffffff;
    }

    .table-ranking th, .table-ranking td {
        padding: 12px 15px;
        text-align: center;
        border-bottom: 1px solid #444;
    }

    .table-ranking tbody tr:hover {
        background-color: #2a2a2a;
    }

    .table-ranking tbody tr:nth-child(even) {
        background-color: #1e1e1e;
    }

    .table-ranking tbody tr:nth-child(odd) {
        background-color: #2c2c2c;
    }

    th {
        font-weight: 600;
    }

    td {
        color: #ffffff;
    }
</style>
@endsection
