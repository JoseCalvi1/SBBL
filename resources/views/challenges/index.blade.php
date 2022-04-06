@extends('layouts.app')


@section('content')

    <div class="container">
        <h2 class="text-center my-2 font-weight-bold">Entrenamiento y desafíos</h2>
        <div class="row">
            @foreach ($challenges as $key => $challenge)
            <div class="col-md-4 px-2 my-3">
                <div class="{{ $challenge->difficulty }}">
                    {{ $challenge->name }}
                    @if (isset($challenge->profiles[0]) && $challenge->profiles[0]->pivot->challenges_id == $challenge->id && $challenge->profiles[0]->pivot->profiles_id == Auth::user()->id)
                    <form id="formD_{{ $key }}" method="POST" style="float:right;" action="{{ route('challenges.destroy', ['challenges_profiles_id' => $challenge->id]) }}" enctype="multipart/form-data" novalidate>
                        @csrf
                        @method('DELETE')
                        <button type="submit" name="delete_{{ $key }}" style="background: none;
                        border: 0;
                        color: inherit;
                        font: inherit;
                        line-height: normal;
                        overflow: visible;
                        padding: 0;
                        -webkit-user-select: none; /* for button */
                         -webkit-appearance: button; /* for input */
                           -moz-user-select: none;
                            -ms-user-select: none;">
                        <i class="fa fa-check p-1" style="float:right;border:2px solid green;color:green;border-radius:10px;"></i>
                        </button>
                    </form>
                    @else
                        <check-challenge challenges-profiles-id="{{ $challenge->id }}"></check-challenge>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

@endsection
