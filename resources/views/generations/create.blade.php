@extends('layouts.app')


@section('content')

<a href="{{ route('generations.index') }}" class="btn btn-outline-primary mr-2 text-uppercase font-weight-bold m-4">
    Volver
</a>

<h2 class="text-center mb-5">Añadir nuevo duelo</h2>

<div class="row justify-content-center m-0 mt-5">
    <div class="col-md-8">
    <form method="POST" action="{{ route('generations.gstore') }}" enctype="multipart/form-data" novalidate>
        @csrf


            <div class="form-group">
                <label for="user_id_1">Jugador 1</label>

                <select name="user_id_1" id="user_id_1" class="form-control @error('nombre') is-invalid @enderror">
                        <option value="{{ Auth::user()->id }}">{{ Auth::user()->name }}</option>
                </select>

                    @error('user_id_1')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
            </div>

            <div class="form-group">
                <label for="user_id_2">Jugador 2</label>

                <select name="user_id_2" id="user_id_2" class="form-control @error('nombre') is-invalid @enderror">
                        <option disabled selected>- Selecciona -</option>

                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>

                    @error('user_id_2')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
            </div>

            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Agregar duelo">
            </div>
        </form>
    </div>
</div>

@endsection

