@extends('layouts.app')

@section('content')
<div class="container py-5" style="min-height: 80vh;">
    <h1 class="text-center mb-5 text-white fw-bold" style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
        Productos de la Tienda
    </h1>

    @auth
        @if(auth()->user()->is_jury)
            <div class="text-center mb-4">
                <a href="{{ route('tienda.create') }}" class="btn btn-success px-4 py-2 shadow-sm">
                    <i class="bi bi-plus-circle me-2"></i> Añadir nuevo producto
                </a>
            </div>
        @endif
    @endauth

    <div class="row g-4">
        @forelse($productos as $producto)
            <div class="col-sm-6 col-md-4 col-lg-3">
                <a href="{{ route('tienda.show', $producto) }}" class="text-decoration-none">
                    <div class="card h-100 shadow-lg border-0 rounded-4 overflow-hidden position-relative"
                         style="transition: transform 0.3s ease, box-shadow 0.3s ease;">

                        @php $imagen = $producto->fotos[0] ?? null; @endphp

                        @if($imagen)
                            <img src="data:image/png;base64,{{ $imagen }}"
                                 class="card-img-top"
                                 alt="Imagen del producto"
                                 style="object-fit: cover; height: 220px; width: 100%;">
                        @else
                            <div class="d-flex align-items-center justify-content-center bg-secondary bg-opacity-25"
                                 style="height: 220px;">
                                <span class="text-white-50 fs-5">Sin imagen</span>
                            </div>
                        @endif

                        <div class="card-body bg-dark bg-opacity-75 text-white d-flex flex-column justify-content-between">
                            <h5 class="card-title fw-semibold mb-2 text-truncate" title="{{ $producto->nombre }}">{{ $producto->nombre }}</h5>
                            <p class="card-text text-white-50 small" style="min-height: 3.5rem;">
                                {{ Str::limit($producto->descripcion, 90) }}
                            </p>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12 text-center">
                <p class="text-white fs-5">No hay productos disponibles.</p>
            </div>
        @endforelse
    </div>
</div>

@endsection

@section('styles')

<style>
    a:hover .card {
        transform: translateY(-8px);
        box-shadow: 0 15px 25px rgba(0, 0, 0, 0.4);
    }
</style>
@endsection
