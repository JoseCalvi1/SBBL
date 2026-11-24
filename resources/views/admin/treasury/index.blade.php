@extends('layouts.app')

@section('title', 'Tesorería Blindada SBBL')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .table-tactical { border: 3px solid #000; box-shadow: 6px 6px 0 #000; background: var(--sbbl-blue-2); margin-bottom: 0; }
    .table-tactical thead { background: #000; border-bottom: 4px solid var(--sbbl-gold); }
    .table-tactical th { font-family: 'Oswald', sans-serif; color: var(--shonen-cyan); font-size: 1rem; letter-spacing: 1px; text-transform: uppercase; border: none; padding: 14px; }
    .table-tactical td { background: transparent; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.1); vertical-align: middle; padding: 14px; }
    .table-tactical tbody tr:hover { background: rgba(0, 0, 0, 0.4); }
    .stat-card { border: 3px solid #000; padding: 20px; text-align: center; box-shadow: 5px 5px 0 #000; transition: 0.2s; height: 100%; }
    .text-income { color: #00ff88 !important; }
    .text-expense { color: #ff4444 !important; }
</style>
@endsection

@section('content')
<div class="container-fluid py-4 mb-5 px-lg-4">

    <div class="text-center mb-4">
        <h2 class="font-bangers" style="font-size: 3.5rem; color: var(--sbbl-gold); text-shadow: 2px 2px 0px #000, 4px 4px 0px var(--shonen-red);">
            <i class="fas fa-shield-alt me-2 text-white" style="text-shadow:none;"></i> LIBRO MAYOR DE TESORERÍA
        </h2>
        <p class="text-white-50 fw-bold fs-6 mb-0">
            Registro inmutable y auditado de cobros automáticos PayPal Business, rifas, eventos, tienda y gastos.
        </p>
    </div>

    @if (session('success'))
        <div class="alert alert-shonen alert-shonen-success mb-4 text-center">
            <div><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
        </div>
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

    @if ($pendingRefundsCount > 0)
        <div class="alert border-3 border-warning mb-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 p-3" style="background: rgba(234, 179, 8, 0.15); box-shadow: 5px 5px 0 #000;">
            <div class="text-white fw-bold">
                <i class="fas fa-undo-alt text-warning fs-4 me-2 align-middle"></i>
                Hay <strong class="text-warning fs-5">{{ $pendingRefundsCount }}</strong> baja(s) de eventos de pago pendientes de reembolso manual en PayPal Business.
            </div>
            <a href="{{ route('admin.treasury.index', ['status' => 'refund_requested']) }}" class="btn btn-warning fw-bold border-2 border-dark shrink-0">
                <i class="fas fa-filter me-1"></i> VER PENDIENTES DE REEMBOLSO
            </a>
        </div>
    @endif

    {{-- TARJETAS KPI DEL EJERCICIO ACTIVO --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card" style="background: rgba(0, 255, 136, 0.08); border-color: #00ff88;">
                <h6 class="text-white-50 font-bangers fs-5 m-0">INGRESOS NETOS (EJERCICIO ACTUAL)</h6>
                <div class="font-bangers text-income" style="font-size: 2.8rem;">{{ number_format($totalIngresosNetos, 2, ',', '.') }} €</div>
                <div class="small text-white-50 fw-bold">
                    Bruto: {{ number_format($totalIngresosBrutos, 2, ',', '.') }} € · Tasas PayPal: -{{ number_format($totalComisionesPayPal, 2, ',', '.') }} €
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="background: rgba(255, 68, 68, 0.08); border-color: #ff4444;">
                <h6 class="text-white-50 font-bangers fs-5 m-0">GASTOS TOTALES (EJERCICIO ACTUAL)</h6>
                <div class="font-bangers text-expense" style="font-size: 2.8rem;">{{ number_format($totalGastosNetos, 2, ',', '.') }} €</div>
                <div class="small text-white-50 fw-bold">Salidas verificadas con justificante</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="background: {{ $saldoActual >= 0 ? 'var(--sbbl-blue-2)' : 'rgba(255, 0, 0, 0.2)' }}; border-color: var(--sbbl-gold);">
                <h6 class="text-white-50 font-bangers fs-5 m-0">SALDO NETO DISPONIBLE</h6>
                <div class="font-bangers" style="font-size: 3.2rem; color: {{ $saldoActual >= 0 ? 'var(--sbbl-gold)' : '#ff4444' }};">
                    {{ number_format($saldoActual, 2, ',', '.') }} €
                </div>
                <div class="small text-white-50 fw-bold">
                    Histórico archivado en BD: {{ $archivedCount }} apuntes
                </div>
            </div>
        </div>
    </div>

    {{-- PANEL DE CONTROLES Y FILTROS --}}
    <div class="command-panel p-4 mb-5" style="background: rgba(0,0,0,0.6); border: 2px solid var(--shonen-cyan);">

        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4 gap-3">
            <h4 class="font-bangers text-white m-0" style="font-size: 2rem;">
                <i class="fas fa-list-alt me-2" style="color: var(--shonen-cyan);"></i>
                @if($period === 'archived')
                    MOVIMIENTOS ARCHIVADOS (HISTÓRICO PREVIO)
                @elseif($period === 'all')
                    TODOS LOS MOVIMIENTOS (ACTIVOS + ARCHIVADOS)
                @else
                    MOVIMIENTOS DEL EJERCICIO ACTUAL
                @endif
            </h4>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-success fw-bold border-2 border-dark" data-bs-toggle="modal" data-bs-target="#incomeModal">
                    <i class="fas fa-arrow-down me-1"></i> REGISTRAR INGRESO / SALDO
                </button>
                <button class="btn btn-danger fw-bold border-2 border-dark" data-bs-toggle="modal" data-bs-target="#expenseModal">
                    <i class="fas fa-arrow-up me-1"></i> REGISTRAR GASTO
                </button>
                <a href="{{ route('admin.treasury.export', request()->query()) }}" class="btn btn-info text-dark fw-bold border-2 border-dark">
                    <i class="fas fa-file-csv me-1"></i> EXPORTAR CSV
                </a>
                <button class="btn btn-outline-warning fw-bold" data-bs-toggle="modal" data-bs-target="#resetPeriodModal">
                    <i class="fas fa-archive me-1"></i> CORTE / RESETEAR CAJA
                </button>
            </div>
        </div>

        {{-- FILTROS --}}
        <form method="GET" action="{{ route('admin.treasury.index') }}" class="row g-2 mb-4 p-3 border border-secondary" style="background: #0b1120;">
            <div class="col-md-2">
                <label class="small text-white-50 fw-bold">Periodo</label>
                <select name="period" class="form-select form-select-sm bg-dark text-white border-secondary">
                    <option value="active" {{ $period === 'active' ? 'selected' : '' }}>Ejercicio Actual</option>
                    <option value="archived" {{ $period === 'archived' ? 'selected' : '' }}>Histórico Archivado ({{ $archivedCount }})</option>
                    <option value="all" {{ $period === 'all' ? 'selected' : '' }}>Todo el Histórico</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small text-white-50 fw-bold">Tipo</label>
                <select name="type" class="form-select form-select-sm bg-dark text-white border-secondary">
                    <option value="">Todos</option>
                    <option value="ingreso" {{ request('type') === 'ingreso' ? 'selected' : '' }}>Ingresos</option>
                    <option value="gasto" {{ request('type') === 'gasto' ? 'selected' : '' }}>Gastos</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small text-white-50 fw-bold">Origen</label>
                <select name="source" class="form-select form-select-sm bg-dark text-white border-secondary">
                    <option value="">Todos</option>
                    <option value="paypal_auto" {{ request('source') === 'paypal_auto' ? 'selected' : '' }}>PayPal Automático</option>
                    <option value="manual" {{ request('source') === 'manual' ? 'selected' : '' }}>Manual Admin</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small text-white-50 fw-bold">Categoría</label>
                <select name="category" class="form-select form-select-sm bg-dark text-white border-secondary">
                    <option value="">Todas</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="small text-white-50 fw-bold">Estado</label>
                <select name="status" class="form-select form-select-sm bg-dark text-white border-secondary">
                    <option value="">Todos</option>
                    <option value="completado" {{ request('status') === 'completado' ? 'selected' : '' }}>Completado</option>
                    <option value="refund_requested" {{ request('status') === 'refund_requested' ? 'selected' : '' }}>⚠️ Reembolso Pendiente</option>
                    <option value="reembolsado" {{ request('status') === 'reembolsado' ? 'selected' : '' }}>Reembolsado</option>
                    <option value="pendiente" {{ request('status') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-sm btn-warning fw-bold w-100 border border-dark">FILTRAR</button>
                <a href="{{ route('admin.treasury.index') }}" class="btn btn-sm btn-outline-secondary" title="Limpiar"><i class="fas fa-undo"></i></a>
            </div>
        </form>

        {{-- TABLA DE MOVIMIENTOS --}}
        <div class="table-responsive">
            <table class="table table-tactical align-middle">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Origen</th>
                        <th>Concepto / Trazabilidad</th>
                        <th class="text-center">Categoría</th>
                        <th>Vínculo</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end">Bruto / Comisión</th>
                        <th class="text-end">Importe Neto</th>
                        <th class="text-center">Acciones / Doc</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr style="background: {{ $log->refund_requested ? 'rgba(234, 179, 8, 0.12)' : ($log->type == 'ingreso' ? 'rgba(0, 255, 136, 0.03)' : 'rgba(255, 68, 68, 0.03)') }};">
                            <td class="small text-nowrap">
                                <div><i class="far fa-calendar-alt me-1"></i>{{ $log->created_at->format('d/m/Y H:i') }}</div>
                                @if($log->archived_at)
                                    <span class="badge bg-secondary mt-1" style="font-size: 0.7rem;">ARCHIVADO</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                @if($log->source === 'paypal_auto')
                                    <span class="badge bg-primary border border-light px-2 py-1">
                                        <i class="fab fa-paypal me-1"></i>AUTO VERIFICADO
                                    </span>
                                @else
                                    <span class="badge bg-dark border border-secondary px-2 py-1">
                                        <i class="fas fa-user-shield me-1 text-warning"></i>MANUAL
                                    </span>
                                    @if($log->creator)
                                        <div class="text-white-50 mt-1" style="font-size: 0.72rem;">por {{ $log->creator->name }}</div>
                                    @endif
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold">{{ $log->description }}</div>
                                <div class="small text-white-50" style="font-size: 0.78rem;">
                                    @if($log->paypal_capture_id)
                                        <span class="me-2"><i class="fas fa-fingerprint text-info me-1"></i>Captura: <code>{{ $log->paypal_capture_id }}</code></span>
                                    @elseif($log->reference_id)
                                        <span class="me-2">Ref: <code>{{ $log->reference_id }}</code></span>
                                    @endif
                                    @if($log->paypal_order_id)
                                        <span>Orden: <code>{{ $log->paypal_order_id }}</code></span>
                                    @endif
                                </div>
                                @if($log->notes)
                                    <div class="small text-warning mt-1" style="font-size: 0.78rem; white-space: pre-line;">{{ $log->notes }}</div>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-dark border border-secondary">{{ strtoupper($log->category) }}</span>
                            </td>
                            <td class="small">
                                @if($log->user)
                                    <div class="text-info"><i class="fas fa-user me-1"></i>{{ $log->user->name }}</div>
                                @endif
                                @if($log->event)
                                    <div class="text-warning"><i class="fas fa-trophy me-1"></i><a href="{{ route('events.show', $log->event->id) }}" class="text-warning text-decoration-none">{{ Str::limit($log->event->name, 24) }}</a></div>
                                @endif
                                @if($log->raffle)
                                    <div style="color: #c084fc;"><i class="fas fa-ticket-alt me-1"></i>{{ Str::limit($log->raffle->title, 24) }}</div>
                                @endif
                                @if($log->carrito)
                                    <div class="text-light"><i class="fas fa-shopping-bag me-1"></i><a href="{{ route('admin.pedidos.show', $log->carrito->id) }}" class="text-info">{{ $log->carrito->referencia }}</a></div>
                                @endif
                                @if(!$log->user && !$log->event && !$log->raffle && !$log->carrito)
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($log->refund_requested && $log->status === 'completado')
                                    <span class="badge bg-warning text-dark border border-dark px-2 py-1 d-block mb-1">
                                        <i class="fas fa-exclamation-circle me-1"></i>BAJA / REEMBOLSAR
                                    </span>
                                @endif
                                @if($log->status === 'completado')
                                    <span class="badge bg-success text-white border border-dark px-2 py-1"><i class="fas fa-check-circle me-1"></i>COMPLETADO</span>
                                @elseif($log->status === 'pendiente')
                                    <span class="badge bg-warning text-dark border border-dark px-2 py-1"><i class="fas fa-clock me-1"></i>PENDIENTE</span>
                                @elseif($log->status === 'fallido')
                                    <span class="badge bg-danger text-white border border-dark px-2 py-1"><i class="fas fa-times-circle me-1"></i>FALLIDO</span>
                                @elseif($log->status === 'reembolsado')
                                    <span class="badge bg-secondary text-white border border-light px-2 py-1"><i class="fas fa-undo me-1"></i>REEMBOLSADO</span>
                                @endif
                            </td>
                            <td class="text-end small text-nowrap">
                                <div class="text-white">{{ number_format($log->gross_amount, 2, ',', '.') }} €</div>
                                @if($log->fee > 0)
                                    <div class="text-danger" style="font-size: 0.75rem;">Fee: -{{ number_format($log->fee, 2, ',', '.') }} €</div>
                                @endif
                            </td>
                            <td class="text-end font-bangers fs-4 text-nowrap {{ $log->status === 'reembolsado' ? 'text-secondary text-decoration-line-through' : ($log->type == 'ingreso' ? 'text-income' : 'text-expense') }}">
                                {{ $log->type == 'ingreso' ? '+' : '-' }}{{ number_format($log->net_amount, 2, ',', '.') }} €
                            </td>
                            <td class="text-center text-nowrap">
                                <div class="d-flex justify-content-center gap-1">
                                    @if($log->receipt_b64)
                                        <a href="{{ $log->receipt_b64 }}" download="Justificante_SBBL_{{ $log->id }}" class="btn btn-sm btn-light border border-dark" title="Descargar Ticket/Factura">
                                            <i class="fas fa-file-download"></i>
                                        </a>
                                    @endif

                                    @if($log->type === 'ingreso' && $log->status === 'completado' && !$log->archived_at)
                                        <button type="button"
                                            class="btn btn-sm {{ $log->refund_requested ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary text-white' }}"
                                            title="Confirmar reembolso realizado en PayPal"
                                            onclick="openRefundModal({{ $log->id }}, {{ json_encode($log->description) }}, '{{ number_format($log->gross_amount, 2, ',', '.') }}')">
                                            <i class="fas fa-undo"></i> {{ $log->refund_requested ? 'Confirmar Reembolso' : '' }}
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center p-5 text-white-50 fw-bold">
                                <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                No hay movimientos registrados en este filtro. Si acabas de resetear la caja, puedes registrar el <strong>Saldo Inicial</strong> pulsando «Registrar Ingreso / Saldo».
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
{{-- MODAL INGRESO MANUAL / SALDO INICIAL --}}
<div class="modal fade" id="incomeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: #111; border: 2px solid #00ff88; border-radius: 0;">
            <div class="modal-header border-bottom border-secondary bg-black">
                <h5 class="modal-title font-bangers fs-3 text-white"><i class="fas fa-arrow-down me-2 text-income"></i> REGISTRAR INGRESO MANUAL / SALDO INICIAL</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.treasury.income.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="text-white fw-bold mb-2">Concepto del Ingreso *</label>
                            <input type="text" name="description" class="form-control bg-dark text-white border-secondary" required placeholder="Ej: Saldo actual en cuenta al iniciar ejercicio">
                        </div>
                        <div class="col-md-4">
                            <label class="text-white fw-bold mb-2">Categoría *</label>
                            <select name="category" class="form-select bg-dark text-white border-secondary" required>
                                <option value="Saldo Inicial">Saldo Inicial (Apertura)</option>
                                <option value="Inscripción Torneo">Inscripción Torneo (Efectivo/Bizum)</option>
                                <option value="Rifa">Rifa (Presencial)</option>
                                <option value="Tienda / Merch">Tienda / Merch (Presencial)</option>
                                <option value="Donación">Donación</option>
                                <option value="Patrocinio">Patrocinio</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="text-white fw-bold mb-2">Importe Bruto (€) *</label>
                            <input type="number" step="0.01" min="0.01" name="gross_amount" class="form-control bg-dark text-white border-secondary text-income fw-bold" required placeholder="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="text-white fw-bold mb-2">Comisión/Tasa (€)</label>
                            <input type="number" step="0.01" min="0" name="fee" class="form-control bg-dark text-white border-secondary" value="0.00">
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
                        <div class="col-12">
                            <label class="text-white fw-bold mb-2">Notas internas (Opcional)</label>
                            <input type="text" name="notes" class="form-control bg-dark text-white border-secondary" placeholder="Aclaraciones contables...">
                        </div>
                        <div class="col-12">
                            <label class="text-white fw-bold mb-2"><i class="fas fa-camera text-info me-2"></i>Adjuntar Captura / Justificante (Opcional)</label>
                            <input type="file" name="receipt" class="form-control bg-dark text-white border-secondary" accept="image/jpeg,image/png,image/jpg,application/pdf">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary bg-black">
                    <button type="submit" class="btn btn-success fw-bold w-100 border-2 border-dark" style="padding: 12px; font-size: 1.2rem;">REGISTRAR INGRESO AUDITADO</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL GASTO --}}
<div class="modal fade" id="expenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: #111; border: 2px solid #ff4444; border-radius: 0;">
            <div class="modal-header border-bottom border-secondary bg-black">
                <h5 class="modal-title font-bangers fs-3 text-white"><i class="fas fa-arrow-up me-2 text-expense"></i> REGISTRAR GASTO ASOCIACIÓN</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.treasury.expense.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="text-white fw-bold mb-2">Concepto del Gasto *</label>
                            <input type="text" name="description" class="form-control bg-dark text-white border-secondary" required placeholder="Ej: Compra de premios o estadios">
                        </div>
                        <div class="col-md-4">
                            <label class="text-white fw-bold mb-2">Categoría *</label>
                            <select name="category" class="form-select bg-dark text-white border-secondary" required>
                                <option value="Material de Juego">Material de Juego</option>
                                <option value="Trofeos y Premios">Trofeos y Premios</option>
                                <option value="Producción Merch">Producción Merch</option>
                                <option value="Envíos y Logística">Envíos y Logística</option>
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
                            <input type="number" step="0.01" min="0" name="fee" class="form-control bg-dark text-white border-secondary" value="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="text-white fw-bold mb-2">Nº Factura / Ticket</label>
                            <input type="text" name="reference_id" class="form-control bg-dark text-white border-secondary" placeholder="Opcional">
                        </div>
                        <div class="col-md-6">
                            <label class="text-white fw-bold mb-2">Reembolsado a / Pagado por</label>
                            <select name="user_id" class="form-select select2-treasury">
                                <option value="">-- Pago directo cuenta SBBL --</option>
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
                        <div class="col-12">
                            <label class="text-white fw-bold mb-2">Notas internas (Opcional)</label>
                            <input type="text" name="notes" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-12">
                            <label class="text-white fw-bold mb-2"><i class="fas fa-file-invoice text-warning me-2"></i>Foto de Factura / Ticket</label>
                            <input type="file" name="receipt" class="form-control bg-dark text-white border-secondary" accept="image/jpeg,image/png,image/jpg,application/pdf">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary bg-black">
                    <button type="submit" class="btn btn-danger fw-bold w-100 border-2 border-dark" style="padding: 12px; font-size: 1.2rem;">REGISTRAR GASTO AUDITADO</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL CONFIRMAR REEMBOLSO MANUAL --}}
<div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #111; border: 2px solid var(--sbbl-gold); border-radius: 0;">
            <div class="modal-header border-bottom border-secondary bg-black">
                <h5 class="modal-title font-bangers fs-3 text-warning"><i class="fas fa-undo me-2"></i> CONFIRMAR REEMBOLSO REALIZADO</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="refundModalForm" action="">
                @csrf
                @method('PUT')
                <div class="modal-body p-4 text-white">
                    <p class="mb-2">Vas a marcar como <strong>REEMBOLSADO</strong> el siguiente cobro (tras haber devuelto el importe desde PayPal Business):</p>
                    <div class="p-3 bg-black border border-secondary mb-3">
                        <div class="fw-bold text-info" id="refundModalDesc"></div>
                        <div class="small text-white-50">Importe bruto original: <strong id="refundModalAmount" class="text-white"></strong> €</div>
                    </div>
                    <label class="fw-bold small mb-2">Nota de auditoría (Opcional)</label>
                    <input type="text" name="refund_note" class="form-control bg-dark text-white border-secondary" placeholder="Ej: Reembolsado 100% en PayPal Business">
                </div>
                <div class="modal-footer border-top border-secondary bg-black">
                    <button type="button" class="btn btn-dark border-secondary text-white" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold border-2 border-dark">SÍ, MARCAR COMO REEMBOLSADO</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL CORTE / RESETEO DE CAJA SIN BORRAR BD --}}
<div class="modal fade" id="resetPeriodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #111; border: 2px solid #eab308; border-radius: 0;">
            <div class="modal-header border-bottom border-secondary bg-black">
                <h5 class="modal-title font-bangers fs-3 text-warning"><i class="fas fa-archive me-2"></i> CORTE CONTABLE / RESETEAR CAJA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.treasury.reset') }}">
                @csrf
                <div class="modal-body p-4 text-white">
                    <div class="alert bg-dark border border-warning text-white small">
                        <i class="fas fa-shield-alt text-warning me-1"></i>
                        <strong>Ningún dato se borra de la base de datos:</strong> todos los movimientos actuales pasarán al <em>Histórico Archivado</em> y el saldo del nuevo ejercicio empezará desde 0,00 € (o desde el saldo inicial que indiques aquí debajo).
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Saldo Inicial del nuevo ejercicio (€) (Opcional)</label>
                        <input type="number" step="0.01" min="0" name="initial_balance" class="form-control bg-dark text-white border-secondary" placeholder="0.00 (También puedes añadirlo después a mano)">
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Nota de apertura (Opcional)</label>
                        <input type="text" name="reset_note" class="form-control bg-dark text-white border-secondary" placeholder="Ej: Saldo real en PayPal Business + Banco a fecha de hoy">
                    </div>
                    <div>
                        <label class="fw-bold text-warning mb-1">Escribe <code>RESETEAR</code> para confirmar *</label>
                        <input type="text" name="confirm_word" class="form-control bg-black text-white border-warning fw-bold text-uppercase" required autocomplete="off" placeholder="RESETEAR">
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary bg-black">
                    <button type="button" class="btn btn-dark border-secondary text-white" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold border-2 border-dark">EJECUTAR CORTE DE CAJA</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-treasury').each(function() {
            $(this).select2({
                dropdownParent: $(this).closest('.modal'),
                width: '100%',
                dropdownCssClass: "bg-black border-secondary text-white"
            });
        });
    });

    function openRefundModal(logId, description, grossFormatted) {
        document.getElementById('refundModalForm').action = `/admin/treasury/${logId}/refund`;
        document.getElementById('refundModalDesc').textContent = description;
        document.getElementById('refundModalAmount').textContent = grossFormatted;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('refundModal')).show();
    }
</script>
@endsection
