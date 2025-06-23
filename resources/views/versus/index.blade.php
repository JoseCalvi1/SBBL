@extends('layouts.app')

@section('content')
@if (Auth::user()->is_jury)
<div class="py-4">
    <h2 class="text-center mb-4 text-white font-weight-bold">Administrar Duelos</h2>

    <div class="col-md-10 mx-auto p-4">
        <a href="{{ route('versus.create') }}" class="btn btn-outline-light mb-4 font-weight-bold">
            <i class="fas fa-plus"></i> Crear Duelo
        </a>

        <form method="POST" action="{{ route('puntuarDuelos') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="table-responsive">
                <button type="submit" class="btn btn-primary mb-3">
                    <i class="fas fa-check-double"></i> Puntuar Seleccionados
                </button>
                <table class="table table-hover text-white">
                    <thead class="bg-secondary text-light">
                        <tr>
                            <th scope="col"></th>
                            <th scope="col">Jugador 1</th>
                            <th scope="col">Puntuación 1</th>
                            <th scope="col">Jugador 2</th>
                            <th scope="col">Puntuación 2</th>
                            <th scope="col">Modalidad</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($versus as $duel)
                            <tr class="@if ($duel->status == 'OPEN' && $duel->url)
                                            table-info text-dark
                                        @elseif ($duel->status == 'OPEN')
                                            table-warning text-dark
                                        @else
                                            table-active text-white
                                        @endif">
                                <td>
                                    @if ($duel->status == 'OPEN')
                                        <input type="checkbox" name="duel_ids[]" value="{{ $duel->id }}" class="form-check-input" style="margin: 5px 0 0 0">
                                    @endif
                                </td>
                                <td>{{ $duel->versus_1->name }}</td>
                                <td>{{ $duel->result_1 }}</td>
                                <td>{{ $duel->versus_2->name }}</td>
                                <td>{{ $duel->result_2 }}</td>
                                <td>{{ ucfirst($duel->matchup) }}</td>
                                <td>{{ $duel->created_at->format('d-m-Y') }}</td>
                                <td>@if ($duel->status == "CLOSED")
                                    Válido
                                @elseif ($duel->status == "INVALID")
                                    Inválido
                                @elseif ($duel->status == "OPEN" && $duel->url)
                                    Pendiente
                                @else
                                    Enviado
                                @endif</td>
                                <td>
                                    <div class="d-flex flex-column flex-wrap" style="gap: 0.25rem;">
                                        <div class="d-flex flex-wrap gap-1 mb-1">
                                            <a href="{{ route('versus.edit', ['duel' => $duel->id]) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                            @if ($duel->url)
                                                <a href="{{ $duel->url }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-play"></i> Ver vídeo
                                                </a>
                                            @endif
                                        </div>
                                        <div class="d-flex flex-wrap gap-1">
                                            @if ($duel->status == 'OPEN')
                                                <button type="submit" formaction="{{ route('versus.puntuarDuelo', ['duel' => $duel->id, 'mode' => $duel->matchup, 'winner' => $duel->user_id_1]) }}" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check-circle"></i> Confirmar
                                                </button>
                                                <button type="submit" formaction="{{ route('versus.invalidar', ['duel' => $duel->id]) }}" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-times-circle"></i> Invalidar
                                                </button>
                                            @else
                                                <span class="badge badge-secondary mt-1">Cerrado</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>
@else
<script type="text/javascript">
    window.location = "/";
</script>
@endif
@endsection

@section('scripts')
<script>
    document.getElementById('select-all').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('input[name="duel_ids[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });
</script>
@endsection
