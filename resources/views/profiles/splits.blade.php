@extends('layouts.app')

@section('title', 'Splits Season 2')

@section('styles')
<style>
    .nav-tabs .nav-link {
    color: white;
}

.nav-tabs .nav-link.active {
    color: #0d6efd !important;
}
</style>
@endsection

@section('content')
<div class="container">
    <h1 class="mt-4 mb-4 text-white">Ranking por Splits</h1>

    <ul class="nav nav-tabs" id="splitTabs" role="tablist">
        @foreach($splits as $nombre => $rango)
            <li class="nav-item" role="presentation">
                <button class="nav-link @if ($loop->first) active @endif"
                        id="tab-{{ $loop->index }}"
                        data-bs-toggle="tab"
                        data-bs-target="#content-{{ $loop->index }}"
                        type="button"
                        role="tab">
                    {{ $nombre }}
                </button>
            </li>
        @endforeach
    </ul>


    <div class="tab-content mt-3" id="splitTabsContent">
        @foreach($splits as $nombre => $rango)
            <div class="tab-pane fade @if ($loop->first) show active @endif"
                 id="content-{{ $loop->index }}"
                 role="tabpanel">
                <table class="table table-dark table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Posición</th>
                            <th>Jugador</th>
                            <th>Puntos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data[$nombre] as $index => $jugador)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $jugador->name }}</td>
                                <td>{{ $jugador->total_puntos }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">Sin datos en este split</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
</div>
@endsection
