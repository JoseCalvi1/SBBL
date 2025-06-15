@extends('layouts.app')

@section('title', 'Estadísticas Beyblade X')

@section('styles')
<style>

</style>
@endsection

@section('content')
<div class="container">
    <h2 class="text-white pt-2">Estadísticas de Beyblades</h2>

    <!-- Botón para acceder a la vista de estadísticas separadas -->
    <div class="row pb-2">
        <div class="col-6">
            <a href="{{ route('stats.separate') }}" class="btn btn-secondary w-100">Ver Estadísticas Separadas</a>
        </div>
        <div class="col-6">
            <a href="{{ route('stats.rankingstats') }}" class="btn btn-warning w-100">Ranking de Estadísticas</a>
        </div>
    </div>

    <!-- Filtros -->
    <form method="GET" action="{{ route('stats.index') }}" class="mb-4">
        <div class="form-row align-items-end">
            <div class="form-group col-md-3">
                <label for="blade" class="text-white">Blade</label>
                <select name="blade" id="blade" class="form-control bg-dark text-white border-secondary">
                    <option value="">Seleccionar Blade</option>
                    @foreach($blades as $blade)
                        <option value="{{ $blade }}" {{ $blade == $bladeFilter ? 'selected' : '' }}>{{ $blade }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-3">
                <label for="assist_blade" class="text-white">Assist Blade</label>
                <select name="assist_blade" id="assist_blade" class="form-control bg-dark text-white border-secondary">
                    <option value="">Seleccionar Assist Blade</option>
                    @foreach($assistBlades as $assistBlade)
                        @if($assistBlade !== '-- Selecciona un assist blade --')
                            <option value="{{ $assistBlade }}" {{ $assistBlade == $assistBladeFilter ? 'selected' : '' }}>{{ $assistBlade }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-3">
                <label for="ratchet" class="text-white">Ratchet</label>
                <select name="ratchet" id="ratchet" class="form-control bg-dark text-white border-secondary">
                    <option value="">Seleccionar Ratchet</option>
                    @foreach($ratchets as $ratchet)
                        <option value="{{ $ratchet }}" {{ $ratchet == $ratchetFilter ? 'selected' : '' }}>{{ $ratchet }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-3">
                <label for="bit" class="text-white">Bit</label>
                <select name="bit" id="bit" class="form-control bg-dark text-white border-secondary">
                    <option value="">Seleccionar Bit</option>
                    @foreach($bits as $bit)
                        <option value="{{ $bit }}" {{ $bit == $bitFilter ? 'selected' : '' }}>{{ $bit }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-3">
                <label for="fecha_inicio" class="text-white">Desde</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control bg-dark text-white border-secondary" value="{{ request('fecha_inicio') }}">
            </div>

            <div class="form-group col-md-3">
                <label for="fecha_fin" class="text-white">Hasta</label>
                <input type="date" name="fecha_fin" id="fecha_fin" class="form-control bg-dark text-white border-secondary" value="{{ request('fecha_fin') }}">
            </div>
            <div class="form-group col-md-3">
                <label for="min_partidas" class="text-white">Mínimo de Partidas</label>
                <select name="min_partidas" id="min_partidas" class="form-control bg-dark text-white border-secondary">
                    <option value="0" {{ $minPartidas == 1 ? 'selected' : '' }}>Todas</option>
                    <option value="10" {{ $minPartidas == 10 ? 'selected' : '' }}>10+</option>
                    <option value="50" {{ $minPartidas == 50 ? 'selected' : '' }}>50+</option>
                    <option value="100" {{ $minPartidas == 100 ? 'selected' : '' }}>100+</option>
                </select>
            </div>
            @if(Auth::user())
        <div class="form-group col-md-3 d-flex align-items-center">
            <label for="only_user_parts" class="text-white mb-0 mr-2">Solo mis datos</label>
            <div class="custom-control custom-switch">
                <input
                    type="checkbox"
                    class="custom-control-input"
                    id="only_user_parts"
                    name="only_user_parts"
                    value="on"
                    {{ $userPartsFilter ? 'checked' : '' }}
                >
                <label class="custom-control-label" for="only_user_parts"></label>
            </div>
        </div>
        @endif
        </div>

        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
    </form>


    <div class="table-responsive">
        <table class="table table-striped table-dark">
            <thead>
                <tr>
                    @foreach(['blade', 'assist_blade', 'ratchet', 'bit', 'total_victorias', 'total_derrotas', 'total_partidas', 'percentage_victories', 'puntos_ganados_por_combate', 'puntos_perdidos_por_combate', 'eficiencia'] as $column)
                        <th>
                            <a href="{{ route('stats.index', array_merge(request()->query(), ['sort' => $column, 'order' => $order == 'asc' ? 'desc' : 'asc'])) }}">
                                {{ ucfirst(str_replace('_', ' ', $column)) }}
                            </a>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($beybladeStats as $stat)
                    <tr>
                        <td>{{ $stat->blade }}</td>
                        <td>{{ $stat->assist_blade }}</td>
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
