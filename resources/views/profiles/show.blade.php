@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-12">
            @if ($profile->fondo)
                <div style="background-image: url('/storage/{{ $profile->fondo }}'); background-size: cover; background-repeat: no-repeat; background-position: center; padding: 80px;"></div>
            @else
                <div style="background-image: url('/storage/upload-profiles/SBBLFondo.png'); background-size: cover; background-repeat: repeat; background-position: center; padding: 80px;"></div>
            @endif
        </div>
        <div class="col-md-4" style="margin-top: -20px;">
            <div style="position: relative;">
                @if ($profile->imagen)
                                <img src="/storage/{{ $profile->imagen }}" class="rounded-circle" width="200" style="top: 0; left: 0;">
                            @else
                                <img src="/storage/upload-profiles/DranDaggerBase.png" class="rounded-circle" width="200" style="top: 0; left: 0;">
                            @endif
                            @if ($profile->marco)
                                <img src="/storage/{{ $profile->marco }}" class="rounded-circle" width="200" style="position: absolute; top: 0; left: 0;">
                            @else
                                <img src="/storage/upload-profiles/Marcos/BaseBlue.png" class="rounded-circle" width="200" style="position: absolute; top: 0; left: 0;">
                            @endif
            </div>

        </div>
        <div class="col-md-8">
            <h2 class="text-center mb-2 mt-5 mt-md-0 text-primary">{{ $profile->user->name }}</h2>
            <h3 class="text-center mb-2 mt-5 mt-md-0 text-primary">{{ $profile->user->email }}</h3>
            <h3 class="text-center mb-2 mt-5 mt-md-0 text-primary">Región: @if ($profile->region)
                {{ $profile->region->name }}
            @else
                Por definir
            @endif</h3>
            <h3 class="text-center mb-2 mt-5 mt-md-0 text-primary">Puntos: {{ $profile->points_s2 }}</h3>
            <a href="{{ route('profiles.edit', ['profile' => Auth::user()->id]) }}" class="btn btn-outline-info mr-2 text-uppercase font-weight-bold w-100">
                Editar perfil
            </a>

            <h3 class="titulo-categoria text-uppercase mb-4 mt-4">Palmarés</h3>
            <div class="row">
            @if (count($profile->trophies) != 0)
                @foreach ($profile->trophies as $trophy)
                    <div class="col-md-6">
                        <p class="font-weight-bold">{{ $trophy->pivot->count }}x<svg style="@if($trophy->id == 1 || $trophy->id == 4) fill:gold; @elseif($trophy->id == 2 || $trophy->id == 5) fill:silver; @else fill:rgba(205, 127, 50); @endif" xmlns="http://www.w3.org/2000/svg" width="20" viewBox="0 0 576 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M572.1 82.38C569.5 71.59 559.8 64 548.7 64h-100.8c.2422-12.45 .1078-23.7-.1559-33.02C447.3 13.63 433.2 0 415.8 0H160.2C142.8 0 128.7 13.63 128.2 30.98C127.1 40.3 127.8 51.55 128.1 64H27.26C16.16 64 6.537 71.59 3.912 82.38C3.1 85.78-15.71 167.2 37.07 245.9c37.44 55.82 100.6 95.03 187.5 117.4c18.7 4.805 31.41 22.06 31.41 41.37C256 428.5 236.5 448 212.6 448H208c-26.51 0-47.99 21.49-47.99 48c0 8.836 7.163 16 15.1 16h223.1c8.836 0 15.1-7.164 15.1-16c0-26.51-21.48-48-47.99-48h-4.644c-23.86 0-43.36-19.5-43.36-43.35c0-19.31 12.71-36.57 31.41-41.37c86.96-22.34 150.1-61.55 187.5-117.4C591.7 167.2 572.9 85.78 572.1 82.38zM77.41 219.8C49.47 178.6 47.01 135.7 48.38 112h80.39c5.359 59.62 20.35 131.1 57.67 189.1C137.4 281.6 100.9 254.4 77.41 219.8zM498.6 219.8c-23.44 34.6-59.94 61.75-109 81.22C426.9 243.1 441.9 171.6 447.2 112h80.39C528.1 135.7 526.5 178.7 498.6 219.8z"/></svg> {{ $trophy->name.' Season '.$trophy->season }}</p>
                    </div>
                @endforeach
            @else
                <div class="col-md-6">
                    <p>No hay registros</p>
                </div>
            @endif
            </div>
        </div>
    </div>
    <h2 class="titulo-categoria text-uppercase mb-4 mt-4">Duelos</h2>
    <div class="row mt-2">
        @foreach ($versus as $duel)
            <div class="col-md-4 pb-2">
                {{-- $duel->another --}}
                <div class="versus-card" style="border: 1px solid black;padding: 5px 10px;">
                   @if($duel->event->id)
                    <p class="mb-1 font-weight-bold"><a style="text-decoration: none;color:black;" href="{{ route('events.show', ['event' => $duel->event->id]) }}">{{ $duel->event->name }}</a></p>
                   @else
                    <p class="mb-1 font-weight-bold">GENERATIONS</p>
                   @endif
                    <span style="{{ ($duel->user_id_1 == $duel->winner) ? 'color:green' : 'color:red' }}">{{ $duel->versus_1->name }}</span>
                    vs
                    <span style="{{ ($duel->user_id_2 == $duel->winner) ? 'color:green' : 'color:red' }}">{{ $duel->versus_2->name }}</span>
                    @if ($duel->url)
                        <span class="float-right border-left pl-2"><a style="text-decoration:none;color:black;" href="{{ $duel->url }}">Ver video</a></span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>


@endsection
