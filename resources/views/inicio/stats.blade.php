@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="text-white">Estadísticas de Beyblades</h2>
    <div class="table-responsive">
        <table class="table table-striped table-dark">
            <thead>
                <tr>
                    <th>Blade</th>
                    <th>Ratchet</th>
                    <th>Bit</th>
                    <th>Victorias</th>
                    <th>Derrotas</th>
                    <th>Total Partidas</th> <!-- Nueva columna -->
                    <th>Porcentaje Victorias/Derrotas</th> <!-- Nueva columna -->
                    <th>Puntos Ganados</th>
                    <th>Puntos Perdidos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($beybladeStats as $stat)
                    <tr>
                        <td>{{ $stat->blade }}</td>
                        <td>{{ $stat->ratchet }}</td>
                        <td>{{ $stat->bit }}</td>
                        <td>{{ $stat->total_victorias }}</td>
                        <td>{{ $stat->total_derrotas }}</td>
                        <td>{{ $stat->total_partidas }}</td> <!-- Mostrar total de partidas -->
                        <td>{{ number_format($stat->percentage_victories, 2) }}%</td> <!-- Mostrar porcentaje -->
                        <td>{{ $stat->total_puntos_ganados }}</td>
                        <td>{{ $stat->total_puntos_perdidos }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
