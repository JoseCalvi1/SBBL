@extends('layouts.app')

@section('title', 'Ranking de piezas Beyblade X')

@section('content')
<div class="container pt-2">

    <a href="{{ route('inicio.stats') }}" class="btn btn-outline-primary mr-2 text-uppercase font-weight-bold m-4">
        Volver
    </a>
    <h3 class="text-white">Estadísticas de Blades</h3>
    <div class="table-responsive">
        <table class="table table-striped table-dark">
            <thead>
                <tr>
                    <th><a href="{{ route('stats.separate', ['sort' => 'blade', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Blade</a></th>
                    <th><a href="{{ route('stats.separate', ['sort' => 'total_victorias', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Victorias</a></th>
                    <th><a href="{{ route('stats.separate', ['sort' => 'total_derrotas', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Derrotas</a></th>
                    <th><a href="{{ route('stats.separate', ['sort' => 'total_partidas', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Total Partidas</a></th>
                    <th><a href="{{ route('stats.separate', ['sort' => 'percentage_victories', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Porcentaje Victorias</a></th>
                </tr>
            </thead>
            <tbody>
                @foreach($bladeStats as $stat)
                    <tr>
                        <td>{{ $stat->blade }}</td>
                        <td>{{ $stat->total_victorias }}</td>
                        <td>{{ $stat->total_derrotas }}</td>
                        <td>{{ $stat->total_partidas }}</td>
                        <td>{{ number_format($stat->percentage_victories, 2) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <h3 class="text-white">Estadísticas de Ratchets</h3>
    <div class="table-responsive">
        <table class="table table-striped table-dark">
            <thead>
                <tr>
                    <th><a href="{{ route('stats.separate', ['sort' => 'ratchet', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Ratchet</a></th>
                    <th><a href="{{ route('stats.separate', ['sort' => 'total_victorias', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Victorias</a></th>
                    <th><a href="{{ route('stats.separate', ['sort' => 'total_derrotas', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Derrotas</a></th>
                    <th><a href="{{ route('stats.separate', ['sort' => 'total_partidas', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Total Partidas</a></th>
                    <th><a href="{{ route('stats.separate', ['sort' => 'percentage_victories', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Porcentaje Victorias</a></th>
                </tr>
            </thead>
            <tbody>
                @foreach($ratchetStats as $stat)
                    <tr>
                        <td>{{ $stat->ratchet }}</td>
                        <td>{{ $stat->total_victorias }}</td>
                        <td>{{ $stat->total_derrotas }}</td>
                        <td>{{ $stat->total_partidas }}</td>
                        <td>{{ number_format($stat->percentage_victories, 2) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <h3 class="text-white">Estadísticas de Bits</h3>
    <div class="table-responsive">
        <table class="table table-striped table-dark">
            <thead>
                <tr>
                    <th><a href="{{ route('stats.separate', ['sort' => 'bit', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Bit</a></th>
                    <th><a href="{{ route('stats.separate', ['sort' => 'total_victorias', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Victorias</a></th>
                    <th><a href="{{ route('stats.separate', ['sort' => 'total_derrotas', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Derrotas</a></th>
                    <th><a href="{{ route('stats.separate', ['sort' => 'total_partidas', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Total Partidas</a></th>
                    <th><a href="{{ route('stats.separate', ['sort' => 'percentage_victories', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}">Porcentaje Victorias</a></th>
                </tr>
            </thead>
            <tbody>
                @foreach($bitStats as $stat)
                    <tr>
                        <td>{{ $stat->bit }}</td>
                        <td>{{ $stat->total_victorias }}</td>
                        <td>{{ $stat->total_derrotas }}</td>
                        <td>{{ $stat->total_partidas }}</td>
                        <td>{{ number_format($stat->percentage_victories, 2) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
