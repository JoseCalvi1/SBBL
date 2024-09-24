@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="text-white">Estadísticas de Beyblades</h2>
    <div class="table-responsive">
        <table class="table table-striped table-dark">
            <thead>
                <tr>
                    <th><a href="{{ route('stats.index', ['sort' => 'blade', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Blade</a></th>
                    <th><a href="{{ route('stats.index', ['sort' => 'ratchet', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Ratchet</a></th>
                    <th><a href="{{ route('stats.index', ['sort' => 'bit', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Bit</a></th>
                    <th><a href="{{ route('stats.index', ['sort' => 'total_victorias', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Victorias</a></th>
                    <th><a href="{{ route('stats.index', ['sort' => 'total_derrotas', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Derrotas</a></th>
                    <th><a href="{{ route('stats.index', ['sort' => 'total_partidas', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Total Partidas</a></th>
                    <th><a href="{{ route('stats.index', ['sort' => 'percentage_victories', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Porcentaje Victorias/Derrotas</a></th>
                    <th><a href="{{ route('stats.index', ['sort' => 'puntos_ganados_por_combate', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Puntos Ganados por Combate</a></th>
                    <th><a href="{{ route('stats.index', ['sort' => 'puntos_perdidos_por_combate', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Puntos Perdidos por Combate</a></th>
                    <th><a href="{{ route('stats.index', ['sort' => 'eficiencia', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Puntos OTH</a></th>
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
                        <td>{{ $stat->total_partidas }}</td>
                        <td>{{ number_format($stat->percentage_victories, 2) }}%</td>
                        <td>{{ number_format($stat->puntos_ganados_por_combate, 2) }}</td>
                        <td>{{ number_format($stat->puntos_perdidos_por_combate, 2) }}</td>
                        <td>{{ number_format($stat->eficiencia, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
