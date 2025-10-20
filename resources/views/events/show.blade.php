@extends('layouts.app')

@section('title', 'Página del evento')

@section('content')

<article class="contenido-event bg-white p-5 shadow" style="color:white !important;background-color: transparent !important;">
        <div class="row">
            <div class="col-md-12">
            <h1 class="text-center mb-4 w-100">{{ $event->name }}
                @if ($event->status == "OPEN")
                    <span class="btn btn-success">ABIERTO</span>
                @elseif ($event->status == "INSCRIPCION")
                    <span class="btn btn-light">INSCRIPCIÓN CERRADA</span>
                @elseif ($event->status == "PENDING")
                    <span class="btn btn-warning">PENDIENTE CALIFICAR</span>
                @elseif ($event->status == "REVIEW")
                    <span class="btn btn-info">EN REVISIÓN</span>
                @elseif ($event->status == "INVALID")
                <span class="btn btn-dark">INVÁLIDO</span>
            @else
                    <span class="btn btn-danger">CERRADO</span>
                @endif
            </h1>
        </div>
            <div class="col-md-5">
                <div class="imagen-event">
                    @if ($event->image_mod)
                        <img src="data:image/png;base64,{{ $event->image_mod }}" class="w-100 h-25" style="border-radius: 5px;">
                    @else
                        <img src="/storage/{{ $event->imagen }}" class="w-100 h-25" style="border-radius: 5px;">
                    @endif
                </div>

                @if ($event->beys == 'ranking' || $event->beys == 'rankingplus')
                    <div class="container my-2">
                        <div class="card border-0 shadow-lg rounded-4" style="background: linear-gradient(135deg, #1a1a1a, #2b2b2b);">
                            <div class="card-body text-center text-white p-3">
                                <p class="text-uppercase mb-3" style="color: #ffc107; text-shadow: 1px 1px 4px rgba(0,0,0,0.5);">
                                    💛 Iniciativa: ¡Apoya a tu Árbitro!
                                </p>

                                <div class="d-flex justify-content-center">
                                    <a href="https://www.paypal.com/donate?business=info%40sbbl.es&item_name={{ urlencode(Auth::user()->name . ' apoya a su árbitro de ' . $event->city . ' (' . $event->region->name . ')') }}&currency_code=EUR"
                                    target="_blank"
                                    class="btn btn-warning fw-bold px-4 py-2 rounded-pill shadow-lg"
                                    style="font-size: 1.1rem;">
                                        <i class="fab fa-paypal me-2"></i> Apoyar a {{ $event->region->name }}
                                    </a>
                                </div>

                                <p class="mt-4" style="font-size: 0.95rem; color: #aaa;">
                                    Estas aportaciones se destinan a apoyar la labor de nuestros árbitros y
                                    <strong>la compra del material comunitario</strong>.
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center my-4">
                        <div class="p-4 rounded-4 shadow-lg" style="background: linear-gradient(90deg, #1a1a1a, #3d2c00); color: #fff;">
                            <h5 class="fw-bold mb-3">
                                💛 ¡Apoya a la Comunidad SBBL!
                            </h5>
                            <p class="mb-4" style="font-size: 1.05rem;">
                                Tu aportación nos ayuda a mantener viva la liga, organizar torneos y seguir mejorando la experiencia de todos los bladers.
                            </p>
                            <a href="https://www.paypal.com/donate?business=info%40sbbl.es" target="_blank"
                            class="btn btn-warning fw-bold px-4 py-2 rounded-pill shadow-lg"
                            style="font-size: 1.1rem;">
                                <i class="fab fa-paypal me-2"></i> Contribuir vía PayPal
                            </a>
                        </div>
                    </div>
                @endif

                <div id="app" class="mt-2">
                    <chat-component :event-id="{{$event->id}}" />
                </div>
            </div>
            <div class="col-md-7">
                @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif


                <div class="row">
                    <div class="col-md-6">
                        <div class="event-meta">
                        <p class="mb-2">
                            <span class="fw-bold text-primary">Modalidad:</span>
                            {{ ($event->mode == 'beybladex') ? 'Beyblade X' : 'Beyblade Burst' }} ({{ $event->beys }})
                        </p>

                        @if(in_array($event->beys, ['ranking', 'rankingplus']))
                            <p class="mb-3">
                                <a href="{{ route('inicio.rules') }}" target="_blank"
                                class="btn btn-outline-info btn-sm d-inline-flex align-items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                        class="bi bi-journal-text" viewBox="0 0 16 16">
                                        <path d="M5 10.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
                                        <path d="M3 1v14a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V1H3zm8 13H5a.5.5 0 0 1 0-1h6a.5.5 0 0 1 0 1z"/>
                                    </svg>
                                    Reglas del torneo
                                </a>
                            </p>
                        @endif

                        <p class="mb-2">
                            <span class="fw-bold text-primary">Configuración:</span>
                            {{ $event->deck }} <span class="fw-bold">({{ $event->configuration }})</span>
                        </p>
                        <p class="mb-2">
                            <span class="fw-bold text-primary">Región:</span>
                            {{ $event->region->name }}
                        </p>
                        <p class="mb-2">
                            <span class="fw-bold text-primary">Localidad:</span>
                            {{ $event->city }}
                        </p>
                        <p class="mb-2">
                            <span class="fw-bold text-primary">Lugar:</span>
                            {{ $event->location }}
                        </p>
                        <p class="mb-2">
                            <span class="fw-bold text-primary">Fecha y hora:</span>
                            <event-date fecha="{{ $event->date }}"></event-date> <span class="fw-bold">({{ $event->time }})</span>
                        </p>
                        <p class="mb-3">
                            <span class="fw-bold text-primary">Anotaciones:</span>
                            {!! $event->note !!}
                        </p>

                        @php
                            use Carbon\Carbon;
                            $now = Carbon::now();
                            $eventDate = Carbon::parse($event->date);
                            $diffInHours = $eventDate->diffInHours($now, false); // negativo si está en el futuro
                        @endphp

                        @if (
                            ($event->status == "OPEN") &&
                            (Auth::user()->is_admin || Auth::user()->is_referee) &&
                            ($diffInHours <= 24 && $diffInHours >= 0)
                        )
                            <form method="POST" action="{{ route('events.estado', ['event' => $event->id, 'estado' => 'inscripcion']) }}">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-light mb-2 w-100">
                                    Cerrar inscripción
                                </button>
                            </form>
                        @endif


                        @if (($event->status != "CLOSE" && $event->status != "INVALID") && Auth::user()->is_referee || ($event->status == "OPEN" && $event->created_by == Auth::user()->id))
                            <a href="{{ route('events.edit', ['event' => $event->id]) }}" class="btn btn-dark mb-3 d-block">
                                Editar
                            </a>
                        @endif

                        @if ($suscribe && $event->status != "INVALID")
                            <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#formModal">
                                Introducir deck
                            </button>
                        @endif


                @if (in_array($event->beys, ['ranking', 'rankingplus']) && (in_array($event->status, ['INVALID', 'CLOSE']) || auth()->user()->is_admin))
                    <!-- BOTÓN PARA MOSTRAR VALIDADORES -->
                    <button class="btn btn-danger mt-2 w-100" type="button"
                            data-bs-toggle="collapse" data-bs-target="#reviewCollapse{{ $event->id }}"
                            aria-expanded="false" aria-controls="reviewCollapse{{ $event->id }}">
                    Mostrar validadores y comentarios
                    </button>

                    <!-- SECCIÓN DESPLEGABLE DE VALIDADORES -->
                    <div class="collapse mt-2" id="reviewCollapse{{ $event->id }}">
                    <div class="card card-body bg-light text-dark p-2" style="font-size: 0.85rem;">
                        <strong>Validadores y comentarios:</strong>
                        @if($event->reviews->isEmpty() && !$event->judgeReview)
                        <em>No hay revisiones aún.</em>
                        @elseif (!$event->judgeReview)
                        <ul class="mb-0 ps-3" style="max-height: 150px; overflow-y: auto;">
                            @foreach($event->reviews as $review)
                            <li class="mb-1">
                                <strong>
                                @if(auth()->user()->is_admin || Auth::user()->name == $review->referee->name)
                                    {{ $review->referee->name ?? 'Árbitro desconocido' }}
                                @else
                                    Árbitro {{ $loop->iteration }}
                                @endif
                                </strong>:
                                <span class="badge
                                @if($review->status == 'approved') bg-success
                                @elseif($review->status == 'rejected') bg-danger
                                @else bg-secondary
                                @endif">
                                {{ strtoupper($review->status) }}
                                </span><br>
                                <em>{{ $review->comment }}</em>
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <ul class="mb-0 ps-3" style="max-height: 150px; overflow-y: auto;">
                            <li class="mb-1">
                                <strong>

                                    Juez

                                </strong>:
                                <span class="badge
                                @if($event->judgeReview->final_status == 'approved') bg-success
                                @elseif($event->judgeReview->final_status == 'rejected') bg-danger
                                @else bg-secondary
                                @endif">
                                {{ strtoupper($event->judgeReview->final_status) }}
                                </span><br>
                                <em>{{ $event->judgeReview->comment }}</em>
                            </li>
                        </ul>
                        @endif
                    </div>
                    </div>

                @endif



                                @if ($event->iframe)
                                    <div class="mt-2">
                                        <a href="{{ $event->iframe }}" target="_blank" class="btn btn-info text-uppercase w-100"
                                        style="width: 100%">Ver Video</a>
                                    </div>
                                @endif
                                @if ($event->challonge)
                                    <div class="mt-2">
                                        <a href="{{ $event->challonge }}" target="_blank" class="btn btn-info text-uppercase w-100"
                                        style="width: 100%">Ver Challonge</a>
                                    </div>
                                @endif

                    </div>

                    </div>
                    <div class="col-md-6">
                        @if($event->status == "OPEN" && Auth::user() && $event->date > $hoy)
                    @if (!$suscribe)
                        @if($isRegistered && ($event->beys == "ranking" || $event->beys == "rankingplus"))
                            <span class="alert alert-warning d-block mt-3 p-2 text-center font-weight-bold">
                                ⚠️ Ya te has apuntado a otro torneo esta semana. Recuerda que está prohibido participar en dos torneos la misma semana salvo excepción aprobada por los admins o ser un torneo especial.
                            </span>
                        @endif

                        @if($event->beys === "grancopa")
                            <div style="text-align: center; margin-bottom: 10px;">
                                <strong>💶 Inscripción: 5€</strong> (Añadir <span class="me-1">🦎</span></i>500 al bote)
                            </div>
                                <div id="paypal-button-container" style="text-align: center;"></div>
                        @elseif ($event->beys === "copapaypal")
                            <div style="text-align: center; margin-bottom: 10px;">
                                <strong>💶 Inscripción: 2€</strong> (Añadir <span class="me-1">🦎</span></i>200 al bote)
                            </div>
                                <div id="paypal-button-container" style="text-align: center;"></div>
                        @else
                            <form method="POST" action="{{ route('events.assist', ['event' => $event->id]) }}" enctype="multipart/form-data" novalidate style="text-align: center;">
                                @csrf
                                <div class="form-group py-2">
                                    <input
                                        type="submit"
                                        class="btn btn-primary text-uppercase font-weight-bold m-1 flex-right"
                                        value="Inscribirse"
                                        @if(in_array($event->beys, ['ranking', 'rankingplus']) && $rankingTournamentsLeft == 0)
                                            disabled
                                        @endif
                                    >
                                    @if(in_array($event->beys, ['ranking', 'rankingplus']))
                                        <span class="badge badge-warning ml-2" title="Torneos de ranking restantes este mes" style="font-size: 1rem;">
                                            🎟️ {{ $rankingTournamentsLeft }}
                                        </span>
                                    @endif
                                </div>
                            </form>
                        @endif

                    @php
                        $user = \App\Models\User::findOrFail($event->created_by);
                    @endphp

                    @if ($user && !($user->is_jury || $user->is_referee))
                        <span class="alert alert-warning d-block mt-3 p-2 text-center font-weight-bold">
                            ⚠️ Este evento <bold>NO HA SIDO CREADO POR UN ÁRBITRO</bold>, el material tiene que ser proporcionado por los participantes.
                        </span>
                    @endif

                    @else
                        <form method="POST" action="{{ route('events.noassist', ['event' => $event->id]) }}" style="display: contents; text-align: center;">
                            @method('DELETE')
                            @csrf
                            <button type="submit" class="btn btn-danger mr-2 text-uppercase font-weight-bold m-1 flex-right">No asistiré</button>
                        </form>
                    @endif
                @endif
                        <h4 style="font-weight: bold">Listado de participantes ({{ $assists->count() }})</h4>
                        @if ($event->beys == 'copapaypal' || $event->beys == 'grancopa')
                        <div class="text-center">
                            <div class="bg-dark rounded-pill px-3 py-2 shadow-sm d-inline-block">
                                <span class="fw-bold text-warning">
                                    Bote acumulado: <span class="me-1">🦎</span> {{ number_format($assists->count() * (($event->beys == 'copapaypal') ? 200 : 500)) }}
                                </span>
                            </div>
                        </div>
                        @endif

                        <!-- Botón para copiar nombres -->
                        <button id="copyButton" class="btn btn-outline-primary mt-3 mb-3 w-100">Copiar nombres</button>
                        @if (Auth::user() && Auth::user()->is_jury)
                            <form method="POST" action="{{ route('events.addAssist', ['event' => $event->id]) }}" enctype="multipart/form-data" novalidate class="d-flex justify-content-center align-items-center gap-2 my-4">
                                @csrf
                                <select name="participante_id" id="participante" class="form-select select2" style="width: 300px;">
                                    <option value="">-- Busca o selecciona un participante --</option>
                                    @foreach($participantes as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>

                                <button type="submit" class="btn btn-success fw-bold" title="Añadir participante" style="width: 40px; height: 40px; font-size: 20px;">
                                    +
                                </button>
                            </form>

                        @endif
                        @if($assists->count() < 4 && $event->status == "OPEN")
                            <div class="alert alert-danger">
                                Importante: Si el número de participantes es menor a 4 el torneo no se realizará.
                            </div>
                        @endif
                        @if (count($assists) > 0)
                            <form id="puestos-form" method="POST" action="{{ route('events.updatePuestos', ['event' => $event->id]) }}" enctype="multipart/form-data" novalidate>
                                @csrf
                                @method('PUT')

                                @foreach ($assists as $assist)
                                    <div class="row mb-2">
                                        <div class="col-md-9">
                                            <p class="mb-0">
                                                {{ $assist->name }}
                                                @if($event->beys == "ranking" || $event->beys == "rankingplus") ({{ DB::table('assist_user_event')
                                                ->join('events', 'assist_user_event.event_id', '=', 'events.id')
                                                ->where('assist_user_event.user_id', $assist->id)
                                                ->whereMonth('events.date', \Carbon\Carbon::parse($event->date)->month)
                                                ->whereYear('events.date', \Carbon\Carbon::parse($event->date)->year)
                                                ->whereIn('events.beys', ['ranking', 'rankingplus']) // Añadir filtro de beys
                                                ->where('assist_user_event.puesto', '<>', 'nopresentado') // Filtro por puesto
                                                ->count();

                                                }}
                                                torneos) @endif

                                                @if(!empty($assist->pivot->puesto) && $assist->pivot->puesto !== 'No presentado')
                                                    - {{ $assist->pivot->puesto }}
                                                @endif

                                                @if (Auth::user()->is_admin)
                                                    <b>{{ $assist->email }}</b>
                                                @endif

                                                @if (($event->status != "CLOSE" && $event->status != "INVALID") && Auth::user()->is_referee || ($event->status == "OPEN" && $event->created_by == Auth::user()->id))
                                                    <input type="hidden" name="participantes[{{ $assist->id }}][id]" value="{{ $assist->id }}">
                                                    @php
                                                        $count = $assists->count();
                                                        $options = ['primero', 'segundo']; // Default para <8

                                                        if ($count >= 9 && $count <= 16) {
                                                            $options = ['primero', 'segundo', 'tercero'];
                                                        } elseif ($count >= 17 && $count <= 24) {
                                                            $options = ['primero', 'segundo', 'tercero', 'cuarto'];
                                                        } elseif ($count >= 25 && $count <= 32) {
                                                            $options = ['primero', 'segundo', 'tercero', 'cuarto', 'quinto'];
                                                        } elseif ($count > 32) {
                                                            $options = ['primero', 'segundo', 'tercero', 'cuarto', 'quinto', 'septimo'];
                                                        }

                                                        // Etiquetas para mostrar (puedes modificar si quieres)
                                                        $labels = [
                                                            'primero' => 'Primer puesto',
                                                            'segundo' => 'Segundo puesto',
                                                            'tercero' => 'Tercer puesto',
                                                            'cuarto' => 'Cuarto puesto',
                                                            'quinto' => 'Quinto puesto',
                                                            'septimo' => 'Séptimo puesto',
                                                        ];
                                                    @endphp

                                                    <select class="form-control" name="participantes[{{ $assist->id }}][puesto]">
                                                        <option value="participante" {{ $assist->pivot->puesto == 'participante' ? 'selected' : '' }}>-- Selecciona un puesto --</option>

                                                        @foreach ($options as $opt)
                                                            <option value="{{ $opt }}" {{ $assist->pivot->puesto == $opt ? 'selected' : '' }}>
                                                                {{ $labels[$opt] }}
                                                            </option>
                                                        @endforeach
                                                        <option value="nopresentado" {{ $assist->pivot->puesto == 'nopresentado' ? 'selected' : '' }}>No presentado/a</option>
                                                    </select>

                                                @endif
                                            </p>
                                        </div>
                                        <div class="col-md-3 text-right">
                                            @php
                                                $today = \Carbon\Carbon::now()->format('Y-m-d');  // Obtener la fecha actual en formato 'Y-m-d'
                                                $eventDate = \Carbon\Carbon::parse($event->date)->format('Y-m-d');  // Fecha del evento en formato 'Y-m-d'
                                            @endphp
                                            @if(($today === $eventDate && isset($resultsByParticipant[Auth::user()->id]) && count($resultsByParticipant[Auth::user()->id]) > 0) || $today > $eventDate || Auth::user()->is_admin)
                                                <button type="button" class="btn btn-info btn-sm" data-toggle="collapse" data-target="#results-{{ $assist->id }}" aria-expanded="false" aria-controls="results-{{ $assist->id }}" title="Ver resultados">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    @if($today === $eventDate && isset($resultsByParticipant[Auth::user()->id]) && count($resultsByParticipant[Auth::user()->id]) > 0 || $today > $eventDate || Auth::user()->is_admin)
                                        <div class="collapse" id="results-{{ $assist->id }}">
                                            <div class="card card-body mb-3" style="background-color: rgb(2, 0, 97)">
                                                @if(isset($resultsByParticipant[$assist->id]) && count($resultsByParticipant[$assist->id]) > 0)
                                                    @foreach($resultsByParticipant[$assist->id] as $index => $result)
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <p> · {{ $result->blade }} {{ $result->assist_blade }} {{ $result->ratchet }} {{ $result->bit }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <p>No hay resultados registrados para este participante.</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                                <div class="mt-4">
                                    <h4 class="mb-4 text-info border-bottom border-info pb-2">Datos del torneo</h4>

                                    <div class="form-group">
                                        <label for="iframe" class="fw-semibold text-light">Link al video del torneo:</label>
                                        <input type="url" name="iframe" id="iframe" class="form-control mb-2 bg-dark text-light border-secondary"
                                            placeholder="https://www.youtube.com/embed/tu-video"
                                            value="{{ old('iframe', $event->iframe ?? '') }}"
                                            required>
                                    </div>

                                    <div class="form-group">
                                        <label for="challonge" class="fw-semibold text-light">Enlace al torneo en Challonge:</label>
                                        <input type="url" name="challonge" id="challonge" class="form-control mb-2 bg-dark text-light border-secondary"
                                            placeholder="https://challonge.com/es/"
                                            value="{{ old('challonge', $event->challonge ?? '') }}"
                                            required>
                                    </div>
                                </div>

                                @if (($event->status != "CLOSE" && $event->status != "INVALID") && Auth::user()->is_referee || ($event->status == "OPEN" && $event->created_by == Auth::user()->id))
                                    <div class="form-group py-2">
                                        <input type="submit" onclick="return confirm('¿Has rellenado el podio y los enlaces correctamente?');" class="btn btn-outline-success text-uppercase font-weight-bold flex-right" value="Enviar resultados"  style="width: 100%">
                                    </div>
                                @endif
                            </form>

                        @else
                            <p>No hay participantes.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </article>

@endsection

@section('styles')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- CDN de Select2 CSS -->
    <style>
        .select2-container--default .select2-selection--single {
            z-index: 9999 !important; /* Asegúrate de que el select2 se muestra sobre otros elementos */
        }
        .select2-selection--single {
    height: 38px !important; /* igual que form-control de Bootstrap */
    padding: 0.375rem 0.75rem;
}


    </style>
@endsection

@section('scripts')
    @php
        $paypalClientId = config('paypal.mode') === 'sandbox'
            ? config('paypal.sandbox.client_id')
            : config('paypal.live.client_id');
    @endphp

    <script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency=EUR"></script>

    <script>
        jQuery(document).ready(function () {
            const isReferee = @json(Auth::user()->is_jury);

            // 👉 Botón PayPal solo si es GranCopa
            @if($event->beys === "grancopa")
                paypal.Buttons({
                    style: {
                        layout: 'vertical',   // o 'horizontal'
                        size: 'responsive',        // 'small' | 'medium' | 'large' | 'responsive'
                        shape: 'rect',        // 'rect' | 'pill'
                        color: 'gold',        // 'gold' | 'blue' | 'silver' | 'black'
                        label: 'pay'          // 'paypal' | 'checkout' | 'buynow' | 'pay'
                    },
                    createOrder: function(data, actions) {
                        return actions.order.create({
                            purchase_units: [{
                                description: "Inscripción Gran Copa",
                                amount: {
                                    value: "5.00"
                                }
                            }]
                        });
                    },
                    onApprove: function(data, actions) {
                        return actions.order.capture().then(function(details) {
                            fetch("{{ route('events.assist', ['event' => $event->id]) }}", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                },
                                body: JSON.stringify({
                                    paypal_order_id: data.orderID,
                                    payer: details.payer
                                })
                            }).then(res => {
                                if (res.ok) {
                                    window.location.reload();
                                } else {
                                    alert("⚠️ Hubo un problema al inscribirte. Intenta de nuevo.");
                                }
                            });
                        });
                    }
                }).render("#paypal-button-container");
            @endif

            // 👉 Botón PayPal solo si es GranCopa
            @if($event->beys === "copapaypal")
                paypal.Buttons({
                    style: {
                        layout: 'vertical',   // o 'horizontal'
                        size: 'responsive',        // 'small' | 'medium' | 'large' | 'responsive'
                        shape: 'rect',        // 'rect' | 'pill'
                        color: 'gold',        // 'gold' | 'blue' | 'silver' | 'black'
                        label: 'pay'          // 'paypal' | 'checkout' | 'buynow' | 'pay'
                    },
                    createOrder: function(data, actions) {
                        return actions.order.create({
                            purchase_units: [{
                                description: "Inscripción Copa Paypal",
                                amount: {
                                    value: "2.00"
                                }
                            }]
                        });
                    },
                    onApprove: function(data, actions) {
                        return actions.order.capture().then(function(details) {
                            fetch("{{ route('events.assist', ['event' => $event->id]) }}", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                },
                                body: JSON.stringify({
                                    paypal_order_id: data.orderID,
                                    payer: details.payer
                                })
                            }).then(res => {
                                if (res.ok) {
                                    window.location.reload();
                                } else {
                                    alert("⚠️ Hubo un problema al inscribirte. Intenta de nuevo.");
                                }
                            });
                        });
                    }
                }).render("#paypal-button-container");
            @endif

            // 👉 Copiar nombres al portapapeles
            const copyBtn = jQuery('#copyButton');
            if (copyBtn.length) {
                copyBtn.on('click', () => {
                    const participants = {!! json_encode($assists->pluck('name')->values()->toArray()) !!};
                    const names = participants.join('\n');

                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(names)
                            .then(() => alert('✅ Nombres copiados al portapapeles'))
                            .catch(() => fallbackCopy(names));
                    } else {
                        fallbackCopy(names);
                    }

                    function fallbackCopy(text) {
                        const tempTextArea = document.createElement('textarea');
                        tempTextArea.value = text;
                        document.body.appendChild(tempTextArea);
                        tempTextArea.select();
                        document.execCommand('copy');
                        document.body.removeChild(tempTextArea);
                        alert('✅ Nombres copiados al portapapeles');
                    }
                });
            }

            // 👉 Validación de formulario si no es referee
            if (!isReferee) {
                jQuery("input[type='submit'][value='Enviar resultados']").on('click', function(event) {
                    const iframeInput = jQuery("input[name='iframe']");
                    const challongeInput = jQuery("input[name='challonge']");
                    if (!iframeInput.val()?.trim() || !challongeInput.val()?.trim()) {
                        event.preventDefault();
                        alert("⚠️ Debes introducir un enlace de video y de challonge antes de enviar los resultados.");
                    }
                });
            }

            // 👉 Re-inicializar Select2 al mostrar el modal
            jQuery('#formModal').on('shown.bs.modal', function () {
                jQuery('.select2').select2({
                    dropdownParent: jQuery('#formModal')
                });
            });
        jQuery(document).ready(function() {
                jQuery('.select2').select2({
                    width: '100%',
                    placeholder: "Añadir participante",
            });
        });

            // 👉 Inicializar tooltips
            jQuery('[data-toggle="tooltip"]').tooltip();
        });

    </script>

<!-- Modal para introducir decks - Versión Bootstrap 5 -->
<div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="formModalLabel">Resultados del deck en el torneo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Formulario dentro del popup -->
                <form method="POST" action="{{ route('tournament.results.store', ['eventId' => $event->id]) }}">
                    @csrf

                    @if(Auth::user()->is_referee && 1==2 )
                        @foreach($assists as $assist)
                            <div class="mb-4">
                                <h4>{{ $assist->name }} ({{ $assist->email }})</h4>
                                @foreach(range(1, 3) as $index)
                                    <div class="row g-3 mb-3">
                                        <!-- Blade -->
                                        <div class="col-md-2">
                                            <label for="blade_{{ $assist->id }}_{{ $index }}" class="form-label">Blade</label>
                                            <select class="form-select select2" id="blade_{{ $assist->id }}_{{ $index }}" name="blade[{{ $assist->id }}][{{ $index }}]" required>
                                                <option value="">-- Selecciona un blade --</option>
                                                @foreach($bladeOptions as $option)
                                                    <option value="{{ $option }}"
                                                        @if(isset($resultsByParticipant[$assist->id][$index-1]) && $resultsByParticipant[$assist->id][$index-1]->blade == $option) selected @endif>
                                                        {{ $option }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Assist blade -->
                                        <div class="col-md-2">
                                            <label for="assist_blade_{{ $assist->id }}_{{ $index }}" class="form-label">Assist blade (Solo CX)</label>
                                            <select class="form-select select2" id="assist_blade_{{ $assist->id }}_{{ $index }}" name="assist_blade[{{ $assist->id }}][{{ $index }}]">
                                                <option value=""></option>
                                                @foreach($assistBladeOptions as $option)
                                                    <option value="{{ $option }}"
                                                        @if(isset($resultsByParticipant[$assist->id][$index-1]) && $resultsByParticipant[$assist->id][$index-1]->assist_blade == $option) selected @endif>
                                                        {{ $option }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Ratchet -->
                                        <div class="col-md-2">
                                            <label for="ratchet_{{ $assist->id }}_{{ $index }}" class="form-label">Ratchet</label>
                                            <select class="form-select select2" id="ratchet_{{ $assist->id }}_{{ $index }}" name="ratchet[{{ $assist->id }}][{{ $index }}]" required>
                                                <option value="">-- Selecciona un ratchet --</option>
                                                @foreach($ratchetOptions as $option)
                                                    <option value="{{ $option }}"
                                                        @if(isset($resultsByParticipant[$assist->id][$index-1]) && $resultsByParticipant[$assist->id][$index-1]->ratchet == $option) selected @endif>
                                                        {{ $option }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Bit -->
                                        <div class="col-md-2">
                                            <label for="bit_{{ $assist->id }}_{{ $index }}" class="form-label">Bit</label>
                                            <select class="form-select select2" id="bit_{{ $assist->id }}_{{ $index }}" name="bit[{{ $assist->id }}][{{ $index }}]" required>
                                                <option value="">-- Selecciona un bit --</option>
                                                @foreach($bitOptions as $option)
                                                    <option value="{{ $option }}"
                                                        @if(isset($resultsByParticipant[$assist->id][$index-1]) && $resultsByParticipant[$assist->id][$index-1]->bit == $option) selected @endif>
                                                        {{ $option }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Victorias -->
                                        <div class="col-md-1">
                                            <label for="victorias_{{ $assist->id }}_{{ $index }}" class="form-label">Victorias</label>
                                            <input type="number" class="form-control" id="victorias_{{ $assist->id }}_{{ $index }}" name="victorias[{{ $assist->id }}][{{ $index }}]" value="{{ isset($resultsByParticipant[$assist->id][$index-1]) ? $resultsByParticipant[$assist->id][$index-1]->victorias : 0 }}">
                                        </div>

                                        <!-- Derrotas -->
                                        <div class="col-md-1">
                                            <label for="derrotas_{{ $assist->id }}_{{ $index }}" class="form-label">Derrotas</label>
                                            <input type="number" class="form-control" id="derrotas_{{ $assist->id }}_{{ $index }}" name="derrotas[{{ $assist->id }}][{{ $index }}]" value="{{ isset($resultsByParticipant[$assist->id][$index-1]) ? $resultsByParticipant[$assist->id][$index-1]->derrotas : 0 }}">
                                        </div>

                                        <!-- Puntos Ganados -->
                                        <div class="col-md-1">
                                            <label for="puntos_ganados_{{ $assist->id }}_{{ $index }}" class="form-label">P. Ganados</label>
                                            <input type="number" class="form-control" id="puntos_ganados_{{ $assist->id }}_{{ $index }}" name="puntos_ganados[{{ $assist->id }}][{{ $index }}]" value="{{ isset($resultsByParticipant[$assist->id][$index-1]) ? $resultsByParticipant[$assist->id][$index-1]->puntos_ganados : 0 }}">
                                        </div>

                                        <!-- Puntos Perdidos -->
                                        <div class="col-md-1">
                                            <label for="puntos_perdidos_{{ $assist->id }}_{{ $index }}" class="form-label">P. Perdidos</label>
                                            <input type="number" class="form-control" id="puntos_perdidos_{{ $assist->id }}_{{ $index }}" name="puntos_perdidos[{{ $assist->id }}][{{ $index }}]" value="{{ isset($resultsByParticipant[$assist->id][$index-1]) ? $resultsByParticipant[$assist->id][$index-1]->puntos_perdidos : 0 }}">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach

                    @else
                        <!-- Si no es referee, solo se muestra su propio deck -->
                        <div class="mb-4">
                            <h4>{{ Auth::user()->name }}'s Deck</h4>
                            @foreach(range(1, 3) as $index)
                                <div class="row g-3 mb-3">
                                    <!-- Blade -->
                                    <div class="col-md-2">
                                        <label for="blade_{{ Auth::user()->id }}_{{ $index }}" class="form-label">Blade</label>
                                        <select class="form-select select2" id="blade_{{ Auth::user()->id }}_{{ $index }}" name="blade[{{ Auth::user()->id }}][]" required>
                                            <option value="">-- Selecciona un blade --</option>
                                            @foreach($bladeOptions as $option)
                                                <option value="{{ $option }}"
                                                    @if(isset($results[$index-1]) && $results[$index-1]->blade == $option) selected @endif>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Assist blade -->
                                    <div class="col-md-2">
                                        <label for="assist_blade_{{ Auth::user()->id }}_{{ $index }}" class="form-label">Assist blade (Solo CX)</label>
                                        <select class="form-select select2" id="ratchet_{{ Auth::user()->id }}_{{ $index }}" name="assist_blade[{{ Auth::user()->id }}][]">
                                            <option value="">-- Selecciona un assist blade --</option>
                                            @foreach($assistBladeOptions as $option)
                                                <option value="{{ $option }}"
                                                    @if(isset($results[$index-1]) && $results[$index-1]->assist_blade == $option) selected @endif>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Ratchet -->
                                    <div class="col-md-2">
                                        <label for="ratchet_{{ Auth::user()->id }}_{{ $index }}" class="form-label">Ratchet</label>
                                        <select class="form-select select2" id="ratchet_{{ Auth::user()->id }}_{{ $index }}" name="ratchet[{{ Auth::user()->id }}][]" required>
                                            <option value="">-- Selecciona un ratchet --</option>
                                            @foreach($ratchetOptions as $option)
                                                <option value="{{ $option }}"
                                                    @if(isset($results[$index-1]) && $results[$index-1]->ratchet == $option) selected @endif>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Bit -->
                                    <div class="col-md-2">
                                        <label for="bit_{{ Auth::user()->id }}_{{ $index }}" class="form-label">Bit</label>
                                        <select class="form-select select2" id="bit_{{ Auth::user()->id }}_{{ $index }}" name="bit[{{ Auth::user()->id }}][]">
                                            <option value="">-- Selecciona un bit --</option>
                                            @foreach($bitOptions as $option)
                                                <option value="{{ $option }}"
                                                    @if(isset($results[$index-1]) && $results[$index-1]->bit == $option) selected @endif>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Victorias -->
                                    <div class="col-md-1">
                                        <label for="victorias_{{ Auth::user()->id }}_{{ $index }}" class="form-label">Victorias</label>
                                        <input type="number" class="form-control" id="victorias_{{ Auth::user()->id }}_{{ $index }}" name="victorias[{{ Auth::user()->id }}][]" value="{{ isset($results[$index-1]) ? $results[$index-1]->victorias : 0 }}">
                                    </div>

                                    <!-- Derrotas -->
                                    <div class="col-md-1">
                                        <label for="derrotas_{{ Auth::user()->id }}_{{ $index }}" class="form-label">Derrotas</label>
                                        <input type="number" class="form-control" id="derrotas_{{ Auth::user()->id }}_{{ $index }}" name="derrotas[{{ Auth::user()->id }}][]" value="{{ isset($results[$index-1]) ? $results[$index-1]->derrotas : 0 }}">
                                    </div>

                                    <!-- Puntos Ganados -->
                                    <div class="col-md-1">
                                        <label for="puntos_ganados_{{ Auth::user()->id }}_{{ $index }}" class="form-label">P. Ganados</label>
                                        <input type="number" class="form-control" id="puntos_ganados_{{ Auth::user()->id }}_{{ $index }}" name="puntos_ganados[{{ Auth::user()->id }}][]" value="{{ isset($results[$index-1]) ? $results[$index-1]->puntos_ganados : 0 }}">
                                    </div>

                                    <!-- Puntos Perdidos -->
                                    <div class="col-md-1">
                                        <label for="puntos_perdidos_{{ Auth::user()->id }}_{{ $index }}" class="form-label">P. Perdidos</label>
                                        <input type="number" class="form-control" id="puntos_perdidos_{{ Auth::user()->id }}_{{ $index }}" name="puntos_perdidos[{{ Auth::user()->id }}][]" value="{{ isset($results[$index-1]) ? $results[$index-1]->puntos_perdidos : 0 }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <button type="submit" class="btn btn-primary">Guardar resultados</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
