@extends('layouts.app')

@section('content')

<div class="container mt-5">
    <div class="row">
        <div class="col-md-4">
            @if ($profile->imagen)
                <img src="/storage/{{ $profile->imagen }}" class="rounded-circle" width="250">
            @endif

        </div>
        <div class="col-md-8">
            <h2 class="text-center mb-2 mt-5 mt-md-0 text-primary">{{ $profile->user->name }}</h2>
            <h3 class="text-center mb-2 mt-5 mt-md-0 text-primary">{{ $profile->user->email }}</h3>
            <h3 class="text-center mb-2 mt-5 mt-md-0 text-primary">Región: @if ($region)
                {{ $region->name }}
            @else
                Por definir
            @endif</h3>
            <a href="{{ route('profiles.edit', ['profile' => Auth::user()->id]) }}" class="btn btn-outline-info mr-2 text-uppercase font-weight-bold w-100">
                Editar perfil
            </a>

        </div>
    </div>
</div>

<h3 class="text-center my-5">Videos subidos por {{ $profile->user->name }} WIP</h3>


@endsection
