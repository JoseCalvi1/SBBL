@extends('layouts.app')

@section('title', 'Nacional 2025: ¡Revive el Torneo!')

@section('styles')
<style>
    /* --- CABECERA Y TÍTULO RESPONSIVE --- */
    .show-2025-header {
        border-bottom: 4px solid var(--shonen-cyan);
        padding-bottom: 20px;
        margin-bottom: 40px;
    }
    .show-2025-title {
        font-size: clamp(2.5rem, 6vw, 4.5rem); /* Título fluido y responsive */
        color: #fff;
        text-shadow: 2px 2px 0px var(--shonen-cyan), 4px 4px 0px #000;
        letter-spacing: 2px;
        line-height: 1.1;
    }

    /* --- SECCIONES TÁCTICAS (Vídeo, Bracket, Galería) --- */
    .tactical-section {
        background: var(--sbbl-blue-2);
        border: 3px solid #000;
        box-shadow: 8px 8px 0 #000;
        padding: 30px;
        margin-bottom: 40px;
    }
    .section-title {
        font-family: 'Oswald', cursive;
        color: var(--sbbl-gold);
        letter-spacing: 1px;
        font-size: 2rem;
        text-transform: uppercase;
        text-shadow: 2px 2px 0 #000;
        margin-bottom: 20px;
    }

    /* --- CONTENEDOR DEL BRACKET DE CHALLONGE --- */
    .challonge-wrapper {
        border: 2px solid var(--shonen-cyan);
        box-shadow: 0 0 15px rgba(0, 255, 204, 0.2);
        background: #111;
        border-radius: 4px;
        overflow: hidden;
    }

    /* --- GALERÍA INTELIGENTE Y EFECTO SKELETON --- */
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
    }
    .gallery-item {
        border: 2px solid #000;
        position: relative;
        overflow: hidden;
        height: 200px;
        cursor: pointer;
        /* Efecto Escáner/Skeleton mientras carga la imagen */
        background: linear-gradient(90deg, #111 25%, #222 50%, #111 75%);
        background-size: 200% 100%;
        animation: skeleton-loading 1.5s infinite;
    }
    @keyframes skeleton-loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    .gallery-img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 5px;
        transition: transform 0.3s ease;
        position: relative;
        z-index: 2; /* Para que la imagen quede por encima del esqueleto al cargar */
    }

    /* Efecto Lupa al pasar el ratón */
    .gallery-overlay {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 3;
    }
    .gallery-item:hover .gallery-overlay { opacity: 1; }
    .gallery-item:hover .gallery-img { transform: scale(1.05); }
    .gallery-overlay i {
        color: var(--shonen-cyan);
        font-size: 3rem;
        filter: drop-shadow(0 0 10px var(--shonen-cyan));
    }

    /* --- VISOR MODAL (LIGHTBOX) --- */
    .lightbox-modal .modal-content { background: transparent; border: none; }
    .lightbox-img {
        max-width: 100%;
        max-height: 85vh;
        object-fit: contain;
        border: 4px solid var(--shonen-cyan);
        box-shadow: 0 0 40px rgba(0, 255, 204, 0.3);
        background: #000;
    }
    .lightbox-close {
        position: absolute;
        top: -15px; right: -15px;
        background: var(--shonen-red);
        color: #fff;
        border: 2px solid #000;
        border-radius: 50%;
        width: 40px; height: 40px;
        font-size: 1.5rem;
        display: flex; justify-content: center; align-items: center;
        cursor: pointer;
        z-index: 1055;
        box-shadow: 2px 2px 0 #000;
        transition: transform 0.2s;
    }
    .lightbox-close:hover { transform: scale(1.1); }

    /* --- LLAMAMIENTO A COMPARTIR FOTOS (CTA) --- */
    .cta-section {
        background: linear-gradient(135deg, rgba(0, 255, 204, 0.05) 0%, rgba(0,0,0,0.6) 100%);
        border: 3px dashed var(--shonen-cyan);
        box-shadow: inset 0 0 20px rgba(0, 255, 204, 0.1);
        padding: 40px 20px;
        margin-bottom: 40px;
        text-align: center;
        transition: all 0.3s ease;
    }
    .cta-section:hover {
        background: linear-gradient(135deg, rgba(0, 255, 204, 0.1) 0%, rgba(0,0,0,0.8) 100%);
        border-style: solid;
        box-shadow: 0 0 20px rgba(0, 255, 204, 0.2);
    }
    .cta-title {
        font-family: 'Oswald', cursive;
        color: #fff;
        letter-spacing: 2px;
        font-size: clamp(1.8rem, 4vw, 2.5rem);
        text-shadow: 2px 2px 0 #000;
        text-transform: uppercase;
    }

    /* Ajustes para móviles */
    @media (max-width: 768px) {
        .tactical-section { padding: 20px 15px; }
        .gallery-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
        .gallery-item { height: 150px; }
    }
</style>
@endsection

@section('content')
<div class="container py-5">

    {{-- CABECERA --}}
    <div class="show-2025-header d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
        <div>
            <h1 class="show-2025-title m-0"><i class="fas fa-history me-2"></i> GRAN NACIONAL 2025</h1>
            <p class="text-white-50 fw-bold m-0 fs-5 mt-2">EL REGISTRO DEFINITIVO DEL TORNEO.</p>
        </div>

        <a href="{{ route('nacional.inicio') }}" class="btn-shonen btn-shonen-warning fs-5">
            <span><i class="fas fa-arrow-left me-2"></i> VOLVER AL PORTAL</span>
        </a>
    </div>

    {{-- VÍDEO DESTACADO --}}
    <div class="tactical-section">
        <h2 class="section-title"><i class="fab fa-youtube me-2"></i> MULTIMEDIA</h2>
        <div class="ratio ratio-16x9 border border-dark border-3 shadow">
            {{-- Cambia ID_VIDEO_YOUTUBE por el ID del vídeo que corresponda --}}
            <iframe src="https://www.youtube.com/embed/wkFnz8kPs3M?si=Dot6IDj6hHy7WqLc" title="SBBL Nacional 2025" allowfullscreen></iframe>
        </div>
    </div>

    {{-- BRACKET CHALLONGE --}}
    <div class="tactical-section">
        <h2 class="section-title text-info"><i class="fas fa-sitemap me-2"></i> PANEL DE COMBATES Y PODIO</h2>
        <p class="text-white-50 fw-bold mb-4">Explora el árbol del torneo, los enfrentamientos directos y la clasificación final del Nacional 2025.</p>

        <div class="challonge-wrapper">
            <iframe src="https://challonge.com/es/GranNacionalSBBL2025/module" width="100%" height="600" frameborder="0" scrolling="auto" allowtransparency="true"></iframe>
        </div>
    </div>

    {{-- GALERÍA DE FOTOS INTELIGENTE --}}
    <div class="tactical-section">
        <h2 class="section-title"><i class="fas fa-camera-retro me-2"></i> ARCHIVO FOTOGRÁFICO</h2>

        @if(count($fotos) > 0)
            <div class="gallery-grid">
                @foreach($fotos as $foto)
                    <div class="gallery-item" onclick="openLightbox('{{ $foto }}')">
                        <img src="{{ $foto }}" class="gallery-img" alt="Nacional 2025" loading="lazy" decoding="async">
                        <div class="gallery-overlay">
                            <i class="fas fa-search-plus"></i>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5 text-white-50 fs-5 fw-bold border border-secondary" style="background: rgba(0,0,0,0.3);">
                <i class="fas fa-images fa-3x mb-3 text-secondary"></i><br>
                PROCESANDO IMÁGENES...<br>
                <small class="fw-normal">No se han detectado archivos en el directorio táctico.</small>
            </div>
        @endif
    </div>

    {{-- LLAMAMIENTO A COMPARTIR FOTOS (CTA) --}}
    <div class="cta-section">
        <i class="fas fa-sd-card mb-3" style="font-size: 4rem; color: var(--shonen-cyan); filter: drop-shadow(0 0 10px rgba(0,255,204,0.5));"></i>
        <h3 class="cta-title">¿TIENES MÁS RECUERDOS?</h3>
        <p class="text-white-50 fw-bold fs-5 mb-4" style="max-width: 700px; margin: 0 auto;">
            Si capturaste momentos épicos durante la batalla y quieres que formen parte del Archivo Histórico de la SBBL, envíanos tu material.
        </p>

        <a href="mailto:admin@sbbl.es?subject=Fotos%20Nacional%202025" class="btn-shonen px-4 py-3" style="background: var(--shonen-cyan); color: #000; border-color: #000; font-size: clamp(1rem, 3vw, 1.5rem);">
            <span><i class="fas fa-envelope-open-text me-2"></i> ENVIAR FOTOS AL ALTO MANDO</span>
        </a>
    </div>

</div>

@endsection

@section('scripts')
<div class="modal fade lightbox-modal" id="photoLightbox" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content text-center position-relative">
            <div class="lightbox-close" data-bs-dismiss="modal"><i class="fas fa-times"></i></div>
            <div class="modal-body p-0">
                <img src="" id="lightboxImage" class="lightbox-img" alt="Foto ampliada">
            </div>
        </div>
    </div>
</div>
<script>
    // Función para el Visor Táctico de la galería
    function openLightbox(imageSrc) {
        document.getElementById('lightboxImage').src = imageSrc;
        var myModal = new bootstrap.Modal(document.getElementById('photoLightbox'));
        myModal.show();
    }
</script>
@endsection
