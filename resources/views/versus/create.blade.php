@extends('layouts.app')


@section('content')

<a href="{{ route('versus.index') }}" class="btn btn-outline-primary mr-2 text-uppercase font-weight-bold m-4">
    Volver
</a>

<h2 class="text-center mb-5">Añadir nuevo duelo</h2>

<div class="row justify-content-center mt-5">
    <div class="col-md-8">
    <form method="POST" action="{{ route('versus.store') }}" enctype="multipart/form-data" novalidate>
        @csrf
        @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

            <div class="form-group">
                <label for="user_id_1">Jugador 1 (VICTORIA)</label>

                <select name="user_id_1" id="user_id_1" class="form-control @error('nombre') is-invalid @enderror">
                        <option disabled selected>- Selecciona -</option>

                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>

                    @error('user_id_1')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
            </div>

            <div class="form-group">
                <label for="result_1">Resultado jugador 1</label>

                <input type="number"
                    name="result_1"
                    class="form-control @error('result_1') is-invalid @enderror"
                    id="result_1"
                    placeholder="0"
                    value="{{old('result_1')}}"
                    />

                    @error('url')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
            </div>

            <div class="form-group">
                <label for="user_id_2">Jugador 2 (DERROTA)</label>

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
                <label for="url">Resultado jugador 2</label>

                <input type="number"
                    name="result_2"
                    class="form-control @error('url') is-invalid @enderror"
                    id="result_2"
                    placeholder="0"
                    value="{{old('result_2')}}"
                    />

                    @error('url')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
            </div>

            <div class="form-group">
                <label for="modalidad">Modalidad</label>

                <select name="modalidad" id="modalidad" class="form-control @error('modalidad') is-invalid @enderror">
                        <option disabled selected>- Selecciona -</option>
                        <option value="beybladeburst">Beyblade Burst</option>
                        <option value="beybladex">Beyblade X</option>
                </select>

                    @error('modalidad')
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

