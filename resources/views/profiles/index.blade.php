@extends('layouts.app')

@section('title', 'Bladers SBBL')

@section('content')
<div class="container py-4">
    <h2 class="text-uppercase text-center mb-4 mt-3 fw-bold text-light">
        Bladers ({{ count($bladers) }})
    </h2>

    {{-- 🎁 Filtros --}}
    <form method="GET" class="d-flex flex-wrap justify-content-center gap-3 mb-5 p-3">
        <div>
            <label for="region" class="form-label text-light fw-semibold mb-0">Región:</label>
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
            <label for="free_agent" class="form-label text-light fw-semibold mb-0">Buscando equipo:</label>
            <select name="free_agent" id="free_agent" class="form-select bg-dark text-light border-secondary">
                <option value="">Todos</option>
                <option value="1" {{ request('free_agent') === '1' ? 'selected' : '' }}>Sí</option>
                <option value="0" {{ request('free_agent') === '0' ? 'selected' : '' }}>No</option>
            </select>
        </div>
        <div class="align-self-end">
            <button type="submit" class="btn btn-warning fw-bold shadow-sm" onclick="this.innerHTML = '<span class=\'spinner-border spinner-border-sm\' role=\'status\' aria-hidden=\'true\'></span> Filtrando...'">Filtrar</button>
        </div>
    </form>

    {{-- Paginación superior --}}
    @if (method_exists($bladers, 'lastPage') && $bladers->lastPage() > 1)
        <nav class="d-flex justify-content-center mb-3">
            {{ $bladers->onEachSide(1)->links() }}
        </nav>
    @endif

    <div class="row justify-content-center">
        @foreach ($bladers->unique('id') as $blader)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
            @php
                // 1. Lógica de Suscripción simplificada (Array Map en lugar de Switch)
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

                // 2. Consultas de Copas (Se mantienen igual)
                // Nota: Asegúrate de si $blader es el 'Profile' o el 'User' para usar la ID correcta.
                // Asumo que $blader es 'Profile' porque usas $blader->user->...
                $userId = $blader->user_id ?? $blader->id;

                $hasGranCopaHalloween = DB::table('assist_user_event')
                    ->join('events', 'assist_user_event.event_id', '=', 'events.id')
                    ->where('assist_user_event.user_id', $userId)
                    ->whereRaw('LOWER(events.name) LIKE ?', ['%let it%'])
                    ->exists();

                $hasGranCopaSantaKlaw = $blader->trophies->contains('name', 'Gran Copa Santa Klaw');

                // 3. ¡VARIABLES DE IMAGEN ELIMINADAS! Usaremos los atributos mágicos abajo.
            @endphp

            {{-- Usamos $blader->fondo_url --}}
            <div class="tarjeta {{ $subscriptionClass }}"
                style="background-image: url('{{ $blader->fondo_url }}')"
                data-bs-toggle="modal"
                data-bs-target="#bladerModal"
                data-blader-id="{{ $blader->id }}">

                <div class="avatar-container">
                    {{-- Usamos $blader->avatar_url --}}
                    <img src="{{ $blader->avatar_url }}" class="rounded-circle img-blader" loading="lazy" alt="Avatar">

                    {{-- Usamos $blader->marco_url --}}
                    <img src="{{ $blader->marco_url }}" class="rounded-circle marco-blader" loading="lazy" alt="Marco">
                </div>

                {{-- CONTENEDOR DE VISIBILIDAD DE TEXTO --}}
                <div class="text-overlay">
                    <div class="info text-center">
                        <h5 class="fw-bold {{ $subscriptionClass }}">{{ $blader->user->name }}</h5>
                        <p class="mb-1">{{ $blader->subtitulo }}</p>
                        <p class="fw-semibold">{{ $blader->region->name ?? 'No definida' }}</p>
                    </div>
                </div>

                <div class="efecto-hover"></div>

                @if ($blader->free_agent)
                    <div class="free-agent-label" title="Blader disponible para formar parte de un equipo">Open to work</div>
                @endif

                <div class="iconos">
                    @if ($hasGranCopaHalloween)
                        <i class="fas fa-ghost text-warning me-2" title="Gran Copa Let It R.I.P."></i>
                    @endif
                    @if ($hasGranCopaSantaKlaw)
                        <i class="fas fa-snowflake text-info" title="Gran Copa Santa Klaw"></i>
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
{{-- 1. Modal Detalle Blader - La estructura es vacía y será llenada por JS --}}
<div class="modal fade" id="bladerModal" tabindex="-1" aria-labelledby="bladerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-light border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold" id="bladerModalLabel">Detalles del Blader</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center">
                {{-- Contenido inicial vacío, se llenará con AJAX --}}
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const bladerModal = document.getElementById('bladerModal');

    // 🔑 IMPORTANTE: Reemplaza BLADER_ID con el marcador que configuraste en tu ruta Laravel
    const detailsRoute = '{{ route("blader.details", ["id" => "BLADER_ID"]) }}';

    bladerModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const bladerId = button.getAttribute('data-blader-id');
        const modalBody = bladerModal.querySelector('.modal-body');

        if (!bladerId) {
            modalBody.innerHTML = '<div class="text-center py-5"><p class="text-danger">ID no encontrado.</p></div>';
            return;
        }

        // 1. Mostrar spinner de carga
        modalBody.innerHTML = '<div class="text-center py-5"><span class="spinner-border text-warning" role="status"></span><p class="mt-2 text-light">Cargando detalles...</p></div>';

        // 2. Realizar la petición AJAX
        const url = detailsRoute.replace('BLADER_ID', bladerId);

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('La respuesta de red no fue satisfactoria.');
                }
                return response.json();
            })
            .then(data => {
                console.log("Datos del Blader recibidos:", data);
                // 3. Renderizar el contenido con los datos
                renderModalContent(modalBody, data);
            })
            .catch(error => {
                console.error('Fetch error:', error);
                modalBody.innerHTML = '<div class="text-center py-5"><i class="fas fa-exclamation-triangle text-danger"></i><p class="mt-2 text-danger">Error al cargar la información. Revisa la consola y la ruta de Laravel.</p></div>';
            });
    });

    // Función que renderiza el contenido del modal
    function renderModalContent(container, data) {
    const freeAgentClass = data.free_agent === 'Sí' ? 'text-success' : 'text-danger';

    const teamNameHtml = data.equipo_nombre === 'Ninguno'
        ? '<span class="text-danger fw-semibold">Ninguno</span>'
        : `<span class="text-warning fw-semibold">${data.equipo_nombre}</span>`;

    const teamLogoHtml = data.equipo_logo_b64
        ? `<img src="data:image/png;base64,${data.equipo_logo_b64}" alt="Logo Equipo" style="width: 30px; height: 30px; margin-right: 5px;">`
        : '';

    // --- NUEVO: verificamos si el usuario tiene suscripción activa ---
    const isSubscribed = data.is_subscribed; // <- viene del backend
    let statsSection = '';

    if (isSubscribed) {
        statsSection = `
            <h5 class="fw-bold text-warning mb-3">Estadísticas Season 2 (2025/2026)</h5>
            <p><strong>Puntos:</strong> <span class="text-info">${data.puntos_x2 || 0}</span></p>
            <p><strong>Torneos jugados:</strong> <span class="text-info">${data.torneos_jugados || 0}</span></p>
            <hr class="border-secondary w-50 mx-auto">
            <div class="d-flex justify-content-center gap-4 mt-3">
                <div>
                    <h6 class="text-warning">🥇 Primeros</h6>
                    <p class="fs-4 text-light fw-bold mb-0">${data.primeros || 0}</p>
                </div>
                <div>
                    <h6 class="text-secondary">🥈 Segundos</h6>
                    <p class="fs-4 text-light fw-bold mb-0">${data.segundos || 0}</p>
                </div>
                <div>
                    <h6 class="text-danger">🥉 Terceros</h6>
                    <p class="fs-4 text-light fw-bold mb-0">${data.terceros || 0}</p>
                </div>
            </div>

            <hr class="border-secondary w-100 mx-auto">

            <h5 class="fw-bold text-warning mb-3">Estadísticas Season 1 (2024/2025)</h5>
            <p><strong>Puntos:</strong> <span class="text-info">${data.puntos_x1 || 0}</span></p>
            <p><strong>Torneos jugados:</strong> <span class="text-info">${data.torneos_jugados_x1 || 0}</span></p>
            <hr class="border-secondary w-50 mx-auto">
            <div class="d-flex justify-content-center gap-4 mt-3">
                <div>
                    <h6 class="text-warning">🥇 Primeros</h6>
                    <p class="fs-4 text-light fw-bold mb-0">${data.primeros_x1 || 0}</p>
                </div>
                <div>
                    <h6 class="text-secondary">🥈 Segundos</h6>
                    <p class="fs-4 text-light fw-bold mb-0">${data.segundos_x1 || 0}</p>
                </div>
                <div>
                    <h6 class="text-danger">🥉 Terceros</h6>
                    <p class="fs-4 text-light fw-bold mb-0">${data.terceros_x1 || 0}</p>
                </div>
            </div>
        `;
    } else {
        statsSection = `
            <div class="text-center py-4">
                <p class="mb-3 text-white">🔒 Las estadísticas están disponibles solo para miembros suscritos.</p>
                <button class="btn btn-warning fw-bold" onclick="window.location.href='{{ route('planes.index') }}'">
                    Ver planes de suscripción
                </button>
            </div>
        `;
    }

    // Estructura del modal (dos columnas)
    container.innerHTML = `
        <div class="row">
            <!-- Columna izquierda -->
            <div class="col-md-6 text-center border-end border-secondary">
                <div class="modal-avatar-container mx-auto mb-3">
                    <img src="${data.imagen}" class="rounded-circle img-blader-modal">
                    <img src="${data.marco}" class="rounded-circle marco-blader-modal">
                </div>
                <h4 class="fw-bold">${data.nombre}</h4>
                <p class="fst-italic text-muted">${data.subtitulo || ''}</p>
                <hr class="border-secondary">
                <p class="mb-1 d-flex align-items-center justify-content-center">
                    <strong>Equipo:</strong>
                    <span class="ms-2">
                        ${teamLogoHtml}
                        ${teamNameHtml}
                    </span>
                </p>
                <p class="mb-1"><strong>Puntos:</strong> <span class="text-info">${data.equipo_puntos}</span></p>
                <hr class="border-secondary">
                <p class="mb-1"><strong>Región:</strong> <span class="text-info fw-semibold">${data.region}</span></p>
                <p><strong>Buscando equipo:</strong> <span class="fw-bold ${freeAgentClass}">${data.free_agent}</span></p>
            </div>

            <!-- Columna derecha -->
            <div class="col-md-6 text-center">
                ${statsSection}
            </div>
        </div>
    `;
}



    // Lógica de filtros (para mantener el estado)
    const regionSelect = document.getElementById('region');
    const freeAgentSelect = document.getElementById('free_agent');
    if ('{{ request('region') }}') {
        regionSelect.value = '{{ request('region') }}';
    }
    if ('{{ request('free_agent') }}' !== '') {
        freeAgentSelect.value = '{{ request('free_agent') }}';
    }
});
</script>

---

## 🎨 Estilos CSS (Incluye la mejora de visibilidad)

```css
<style>
/* Estilos generales */
body { background-color: #0b0b0b; color: #eee; }
.tarjeta {
    background-size: cover;
    background-position: center;
    border-radius: 15px;
    padding: 20px;
    padding-top: 100px;
    text-align: center;
    color: #fff;
    position: relative;
    overflow: hidden;
    transition: transform .3s ease, box-shadow .3s ease;
    cursor: pointer;
    min-height: 250px;
}
.tarjeta:hover {
    transform: translateY(-5px);
    box-shadow: 0 0 20px rgba(255,255,255,0.2);
}
.efecto-hover {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.15), transparent);
    opacity: 0;
    transition: opacity .3s ease;
}
.tarjeta:hover .efecto-hover { opacity: 1; }

/* 🔑 NUEVO: Contenedor para mejorar la visibilidad del texto */
.text-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 130px; /* Altura que cubre el texto */
    z-index: 3;
    /* Degradado de negro semitransparente a transparente */
    background: linear-gradient(to top, rgba(0, 0, 0, 0.85) 0%, rgba(0, 0, 0, 0.7) 50%, transparent 100%);
    padding-top: 20px;
}

/* Contenedor de Avatar */
.avatar-container {
    width: 100px;
    height: 100px;
    position: absolute;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 5; /* Asegura que el avatar esté visible */
}
.img-blader, .marco-blader {
    width: 90px;
    height: 90px;
    position: absolute;
    top: 5px;
    left: 5px;
}
.marco-blader { z-index: 2; }
.img-blader { z-index: 1; }
.info {
    margin-top: 0px;
    position: relative; /* Para que el texto se posicione dentro del overlay */
    z-index: 4;
}

.free-agent-label {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: #28a745;
    color: white;
    padding: 3px 8px;
    border-radius: 8px;
    font-size: .8rem;
    text-transform: uppercase;
    font-weight: bold;
    z-index: 5;
    transition: background .3s;
}
.free-agent-label:hover { background: #ffc107; color: black; }
.iconos {
    position: absolute;
    bottom: 10px;
    left: 10px;
    font-size: 1.2rem;
    z-index: 5;
}

/* Estilos de Suscripción */
.suscripcion-nivel-3 { text-shadow: 0 0 10px gold; color: gold !important; }
.suscripcion-nivel-2 { text-shadow: 0 0 10px #9be3ff; color: #9be3ff !important; }
.suscripcion-nivel-1 { text-shadow: 0 0 10px #CD7F32; color: #CD7F32 !important; }

/* Estilos para el Modal */
.modal-avatar-container {
    width: 120px;
    height: 120px;
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
}
.img-blader-modal, .marco-blader-modal {
    width: 120px;
    height: 120px;
    position: absolute;
    top: 0;
    left: 0;
}
.marco-blader-modal { z-index: 2; }
.img-blader-modal { z-index: 1; }
.modal-body .col-md-6 {
    padding: 15px;
}
.modal-body h5, .modal-body h6 {
    text-shadow: 0 0 10px rgba(255,255,255,0.2);
}

</style>
@endsection
