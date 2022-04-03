@extends('layouts.app')


@section('content')

<div class="container">
<h2 class="titulo-categoria text-uppercase mb-4 mt-4">Duelos</h2>
    <div class="row mt-2p-">
        @foreach ($versus as $duel)
            <div class="col-md-3 pb-2">
                {{ $duel->another }}
                <div class="versus-card text-center" style="border: 1px solid black;padding: 5px 10px;">
                    <p class="mb-1 font-weight-bold"><a style="text-decoration: none;color:black;" href="{{ route('events.show', ['event' => $duel->event->id]) }}">{{ $duel->event->name }}</a></p>
                    <p class="mb-0" style="{{ ($duel->user_id_1 == $duel->winner) ? 'color:green' : 'color:red' }}">{{ $duel->versus_1->name }}</p>
                    vs
                    <p style="{{ ($duel->user_id_2 == $duel->winner) ? 'color:green' : 'color:red' }}">{{ $duel->versus_2->name }}</p>
                    @if ($duel->url)
                        <a class="d-block font-weight-bold text-uppercase pt-2 pb-2" style="text-decoration: none; color:white;width: 100%; background-color:rgb(87, 170, 244);" href="{{ $duel->url }}">Ver video</a>
                    @else
                        <p>*Vídeo disponible próximamente*</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

@endsection

