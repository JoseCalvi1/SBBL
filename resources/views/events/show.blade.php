    @extends('layouts.app')

    @section('title', $event->name)

    @section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <style>
        /* --- TEMA AZUL OSCURO (Midnight Blue) --- */
        :root {
            --bg-body: #0f172a; --bg-card: #1e293b; --text-main: #f1f5f9;
            --text-muted: #94a3b8; --border-color: #334155; --accent: #38bdf8;
            --bg-input: #020617;
        }

        body { background-color: var(--bg-body) !important; color: var(--text-main) !important; font-family: 'Inter', sans-serif; }

        /* Textos y colores */
        h1, h2, h3, h4, h5, h6, strong, b, label { color: #fff !important; }
        .text-muted, small { color: #cbd5e1 !important; }

        /* Componentes */
        .card, .list-group-item { background-color: var(--bg-card) !important; border-color: var(--border-color) !important; color: var(--text-main) !important; }
        .card-header { background-color: rgba(15, 23, 42, 0.8) !important; border-bottom: 1px solid var(--border-color) !important; }

        /* Inputs */
        .form-control, .form-select { background-color: var(--bg-input) !important; border: 1px solid #475569 !important; color: #fff !important; }
        .form-control:focus, .form-select:focus { border-color: var(--accent) !important; box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.2) !important; }

        /* --- ARREGLO DE SELECT2 (CRÍTICO) --- */

        /* 1. El contenedor del select NO debe tener z-index alto globalmente (Esto arregla la imagen 3) */
        .select2-container { width: 100% !important; }

        /* 2. Solo el menú DESPLEGADO debe estar por encima de todo */
        .select2-dropdown {
            z-index: 999999 !important; /* Muy alto para ganar al modal */
            background-color: #1e293b !important;
            border: 1px solid #38bdf8 !important;
        }

        /* 3. Estilos oscuros para Select2 */
        .select2-container--bootstrap-5 .select2-selection { background-color: var(--bg-input) !important; border-color: #475569 !important; color: #fff !important; }
        .select2-container--bootstrap-5 .select2-selection__rendered { color: #fff !important; }
        .select2-search__field { background-color: #334155 !important; color: #fff !important; }
        .select2-results__option { color: #fff !important; background-color: var(--bg-card) !important; }
        .select2-container--bootstrap-5 .select2-results__option--highlighted { background-color: var(--accent) !important; color: #000 !important; }

        /* Modal */
        .modal-backdrop { z-index: 1050 !important; opacity: 0.85 !important; }
        .modal { z-index: 1060 !important; }
        .modal-content { background-color: var(--bg-card) !important; border: 1px solid #334155 !important; }
        .modal-header, .modal-footer { border-color: #334155 !important; background-color: #0f172a !important; }
        .btn-close-white { filter: invert(1) grayscale(100%) brightness(200%); }
    </style>
    @endsection

    @section('content')
    <div class="container-fluid py-5">

        {{-- LÓGICA CORREGIDA: Calculamos si está inscrito mirando la lista de participantes --}}
        @php
            $isUserInscribed = Auth::check() && $assists->contains('id', Auth::id());
            $hoy = \Carbon\Carbon::now()->format('Y-m-d');
            // Calculamos horas restantes para cerrar inscripción
            $diffInHours = \Carbon\Carbon::parse($event->date)->diffInHours(\Carbon\Carbon::now(), false);
        @endphp

        <div class="row mb-5">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold mb-3 text-white" style="text-shadow: 0 0 20px rgba(56, 189, 248, 0.4);">
                    {{ $event->name }}
                </h1>
                <div class="d-inline-block px-4 py-2 rounded-pill" style="background-color: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
                    @switch($event->status)
                        @case('OPEN') <span class="text-success fw-bold text-uppercase">🟢 Abierto</span> @break
                        @case('INSCRIPCION') <span class="text-warning fw-bold text-uppercase">🔒 Inscripción Cerrada</span> @break
                        @case('PENDING') <span class="text-warning fw-bold text-uppercase">⚠️ Pendiente Calificar</span> @break
                        @case('REVIEW') <span class="text-info fw-bold text-uppercase">🔎 En Revisión</span> @break
                        @case('INVALID') <span class="text-secondary fw-bold text-uppercase">❌ Inválido</span> @break
                        @default <span class="text-danger fw-bold text-uppercase">🔴 Cerrado</span>
                    @endswitch
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-5">

                <div class="card mb-4 overflow-hidden shadow-lg border-0">
                    <div class="position-relative">
                        @if ($event->image_mod)
                            <img src="data:image/png;base64,{{ $event->image_mod }}" class="w-100 object-fit-cover" style="height: 280px;">
                        @else
                            <img src="/storage/{{ $event->imagen }}" class="w-100 object-fit-cover" style="height: 280px;">
                        @endif
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-primary shadow">{{ $event->region->name }}</span>
                        </div>
                        <div class="position-absolute bottom-0 start-0 w-100 p-3" style="background: linear-gradient(to top, #1e293b, transparent);">
                            <h5 class="text-white mb-0"><i class="fas fa-map-marker-alt me-2 text-info"></i>{{ $event->city }}</h5>
                        </div>
                    </div>

                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-25">
                                <span><i class="fas fa-gamepad me-2"></i>Modalidad</span>
                                <span class="fw-bold text-white">{{ ($event->mode == 'beybladex') ? 'Beyblade X' : 'Burst' }} ({{ $event->beys }})</span>
                            </li>
                            <li class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-25">
                                <span><i class="fas fa-calendar me-2"></i>Fecha</span>
                                <span class="fw-bold text-white"><event-date fecha="{{ $event->date }}"></event-date></span>
                            </li>
                            <li class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-25">
                                <span><i class="fas fa-clock me-2"></i>Hora</span>
                                <span class="fw-bold text-white">{{ $event->time }}</span>
                            </li>
                            <li class="py-2 border-bottom border-secondary border-opacity-25">
                                <span class="d-block mb-1"><i class="fas fa-map-pin me-2"></i>Ubicación</span>
                                <span class="d-block text-white bg-dark p-2 rounded small">{!! $event->location !!}</span>
                            </li>
                            @if($event->note)
                            <li class="py-3">
                                <span class="text-warning d-block mb-1"><i class="fas fa-sticky-note me-2"></i>Notas</span>
                                <div class="small text-white fst-italic">{!! $event->note !!}</div>
                            </li>
                            @endif
                        </ul>

                        <div class="d-grid gap-2 mt-4">
                            @if(in_array($event->beys, ['ranking', 'rankingplus']))
                                <a href="{{ route('inicio.rules') }}" target="_blank" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-book me-1"></i> Reglamento Oficial
                                </a>
                            @endif
                            @if ($event->iframe)
                                <a href="{{ $event->iframe }}" target="_blank" class="btn btn-danger btn-sm">
                                    <i class="fab fa-youtube me-1"></i> Ver Video
                                </a>
                            @endif
                            @if ($event->challonge)
                                <a href="{{ $event->challonge }}" target="_blank" class="btn btn-warning btn-sm text-dark">
                                    <i class="fas fa-trophy me-1"></i> Ver Bracket
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header fw-bold text-white">
                        <i class="fas fa-comments me-2 text-info"></i>Chat del Evento
                    </div>
                    <div class="card-body p-0">
                        <div id="app">
                            <chat-component :event-id="{{$event->id}}" />
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-lg-8 col-md-7">

                @if (session('success'))
                    <div class="alert alert-success border-0 shadow-sm mb-4"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger border-0 shadow-sm mb-4"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}</div>
                @endif

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="mb-0 text-white">
                                Participantes <span class="badge bg-info text-dark rounded-pill ms-2">{{ $assists->count() }}</span>
                            </h4>

                            <div class="d-flex gap-2">
                                @if (($event->status == "OPEN") && (Auth::user()->is_admin || Auth::user()->is_referee) && ($diffInHours <= 24 && $diffInHours >= 0))
                                    <form method="POST" action="{{ route('events.estado', ['event' => $event->id, 'estado' => 'inscripcion']) }}">
                                        @csrf @method('PUT')
                                        <button type="submit" class="btn btn-outline-secondary btn-sm">Cerrar Insc.</button>
                                    </form>
                                @endif

                                @if (($event->status != "CLOSE" && $event->status != "INVALID") && Auth::user()->is_referee || ($event->status == "OPEN" && $event->created_by == Auth::user()->id))
                                    <a href="{{ route('events.edit', ['event' => $event->id]) }}" class="btn btn-secondary btn-sm"><i class="fas fa-edit me-1"></i> Editar</a>
                                @endif

                                @if ($isUserInscribed && $event->status != "INVALID")
                                    <button type="button" class="btn btn-info fw-bold btn-sm text-dark" data-bs-toggle="modal" data-bs-target="#formModal">
                                        <i class="fas fa-layer-group me-1"></i> Mi Deck
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="p-3 rounded-3 mb-4" style="background-color: rgba(2, 6, 23, 0.5); border: 1px solid #334155;">
                        @auth
                            @if($event->status == "OPEN" && $event->date > $hoy)

                            {{-- INICIO BLOQUE LIMITES DE ESTADIO --}}
                    @if($event->has_stadium_limit)
                        @php
                            $limit = 9999;
                            $limitText = "Aforo Ilimitado";
                            $alertColor = "success"; // Verde por defecto
                            $currentCount = $assists->count();

                            if ($event->stadiums == 1) {
                                $limit = 19;
                                $limitText = "Aforo asegurado: 19 participantes (1 Estadio)";
                                // Si se pasa del límite, rojo. Si no, amarillo (precaución 1 estadio)
                                $alertColor = ($currentCount >= $limit) ? 'danger' : 'success';
                            } elseif ($event->stadiums == 2) {
                                $limit = 29;
                                $limitText = "Aforo asegurado: 29 participantes (2 Estadios)";
                                // Si se pasa, rojo. Si no, azul (info)
                                $alertColor = ($currentCount >= $limit) ? 'danger' : 'info';
                            }
                        @endphp

                        <div class="alert alert-{{ $alertColor }} shadow-sm text-center mb-4">
                            <h5 class="alert-heading font-weight-bold text-black" style="font-weight: bold;">
                                🏟️ {{ $event->stadiums }} Estadios Disponibles
                            </h5>
                            <p class="mb-2">{{ $limitText }}</p>

                            <div class="progress" style="height: 20px; background-color: rgba(0,0,0,0.1);">
                                <div class="progress-bar bg-{{ $alertColor }}" role="progressbar"
                                     style="width: {{ min(($currentCount / ($limit > 1000 ? 50 : $limit)) * 100, 100) }}%; font-weight: bold;">
                                    {{ $currentCount }} / {{ ($limit > 1000) ? '∞' : $limit }}
                                </div>
                            </div>

                            @if($currentCount >= $limit && $limit < 1000)
                                <hr>
                                <p class="mb-0 font-weight-bold">
                                    ⚠️ Aforo garantizado completo. Las nuevas inscripciones entran en lista de espera.
                                </p>
                            @endif
                        </div>
                    @endif
                    {{-- FIN BLOQUE LIMITES DE ESTADIO --}}
                                @if (!$isUserInscribed)
                                    @if(isset($isRegistered) && $isRegistered && in_array($event->beys, ['ranking', 'rankingplus']))
                                        <div class="alert alert-warning text-dark small mb-3">
                                            <i class="fas fa-exclamation-triangle me-1"></i> Ya estás inscrito en otro torneo de ranking esta semana.
                                        </div>
                                    @endif

                                    @php $creator = \App\Models\User::find($event->created_by); @endphp
                                    @if ($creator && !($creator->is_jury || $creator->is_referee))
                                        <div class="alert alert-info text-dark small mb-3">
                                            Evento creado por fan. Material no garantizado.
                                        </div>
                                    @endif

                                    @if($event->beys === "grancopa" || $event->beys === "copapaypal")
                                        <div class="text-center bg-white p-3 rounded">
                                            <p class="text-dark fw-bold mb-2">Inscripción Requerida: {{ $event->beys === "grancopa" ? '5€' : '2€' }}</p>
                                            <div id="paypal-button-container" class="d-flex justify-content-center"></div>
                                        </div>
                                    @else
                                        <form method="POST" action="{{ route('events.assist', ['event' => $event->id]) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-success w-100 fw-bold py-3 text-uppercase shadow-lg"
                                                @if(in_array($event->beys, ['ranking', 'rankingplus']) && isset($rankingTournamentsLeft) && $rankingTournamentsLeft == 0) disabled @endif>
                                                <i class="fas fa-user-plus me-2"></i> Inscribirse Ahora
                                                @if(in_array($event->beys, ['ranking', 'rankingplus']) && isset($rankingTournamentsLeft))
                                                    <span class="badge bg-white text-success ms-2 shadow-sm">Restantes: {{ $rankingTournamentsLeft }}</span>
                                                @endif
                                            </button>
                                        </form>
                                    @endif

                                @else
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="text-success fw-bold">
                                            <i class="fas fa-check-circle me-2 fs-4"></i> Estás inscrito en este evento.
                                        </div>
                                        <form method="POST" action="{{ route('events.noassist', ['event' => $event->id]) }}">
                                            @method('DELETE') @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Cancelar asistencia</button>
                                        </form>
                                    </div>
                                @endif

                            @elseif($event->status != "OPEN")
                                <div class="text-center text-white py-2">Las inscripciones están cerradas.</div>
                            @else
                                <div class="text-center text-white py-2">El evento ya ha finalizado.</div>
                            @endif
                        @else
                            <div class="text-center text-warning">Inicia sesión para inscribirte.</div>
                        @endauth
                        </div>

                        <div class="d-flex gap-2 mb-3">
                            <button id="copyButton" class="btn btn-outline-light btn-sm flex-fill border-secondary">
                                <i class="fas fa-copy me-1"></i> Copiar Nombres
                            </button>
                            @if (Auth::user() && Auth::user()->is_jury)
                                <button id="copyButtonEmail" class="btn btn-outline-light btn-sm flex-fill border-secondary">
                                    <i class="fas fa-envelope me-1"></i> Copiar Emails
                                </button>
                            @endif
                        </div>

                        @if (Auth::user() && Auth::user()->is_jury)
                            <div class="mb-3">
                                <form method="POST" action="{{ route('events.addAssist', ['event' => $event->id]) }}" class="input-group">
                                    @csrf
                                    <select name="participante_id" class="form-select form-select-sm select2">
                                        <option value="">Añadir participante manual...</option>
                                        @foreach($participantes as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-plus"></i></button>
                                </form>
                            </div>
                        @endif

                        @if(count($assists) > 0)
                            <form method="POST" action="{{ route('events.updatePuestos', ['event' => $event->id]) }}">
                                @csrf @method('PUT')
                                <div class="list-group list-group-flush rounded overflow-hidden">
                                    @foreach ($assists as $assist)
                                        <div class="list-group-item">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 35px; height: 35px; font-size: 0.9rem;">
                                                        {{ substr($assist->name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-white">{{ $assist->name }}</div>
                                                        @if(!empty($assist->pivot->puesto) && $assist->pivot->puesto !== 'participante')
                                                            <span class="badge bg-info text-dark rounded-pill">{{ $assist->pivot->puesto }}</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                                                    @if (($event->status != "CLOSE" && $event->status != "INVALID") && Auth::user()->is_referee || ($event->status == "OPEN" && $event->created_by == Auth::user()->id))
                                                        <input type="hidden" name="participantes[{{ $assist->id }}][id]" value="{{ $assist->id }}">
                                                        <select class="form-select form-select-sm" style="width: 130px;" name="participantes[{{ $assist->id }}][puesto]">
                                                            <option value="participante" {{ $assist->pivot->puesto == 'participante' ? 'selected' : '' }}>Participante</option>
                                                            <option value="primero" {{ $assist->pivot->puesto == 'primero' ? 'selected' : '' }}>1º Lugar</option>
                                                            <option value="segundo" {{ $assist->pivot->puesto == 'segundo' ? 'selected' : '' }}>2º Lugar</option>
                                                            <option value="tercero" {{ $assist->pivot->puesto == 'tercero' ? 'selected' : '' }}>3º Lugar</option>
                                                            <option value="nopresentado" {{ $assist->pivot->puesto == 'nopresentado' ? 'selected' : '' }}>No Pres.</option>
                                                        </select>
                                                    @endif

                                                    @php
                                                        $hasRes = isset($resultsByParticipant[$assist->id]) && count($resultsByParticipant[$assist->id]) > 0;
                                                    @endphp
                                                    @if($hasRes)
                                                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="collapse" data-bs-target="#res-{{ $assist->id }}">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="collapse mt-2" id="res-{{ $assist->id }}">
                                                <div class="card card-body p-2 small border-0" style="background-color: rgba(0,0,0,0.3);">
                                                    @if($hasRes)
                                                        @foreach($resultsByParticipant[$assist->id] as $res)
                                                            <div class="d-flex gap-2 border-bottom border-secondary border-opacity-25 py-1">
                                                                <span class="text-white fw-bold">{{ $res->blade }}</span>
                                                                <span>{{ $res->assist_blade }} {{ $res->ratchet }}  {{ $res->bit }}</span>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @if (($event->status != "CLOSE" && $event->status != "INVALID") && Auth::user()->is_referee || ($event->status == "OPEN" && $event->created_by == Auth::user()->id))
                                    <div class="card-footer mt-3 border rounded p-3">
                                        <h6 class="text-warning mb-2 small text-uppercase fw-bold">Cierre de Torneo</h6>
                                        <input type="url" name="iframe" class="form-control form-control-sm mb-2" placeholder="Link YouTube" value="{{ old('iframe', $event->iframe) }}" required>
                                        <input type="url" name="challonge" class="form-control form-control-sm mb-2" placeholder="Link Challonge" value="{{ old('challonge', $event->challonge) }}" required>
                                        <button type="submit" onclick="return confirm('¿Confirmar?');" class="btn btn-primary w-100 btn-sm fw-bold">GUARDAR RESULTADOS</button>
                                    </div>
                                @endif
                            </form>
                        @else
                            <div class="p-5 text-center text-muted border border-secondary border-opacity-25 rounded border-dashed mt-3">
                                <i class="fas fa-users-slash fs-1 mb-2 opacity-50"></i>
                                <p class="mb-0">Aún no hay participantes inscritos.</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>


    @endsection

    @section('scripts')
    @include('events.partials.deck_modal')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @php
        $paypalClientId = config('paypal.mode') === 'sandbox' ? config('paypal.sandbox.client_id') : config('paypal.live.client_id');
    @endphp
    <script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency=EUR"></script>

    <script>
        jQuery(document).ready(function ($) {

            // 1. Inicializar Select2 de la página principal (fuera del modal)
            // NO le ponemos dropdownParent para que funcione normal en la página
            $('.select2').not('.select2-modal').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            // 2. Inicializar Select2 DEL MODAL
            // Usamos el evento shown.bs.modal para asegurar que el modal existe
            $('#formModal').on('shown.bs.modal', function () {
                $('.select2-modal').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    dropdownParent: $('#formModal') // <--- ESTO ARREGLA EL SCROLL
                });
            });

            // Configuración PayPal
            @if(in_array($event->beys, ["grancopa", "copapaypal"]))
                const amount = "{{ $event->beys === 'grancopa' ? '5.00' : '2.00' }}";
                paypal.Buttons({
                    style: { layout: 'vertical', color: 'gold', shape: 'pill', label: 'pay' },
                    createOrder: (data, actions) => {
                        return actions.order.create({ purchase_units: [{ description: "Inscripción", amount: { value: amount } }] });
                    },
                    onApprove: (data, actions) => {
                        return actions.order.capture().then(details => {
                            fetch("{{ route('events.assist', ['event' => $event->id]) }}", {
                                method: "POST",
                                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                                body: JSON.stringify({ paypal_order_id: data.orderID })
                            }).then(res => { if (res.ok) window.location.reload(); });
                        });
                    }
                }).render("#paypal-button-container");
            @endif

            // Copiar al portapapeles
            $('#copyButton').on('click', function() {
                const names = {!! json_encode($assists->pluck('name')->values()->toArray()) !!}.join('\n');
                if(navigator.clipboard) {
                    navigator.clipboard.writeText(names).then(() => alert('Nombres copiados'));
                } else {
                    alert('Navegador no compatible');
                }
            });
        });
    </script>
    @endsection
