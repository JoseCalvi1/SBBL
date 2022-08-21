@extends('layouts.app')


@section('content')

<div class="container">
<h2 class="titulo-categoria text-uppercase mb-4 mt-4">Duelos Generations</h2><br>
@if ($diasDiferencia > 13)
    <a href="{{ route('generations.create') }}" class="btn btn-outline-danger mr-2 mb-4 text-uppercase font-weight-bold">
        Crear duelo
    </a>
@endif
    <div class="row mt-2">
        @foreach ($versus as $duel)
            <div class="col-md-3 pb-2">
                {{ $duel->another }}
                <div class="versus-card text-center" style="border: 1px solid black;padding: 5px 10px;background:rgba(132, 131, 131, 0.258)">
                    <p class="mb-1 mt-2">
                        <span class="mb-0" style="{{ ($duel->user_id_1 == $duel->winner) ? 'color:green' : 'color:red' }}">{{ $duel->versus_1->name }}</span>
                        vs
                        <span style="{{ ($duel->user_id_2 == $duel->winner) ? 'color:green' : 'color:red' }}">{{ $duel->versus_2->name }}</span>
                    </p>
                    <p>{{ $duel->result }}</p>
                    <p class="m-0">{{ $duel->status }}</p>
                    @if ($duel->url)
                        <a class="d-block font-weight-bold text-uppercase pt-2 pb-2" style="text-decoration: none; color:white;width: 100%; background-color:rgb(87, 170, 244);" href="{{ $duel->url }}">Ver video</a>
                    @else
                        <p>*Vídeo disponible próximamente*</p>
                    @endif
                    @if ($duel->status == 'Abierto' && $duel->versus_1->id == Auth::user()->id)
                        <a class="d-block font-weight-bold text-uppercase pt-2 pb-2" style="text-decoration: none; color:white;width: 100%; background-color:red;" href="{{ route('generations.edit', ['versus' => $duel->id]) }}">Ver duelo</a>
                    @elseif (Auth::user()->is_admin && $duel->status == 'Pendiente')
                    <form method="POST" action="{{ route('generations.update', ['versus' => $duel->id]) }}" enctype="multipart/form-data" novalidate>
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <input type="hidden"
                                    name="complete"
                                    class="form-control"
                                    id="complete"
                                    value="complete"
                                    />
                            <input type="submit" class="btn btn-success" value="Confirmar">
                        </div>
                    </form>
                    @endif

                </div>
            </div>
        @endforeach
    </div>
</div>

@endsection

