@extends('layouts.app')

@section('content')
<div class="container text-white position-relative">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        <h1 class="m-0">Productos</h1>

        <!-- Botón de información -->
        <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#infoTiendaModal">
            <i class="fas fa-info-circle me-1"></i> Cómo funciona la tienda
        </button>

        @php
        use Illuminate\Support\Facades\DB;
        use Illuminate\Support\Facades\Auth;

        $coinCount = 0;

        if (Auth::check()) {
            $trophyId = DB::table('trophies')->where('name', 'SBBL Coin')->value('id');

            if ($trophyId) {
                $coinCount = DB::table('profilestrophies')
                    ->where('trophies_id', $trophyId)
                    ->where('profiles_id', Auth::id())
                    ->value('count') ?? 0;
            }
        }
        @endphp

        <div class="bg-dark rounded-pill px-3 py-2 shadow-sm">
            <span class="fw-bold text-warning">
                Lagartos: {{ number_format($coinCount) }} <span class="me-1">🦎</span>
            </span>
        </div>
    </div>

    <div class="row g-4">
        @foreach($productos as $producto)
        <div class="col-md-4">
            <div class="card bg-dark text-white h-100 shadow-sm rounded-4">
                <div class="card-img-top position-relative" style="overflow:hidden; height:300px;">
                    <img src="{{ $producto->fotos }}" class="producto-img img-fluid h-100 w-100 object-fit-cover" alt="{{ $producto->nombre }}">
                </div>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">{{ $producto->nombre }}</h5>
                    <p class="card-text text-truncate" title="{{ $producto->descripcion }}">{{ $producto->descripcion }}</p>
                    <p class="card-text fs-5 fw-bold">
                        {{ $producto->precio }} €
                        @if (Auth::user())
                             / {{ $producto->precio * 150 }} <span class="me-1">🦎</span>
                        @endif
                    </p>

                    <form action="{{ route('carrito.add', $producto->id) }}" method="POST" class="mt-auto">
                        @csrf

                        {{-- Colores --}}
                        @if(count($producto->colores) > 0)
                        <div class="mb-2">
                            <label>Color:</label>
                            <select name="color" class="form-select color-select" data-producto="{{ $producto->id }}">
                                <option value="{{ $producto->fotos }}">Original</option>
                                @foreach($producto->colores as $color)
                                    <option value="{{ $color['foto'] ?? '' }}">{{ $color['nombre'] ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        {{-- Tallas --}}
                        @if(count($producto->tallas) > 0)
                        <div class="mb-2">
                            <label>Talla:</label>
                            <select name="talla" class="form-select">
                                @foreach($producto->tallas as $talla)
                                    <option value="{{ $talla }}">{{ $talla }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="d-flex mb-2">
                            <input type="number" name="cantidad" value="1" min="1" class="form-control me-2">
                            <button type="submit" class="btn btn-success flex-shrink-0">Añadir</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>


@endsection

@section('scripts')
<!-- Modal informativo -->
<div class="modal fade text-dark" id="infoTiendaModal" tabindex="-1" aria-labelledby="infoTiendaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="infoTiendaModalLabel">
                    <i class="fas fa-store me-2"></i>Cómo funciona la tienda
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <p>🛒 <strong>1. Añade productos al carrito:</strong> Elige los artículos que quieras comprar y añádelos a tu carrito desde esta página.</p>

                <p>📦 <strong>2. Solicita la compra:</strong> Desde el carrito podrás enviar una <strong>solicitud de compra</strong>. La transacción se gestionará mediante <strong>Vinted</strong>, donde deberás pagar el precio del paquete y el envío.</p>

                <hr>

                <h6 class="fw-bold text-primary">💶 Pago con dinero real</h6>
                <ul>
                    <li>Se realiza un pago dividido:</li>
                    <li>➡️ <strong>50%</strong> se paga directamente en esta web mediante la pasarela de <strong>PayPal</strong>.</li>
                    <li>➡️ El otro <strong>50% + envío</strong> se paga en <strong>Vinted</strong>.</li>
                </ul>

                <h6 class="fw-bold text-warning mt-3"><i class="fas fa-coins me-1"></i>🪙 Pago con SBBL Coins</h6>
                <ul>
                    <li>Solo disponible para usuarios <strong>registrados</strong> en la web.</li>
                    <li>Los SBBL Coins se consiguen participando en <strong>Gran Copas</strong> o <strong>Copas PayPal</strong>.</li>
                    <li>Si pagas con coins, <strong>solo tendrás que abonar el pago mínimo de 1 €</strong> que exige Vinted, además del envío.</li>
                </ul>

                <hr>

                <p>📧 Tras completar la solicitud, <strong>se te contactará al correo electrónico</strong> que indiques para coordinar el envío y el método de pago.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.color-select').forEach(function(select){
        select.addEventListener('change', function(){
            const card = this.closest('.card');
            const img = card.querySelector('.producto-img');
            if(this.value) {
                img.src = this.value;
            }
        });
    });
});
</script>
@endsection
