@extends('layouts.app')


@section('content')

<a href="{{ route('versus.index') }}" class="btn btn-outline-primary mr-2 text-uppercase font-weight-bold m-4">
    Volver
</a>

<h2 class="text-center mb-5">Añadir nuevo duelo</h2>

<div class="row justify-content-center mt-5">
    <div class="col-md-8">
        <form method="POST" action="{{ route('versus.update', ['duel' => $duel->id]) }}" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')

            <div class="form-group">
                <label for="user_id_1">Jugador 1</label>

                <select name="user_id_1" id="user_id_1" class="form-control @error('nombre') is-invalid @enderror">
                    @if ($duel->versus_1)
                        <option value="{{ $duel->versus_1->id }}">{{ $duel->versus_1->name }}</option>
                    @else
                        <option disabled selected>- Selecciona -</option>
                    @endif

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
                <label for="user_id_2">Jugador 2</label>

                <select name="user_id_2" id="user_id_2" class="form-control @error('nombre') is-invalid @enderror">
                    @if ($duel->versus_2)
                        <option value="{{ $duel->versus_2->id }}">{{ $duel->versus_2->name }}</option>
                    @else
                        <option disabled selected>- Selecciona -</option>
                    @endif

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
                <label for="winner">Ganador</label>

                <select name="winner" id="winner" class="form-control @error('nombre') is-invalid @enderror">
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
                <label for="url">URL video</label>

                <input type="text"
                    name="url"
                    class="form-control @error('url') is-invalid @enderror"
                    id="url"
                    placeholder="Lugar del evento"
                    value="{{ $duel->url }}"
                    />

                    @error('url')
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

