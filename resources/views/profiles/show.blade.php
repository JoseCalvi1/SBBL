@extends('layouts.app')

{{--
|--------------------------------------------------------------------------
| ESTILOS (Mantenidos en el @section('styles') para simplificar)
|--------------------------------------------------------------------------
--}}
@section('styles')
<style>
/* Estilos para el card de duelo */
.duel-card {
    position: relative;
    border: 2px solid #343a40;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    color: white;
    background-size: cover;
    background-position: center;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 250px;
}

.overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom right, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.9));
    z-index: 1;
}

.duel-info, .duel-mode {
    position: relative;
    z-index: 2;
}

.duel-info {
    display: flex;
    align-items: center;
    justify-content: space-around;
    flex: 1;
    flex-wrap: wrap;
}

.duel-player {
    text-align: center;
}

.player-name {
    font-size: 1.25rem;
    font-weight: bold;
}

.player-score {
    font-size: 1.125rem;
    margin-top: 5px;
}

.vs {
    font-size: 1.5rem;
    text-align: center;
    margin: 0 10px;
    flex-basis: 100%;
}

.duel-mode {
    background-color: #343a40;
    color: #ffffff;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    margin-bottom: 1rem;
    align-self: flex-start;
}

.mode {
    font-size: 1rem;
}

/* Estilos para el card de suscripción */
.suscripcion-card {
    background-color: #1e1e2f !important;
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Colores y Badges */
.suscripcion-nivel-3 { color: gold; font-weight: bold; }
.suscripcion-nivel-2 { color: #c0e5fb; font-weight: bold; }
.suscripcion-nivel-1 { color: #CD7F32; font-weight: bold; }

.badge.suscripcion-nivel-3 { background: rgba(255, 215, 0, 0.1); border: 1px solid gold; }
.badge.suscripcion-nivel-2 { background: rgba(192, 229, 251, 0.1); border: 1px solid #c0e5fb; }
.badge.suscripcion-nivel-1 { background: rgba(205, 127, 50, 0.1); border: 1px solid #CD7F32; }

/* Estilo para el contenedor de la imagen de perfil para evitar solapamientos con margen negativo */
.profile-avatar-container {
    margin-top: -80px;
    position: relative;
    z-index: 10;
    width: 200px;
    height: 200px;
}

.profile-img-base {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: 1;
}

.profile-img-frame {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: 2;
}
</style>
@endsection

{{--
|--------------------------------------------------------------------------
| CONTENIDO PRINCIPAL
|--------------------------------------------------------------------------
--}}
@section('content')
@if (Auth::check() && (Auth::user()->profile->id == $profile->id || Auth::user()->is_admin))
<div class="container pb-4">

    {{-- 🏆 SECCIÓN DE CABECERA Y PERFIL --}}
    <div class="row">
        {{-- Fondo del perfil --}}
        <div class="col-12 p-0">
            @php
                $fondoUrl = $profile->fondo ? "/storage/{$profile->fondo}" : '/storage/upload-profiles/SBBLFondo.png';
                $backgroundStyle = $profile->fondo ? 'background-size: cover; background-repeat: no-repeat;' : 'background-repeat: repeat;';
            @endphp
            <div class="profile-header-background" style="background-image: url('{{ $fondoUrl }}'); {{ $backgroundStyle }} background-position: center; height: 160px; border-radius: 10px 10px 0 0;"></div>
        </div>

        {{-- Avatar, Nombre y Estadísticas --}}
        <div class="col-md-4 d-flex justify-content-center justify-content-md-start">
            <div class="profile-avatar-container">
                {{-- Imagen del Perfil --}}
                @php
                    $imgSrc = $profile->imagen ? "/storage/{$profile->imagen}" : '/storage/upload-profiles/Base/DranDagger.webp';
                    $imgPadding = strpos($profile->imagen ?? '', '.gif') !== false ? 'padding: 20px;' : '';
                    $marcoSrc = $profile->marco ? "/storage/{$profile->marco}" : '/storage/upload-profiles/Marcos/BaseBlue.png';
                @endphp
                <img src="{{ $imgSrc }}" class="rounded-circle profile-img-base" width="200" height="200" style="{{ $imgPadding }}">

                {{-- Marco del Perfil --}}
                <img src="{{ $marcoSrc }}" class="rounded-circle profile-img-frame" width="200" height="200">
            </div>
        </div>

        {{-- Información Principal del Usuario --}}
        <div class="col-md-8 pt-4 pt-md-0 d-flex flex-column justify-content-center">
            <h1 class="text-center text-md-start mb-2 mt-md-0 text-white display-5">
                {{ $profile->user->name }}
            </h1>

            {{-- Lógica de las Lagartos (Coins) --}}
            @php
                $coinCount = 0;
                if (Auth::check()) {
                    $trophy = DB::table('trophies')->where('name', 'SBBL Coin')->first();
                    if ($trophy) {
                        $coinCount = DB::table('profilestrophies')
                            ->where('trophies_id', $trophy->id)
                            ->where('profiles_id', Auth::user()->id)
                            ->value('count') ?? 0;
                    }
                }
            @endphp
            <div class="d-flex flex-wrap align-items-center gap-3">
                <div class="bg-dark rounded-pill px-3 py-2 shadow-sm d-inline-flex">
                    <span class="fw-bold text-warning h5 mb-0">
                        Lagartos: {{ number_format($coinCount) }} <span class="ms-1">🦎</span>
                    </span>
                </div>

                <h3 class="text-white h5 mb-0">Región:
                    <span class="fw-normal">{{ $profile->region->name ?? 'Por definir' }}</span>
                </h3>

                <h3 class="text-white h5 mb-0">Puntos:
                    <span class="badge text-bg-secondary">X: {{ $profile->points_x2 }}</span>
                </h3>
            </div>

            @if (Auth::user()->profile->id == $profile->id)
                <div class="mt-3">
                    <a href="{{ route('profiles.edit', ['profile' => Auth::user()->id]) }}" class="btn btn-outline-info text-uppercase font-weight-bold w-100 w-md-50">
                        Editar perfil
                    </a>
                </div>
            @endif
            @if($subscription)
                <div class="mt-3">
                    <a href="{{ route('collection.index') }}" class="btn btn-warning text-uppercase font-weight-bold w-100 w-md-50">
                        Ver mi colección
                    </a>
                </div>
            @endif
        </div>
    </div>

    <hr class="border-secondary my-4">

    {{-- 🌟 SECCIÓN DE SUSCRIPCIÓN (Lógica IF/ELSEIF/ELSE) --}}
    @if($subscription)
        @php
            // Obtener el slug del plan en minúsculas
            $planName = strtolower($subscription->plan->slug);

            // Inicializar variables
            $badgeClass = '';
            $mensaje = '';

            // Asignar clases y mensajes usando la estructura IF/ELSEIF tradicional
            if ($planName === 'oro') {
                $badgeClass = 'suscripcion-nivel-3';
                $mensaje = '¡Gracias por apoyar al máximo nivel! 🎉';
            } elseif ($planName === 'plata') {
                $badgeClass = 'suscripcion-nivel-2';
                $mensaje = 'Disfruta de tus beneficios Plata ⚡';
            } elseif ($planName === 'bronce') {
                $badgeClass = 'suscripcion-nivel-1';
                $mensaje = 'Estás en el plan Bronce 🪙';
            } else {
                $badgeClass = 'bg-dark';
                $mensaje = '';
            }

            // Determinar el período
            $periodo = $subscription->period === 'monthly' ? 'Mensual' : 'Anual';
        @endphp

        <div class="card suscripcion-card text-light shadow-lg border-0 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <span class="badge {{ $badgeClass }} fs-6 py-2 px-3 me-3">{{ ucfirst($planName) }}</span>
                    <h5 class="card-title mb-0 fs-5">Suscripción {{ $periodo }}</h5>
                </div>

                <p class="card-text mb-2">
                    ✅ Activa desde: <strong class="text-white">{{ $subscription->started_at->format('d/m/Y') }}</strong>
                </p>
                <p class="card-text">
                    📅 Expira el: <strong class="text-white">{{ $subscription->ended_at->format('d/m/Y') }}</strong>
                </p>

                @if($mensaje)
                    <p class="fw-bold mt-3 mb-0 {{ $badgeClass }}">{{ $mensaje }}</p>
                @endif
            </div>
        </div>
    @endif

    {{-- 🔔 ALERTAS Y MENSAJES --}}
    <div class="mt-3">
        @if(session('success'))
            <div class="alert alert-success" role="alert">{{ session('success') }}</div>
        @elseif(session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif
    </div>

    {{-- 📨 INVITACIONES PENDIENTES --}}
    @if(!$invitacionesPendientes->isEmpty())
    <div class="card bg-dark text-white mb-4 shadow">
        <div class="card-body">
            <h2 class="card-title border-bottom border-white pb-2 mb-3">Invitaciones Pendientes</h2>
            <ul class="list-group list-group-flush">
                @foreach ($invitacionesPendientes as $invitacion)
                    <li class="list-group-item bg-dark border-secondary border-bottom p-3">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center text-white">
                            <span class="mb-2 mb-sm-0">Equipo: <strong>{{ $invitacion->team->name }}</strong></span>
                            <div class="btn-group" role="group">
                                <form action="{{ route('invitations.accept', $invitacion) }}" method="POST" class="me-2">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">Aceptar</button>
                                </form>
                                <form action="{{ route('invitations.reject', $invitacion) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm">Rechazar</button>
                                </form>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- 📅 NAVEGACIÓN DE MESES --}}
    <div class="d-flex justify-content-between align-items-center mt-5 mb-4 p-3 bg-dark rounded shadow-sm">
        @php
            $prevMonthUrl = route('profiles.show', ['profile' => $profile->id, 'month' => $currentMonth == 1 ? 12 : $currentMonth - 1, 'year' => $currentMonth == 1 ? $currentYear - 1 : $currentYear]);
            $nextMonthUrl = route('profiles.show', ['profile' => $profile->id, 'month' => $currentMonth == 12 ? 1 : $currentMonth + 1, 'year' => $currentMonth == 12 ? $currentYear + 1 : $currentYear]);
            $monthName = \Carbon\Carbon::create($currentYear, $currentMonth)->translatedFormat('F Y');
        @endphp
        <a href="{{ $prevMonthUrl }}" class="btn btn-outline-light">
            ← Mes Anterior
        </a>
        <h3 class="text-white h4 mb-0">{{ $monthName }}</h3>
        <a href="{{ $nextMonthUrl }}" class="btn btn-outline-light">
            Mes Siguiente →
        </a>
    </div>

    {{-- ⚔️ DUELOS MENSUALES --}}
    <!--<h2 class="titulo-categoria text-uppercase mb-4 mt-4 text-white border-bottom pb-2">Duelos Mensuales</h2>
    <div class="row mt-2">
        @forelse ($versus as $duelo)
            <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                <div class="duel-card" style="background-image: url('/storage/{{ $duelo->result_1 > $duelo->result_2 ? $duelo->versus_1->profile->fondo : $duelo->versus_2->profile->fondo }}');">
                    <div class="overlay"></div>

                    @php
                        $mode = $duelo->matchup == "beybladex" ? "Beyblade X" : "Beyblade Burst";
                        // Lógica del status del duelo con IF/ELSEIF/ELSE
                        if ($duelo->status == "CLOSED") {
                            $statusText = 'Válido';
                        } elseif ($duelo->status == "INVALID") {
                            $statusText = 'Inválido';
                        } elseif ($duelo->status == "OPEN" && $duelo->url) {
                            $statusText = 'Pendiente';
                        } else {
                            $statusText = 'Enviado';
                        }
                    @endphp

                    <div class="duel-mode">
                        <span class="mode">{{ $mode }} - **{{ $statusText }}**</span>
                    </div>

                    <div class="duel-info d-flex align-items-center justify-content-around flex-grow-1">
                        <div class="duel-player">
                            <span class="player-name">{{ $duelo->versus_1->name }}</span>
                        </div>

                        <div class="vs d-flex flex-column align-items-center mx-3">
                            <span class="player-score h2 mb-0">{{ $duelo->result_1 }}</span>
                            <span class="h4 my-1">VS</span>
                            <span class="player-score h2 mb-0">{{ $duelo->result_2 }}</span>
                        </div>

                        <div class="duel-player">
                            <span class="player-name">{{ $duelo->versus_2->name }}</span>
                        </div>

                        {{-- Botones de Acción --}}
                        @if (Auth::check() && ($duelo->user_id_1 == Auth::user()->id || $duelo->user_id_2 == Auth::user()->id))
                            <div class="w-100 d-grid gap-2 mt-3">
                                <a href="{{ route('versus.versusdeck', ['duel' => $duelo->id, 'deck' => Auth::user()->id]) }}" class="btn btn-warning fw-bold">
                                    Introducir Deck
                                </a>
                                <button type="button" class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#modalDeck{{ $duelo->id }}">
                                    Vídeo
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Modal: Usando Bootstrap 5 data-bs-toggle --}}
            @auth
            <div class="modal fade" id="modalDeck{{ $duelo->id }}" tabindex="-1" aria-labelledby="modalDeckLabel{{ $duelo->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content bg-dark text-white">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalDeckLabel{{ $duelo->id }}">Actualizar video</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="{{ route('versus.updateVideo', ['versus' => $duelo->id]) }}" class="p-4">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="url{{ $duelo->id }}" class="form-label">Link al video del duelo:</label>
                                <input type="url" name="url" id="url{{ $duelo->id }}" class="form-control bg-secondary text-white border-0"
                                    placeholder="https://www.youtube.com/embed/tu-video"
                                    value="{{ old('url', $duelo->url ?? '') }}" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-outline-success text-uppercase fw-bold">Enviar datos</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endauth
        @empty
            <p class="col-md-12 text-white">No has participado en ningún duelo este mes</p>
        @endforelse
    </div>-->

    {{-- 🗓️ EVENTOS MENSUALES --}}
    <h2 class="titulo-categoria text-uppercase mb-4 mt-2 text-white border-bottom pb-2">Eventos Mensuales</h2>
    <div class="row mt-2">
        @forelse ($eventos as $evento)
            <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                <div class="card h-100 d-flex flex-column text-center border-3 shadow-lg" style="background-color: #283b63; color: white; border-color: #ffffff !important;">

                    {{-- Imagen del Evento --}}
                    @php
                        $eventImageUrl = $evento->image_mod ? 'data:image/png;base64,' . $evento->image_mod : "/storage/{$evento->imagen}";
                    @endphp
                    <div class="event-image-container" style="height: 180px; background: url('{{ $eventImageUrl }}') center center no-repeat; background-size: cover; border-bottom: 2px solid #ffffff;"></div>

                    <div class="card-body d-flex flex-column justify-content-between p-3 flex-grow-1">
                        <div>
                            <h3 class="h5 fw-bold mb-1">{{ $evento->name }}</h3>
                            <p class="mb-2">{{ $evento->region->name }}</p>
                            <p class="text-secondary mb-3"><event-date fecha="{{ $evento->date }}"></event-date></p>
                        </div>

                        <a href="{{ route('events.show', ['event' => $evento->id]) }}" class="btn btn-outline-light fw-bold text-uppercase mt-auto">
                            Ver evento
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <p class="col-md-12 text-white">No has participado en ningún evento este mes</p>
        @endforelse
    </div>
</div>
@endif
@endsection
