@extends('layouts.app')

@section('title', 'Tesorería SBBL')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .table-tactical { border: 3px solid #000; box-shadow: 6px 6px 0 #000; background: var(--sbbl-blue-2); margin-bottom: 0; }
    .table-tactical thead { background: #000; border-bottom: 4px solid var(--sbbl-gold); }
    .table-tactical th { font-family: 'Oswald', sans-serif; color: var(--shonen-cyan); font-size: 1.1rem; letter-spacing: 1px; text-transform: uppercase; border: none; padding: 15px; }
    .table-tactical td { background: transparent; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.1); vertical-align: middle; padding: 15px; }
    .table-tactical tbody tr:hover { background: rgba(0, 0, 0, 0.4); }
    .stat-card { border: 3px solid #000; padding: 20px; text-align: center; box-shadow: 5px 5px 0 #000; transition: 0.2s; }

    .text-income { color: #00ff88 !important; }
    .text-expense { color: #ff4444 !important; }
</style>
@endsection

@section('content')
<div class="container-fluid py-4 mb-5">

    <div class="text-center mb-5">
        <h2 class="font-bangers" style="font-size: 3.5rem; color: var(--sbbl-gold); text-shadow: 2px 2px 0px #000, 4px 4px 0px var(--shonen-red);">
            <i class="fas fa-file-invoice-dollar me-2 text-white" style="text-shadow:none;"></i> TESORERÍA
        </h2>
        <p class="text-white fw-bold fs-5">Control de ingresos, gastos y facturación de la asociación.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-shonen alert-shonen-success mb-4 text-center"><div><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div></div>
    @endif
    @if ($errors->any())
        <div class="alert alert-shonen alert-shonen-danger mb-4 text-center">
            <ul class="mb-0 list-unstyled">
                @foreach ($errors->all() as $error)
                    <li><i class="fas fa-exclamation-triangle me-2"></i>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3 mb-5">
        <div class="col-md-4">
            <div class="stat-card" style="background: rgba(0, 255, 136, 0.1); border-color: #00ff88;">
                <h6 class="text-white-50 font-bangers fs-5 m-0">TOTAL INGRESOS (NETO)</h6>
                <div class="font-bangers text-income" style="font-size: 3rem;">{{ number_format($totalIngresosNetos, 2, ',', '.') }} €</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="background: rgba(255, 68, 68, 0.1); border-color: #ff4444;">
                <h6 class="text-white-50 font-bangers fs-5 m-0">TOTAL GASTOS</h6>
                <div class="font-bangers text-expense" style="font-size: 3rem;">{{ number_format($totalGastosNetos, 2, ',', '.') }} €</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="background: {{ $saldoActual >= 0 ? 'var(--sbbl-blue-2)' : 'rgba(255, 0, 0, 0.2)' }}; border-color: var(--sbbl-gold);">
                <h6 class="text-white-50 font-bangers fs-5 m-0">SALDO ACTUAL ASOCIACIÓN</h6>
                <div class="font-bangers" style="font-size: 3.5rem; color: {{ $saldoActual >= 0 ? 'var(--sbbl-gold)' : '#ff4444' }};">
                    {{ number_format($saldoActual, 2, ',', '.') }} €
                </div>
            </div>
        </div>
    </div>

    <div class="command-panel p-4 mb-5" style="background: rgba(0,0,0,0.5); border: 2px solid var(--shonen-cyan);">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
            <h4 class="font-bangers text-white m-0" style="font-size: 2rem;">
                <i class="fas fa-list-alt me-2" style="color: var(--shonen-cyan);"></i> HISTORIAL DE MOVIMIENTOS
            </h4>
            <div class="d-flex gap-2">
                <button class="btn btn-success fw-bold border border-dark" data-bs-toggle="modal" data-bs-target="#incomeModal">
                    <i class="fas fa-arrow-down me-2"></i> REGISTRAR INGRESO
                </button>
                <button class="btn btn-danger fw-bold border border-dark" data-bs-toggle="modal" data-bs-target="#expenseModal">
                    <i class="fas fa-arrow-up me-2"></i> REGISTRAR GASTO
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-tactical align-middle">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Concepto / Descripción</th>
                        <th class="text-center">Categoría</th>
                        <th>Relacionado con</th>
                        <th class="text-center">Estado</th> <th class="text-end">Importe Neto</th>
                        <th class="text-center">Justificante</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr style="background: {{ $log->type == 'ingreso' ? 'rgba(0, 255, 136, 0.03)' : 'rgba(255, 68, 68, 0.03)' }};">
                            <td class="small"><i class="far fa-calendar-alt me-1"></i> {{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="fw-bold fs-6">{{ $log->description }}</div>
                                @if($log->reference_id) <div class="small" style="font-size: 0.8rem;">Ref: {{ $log->reference_id }}</div> @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-dark border border-secondary">{{ strtoupper($log->category) }}</span>
                            </td>
                            <td>
                                @if($log->user) <div class="text-info small"><i class="fas fa-user me-1"></i>{{ $log->user->name }}</div> @endif
                                @if($log->event) <div class="text-warning small"><i class="fas fa-trophy me-1"></i>{{ Str::limit($log->event->name, 20) }}</div> @endif
                                @if(!$log->user && !$log->event) <span class="text-secondary small">-</span> @endif
                            </td>
                            <td class="text-center">
                                @if($log->status === 'completado')
                                    <span class="badge bg-success text-white border border-dark px-2 py-1"><i class="fas fa-check-circle me-1"></i>COMPLETADO</span>
                                @elseif($log->status === 'pendiente')
                                    <span class="badge bg-warning text-dark border border-dark px-2 py-1"><i class="fas fa-clock me-1"></i>PENDIENTE</span>
                                @elseif($log->status === 'fallido')
                                    <span class="badge bg-danger text-white border border-dark px-2 py-1"><i class="fas fa-times-circle me-1"></i>FALLIDO</span>
                                @elseif($log->status === 'reembolsado')
                                    <span class="badge bg-secondary text-white border border-dark px-2 py-1"><i class="fas fa-undo me-1"></i>REEMBOLSADO</span>
                                @endif
                            </td>
                            <td class="text-end font-bangers fs-4 {{ $log->type == 'ingreso' ? 'text-income' : 'text-expense' }}">
                                {{ $log->type == 'ingreso' ? '+' : '-' }}{{ number_format($log->net_amount, 2, ',', '.') }} €
                            </td>
                            <td class="text-center">
                                @if($log->receipt_b64)
                                    <a href="{{ $log->receipt_b64 }}" download="Justificante_SBBL_{{ $log->id }}" class="btn btn-sm btn-light border border-dark" title="Descargar Ticket/Factura">
                                        <i class="fas fa-file-download"></i> Descargar
                                    </a>
                                @else
                                    <span class="text-secondary small"><i class="fas fa-times me-1"></i>No adjunto</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    @if($logs->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center p-4">No hay movimientos registrados en el Libro Mayor.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>



@endsection

@section('scripts')
<div class="modal fade" id="incomeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: #111; border: 2px solid #00ff88; border-radius: 0;">
            <div class="modal-header border-bottom border-secondary bg-black">
                <h5 class="modal-title font-bangers fs-3 text-white"><i class="fas fa-arrow-down me-2 text-income"></i> AÑADIR INGRESO MANUAL</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.treasury.income.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="text-white fw-bold mb-2">Concepto del Ingreso *</label>
                            <input type="text" name="description" class="form-control bg-dark text-white border-secondary" required placeholder="Ej: Pago en mano inscripción torneo">
                        </div>
                        <div class="col-md-4">
                            <label class="text-white fw-bold mb-2">Categoría *</label>
                            <select name="category" class="form-select bg-dark text-white border-secondary" required>
                                <option value="Inscripción Torneo">Inscripción Torneo</option>
                                <option value="Suscripción">Suscripción</option>
                                <option value="Donación">Donación</option>
                                <option value="Patrocinio">Patrocinio</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="text-white fw-bold mb-2">Importe Recibido (€) *</label>
                            <input type="number" step="0.01" min="0.01" name="gross_amount" class="form-control bg-dark text-white border-secondary text-income fw-bold" required placeholder="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="text-white fw-bold mb-2">Comisiones/Tasas (€)</label>
                            <input type="number" step="0.01" min="0" name="fee" class="form-control bg-dark text-white border-secondary" placeholder="0.00">
                            <small class="text-white-50">Solo si se pagó por pasarela</small>
                        </div>
                        <div class="col-md-4">
                            <label class="text-white fw-bold mb-2">Ref. Transacción / Bizum</label>
                            <input type="text" name="reference_id" class="form-control bg-dark text-white border-secondary" placeholder="Opcional">
                        </div>
                        <div class="col-md-6">
                            <label class="text-white fw-bold mb-2">Asociar a un Blader</label>
                            <select name="user_id" class="form-select select2-treasury">
                                <option value="">-- No asociar --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="text-white fw-bold mb-2">Asociar a un Torneo</label>
                            <select name="event_id" class="form-select select2-treasury">
                                <option value="">-- No asociar --</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}">{{ \Carbon\Carbon::parse($event->date)->format('d/m/y') }} - {{ Str::limit($event->name, 40) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mt-4">
                            <label class="text-white fw-bold mb-2"><i class="fas fa-camera text-info me-2"></i>Adjuntar Captura / Ticket (Opcional)</label>
                            <input type="file" name="receipt" class="form-control bg-dark text-white border-secondary" accept="image/jpeg,image/png,image/jpg,application/pdf">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary bg-black">
                    <button type="submit" class="btn btn-success fw-bold w-100 border-2 border-dark" style="padding: 12px; font-size: 1.2rem;">REGISTRAR INGRESO</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="expenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: #111; border: 2px solid #ff4444; border-radius: 0;">
            <div class="modal-header border-bottom border-secondary bg-black">
                <h5 class="modal-title font-bangers fs-3 text-white"><i class="fas fa-arrow-up me-2 text-expense"></i> AÑADIR GASTO ASOCIACIÓN</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.treasury.expense.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="text-white fw-bold mb-2">Concepto del Gasto *</label>
                            <input type="text" name="description" class="form-control bg-dark text-white border-secondary" required placeholder="Ej: Compra de 2 estadios Xtreme">
                        </div>
                        <div class="col-md-4">
                            <label class="text-white fw-bold mb-2">Categoría *</label>
                            <select name="category" class="form-select bg-dark text-white border-secondary" required>
                                <option value="Material de Juego">Material de Juego</option>
                                <option value="Trofeos y Premios">Trofeos y Premios</option>
                                <option value="Dietas Arbitraje">Dietas Arbitraje</option>
                                <option value="Servidores Web">Servidores Web / Hosting</option>
                                <option value="Alquiler Local">Alquiler de Local</option>
                                <option value="Marketing/Publicidad">Marketing / Publicidad</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="text-white fw-bold mb-2">Coste del Gasto (€) *</label>
                            <input type="number" step="0.01" min="0.01" name="gross_amount" class="form-control bg-dark text-white border-secondary text-expense fw-bold" required placeholder="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="text-white fw-bold mb-2">Comisiones Bancarias (€)</label>
                            <input type="number" step="0.01" min="0" name="fee" class="form-control bg-dark text-white border-secondary" placeholder="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="text-white fw-bold mb-2">Nº Factura / Ticket</label>
                            <input type="text" name="reference_id" class="form-control bg-dark text-white border-secondary" placeholder="Opcional">
                        </div>
                        <div class="col-md-6">
                            <label class="text-white fw-bold mb-2">Reembolsado a / Pagado por</label>
                            <select name="user_id" class="form-select select2-treasury">
                                <option value="">-- Pago directo de la cuenta SBBL --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="text-white fw-bold mb-2">Gasto del Torneo (Si aplica)</label>
                            <select name="event_id" class="form-select select2-treasury">
                                <option value="">-- Gasto general --</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}">{{ \Carbon\Carbon::parse($event->date)->format('d/m/y') }} - {{ Str::limit($event->name, 40) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mt-4">
                            <label class="text-white fw-bold mb-2"><i class="fas fa-file-invoice text-warning me-2"></i>Foto de Factura / Ticket (Muy Recomendado)</label>
                            <input type="file" name="receipt" class="form-control bg-dark text-white border-secondary" accept="image/jpeg,image/png,image/jpg,application/pdf">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary bg-black">
                    <button type="submit" class="btn btn-danger fw-bold w-100 border-2 border-dark" style="padding: 12px; font-size: 1.2rem;">REGISTRAR GASTO</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Inicializamos Select2 para ambos modales
        $('.select2-treasury').each(function() {
            $(this).select2({
                dropdownParent: $(this).closest('.modal'), // Asegura que funcione dentro de los modales
                width: '100%',
                dropdownCssClass: "bg-black border-secondary text-white"
            });
        });
    });
</script>
@endsection
