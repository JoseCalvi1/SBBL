@extends('layouts.app')

@section('content')
<a href="{{ route('versus.index') }}" class="btn btn-outline-primary mr-2 text-uppercase font-weight-bold m-4">
    Volver
</a>

<h2 class="text-center mb-5 text-white">Editar Duelo</h2>

<div class="row justify-content-center mt-5">
    <div class="col-md-8">
        <form method="POST" action="{{ route('versus.update', $duel->id) }}" enctype="multipart/form-data" novalidate style="color: white">
            @csrf
            @method('PUT')

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <div class="form-group">
                <label for="user_id_1">Jugador 1</label>
                <select name="user_id_1" id="user_id_1" class="form-control select2 @error('user_id_1') is-invalid @enderror">
                    <option disabled>- Selecciona -</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ $duel->user_id_1 == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
                @error('user_id_1')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="result_1">Resultado jugador 1</label>
                <input type="number"
                       name="result_1"
                       class="form-control @error('result_1') is-invalid @enderror"
                       id="result_1"
                       value="{{ $duel->result_1 }}"
                />
                @error('result_1')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="user_id_2">Jugador 2</label>
                <select name="user_id_2" id="user_id_2" class="form-control select2 @error('user_id_2') is-invalid @enderror">
                    <option disabled>- Selecciona -</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ $duel->user_id_2 == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
                @error('user_id_2')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="result_2">Resultado jugador 2</label>
                <input type="number"
                       name="result_2"
                       class="form-control @error('result_2') is-invalid @enderror"
                       id="result_2"
                       value="{{ $duel->result_2 }}"
                />
                @error('result_2')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="modalidad">Modalidad</label>
                <select name="modalidad" id="modalidad" class="form-control @error('modalidad') is-invalid @enderror">
                    <option disabled>- Selecciona -</option>
                    <option value="beybladeburst" {{ $duel->matchup == 'beybladeburst' ? 'selected' : '' }}>Beyblade Burst</option>
                    <option value="beybladex" {{ $duel->matchup == 'beybladex' ? 'selected' : '' }}>Beyblade X</option>
                </select>
                @error('modalidad')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="status">Estado</label>
                <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                    <option value="OPEN" {{ $duel->status == 'OPEN' ? 'selected' : '' }}>Enviado</option>
                    <option value="CLOSED" {{ $duel->status == 'CLOSED' ? 'selected' : '' }}>Válido</option>
                    <option value="INVALID" {{ $duel->status == 'INVALID' ? 'selected' : '' }}>Inválido</option>
                </select>
                @error('status')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Guardar cambios">
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
    <!-- CDN de jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- CDN de Select2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script>
        jQuery(document).ready(function() {
            jQuery('.select2').select2();
        });
    </script>
@endsection
