@extends('layouts.app')

@section('title', 'BEYCON 2026')

@section('styles')
<style>
    /* Estilos heredados de la estética Shonen/SBBL */
    .season-title {
        font-family: 'Oswald', cursive;
        font-size: 4rem;
        color: var(--sbbl-gold);
        text-shadow: 3px 3px 0 #000, 6px 6px 0 var(--shonen-blue);
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 1rem;
        line-height: 1.1;
    }

    .section-title {
        font-family: 'Oswald', cursive;
        font-size: 2.5rem;
        color: #fff;
        border-bottom: 4px solid var(--shonen-red);
        display: inline-block;
        padding-bottom: 5px;
        margin-bottom: 2rem;
        text-transform: uppercase;
    }

    .podium-card {
        background: var(--sbbl-blue-2);
        border: 3px solid #000;
        box-shadow: 6px 6px 0 var(--sbbl-gold);
        transition: transform 0.2s;
        height: 100%;
    }
    .podium-card:hover {
        transform: translateY(-5px);
        box-shadow: 8px 8px 0 var(--shonen-cyan);
    }
    .podium-1st { border-color: var(--sbbl-gold); }
    .podium-2nd { border-color: #c0c0c0; box-shadow: 6px 6px 0 #c0c0c0; }
    .podium-3rd { border-color: #cd7f32; box-shadow: 6px 6px 0 #cd7f32; }

    .stat-box {
        background: #000;
        border: 2px dashed var(--sbbl-gold);
        padding: 20px;
        text-align: center;
        transform: skewX(-5deg);
    }
    .stat-box > * { transform: skewX(5deg); }
    .stat-number { font-family: 'Oswald', cursive; font-size: 3rem; color: var(--shonen-cyan); line-height: 1; }
    .stat-label { font-weight: 900; color: #fff; text-transform: uppercase; letter-spacing: 1px; }

    .achievement-item {
        background: rgba(0,0,0,0.5);
        border-left: 5px solid var(--shonen-red);
        padding: 15px;
        margin-bottom: 15px;
    }

    .gallery-img-container {
        position: relative;
        border: 3px solid #000;
        overflow: hidden;
    }
    .gallery-item {
    position: relative; /* Súper importante para que el caption y el overlay no se salgan */
    overflow: hidden;
}

.gallery-caption {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    background: rgba(0,0,0,0.85); /* Fondo oscuro semitransparente */
    color: #fff;
    padding: 10px;
    font-size: 0.9rem;
    font-weight: bold;
    transform: translateY(100%); /* Lo esconde debajo del borde inicial */
    transition: transform 0.3s ease;
    pointer-events: none; /* Para que el clic vaya directo al Lightbox */
}

.gallery-item:hover .gallery-caption {
    transform: translateY(0); /* Sube la caja de texto al pasar el ratón */
}
/* --- ESTILOS DE LA CUADRÍCULA (GRID) --- */
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); /* Crea columnas adaptables de min 200px */
    gap: 15px; /* Espacio entre fotos */
}

/* Forzar que las imágenes mantengan su proporción sin alargarse */
.gallery-img {
    width: 100%;
    height: 200px; /* Puedes ajustar la altura a tu gusto */
    object-fit: cover; /* Esto evita que se aplasten o deformen */
    border: 3px solid #000;
    transition: transform 0.3s ease;
}

/* El fondo oscuro con la lupa al pasar el ratón */
.gallery-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    color: var(--sbbl-gold);
    font-size: 2rem;
    cursor: pointer;
}

.gallery-item:hover .gallery-overlay {
    opacity: 1; /* Muestra la lupa */
}

.gallery-item:hover .gallery-img {
    transform: scale(1.05); /* Efecto de zoom ligero en la foto */
}


</style>
@endsection

@section('content')
<div class="container-fluid py-5">

    {{-- HEADER DE TEMPORADA --}}
    <div class="row mb-5 text-center">
        <div class="col-12">
            <h1 class="season-title">BEYCON 2026</h1>
            <p class="text-white fw-bold fs-5 mx-auto" style="max-width: 800px;">
                El año que redefinió el panorama competitivo de la SBBL. Nuevos combos, sorpresas en la arena y el nacimiento de leyendas en el Gran Nacional.
            </p>
        </div>
    </div>

    {{-- ESTADÍSTICAS GLOBALES DE LA TEMPORADA --}}
    <div class="row g-4 mb-5 justify-content-center">
        <div class="col-6 col-md-3">
            <div class="stat-box">
                <div class="stat-number">1300+</div>
                <div class="stat-label">Bladers Activos</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-box">
                <div class="stat-number">300+</div>
                <div class="stat-label">Torneos</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-box">
                <div class="stat-number">89</div>
                <div class="stat-label">Equipos Formados</div>
            </div>
        </div>
    </div>

    {{-- SECCIÓN: PODIO GRAN NACIONAL --}}
    <div class="row mb-5">
        <div class="col-12 text-center mb-4">
            <h2 class="section-title"><i class="fas fa-trophy me-2 text-warning"></i> GRAN NACIONAL 2026</h2>
            <div class="mb-3">
                <a href="https://challonge.com/es/GRANNACIONALSBBL26" target="_blank" class="btn btn-outline-warning fw-bold font-Oswald">VER BRACKET</a>
            </div>
        </div>

        <div class="col-md-4 order-2 order-md-1 mb-4">
            <div class="card podium-card podium-2nd text-center p-4">
                <div class="font-Oswald text-white mb-2" style="font-size: 1.5rem; color: #c0c0c0 !important;">SUBCAMPEÓN</div>
                <h3 class="font-Oswald fs-2 mb-3">Wildbyt</h3>
                <div class="bg-black p-2 border border-secondary text-white fw-bold small">
                    <i class="fas fa-tools me-1 text-secondary"></i> Deck:<br>
                    Meteor Dragoon 5-60 · Level<br>
                    Pegasus Blast Free 9-60 · Hexa<br>
                    Shark Scale 1-70 · Jolt
                </div>
            </div>
        </div>

        <div class="col-md-4 order-1 order-md-2 mb-4">
            <div class="card podium-card podium-1st text-center p-4" style="transform: scale(1.05); z-index: 10;">
                <i class="fas fa-crown fa-3x mb-2" style="color: var(--sbbl-gold);"></i>
                <div class="font-Oswald mb-1" style="font-size: 2rem; color: var(--sbbl-gold);">CAMPEONA NACIONAL</div>
                <h3 class="font-Oswald fs-1 mb-3">Supereuyii</h3>
                <div class="bg-black p-3 border border-warning text-white fw-bold">
                    <i class="fas fa-star text-warning me-1"></i> Deck:<br>
                    Combo 1<br>Combo 2<br>Combo 3
                </div>
            </div>
        </div>

        <div class="col-md-4 order-3 order-md-3 mb-4">
            <div class="card podium-card podium-3rd text-center p-4">
                <div class="font-Oswald mb-2" style="font-size: 1.5rem; color: #cd7f32 !important;">3º PUESTO</div>
                <h3 class="font-Oswald fs-2 mb-3">Rageful Spark</h3>
                <div class="bg-black p-2 border border-secondary text-white fw-bold small">
                    <i class="fas fa-tools me-1 text-secondary"></i> Deck:<br>
                    Combo 1<br>Combo 2<br>Combo 3
                </div>
            </div>
        </div>
    </div>

    {{-- SECCIÓN: NACIONAL POR EQUIPOS --}}
    <div class="row mb-5 bg-black p-5 border border-dark shadow-lg">
        <div class="col-12 text-center mb-4">
            <h2 class="section-title" style="border-color: var(--shonen-cyan);"><i class="fas fa-shield-alt me-2" style="color: var(--shonen-cyan);"></i> NACIONAL POR EQUIPOS 2026</h2>
            <div class="mb-4">
                <a href="https://challonge.com/es/NACIONALEQUIPOSSBBL26" target="_blank" class="btn btn-outline-info fw-bold font-Oswald">VER BRACKET</a>
            </div>
        </div>

        <div class="col-md-4 text-center mb-4">
            <h4 class="font-Oswald mb-3" style="color: #c0c0c0;">2º PUESTO</h4>
            <div class="border border-secondary p-3" style="background: rgba(255,255,255,0.05);">
                <h5 class="text-white fw-bold">Bar Paco</h5>
                <p class="text-white small mb-0">Alexkorbo • Lil Fruto Seco • Supereuyii • Darwin</p>
            </div>
        </div>

        <div class="col-md-4 text-center mb-4">
            <h4 class="font-Oswald mb-3" style="color: var(--sbbl-gold);"><i class="fas fa-trophy me-2"></i>CAMPEONES</h4>
            <div class="border border-warning p-4" style="background: rgba(255, 193, 7, 0.1);">
                <h4 class="text-white fw-bold font-Oswald fs-3">Los Duendes Garrapatas</h4>
                <p class="text-white fw-bold mb-0">Tragabuche • Chupa Tinta • Caitlyn</p>
            </div>
        </div>

        <div class="col-md-4 text-center mb-4">
            <h4 class="font-Oswald mb-3" style="color: #cd7f32;">3º PUESTO</h4>
            <div class="border border-secondary p-3" style="background: rgba(255,255,255,0.05);">
                <h5 class="text-white fw-bold">Azure Inferno</h5>
                <p class="text-white small mb-0">IsraNewGen • Akane • MiDi</p>
            </div>
        </div>
    </div>

    {{-- SECCIÓN DIVIDIDA: LOGROS Y GALERÍA --}}
    <div class="row g-5">

        {{-- LOGROS / HITOS --}}
        <div class="col-lg-5">
            <h2 class="section-title"><i class="fas fa-medal me-2 text-danger"></i> HITOS DE LA TEMPORADA</h2>

            <div class="achievement-item">
                <h5 class="font-Oswald text-white mb-1" style="color: var(--sbbl-gold) !important;">PRIMER PUESTO INDIVIDUAL</h5>
                <p class="text-white-50 mb-0 fw-bold">Sesiomaru</p>
            </div>

            <div class="achievement-item border-info">
                <h5 class="font-Oswald text-white mb-1 text-info">PRIMER PUESTO EQUIPOS</h5>
                <p class="text-white-50 mb-0 fw-bold">Lightning Dragons</p>
            </div>

            <div class="achievement-item border-warning">
                <h5 class="font-Oswald text-white mb-1 text-warning">COMBO DEL AÑO</h5>
                <p class="text-white-50 mb-0 fw-bold">Wizard Rod | 1-60 · Hexa</p>
            </div>
        </div>

        {{-- GALERÍA CON CONTEXTO (CAPTIONS) --}}
<div class="col-lg-7">
    <h2 class="section-title"><i class="fas fa-camera-retro me-2 text-success"></i> MEMORIAS</h2>

    @if(count($fotos) > 0)
        <div class="gallery-grid">
            @foreach($fotos as $foto)
                @php
                    // Truco para la versión estática: Extraemos el nombre del archivo para hacer un caption automático
                    $filename = pathinfo($foto, PATHINFO_FILENAME);
                    $cleanName = strtoupper(str_replace(['_', '-'], ' ', $filename));
                @endphp

                <div class="gallery-item" onclick="openLightbox('{{ $foto }}')">
                    <img src="{{ $foto }}" class="gallery-img" alt="BEYCON" loading="lazy" decoding="async">

                    {{-- Overlay del Lupa --}}
                    <div class="gallery-overlay">
                        <i class="fas fa-search-plus"></i>
                    </div>

                    {{-- Caja de Contexto (Caption) que sube en el hover --}}
                    <div class="gallery-caption">
                        <span class="text-warning"><i class="fas fa-quote-left me-1"></i></span>
                        <span class="fw-bold">{{ $cleanName }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5 text-white-50 fs-5 fw-bold border border-secondary" style="background: rgba(0,0,0,0.3);">
            <i class="fas fa-images fa-3x mb-3 text-secondary"></i><br>
            PROCESANDO IMÁGENES...<br>
            <small class="fw-normal">No se han detectado archivos en el directorio.</small>
        </div>
    @endif
</div>

{{-- BOTÓN DE VOLVER (Asegurado dentro de un col-12 para no romper la cuadrícula) --}}
<div class="col-12 mt-5 text-center text-lg-start">
    <a href="{{ route('nacional.inicio') }}" class="btn-shonen btn-shonen-warning px-5 py-2">
        <span><i class="fas fa-arrow-left me-2"></i> VOLVER AL PORTAL</span>
    </a>
</div>
    </div>

</div>
@endsection

@section('scripts')
{{-- ESTRUCTURA DEL LIGHTBOX (Invisible por defecto) --}}
<div id="imageLightbox" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-header border-0 position-absolute top-0 end-0 z-3 p-3">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="background-color: #000; border: 2px solid #fff; opacity: 1; padding: 10px;"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="lightboxImage" src="" class="img-fluid border border-4 border-dark shadow-lg" style="max-height: 90vh; background: #000;">
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT PARA ABRIR LA FOTO --}}
<script>
    function openLightbox(imageUrl) {
        // Seleccionamos la imagen dentro del modal
        const lightboxImg = document.getElementById('lightboxImage');
        // Le asignamos la ruta de la foto clickada
        lightboxImg.src = imageUrl;

        // Abrimos el modal nativo de Bootstrap 5
        const modal = new bootstrap.Modal(document.getElementById('imageLightbox'));
        modal.show();
    }
</script>
@endsection
