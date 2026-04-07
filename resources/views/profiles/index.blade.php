@extends('layouts.app')

@section('title', 'Bladers SBBL')

@section('styles')
<style>
    /* ====================================================================
       ESTILOS ESPECÍFICOS DE LA PÁGINA BLADERS (El resto hereda del layout)
       ==================================================================== */

    /* ── TÍTULO DE PÁGINA ── */
    .page-title {
        font-family: 'Oswald', cursive;
        font-size: 3rem;
        color: var(--sbbl-gold);
        text-shadow: 2px 2px 0px #000, 4px 4px 0px var(--shonen-red);
        letter-spacing: 2px;
    }

    /* ── FILTROS ── */
    .filtros-box {
        background: var(--sbbl-blue-2);
        border: 3px solid #000;
        box-shadow: 6px 6px 0px #000;
        border-radius: 0;
        transform: skewX(-2deg);
    }
    .filtros-box > * { transform: skewX(2deg); }
    .filtros-box select {
        border: 2px solid #000;
        border-radius: 0;
        font-weight: 900;
        background: #111 !important;
        color: #fff !important;
    }
    .filtros-box select:focus { box-shadow: none; border-color: var(--sbbl-gold); }

    .btn-filter {
        background: var(--sbbl-gold);
        color: #000;
        border: 3px solid #000;
        font-family: 'Oswald', cursive;
        font-size: 1.2rem;
        border-radius: 0;
        box-shadow: 3px 3px 0 #000;
        transition: 0.2s;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .btn-filter:hover {
        transform: translate(-2px, -2px);
        box-shadow: 5px 5px 0 var(--shonen-red);
        background: #fff;
        color: #000;
    }

    /* ── TARJETAS DE BLADERS ── */
    .tarjeta {
        background-size: cover;
        background-position: center;
        border-radius: 0 15px 0 15px; /* Estilo panel shonen */
        border: 3px solid #000;
        box-shadow: 6px 6px 0px rgba(0,0,0,0.8);
        padding: 20px;
        padding-top: 100px;
        text-align: center;
        color: #fff;
        position: relative;
        overflow: hidden;
        transition: all .2s ease;
        cursor: pointer;
        min-height: 250px;
        background-color: var(--sbbl-blue-3);
    }
    .tarjeta:hover {
        transform: translate(-3px, -3px);
        box-shadow: 9px 9px 0px var(--sbbl-gold);
        border-color: var(--sbbl-gold);
    }

    .efecto-hover {
        position: absolute; inset: 0;
        background: linear-gradient(135deg, rgba(255,255,255,0.1), transparent);
        opacity: 0; transition: opacity .3s ease;
    }
    .tarjeta:hover .efecto-hover { opacity: 1; }

    .text-overlay {
        position: absolute; bottom: 0; left: 0; right: 0;
        z-index: 3;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.95) 0%, rgba(0, 0, 0, 0.7) 60%, transparent 100%);
        padding-top: 40px; padding-bottom: 45px;
        border-top: 3px solid #000;
    }

    /* Avatar en la tarjeta */
    .avatar-container {
        width: 100px; height: 100px;
        position: absolute; top: 10px; left: 50%;
        transform: translateX(-50%);
        display: flex; justify-content: center; align-items: center;
        z-index: 5;
    }
    .img-blader, .marco-blader {
        width: 90px; height: 90px;
        position: absolute; top: 5px; left: 5px;
    }
    /* Estilo de avatar (100% Redondo) */
    .img-blader {
        z-index: 1;
        border-radius: 50%;
        border: 2px solid var(--sbbl-gold);
        object-fit: cover;
    }
    .marco-blader { z-index: 2; border-radius: 50%; }

    .info { position: relative; z-index: 4; }
    .info h5 {
        font-family: 'Oswald', cursive;
        font-size: 1.6rem;
        letter-spacing: 1px;
        color: var(--sbbl-gold);
        text-shadow: 2px 2px 0 #000;
    }

    /* Etiqueta Open To Work */
    .free-agent-label {
        position: absolute; bottom: 10px; right: 10px;
        background: var(--shonen-cyan); color: #000;
        padding: 3px 8px; border: 2px solid #000;
        font-family: 'Oswald', cursive; font-size: 1rem;
        letter-spacing: 1px; z-index: 5;
        box-shadow: 2px 2px 0 #000;
        transform: skewX(-10deg);
        transition: background .3s;
    }
    .free-agent-label:hover { background: #fff; }

    .iconos {
        position: absolute; bottom: 10px; left: 10px;
        z-index: 10; display: flex; gap: 5px;
    }
    .iconos i {
        background: #000; border: 2px solid #fff; border-radius: 50%;
        padding: 5px; font-size: 0.8rem;
    }

    .texto-limitado {
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        overflow: hidden; text-overflow: ellipsis; min-height: 2.5em;
        font-weight: 700; font-size: 0.85rem; color: #ddd;
    }

    /* ── MODAL ESTILO SHONEN ── */
    .modal-content {
        background: var(--sbbl-blue-2);
        border: 4px solid #000;
        border-radius: 0 20px 0 20px;
        box-shadow: 10px 10px 0px #000;
        color: #fff;
    }
    .modal-header {
        background: #000;
        border-bottom: 3px solid var(--sbbl-gold);
        border-radius: 0 15px 0 0;
    }
    .modal-title { font-family: 'Oswald', cursive; font-size: 1.8rem; color: var(--sbbl-gold); letter-spacing: 1px; }

    .modal-avatar-container {
        width: 140px; height: 140px;
        position: relative; display: flex; justify-content: center; align-items: center;
    }
    .img-blader-modal, .marco-blader-modal { width: 130px; height: 130px; position: absolute; top: 5px; left: 5px; }
    .img-blader-modal {
        z-index: 1; border-radius: 50%; border: 3px solid var(--sbbl-gold);
        box-shadow: 4px 4px 0 #000; object-fit: cover;
    }
    .marco-blader-modal { z-index: 2; border-radius: 50%; }

    /* ── PAGINACIÓN ── */
    .pagination .page-item .page-link {
        background: #000; border: 2px solid #fff; color: #fff;
        font-family: 'Oswald', cursive; font-size: 1.2rem; border-radius: 0;
        margin: 0 3px; transform: skewX(-10deg);
        box-shadow: 3px 3px 0 #000;
    }
    .pagination .page-item.active .page-link {
        background: var(--sbbl-gold); color: #000; border-color: #000; box-shadow: 2px 2px 0 var(--shonen-red);
    }
</style>
@endsection

@section('content')
<div class="container py-4">
    <h2 class="text-center mb-4 mt-3 page-title">
        LISTA DE BLADERS ({{ count($bladers) }})
    </h2>

    {{-- 🎁 Filtros --}}
    <form method="GET" class="d-flex flex-wrap justify-content-center gap-3 mb-5 p-3 filtros-box mx-auto" style="max-width: 800px;">
        <div>
            <label for="region" class="form-label text-light fw-bold mb-0 text-uppercase" style="font-family: 'Oswald', cursive; letter-spacing: 1px; color: var(--sbbl-gold) !important;">Región:</label>
            <select name="region" id="region" class="form-select bg-dark text-light border-secondary">
                <option value="">Todas</option>
                @foreach ($regiones as $region)
                    <option value="{{ $region->id }}" {{ request('region') == $region->id ? 'selected' : '' }}>
                        {{ $region->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="free_agent" class="form-label text-light fw-bold mb-0 text-uppercase" style="font-family: 'Oswald', cursive; letter-spacing: 1px; color: var(--sbbl-gold) !important;">Buscando equipo:</label>
            <select name="free_agent" id="free_agent" class="form-select bg-dark text-light border-secondary">
                <option value="">Todos</option>
                <option value="1" {{ request('free_agent') === '1' ? 'selected' : '' }}>Sí</option>
                <option value="0" {{ request('free_agent') === '0' ? 'selected' : '' }}>No</option>
            </select>
        </div>
        <div class="align-self-end">
            <button type="submit" class="btn btn-filter px-4 text-white" onclick="this.innerHTML = '<span class=\'spinner-border spinner-border-sm\' role=\'status\' aria-hidden=\'true\'></span> BÚSQUEDA...'">FILTRAR</button>
        </div>
    </form>

    {{-- Paginación superior --}}
    @if (method_exists($bladers, 'lastPage') && $bladers->lastPage() > 1)
        <nav class="d-flex justify-content-center mb-4">
            {{ $bladers->onEachSide(1)->links() }}
        </nav>
    @endif

    <div class="row justify-content-center">
        @foreach ($bladers->unique('id') as $blader)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
            @php
                // Lógica de Suscripción
                $subscriptionClass = '';
                if ($blader->user->activeSubscription) {
                    $slug = $blader->user->activeSubscription->plan->slug;
                    $subscriptionMap = [
                        'oro'    => 'suscripcion-nivel-3',
                        'plata'  => 'suscripcion-nivel-2',
                        'bronce' => 'suscripcion-nivel-1',
                    ];
                    $subscriptionClass = $subscriptionMap[$slug] ?? '';
                }

                $userId = $blader->user_id ?? $blader->id;

                $hasGranCopaHalloween = DB::table('assist_user_event')
                    ->join('events', 'assist_user_event.event_id', '=', 'events.id')
                    ->where('assist_user_event.user_id', $userId)
                    ->whereRaw('LOWER(events.name) LIKE ?', ['%let it%'])
                    ->exists();

                $hasGranCopaSantaKlaw = DB::table('assist_user_event')
                    ->join('events', 'assist_user_event.event_id', '=', 'events.id')
                    ->where('assist_user_event.user_id', $userId)
                    ->whereRaw('LOWER(events.name) LIKE ?', ['%x-mas%'])
                    ->exists();

                $hasGranCopaLigeraRevival = DB::table('assist_user_event')
                    ->join('events', 'assist_user_event.event_id', '=', 'events.id')
                    ->where('assist_user_event.user_id', $userId)
                    ->whereRaw('LOWER(events.name) LIKE ?', ['%revival%'])
                    ->exists();
            @endphp

            <div class="tarjeta {{ $subscriptionClass }}"
                style="background-image: url('{{ $blader->fondo_url }}')"
                data-bs-toggle="modal"
                data-bs-target="#bladerModal"
                data-blader-id="{{ $blader->id }}">

                <div class="avatar-container">
                    <img src="{{ $blader->avatar_url }}" class="img-blader" loading="lazy" alt="Avatar">
                    <img src="{{ $blader->marco_url }}" class="marco-blader" loading="lazy" alt="Marco">
                </div>

                <div class="text-overlay">
                    <div class="info text-center">
                        <h5 class="{{ $subscriptionClass }}">{{ $blader->user->name }}</h5>
                        <p class="mb-1 texto-limitado" title="{{ $blader->subtitulo }}">{{ $blader->subtitulo }}</p>
                        <p class="fw-bold text-white mb-0 mt-1" style="font-size:0.8rem; border-top: 1px dashed #555; padding-top: 5px;">{{ $blader->region->name ?? 'ZONA DESCONOCIDA' }}</p>
                    </div>
                </div>

                <div class="efecto-hover"></div>

                @if ($blader->free_agent)
                    <div class="free-agent-label" title="Blader disponible">BUSCA FACCIÓN</div>
                @endif

                <div class="iconos">
                    @if ($hasGranCopaHalloween)
                        <i class="fas fa-ghost text-warning" title="Gran Copa Let It R.I.P."></i>
                    @endif
                    @if ($hasGranCopaSantaKlaw)
                        <i class="fas fa-snowflake text-info" title="Gran Copa X-MAS"></i>
                    @endif
                    @if ($hasGranCopaLigeraRevival)
                        <i class="fas fa-feather-alt text-success" title="Gran Copa Ligera Revival"></i>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Paginación inferior --}}
    @if (method_exists($bladers, 'lastPage') && $bladers->lastPage() > 1)
        <nav class="d-flex justify-content-center my-4">
            {{ $bladers->onEachSide(1)->links() }}
        </nav>
    @endif
</div>


@endsection

@section('scripts')
{{-- Modal Detalle Blader --}}
<div class="modal fade" id="bladerModal" tabindex="-1" aria-labelledby="bladerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bladerModalLabel">STATUS DEL BLADER</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center p-0">
                {{-- Contenido inicial vacío, se llenará con AJAX --}}
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const bladerModal = document.getElementById('bladerModal');

    // 🔑 IMPORTANTE: Reemplaza BLADER_ID si es necesario en tu ruta web.php
    const detailsRoute = '{{ route("blader.details", ["id" => "BLADER_ID"]) }}';

    bladerModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const bladerId = button.getAttribute('data-blader-id');
        const modalBody = bladerModal.querySelector('.modal-body');

        if (!bladerId) {
            modalBody.innerHTML = '<div class="text-center py-5"><p class="text-danger font-Oswald fs-3">ERROR DE LECTURA DE ENERGÍA</p></div>';
            return;
        }

        modalBody.innerHTML = '<div class="text-center py-5"><span class="spinner-border text-warning" role="status"></span><p class="mt-2 font-Oswald fs-4" style="color: var(--sbbl-gold);">ANALIZANDO COSMOS...</p></div>';

        const url = detailsRoute.replace('BLADER_ID', bladerId);

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Error en la red.');
                return response.json();
            })
            .then(data => {
                renderModalContent(modalBody, data, bladerId);
            })
            .catch(error => {
                modalBody.innerHTML = '<div class="text-center py-5"><i class="fas fa-exclamation-triangle text-danger fa-3x"></i><p class="mt-2 text-danger font-Oswald fs-4">ERROR AL CARGAR LA INFORMACIÓN.</p></div>';
            });
    });

    function renderModalContent(container, data, realBladerId) {
        const freeAgentText = data.free_agent === 'Sí' ? 'LIBRE PARA RECLUTAR' : 'COMPROMETIDO';
        const freeAgentClass = data.free_agent === 'Sí' ? 'color: var(--shonen-cyan);' : 'color: var(--shonen-red);';

        const teamNameHtml = data.equipo_nombre === 'Ninguno'
            ? '<span style="color: var(--shonen-red); font-family: \'Oswald\', cursive; font-size: 1.2rem; letter-spacing: 1px;">Lobo Solitario</span>'
            : `<span style="color: var(--sbbl-gold); font-family: \'Oswald\', cursive; font-size: 1.4rem; letter-spacing: 1px; text-shadow: 1px 1px 0 #000;">${data.equipo_nombre}</span>`;

        const teamLogoHtml = data.equipo_logo_b64
            ? `<img src="data:image/png;base64,${data.equipo_logo_b64}" alt="Logo" style="width: 35px; height: 35px; margin-right: 5px; border: 2px solid #000;">`
            : '';

        const isSubscribed = data.is_subscribed;
        let statsSection = '';

        if (isSubscribed) {
            let rawName = data.nombre || '';
            let slug = rawName.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9\-_.]/g, '');
            let paddedId = String(realBladerId).padStart(4, '0');
            let profileUrl = `/blader/${slug}-${paddedId}`;

            statsSection = `
                <div class="p-4" style="background: rgba(0,0,0,0.5); border-bottom: 3px solid #000;">
                    <h5 class="font-Oswald" style="color: var(--sbbl-gold); font-size: 1.5rem; text-shadow: 2px 2px 0 #000;">Temporada 2 (25/26)</h5>
                    <div class="d-flex justify-content-center gap-4 mb-3">
                        <p class="mb-0 fw-bold text-white">PUNTOS: <span class="font-Oswald fs-4" style="color: #fff;">${data.puntos_x2 || 0}</span></p>
                        <p class="mb-0 fw-bold text-white">TORNEOS: <span class="font-Oswald fs-4" style="color: #fff;">${data.torneos_jugados || 0}</span></p>
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <div style="background: #000; border: 2px solid #333; padding: 5px 15px; transform: skewX(-5deg);">
                            <h6 style="color: var(--sbbl-gold); font-weight: 900; margin:0; transform: skewX(5deg);">🥇</h6>
                            <p class="font-Oswald fs-3 mb-0 text-white" style="transform: skewX(5deg);">${data.primeros || 0}</p>
                        </div>
                        <div style="background: #000; border: 2px solid #333; padding: 5px 15px; transform: skewX(-5deg);">
                            <h6 style="color: #fff; font-weight: 900; margin:0; transform: skewX(5deg);">🥈</h6>
                            <p class="font-Oswald fs-3 mb-0 text-white" style="transform: skewX(5deg);">${data.segundos || 0}</p>
                        </div>
                        <div style="background: #000; border: 2px solid #333; padding: 5px 15px; transform: skewX(-5deg);">
                            <h6 style="color: #ff9d47; font-weight: 900; margin:0; transform: skewX(5deg);">🥉</h6>
                            <p class="font-Oswald fs-3 mb-0 text-white" style="transform: skewX(5deg);">${data.terceros || 0}</p>
                        </div>
                    </div>
                </div>

                <div class="p-3" style="background: rgba(0,0,0,0.3); border-bottom: 3px solid #000;">
                    <h5 class="font-Oswald text-white mb-2" style="font-size: 1.2rem;">Temporada 1 (24/25)</h5>
                    <div class="d-flex justify-content-center gap-4">
                        <p class="mb-0 fw-bold text-white" style="font-size: 0.9rem;">PTS: <span class="font-Oswald fs-5 text-white">${data.puntos_x1 || 0}</span></p>
                        <p class="mb-0 fw-bold text-white" style="font-size: 0.9rem;">TORNEOS: <span class="font-Oswald fs-5 text-white">${data.torneos_jugados_x1 || 0}</span></p>
                    </div>
                </div>

                <div class="text-center p-4">
                    <a href="${profileUrl}" class="btn text-uppercase fw-bold text-dark" target="_blank"
                       style="background: var(--sbbl-gold); border: 3px solid #000; border-radius: 0; font-family: 'Oswald', cursive; font-size: 1.3rem; letter-spacing: 1px; box-shadow: 4px 4px 0 #000; transform: skewX(-5deg); display: inline-block;">
                        <span style="transform: skewX(5deg); display: block;"><i class="fas fa-bolt me-2"></i> VER PERFIL COMPLETO</span>
                    </a>
                </div>
            `;
        } else {
            statsSection = `
                <div class="text-center py-5 h-100 d-flex flex-column justify-content-center align-items-center" style="background: rgba(0,0,0,0.5);">
                    <i class="fas fa-lock fa-3x mb-3" style="color: var(--shonen-red);"></i>
                    <p class="mb-3 text-white fw-bold">Las estadísticas completas están bloqueadas.</p>
                    <button class="btn fw-bold text-white" onclick="window.location.href='{{ route('planes.index') }}'"
                            style="background: var(--shonen-blue); border: 3px solid #000; font-family: 'Oswald', cursive; font-size: 1.2rem; box-shadow: 4px 4px 0 var(--shonen-red); transform: skewX(-5deg);">
                        <span style="transform: skewX(5deg); display: block;">DESBLOQUEAR AURA</span>
                    </button>
                </div>
            `;
        }

        container.innerHTML = `
            <div class="row m-0">
                <div class="col-md-5 text-center border-end border-dark p-4" style="border-right-width: 3px !important; background: var(--sbbl-blue-1);">
                    <div class="modal-avatar-container mx-auto mb-3">
                        <img src="${data.imagen}" class="img-blader-modal">
                        <img src="${data.marco}" class="marco-blader-modal">
                    </div>
                    <h3 class="font-Oswald" style="color: var(--sbbl-gold); text-shadow: 2px 2px 0 #000; font-size: 2.2rem; line-height: 1;">${data.nombre}</h3>
                    <p class="fst-italic fw-bold text-white bg-dark d-inline-block px-3 py-1 mt-2" style="border-left: 4px solid var(--shonen-blue); font-size: 0.9rem;">${data.subtitulo || 'Sin lema'}</p>

                    <hr class="border-dark my-4" style="border-width: 3px; opacity: 1;">

                    <div class="mb-3">
                        <span class="d-block text-white fw-bold mb-1" style="font-size:0.8rem;">EQUIPO:</span>
                        <div class="d-flex justify-content-center align-items-center bg-black p-2" style="border: 2px solid #333;">
                            ${teamLogoHtml}
                            ${teamNameHtml}
                        </div>
                    </div>

                    <p class="font-Oswald fs-4 mb-3 text-white">PODER EQUIPO: <span style="color: var(--sbbl-gold); text-shadow: 1px 1px 0 #000;">${data.equipo_puntos}</span></p>

                    <div class="d-flex justify-content-center gap-2 mt-3 flex-wrap">
                        <span class="badge bg-black text-white border border-white py-2 px-3" style="font-family: 'Oswald', cursive; font-size: 1rem; border-radius: 0;">🗺️ ${data.region}</span>
                        <span class="badge py-2 px-3 text-white" style="font-family: 'Oswald', cursive; font-size: 1rem; border: 2px solid #000; border-radius: 0; ${freeAgentClass} background: #000;">${freeAgentText}</span>
                    </div>
                </div>

                <div class="col-md-7 text-center p-0" style="background: var(--sbbl-blue-2);">
                    ${statsSection}
                </div>
            </div>
        `;
    }

    // Filtros
    const regionSelect = document.getElementById('region');
    const freeAgentSelect = document.getElementById('free_agent');
    if ('{{ request('region') }}') regionSelect.value = '{{ request('region') }}';
    if ('{{ request('free_agent') }}' !== '') freeAgentSelect.value = '{{ request('free_agent') }}';
});
</script>
@endsection
