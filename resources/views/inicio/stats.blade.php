@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="text-white pt-2">Estadísticas de Beyblades</h2>

    <!-- Botón para acceder a la vista de estadísticas separadas -->
    <div class="mb-3">
        <a href="{{ route('stats.separate') }}" class="btn btn-secondary">Ver Estadísticas Separadas</a>
    </div>

    <!-- Filtros -->
    <form method="GET" action="{{ route('stats.index') }}" class="mb-4">
        <div class="form-row align-items-end">
            <div class="form-group col-md-4">
                <label for="blade" class="text-white">Blade</label>
                <select name="blade" id="blade" class="form-control bg-dark text-white border-secondary">
                    <option value="">Seleccionar Blade</option>
                    @foreach($blades as $blade)
                        <option value="{{ $blade }}" {{ $blade == $bladeFilter ? 'selected' : '' }}>{{ $blade }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="ratchet" class="text-white">Ratchet</label>
                <select name="ratchet" id="ratchet" class="form-control bg-dark text-white border-secondary">
                    <option value="">Seleccionar Ratchet</option>
                    @foreach($ratchets as $ratchet)
                        <option value="{{ $ratchet }}" {{ $ratchet == $ratchetFilter ? 'selected' : '' }}>{{ $ratchet }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="bit" class="text-white">Bit</label>
                <select name="bit" id="bit" class="form-control bg-dark text-white border-secondary">
                    <option value="">Seleccionar Bit</option>
                    @foreach($bits as $bit)
                        <option value="{{ $bit }}" {{ $bit == $bitFilter ? 'selected' : '' }}>{{ $bit }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
    </form>



    <div class="table-responsive">
        <table class="table table-striped table-dark">
            <thead>
                <tr>
                    <th><a href="{{ route('stats.index', ['sort' => 'blade', 'order' => $order == 'asc' ? 'desc' : 'asc', 'blade' => $bladeFilter, 'ratchet' => $ratchetFilter, 'bit' => $bitFilter]) }}">Blade</a></th>
                    <th><a href="{{ route('stats.index', ['sort' => 'ratchet', 'order' => $order == 'asc' ? 'desc' : 'asc', 'blade' => $bladeFilter, 'ratchet' => $ratchetFilter, 'bit' => $bitFilter]) }}">Ratchet</a></th>
                    <th><a href="{{ route('stats.index', ['sort' => 'bit', 'order' => $order == 'asc' ? 'desc' : 'asc', 'blade' => $bladeFilter, 'ratchet' => $ratchetFilter, 'bit' => $bitFilter]) }}">Bit</a></th>
                    <th><a href="{{ route('stats.index', ['sort' => 'total_victorias', 'order' => $order == 'asc' ? 'desc' : 'asc', 'blade' => $bladeFilter, 'ratchet' => $ratchetFilter, 'bit' => $bitFilter]) }}">Victorias</a></th>
                    <th><a href="{{ route('stats.index', ['sort' => 'total_derrotas', 'order' => $order == 'asc' ? 'desc' : 'asc', 'blade' => $bladeFilter, 'ratchet' => $ratchetFilter, 'bit' => $bitFilter]) }}">Derrotas</a></th>
                    <th><a href="{{ route('stats.index', ['sort' => 'total_partidas', 'order' => $order == 'asc' ? 'desc' : 'asc', 'blade' => $bladeFilter, 'ratchet' => $ratchetFilter, 'bit' => $bitFilter]) }}">Total Partidas</a></th>
                    <th><a href="{{ route('stats.index', ['sort' => 'percentage_victories', 'order' => $order == 'asc' ? 'desc' : 'asc', 'blade' => $bladeFilter, 'ratchet' => $ratchetFilter, 'bit' => $bitFilter]) }}">Porcentaje Victorias/Derrotas</a></th>
                    <th><a href="{{ route('stats.index', ['sort' => 'puntos_ganados_por_combate', 'order' => $order == 'asc' ? 'desc' : 'asc', 'blade' => $bladeFilter, 'ratchet' => $ratchetFilter, 'bit' => $bitFilter]) }}">Puntos Ganados por Combate</a></th>
                    <th><a href="{{ route('stats.index', ['sort' => 'puntos_perdidos_por_combate', 'order' => $order == 'asc' ? 'desc' : 'asc', 'blade' => $bladeFilter, 'ratchet' => $ratchetFilter, 'bit' => $bitFilter]) }}">Puntos Perdidos por Combate</a></th>
                    <th><a href="{{ route('stats.index', ['sort' => 'eficiencia', 'order' => $order == 'asc' ? 'desc' : 'asc', 'blade' => $bladeFilter, 'ratchet' => $ratchetFilter, 'bit' => $bitFilter]) }}">Puntos OTH</a></th>
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
