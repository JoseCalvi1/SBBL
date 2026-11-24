@extends('layouts.app')

@section('title', 'Tienda Oficial SBBL')

@section('styles')
<style>
    .store-header { border-bottom: 4px solid var(--sbbl-gold); padding-bottom: 20px; margin-bottom: 40px; }
    .store-title { font-family: 'Oswald', cursive; font-size: 3.5rem; color: var(--text-bright); text-shadow: 2px 2px 0 #000, 4px 4px 0 var(--shonen-cyan); text-transform: uppercase; letter-spacing: 2px; }
    .product-card { background: var(--sbbl-blue-2); border: 3px solid #000; box-shadow: 8px 8px 0 #000; transition: transform 0.2s, box-shadow 0.2s; }
    .product-card:hover { transform: translate(-3px, -3px); box-shadow: 11px 11px 0 var(--sbbl-gold); border-color: var(--sbbl-gold); }
    .product-img-wrapper { border-bottom: 3px solid #000; position: relative; overflow: hidden; background: #000; }
    .product-card:hover .product-img { transform: scale(1.05); }
    .product-title { font-family: 'Oswald', cursive; color: var(--shonen-cyan); letter-spacing: 1px; font-size: 1.8rem; text-transform: uppercase; text-shadow: 2px 2px 0 #000; }
    .product-price { font-family: 'Oswald', cursive; font-size: 2rem; color: var(--sbbl-gold); text-shadow: 2px 2px 0 #000; }
    .custom-select { background-color: #111 !important; border: 2px solid #555 !important; color: #fff !important; font-weight: bold; border-radius: 0; }
    .custom-select:focus { border-color: var(--shonen-cyan) !important; box-shadow: none; }
    .producto-img { transition: transform 0.3s ease, opacity 0.15s ease; cursor: pointer; }
    .image-lightbox .modal-content { background: transparent; border: none; }
    .image-lightbox .modal-header { border: none; padding: 0; position: absolute; top: -15px; right: -15px; z-index: 10; }
    .image-lightbox .btn-close { background-color: var(--shonen-red); opacity: 1; border: 2px solid #fff; border-radius: 50%; padding: 10px; }
</style>
@endsection

@section('content')
<div class="container py-5">

    <div class="store-header d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
        <div>
            <h1 class="store-title m-0 text-white"><i class="fas fa-store me-3"></i> TIENDA MERCH SBBL</h1>
            <p class="text-white-50 fw-bold m-0 fs-5 mt-2">
                @if($shopSettings->shipping_enabled && $shopSettings->pickup_enabled)
                    RECOGIDA EN EVENTO OFICIAL O ENVÍO A DOMICILIO DISPONIBLE.
                @elseif($shopSettings->shipping_enabled)
                    ENVÍO A DOMICILIO DISPONIBLE EN TODA ESPAÑA.
                @else
                    {{ mb_strtoupper($shopSettings->pickup_label) }}
                @endif
            </p>
        </div>

        <div class="d-flex gap-2">
            <button class="btn-shonen btn-shonen-info fs-5" data-bs-toggle="modal" data-bs-target="#infoTiendaModal">
                <span><i class="fas fa-info-circle me-1"></i> CONDICIONES</span>
            </button>

            @php
                $carritoSesion = \App\Models\Carrito::where('session_id', request()->session()->getId())
                                    ->where('estado_pago', 'pendiente')
                                    ->with('productos')
                                    ->first();
                $cantidadCarrito = $carritoSesion ? $carritoSesion->productos->sum(fn($p) => $p->pivot->cantidad) : 0;
            @endphp

            <a href="{{ route('carrito.show') }}" class="btn-shonen btn-shonen-warning fs-5 position-relative">
                <span>
                    <i class="fas fa-shopping-cart me-2"></i> VER CARRITO
                    @if($cantidadCarrito > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-dark" style="font-family: 'Oswald', sans-serif;">
                            {{ $cantidadCarrito }}
                        </span>
                    @endif
                </span>
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-shonen alert-shonen-success text-center mb-5"><div><i class="fas fa-check-circle me-2"></i> {{ session('success') }}</div></div>
    @endif
    @if (session('error'))
        <div class="alert alert-shonen alert-shonen-danger text-center mb-5"><div><i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}</div></div>
    @endif
    @if ($errors->any())
        <div class="alert alert-shonen alert-shonen-danger text-center mb-5">
            <div>
                <ul class="mb-0 list-unstyled">
                    @foreach ($errors->all() as $error) <li><i class="fas fa-times me-2"></i>{{ $error }}</li> @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="row g-5">
        @forelse($productos as $producto)
        <div class="col-md-6 col-lg-4">
            <div class="product-card h-100 d-flex flex-column">

                <div class="product-img-wrapper" style="height: 350px;">
                    <img src="{{ $producto->fotos }}" class="producto-img img-fluid h-100 w-100 object-fit-contain p-3" alt="{{ $producto->nombre }}" data-bs-toggle="modal" data-bs-target="#imageLightboxModal" onclick="openLightbox(this.src)">
                    @if($producto->stock <= 0)
                        <span class="badge bg-danger border border-dark position-absolute top-0 end-0 m-3 fs-6 font-Oswald">AGOTADO</span>
                    @else
                        <span class="badge bg-dark border border-secondary position-absolute top-0 end-0 m-3 small">Stock: {{ $producto->stock }}</span>
                    @endif
                </div>

                <div class="p-4 d-flex flex-column flex-grow-1">
                    <h5 class="product-title">{{ $producto->nombre }}</h5>
                    <p class="text-white-50 fw-bold mb-4" style="min-height: 48px;">{{ $producto->descripcion }}</p>

                    <div class="d-flex justify-content-between align-items-center mb-4 border-top border-bottom border-dark py-2" style="background: rgba(0,0,0,0.3);">
                        <span class="text-white fw-bold ps-2">PRECIO UNITARIO:</span>
                        <span class="product-price pe-2">{{ number_format($producto->precio, 2, ',', '.') }} €</span>
                    </div>

                    <form action="{{ route('carrito.add', $producto->id) }}" method="POST" class="mt-auto">
                        @csrf

                        <div class="row g-2 mb-3">
                            @if(count($producto->colores) > 0)
                            <div class="col-6">
                                <label class="text-white fw-bold small mb-1">COLOR</label>
                                <select name="color" class="form-select custom-select color-select" data-producto="{{ $producto->id }}">
                                    <option value="{{ $producto->fotos }}">Original</option>
                                    @foreach($producto->colores as $color)
                                        <option value="{{ $color['foto'] ?? '' }}">{{ $color['nombre'] ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            @if(count($producto->tallas) > 0)
                            <div class="col-6">
                                <label class="text-white fw-bold small mb-1">TALLA</label>
                                <select name="talla" class="form-select custom-select">
                                    @foreach($producto->tallas as $talla)
                                        <option value="{{ $talla }}">{{ $talla }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>

                        @if($producto->stock > 0)
                            <div class="d-flex gap-2">
                                <input type="number" name="cantidad" value="1" min="1" max="{{ $producto->stock }}" class="form-control custom-select text-center fs-5" style="width: 80px;">
                                <button type="submit" class="btn btn-success fw-bold border-2 border-dark flex-grow-1 fs-5 font-Oswald" style="border-radius: 0; letter-spacing: 1px;">
                                    AÑADIR <i class="fas fa-cart-plus ms-1"></i>
                                </button>
                            </div>
                        @else
                            <button type="button" disabled class="btn btn-secondary fw-bold border-2 border-dark w-100 fs-5 font-Oswald" style="border-radius: 0;">
                                SIN STOCK
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="fas fa-box-open fa-4x text-secondary mb-4"></i>
                <h3 class="font-Oswald text-white fs-1 mb-3">CATÁLOGO SIN ARTÍCULOS</h3>
                <p class="text-white-50 fw-bold fs-5">Aún no hay artículos disponibles en la tienda. Vuelve pronto.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection

@section('scripts')
<div class="modal fade" id="infoTiendaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border: 3px solid var(--shonen-cyan); border-radius: 0; background: var(--sbbl-blue-2);">
            <div class="modal-header border-bottom border-dark" style="background: #000;">
                <h5 class="modal-title font-Oswald fs-3 text-white">
                    <i class="fas fa-info-circle me-2" style="color: var(--shonen-cyan);"></i> CONDICIONES DE COMPRA
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4 text-white">
                <div class="p-3 mb-4 border border-secondary" style="background: rgba(0,0,0,0.5);">
                    <h5 class="font-Oswald" style="color: var(--sbbl-gold);"><i class="fas fa-truck-pickup me-2"></i> 1. MODALIDAD DE ENTREGA</h5>
                    <p class="mb-0 fw-bold">
                        {{ $shopSettings->pickup_instructions ?: 'Tu pedido quedará registrado con tu código de referencia y correo electrónico para su recogida presencial en evento oficial SBBL.' }}
                        @if($shopSettings->shipping_enabled)
                            <br><span class="text-info">También dispones de opción de envío a domicilio seleccionándolo en el carrito (+{{ number_format($shopSettings->shipping_cost, 2, ',', '.') }} €).</span>
                        @endif
                    </p>
                </div>
                <div class="p-3 mb-4 border border-secondary" style="background: rgba(0,0,0,0.5);">
                    <h5 class="font-Oswald" style="color: var(--shonen-cyan);"><i class="fas fa-credit-card me-2"></i> 2. PAGO VERIFICADO</h5>
                    <p class="mb-0 fw-bold">Todos los pagos se procesan en tiempo real a través de <strong>PayPal Business</strong> (cuenta PayPal o Tarjeta bancaria). No es obligatorio tener cuenta en la web para comprar merch.</p>
                </div>
                <div class="p-3 border border-secondary" style="background: rgba(0,0,0,0.5);">
                    <h5 class="font-Oswald" style="color: var(--shonen-red);"><i class="fas fa-id-badge me-2"></i> 3. COMPROBANTE Y REFERENCIA</h5>
                    <p class="mb-0 fw-bold">Al completar el pago obtendrás tu código único de referencia (ej. <code>SBBL-XXXXXX</code>) y recibirás confirmación por correo electrónico.</p>
                </div>
            </div>
            <div class="modal-footer border-top border-dark" style="background: #000;">
                <button type="button" class="btn fw-bold border border-secondary text-white bg-dark" data-bs-dismiss="modal">ENTENDIDO</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade image-lightbox" id="imageLightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0 text-center position-relative">
                <img id="lightboxImage" src="" class="img-fluid" style="border: 4px solid var(--sbbl-gold); box-shadow: 0 0 30px rgba(0,0,0,0.8); max-height: 85vh; object-fit: contain; background: #000;">
            </div>
        </div>
    </div>
</div>
<script>
function openLightbox(imageSrc) {
    document.getElementById('lightboxImage').src = imageSrc;
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.color-select').forEach(function(select){
        select.addEventListener('change', function(){
            const card = this.closest('.product-card');
            const img = card.querySelector('.producto-img');
            if(this.value) {
                img.style.opacity = 0;
                setTimeout(() => {
                    img.src = this.value;
                    img.style.opacity = 1;
                }, 150);
            }
        });
    });
});
</script>
@endsection
