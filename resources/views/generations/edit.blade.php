@extends('layouts.app')


@section('content')

<a href="{{ route('generations.versus') }}" class="btn btn-outline-primary mr-2 text-uppercase font-weight-bold m-4">
    Volver
</a>

<div class="text-center">
    <h2 class="font-weight-bold mb-5">Introducir resultados</h2>
    <h3>{{ $versus->versus_1->name }} VS {{ $versus->versus_2->name }}</h3>

    <p style="color:green">{{ $versus->status }}</p>
    <h4 class="font-weight-bold mt-2">Enfrentamientos</h4>
    <p>{!! $versus->matchup !!}</p>
    <div class="row justify-content-center m-0 mt-5">
        <div class="col-md-8">
        <form method="POST" action="{{ route('generations.update', ['versus' => $versus->id]) }}" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="winner">Ganador</label>

                <select name="winner" id="winner" class="form-control @error('winner') is-invalid @enderror">
                        <option disabled selected>- Selecciona -</option>

                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>

                    @error('winner')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
            </div>

            <div class="form-group">
                <label for="result">Resultado</label>

                <input type="text"
                    name="result"
                    class="form-control @error('result') is-invalid @enderror"
                    id="result"
                    placeholder="0 - 0"
                    value="{{old('result')}}"
                    />

                    @error('result')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
            </div>

                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Enviar duelo">
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

