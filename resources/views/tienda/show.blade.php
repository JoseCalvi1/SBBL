@extends('layouts.app')

@section('content')
<div class="container py-5 text-white">
    <div>
        <div class="row g-4">
            <div class="col-md-6">
                @if($producto->fotos && is_array($producto->fotos) && count($producto->fotos))
                    <div id="carouselFotos" class="carousel slide rounded overflow-hidden" data-bs-ride="carousel" style="max-height: 500px;">
                        <div class="carousel-inner">
                            @foreach($producto->fotos as $index => $foto)
                                <div class="carousel-item @if($index === 0) active @endif">
                                    <img src="data:image/png;base64,{{ $foto }}" class="d-block w-100 object-fit-cover" alt="Imagen {{ $index + 1 }}" style="height: 500px;">
                                </div>
                            @endforeach
                        </div>
                        @if(count($producto->fotos) > 1)
                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselFotos" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Anterior</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carouselFotos" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Siguiente</span>
                            </button>
                        @endif
                    </div>
                @else
                    <img src="https://via.placeholder.com/500x500?text=Sin+imagen" class="img-fluid rounded" alt="Sin imagen" style="max-height: 500px;">
                @endif
            </div>

            <div class="col-md-6 d-flex flex-column justify-content-center">
                <div class="px-3">
                    <h2 class="mb-3">{{ $producto->nombre }}</h2>
                    <p class="mb-4" style="white-space: pre-line;">{{ $producto->descripcion }}</p>

                    @if($producto->enlaces && is_array($producto->enlaces) && count($producto->enlaces))
                        <div class="mb-4">
                            @foreach($producto->enlaces as $enlace)
                                <a href="{{ $enlace }}" target="_blank" class="btn btn-outline-primary me-2 mb-2">
                                    Ver más / Comprar
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <a href="{{ route('tienda.index') }}" class="btn btn-secondary">← Volver a la tienda</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
