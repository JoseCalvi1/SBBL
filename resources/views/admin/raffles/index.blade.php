@extends('layouts.app')

@section('title', 'Gestión de Rifas SBBL')

@section('styles')
<style>
    .raffle-admin-card { background: var(--sbbl-blue-2); border: 3px solid #000; box-shadow: 6px 6px 0 #000; }
    .table-tactical { border: 3px solid #000; background: var(--sbbl-blue-2); }
    .table-tactical thead { background: #000; border-bottom: 3px solid var(--sbbl-gold); }
    .table-tactical th { font-family: 'Oswald', sans-serif; color: var(--shonen-cyan); text-transform: uppercase; border: none; padding: 12px; }
    .table-tactical td { color: #fff; border-bottom: 1px solid rgba(255,255,255,0.1); vertical-align: middle; padding: 12px; }
</style>
@endsection

@section('content')
<div class="container py-4 mb-5">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="font-bangers m-0" style="font-size: 3rem; color: var(--sbbl-gold); text-shadow: 2px 2px 0 #000, 4px 4px 0 var(--shonen-red);">
                <i class="fas fa-ticket-alt me-2 text-white" style="text-shadow:none;"></i> GESTOR DE RIFAS Y PAPELETAS
            </h2>
            <p class="text-white-50 fw-bold mb-0">Las rifas funcionan de una en una: al activar una rifa, cualquier otra pasa a pausada automáticamente.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('tickets.buy') }}" target="_blank" class="btn btn-outline-info fw-bold">
                <i class="fas fa-external-link-alt me-1"></i> Vista Pública
            </a>
            <button class="btn btn-success fw-bold border-2 border-dark" data-bs-toggle="modal" data-bs-target="#createRaffleModal">
                <i class="fas fa-plus me-1"></i> NUEVA RIFA
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-shonen alert-shonen-success mb-4 text-center"><div><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div></div>
    @endif
    @if ($errors->any())
        <div class="alert alert-shonen alert-shonen-danger mb-4 text-center">
            <ul class="mb-0 list-unstyled">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-tactical align-middle">
            <thead>
                <tr>
                    <th>Rifa</th>
                    <th class="text-center">Prefijo</th>
                    <th class="text-center">Precio</th>
                    <th class="text-center">Vendidas</th>
                    <th class="text-center">Máx / Usuario</th>
                    <th class="text-center">Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($raffles as $r)
                    <tr style="{{ $r->is_active ? 'background: rgba(0, 255, 136, 0.08);' : '' }}">
                        <td>
                            <div class="fw-bold fs-5">{{ $r->title }}</div>
                            @if($r->ends_at)
                                <div class="small text-white-50"><i class="far fa-clock me-1"></i>Cierre: {{ $r->ends_at->format('d/m/Y H:i') }}</div>
                            @endif
                            @if($r->drawn_at)
                                <div class="small text-warning fw-bold"><i class="fas fa-trophy me-1"></i>Sorteada el {{ $r->drawn_at->format('d/m/Y H:i') }}</div>
                            @endif
                        </td>
                        <td class="text-center"><code>{{ $r->ticket_prefix }}</code></td>
                        <td class="text-center fw-bold" style="color: var(--sbbl-gold);">{{ number_format($r->ticket_price, 2, ',', '.') }} €</td>
                        <td class="text-center fw-bold">
                            {{ $r->tickets_count }} / {{ $r->max_tickets ?? '∞' }}
                        </td>
                        <td class="text-center">{{ $r->max_per_user }}</td>
                        <td class="text-center">
                            @if($r->is_active)
                                <span class="badge bg-success border border-dark px-2 py-1">🟢 EN VENTA</span>
                            @elseif($r->drawn_at)
                                <span class="badge bg-warning text-dark border border-dark px-2 py-1">🏆 SORTEADA</span>
                            @else
                                <span class="badge bg-secondary border border-dark px-2 py-1">PAUSADA</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <form action="{{ route('admin.raffles.toggle', $r->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-sm {{ $r->is_active ? 'btn-outline-warning' : 'btn-success' }} fw-bold">
                                    <i class="fas {{ $r->is_active ? 'fa-pause' : 'fa-play' }} me-1"></i>{{ $r->is_active ? 'Pausar' : 'Activar' }}
                                </button>
                            </form>
                            <a href="{{ route('admin.raffles.tickets', $r->id) }}" class="btn btn-sm btn-info text-dark fw-bold" title="Ver papeletas">
                                <i class="fas fa-list me-1"></i> Papeletas
                            </a>
                            <a href="{{ route('tickets.sorteo', ['raffle_id' => $r->id]) }}" class="btn btn-sm btn-warning text-dark fw-bold" title="Ir al Sorteo">
                                <i class="fas fa-dice me-1"></i> Sorteo
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-light"
                                onclick='openEditRaffleModal(@json($r))'>
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center p-5 text-white-50 fw-bold">
                            No hay rifas creadas todavía. Pulsa «Nueva Rifa» para lanzar la primera campaña.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
{{-- MODAL CREAR RIFA --}}
<div class="modal fade" id="createRaffleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #111; border: 2px solid var(--sbbl-gold); border-radius: 0;">
            <div class="modal-header border-bottom border-secondary bg-black">
                <h5 class="modal-title font-Oswald fs-3 text-warning"><i class="fas fa-ticket-alt me-2"></i> CREAR NUEVA RIFA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.raffles.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4 text-white">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="fw-bold mb-1">Título de la Rifa *</label>
                            <input type="text" name="title" class="form-control bg-dark text-white border-secondary" required placeholder="Ej: Gran Rifa Especial SBBL">
                        </div>
                        <div class="col-md-4">
                            <label class="fw-bold mb-1">Prefijo Papeleta *</label>
                            <input type="text" name="ticket_prefix" class="form-control bg-dark text-white border-secondary text-uppercase" required maxlength="16" value="RIFA{{ now()->format('y') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold mb-1">Precio Papeleta (€) *</label>
                            <input type="number" step="0.01" min="0.50" name="ticket_price" class="form-control bg-dark text-white border-secondary fw-bold text-warning" required placeholder="2.00">
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold mb-1">Aforo Total (Opcional)</label>
                            <input type="number" min="1" name="max_tickets" class="form-control bg-dark text-white border-secondary" placeholder="Vacío = sin límite">
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold mb-1">Máx. por Usuario *</label>
                            <input type="number" min="1" max="500" name="max_per_user" class="form-control bg-dark text-white border-secondary" required value="10">
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold mb-1">Nº Premiados *</label>
                            <input type="number" min="1" max="20" name="winners_count" class="form-control bg-dark text-white border-secondary" required value="3">
                        </div>
                        <div class="col-12">
                            <label class="fw-bold mb-1">Descripción pública</label>
                            <textarea name="description" rows="2" class="form-control bg-dark text-white border-secondary" placeholder="Explica el motivo de la rifa y cómo se realizará el sorteo en directo..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="fw-bold mb-1">Lista de Premios (1º, 2º, 3º...)</label>
                            <textarea name="prizes_info" rows="3" class="form-control bg-dark text-white border-secondary" placeholder="🥇 1º Premio: Estadio Xtreme + Set Beyblade X&#10;🥈 2º Premio: Starter Pack&#10;🥉 3º Premio: Merch SBBL"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold mb-1">Fecha límite de compra (Opcional)</label>
                            <input type="datetime-local" name="ends_at" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold mb-1">Imagen / Cartel (Opcional)</label>
                            <input type="file" name="banner" class="form-control bg-dark text-white border-secondary" accept="image/*">
                        </div>
                        <div class="col-12 mt-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="createActiveSwitch" checked>
                                <label class="form-check-label text-success fw-bold" for="createActiveSwitch">
                                    Activar inmediatamente en la web (desactivará cualquier otra rifa activa)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary bg-black">
                    <button type="button" class="btn btn-dark border-secondary text-white" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold px-4">CREAR RIFA</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDITAR RIFA --}}
<div class="modal fade" id="editRaffleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #111; border: 2px solid var(--shonen-cyan); border-radius: 0;">
            <div class="modal-header border-bottom border-secondary bg-black">
                <h5 class="modal-title font-Oswald fs-3 text-info"><i class="fas fa-edit me-2"></i> EDITAR RIFA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editRaffleForm" action="" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4 text-white">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="fw-bold mb-1">Título de la Rifa *</label>
                            <input type="text" name="title" id="edit_title" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-bold mb-1">Prefijo Papeleta *</label>
                            <input type="text" name="ticket_prefix" id="edit_prefix" class="form-control bg-dark text-white border-secondary text-uppercase" required maxlength="16">
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold mb-1">Precio Papeleta (€) *</label>
                            <input type="number" step="0.01" min="0.50" name="ticket_price" id="edit_price" class="form-control bg-dark text-white border-secondary fw-bold text-warning" required>
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold mb-1">Aforo Total</label>
                            <input type="number" min="1" name="max_tickets" id="edit_max_tickets" class="form-control bg-dark text-white border-secondary" placeholder="Vacío = sin límite">
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold mb-1">Máx. por Usuario *</label>
                            <input type="number" min="1" max="500" name="max_per_user" id="edit_max_per_user" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold mb-1">Nº Premiados *</label>
                            <input type="number" min="1" max="20" name="winners_count" id="edit_winners_count" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        <div class="col-12">
                            <label class="fw-bold mb-1">Descripción pública</label>
                            <textarea name="description" id="edit_description" rows="2" class="form-control bg-dark text-white border-secondary"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="fw-bold mb-1">Lista de Premios</label>
                            <textarea name="prizes_info" id="edit_prizes_info" rows="3" class="form-control bg-dark text-white border-secondary"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold mb-1">Fecha límite de compra</label>
                            <input type="datetime-local" name="ends_at" id="edit_ends_at" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold mb-1">Reemplazar Cartel (Opcional)</label>
                            <input type="file" name="banner" class="form-control bg-dark text-white border-secondary" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary bg-black">
                    <button type="button" class="btn btn-dark border-secondary text-white" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info text-dark fw-bold px-4">GUARDAR CAMBIOS</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditRaffleModal(r) {
        document.getElementById('editRaffleForm').action = `/admin/raffles/${r.id}`;
        document.getElementById('edit_title').value = r.title || '';
        document.getElementById('edit_prefix').value = r.ticket_prefix || '';
        document.getElementById('edit_price').value = r.ticket_price || '';
        document.getElementById('edit_max_tickets').value = r.max_tickets || '';
        document.getElementById('edit_max_per_user').value = r.max_per_user || 10;
        document.getElementById('edit_winners_count').value = r.winners_count || 3;
        document.getElementById('edit_description').value = r.description || '';
        document.getElementById('edit_prizes_info').value = r.prizes_info || '';
        document.getElementById('edit_ends_at').value = r.ends_at ? r.ends_at.substring(0, 16) : '';
        bootstrap.Modal.getOrCreateInstance(document.getElementById('editRaffleModal')).show();
    }
</script>
@endsection
