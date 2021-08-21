@extends('layouts.app')

@section('content')

<a href="{{ route('profiles.show', $profile) }}" class="btn btn-outline-primary m-4 text-uppercase font-weight-bold">
    <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
    </svg>
    Volver
</a>

    <h1 class="text-center mt-2">Editar mi perfil</h1>

    <div class="row justify-content-center mt-5">
        <div class="col-md-10 bg-white p-3">
            <form  action="{{ route('profiles.update', ['profile' => $profile->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="nombre">Nombre</label>

                    <input type="text"
                        name="nombre"
                        class="form-control @error('nombre') is-invalid @enderror"
                        id="nombre"
                        placeholder="Tu nombre"
                        value="{{ $profile->user->name }}"
                        />

                        @error('nombre')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{$message}}</strong>
                            </span>
                        @enderror
                </div>

                <div class="form-group">
                    <label for="region_id">Región</label>

                    <select name="region_id" id="region_id" class="form-control @error('nombre') is-invalid @enderror">
                        @if ($regionT)
                            <option value="{{ $regionT->id }}">{{ $regionT->name }}</option>
                        @else
                            <option disabled selected>- Selecciona -</option>
                        @endif

                        @foreach ($regions as $region)
                            <option value="{{ $region->id }}">{{ $region->name }}</option>
                        @endforeach
                    </select>

                        @error('region_id')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{$message}}</strong>
                            </span>
                        @enderror
                </div>

                <div class="form-group">
                    <label for="default_img">Imagen por defecto</label>

                    <select name="default_img" id="default_img" class="form-control @error('nombre') is-invalid @enderror">
                        <option disabled selected>- Selecciona una imagen -</option>
                        <option value="LuiShirasagijo">Lui Shirasagijo</option>
                        <option value="ValtAoi">Valt Aoi</option>
                        <option value="Aiga">Aiga Akaba</option>
                        <option value="ShuKurenai">Shu Kurenai</option>
                        <option value="LeanValhalla">Lean Valhalla</option>
                        <option value="RanzoKiyama">Ranzo Kiyama</option>
                        <option value="BellDaikokuten">Bell Daikokuten</option>
                    </select>

                        @error('default_img')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{$message}}</strong>
                            </span>
                        @enderror
                </div>


                <div class="form-group mt-4">
                    <label for="imagen">Tu imagen</label>
                    <input
                        id="imagen"
                        type="file"
                        class="form-control @error('imagen') is-invalid @enderror"
                        name="imagen" />

                        @if ($profile->imagen)
                            <div class="mt-4">
                                <p>Imagen Actual:</p>
                                <img src="/storage/{{ $profile->imagen }}" style="width: 300px;">
                            </div>
                            @error('imagen')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{$message}}</strong>
                                    </span>
                            @enderror
                    @endif
                </div>

                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Actualizar perfil">
                </div>
            </form>
        </div>
    </div>


@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.js"
        integrity="sha512-/1nVu72YEESEbcmhE/EvjH/RxTg62EKvYWLG3NdeZibTCuEtW5M4z3aypcvsoZw03FAopi94y04GhuqRU9p+CQ=="
        crossorigin="anonymous" defer></script>
@endsection
