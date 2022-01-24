@extends('layouts.app')

@section('content')

<div class="container mt-5">
    <div class="row">
        <div class="col-md-4">
            @if ($profile->imagen)
                <img src="/storage/{{ $profile->imagen }}" class="rounded-circle" width="250">
            @else
            <img src="../images/default_user.jpg" class="rounded-circle" width="250">
            @endif

        </div>
        <div class="col-md-8">
            <h2 class="text-center mb-2 mt-5 mt-md-0 text-primary">{{ $profile->user->name }}</h2>
            <h3 class="text-center mb-2 mt-5 mt-md-0 text-primary">{{ $profile->user->email }}</h3>
            <h3 class="text-center mb-2 mt-5 mt-md-0 text-primary">Región: @if ($profile->region)
                {{ $profile->region->name }}
            @else
                Por definir
            @endif</h3>
            <h3 class="text-center mb-2 mt-5 mt-md-0 text-primary">Puntos: {{ $profile->points }}</h3>
            <a href="{{ route('profiles.edit', ['profile' => Auth::user()->id]) }}" class="btn btn-outline-info mr-2 text-uppercase font-weight-bold w-100">
                Editar perfil
            </a>

        </div>
    </div>
    <h2 class="titulo-categoria text-uppercase mb-4 mt-4">Duelos</h2>
    <div class="row mt-2">
        @foreach ($versus as $duel)
            <div class="col-md-4 pb-2">
                {{ $duel->another }}
                <div class="versus-card" style="border: 1px solid black;padding: 5px 10px;">
                    <p class="mb-1 font-weight-bold"><a style="text-decoration: none;color:black;" href="{{ route('events.show', ['event' => $duel->event->id]) }}">{{ $duel->event->name }}</a></p>
                    <span style="{{ ($duel->user_id_1 == $duel->winner) ? 'color:green' : 'color:red' }}">{{ $duel->versus_1->name }}</span>
                    vs
                    <span style="{{ ($duel->user_id_2 == $duel->winner) ? 'color:green' : 'color:red' }}">{{ $duel->versus_2->name }}</span>
                    <span class="float-right border-left pl-2"><a style="text-decoration:none;color:black;" href="{{ $duel->url }}">Ver video</a></span>
                </div>
            </div>
        @endforeach
    </div>
</div>


@endsection
