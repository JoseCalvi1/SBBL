@extends('layouts.app')

@section('content')
<div class="container text-white position-relative">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
        <h1 class="m-0">Productos</h1>

        @php
        use Illuminate\Support\Facades\DB;
        use Illuminate\Support\Facades\Auth;

        $coinCount = 0;

        if (Auth::check()) {
            // Buscar el ID del trofeo llamado "SBBL Coin"
            $trophyId = DB::table('trophies')->where('name', 'SBBL Coin')->value('id');

            if ($trophyId) {
                // Buscar en la tabla profilestrophies el registro del usuario actual y ese trofeo
                $coinCount = DB::table('profilestrophies')
                    ->where('trophies_id', $trophyId)
                    ->where('profiles_id', Auth::id())
                    ->value('count') ?? 0;
            }
        }
        @endphp

        <div class="bg-dark rounded-pill px-3 py-2 shadow-sm">
            <span class="fw-bold text-warning">
                SBBL Coins: <i class="fas fa-coins me-1"></i> {{ number_format($coinCount) }}
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
                             / {{ $producto->precio * 100 + 500 }} <i class="fas fa-coins"></i>
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
