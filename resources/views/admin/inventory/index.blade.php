@extends('layouts.app')

@section('title', 'Arsenal SBBL')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .table-tactical { border: 3px solid #000; box-shadow: 6px 6px 0 #000; background: var(--sbbl-blue-2); margin-bottom: 0; }
    .table-tactical thead { background: #000; border-bottom: 4px solid var(--sbbl-gold); }
    .table-tactical th { font-family: 'Oswald', sans-serif; color: var(--shonen-cyan); font-size: 1.1rem; letter-spacing: 1px; text-transform: uppercase; border: none; padding: 15px; }
    .table-tactical td { background: transparent; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.1); vertical-align: middle; padding: 15px; }
    .table-tactical tbody tr:hover { background: rgba(0, 0, 0, 0.4); }
    .stat-card { border: 3px solid #000; padding: 20px; text-align: center; box-shadow: 5px 5px 0 #000; transition: 0.2s; }

    /* Colores de Estado */
    .status-impecable { background-color: #198754; color: #fff; }
    .status-operativo { background-color: #0dcaf0; color: #000; }
    .status-fatigado { background-color: #ffc107; color: #000; }
    .status-critico { background-color: #fd7e14; color: #fff; }
    .status-fuera_combate { background-color: #dc3545; color: #fff; }

    /* Estilo para dropdown Select2 oscuro */
    .select2-container--default .select2-selection--single {
        background-color: #212529 !important;
        border: 1px solid #6c757d !important;
        height: 38px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #fff !important;
        line-height: 36px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4 mb-5">

    <div class="text-center mb-5">
        <h2 class="font-bangers" style="font-size: 3.5rem; color: var(--sbbl-gold); text-shadow: 2px 2px 0px #000, 4px 4px 0px var(--shonen-red);">
            <i class="fas fa-boxes me-2 text-white" style="text-shadow:none;"></i> MATERIAL
        </h2>
        <p class="text-white fw-bold fs-5">Control de inventario, desgaste y custodios por provincia.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-shonen alert-shonen-success mb-4 text-center text-white"><div><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div></div>
    @endif

    <div class="row g-3 mb-5">
        <div class="col-md-4">
            <div class="stat-card" style="background: var(--sbbl-blue-2);">
                <h6 class="text-white-50 font-bangers fs-5 m-0">TOTAL MATERIAL</h6>
                <div class="font-bangers text-white" style="font-size: 3rem;">{{ $stats->total }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="background: rgba(13, 202, 240, 0.1); border-color: #0dcaf0;">
                <h6 class="text-white-50 font-bangers fs-5 m-0">ÓPTIMO ESTADO</h6>
                <div class="font-bangers text-info" style="font-size: 3rem;">{{ $stats->operativos }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card border-danger" style="background: rgba(255, 42, 42, 0.1);">
                <h6 class="text-white-50 font-bangers fs-5 m-0">REVISIÓN URGENTE (CRÍTICOS)</h6>
                <div class="font-bangers text-danger" style="font-size: 3rem;">{{ $stats->criticos }}</div>
            </div>
        </div>
    </div>

    <div class="command-panel p-4 mb-5" style="background: rgba(0,0,0,0.5); border: 2px solid var(--shonen-cyan);">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
            <h4 class="font-bangers text-white m-0" style="font-size: 2rem;">
                <i class="fas fa-list me-2" style="color: var(--shonen-cyan);"></i> REGISTRO NACIONAL
            </h4>
            <button class="btn-shonen btn-shonen-info text-center" data-bs-toggle="modal" data-bs-target="#createModal">
                <span><i class="fas fa-plus-circle me-2"></i> REGISTRAR MATERIAL</span>
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-tactical text-center align-middle">
                <thead>
                    <tr>
                        <th class="text-start">Artículo</th>
                        <th>Categoría</th>
                        <th>Provincia</th>
                        <th>Custodio Actual</th>
                        <th>Estado Vital</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td class="text-start">
                                <div class="fw-bold fs-5">{{ $item->name }}</div>
                                <div class="small">{{ $item->brand ?? 'Marca no especificada' }}</div>
                            </td>
                            <td><span class="badge bg-dark border border-secondary">{{ $item->category->name ?? 'N/A' }}</span></td>
                            <td class="fw-bold text-warning">{{ $item->province->name ?? 'N/A' }}</td>
                            <td>
                                @if($item->custodian)
                                    <span class="text-info fw-bold"><i class="fas fa-user-shield me-1"></i>{{ $item->custodian->name }}</span>
                                @else
                                    <span class="text-secondary"><i class="fas fa-box me-1"></i>En almacén</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge status-{{ $item->status }} border border-dark px-3 py-2 font-bangers fs-6" style="letter-spacing: 1px;">
                                    {{ str_replace('_', ' ', strtoupper($item->status)) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-light border border-dark" data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id }}" title="Actualizar Estado">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.inventory.destroy', $item->id) }}" class="d-inline" onsubmit="return confirm('¿Eliminar este artículo del inventario permanentemente?');">
                                    @method('DELETE') @csrf
                                    <button type="submit" class="btn btn-sm btn-danger border border-dark" title="Eliminar">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach

                    @if($items->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center text-white-50 p-4">El arsenal está vacío. Registra tu primer artículo.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')

@foreach ($items as $item)
<div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-start" style="background: #111; border: 2px solid var(--shonen-cyan); border-radius: 0;">
            <div class="modal-header border-bottom border-secondary bg-black">
                <h5 class="modal-title font-bangers fs-3 text-white">ACTUALIZAR: {{ $item->name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.inventory.update', $item->id) }}">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="text-white fw-bold mb-2">Custodio Físico (Quién lo tiene)</label>
                        <select name="user_id" class="form-select bg-dark text-white border-secondary select2-edit" data-modal-id="#editModal{{ $item->id }}" style="width: 100%;">
                            <option value="">-- En almacén genérico --</option>
                            @foreach($staffUsers as $user)
                                <option value="{{ $user->id }}" {{ $item->user_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="text-white fw-bold mb-2">Nivel de Desgaste</label>
                        <select name="status" class="form-select bg-dark text-white border-secondary">
                            <option value="impecable" {{ $item->status == 'impecable' ? 'selected' : '' }}>IMPECABLE (S)</option>
                            <option value="operativo" {{ $item->status == 'operativo' ? 'selected' : '' }}>OPERATIVO (A)</option>
                            <option value="fatigado" {{ $item->status == 'fatigado' ? 'selected' : '' }}>FATIGADO (B)</option>
                            <option value="critico" {{ $item->status == 'critico' ? 'selected' : '' }}>CRÍTICO (C)</option>
                            <option value="fuera_combate" {{ $item->status == 'fuera_combate' ? 'selected' : '' }}>FUERA DE COMBATE (F)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="text-white fw-bold mb-2">Anotaciones (Roturas, estado...)</label>
                        <textarea name="notes" class="form-control bg-dark text-white border-secondary" rows="3">{{ $item->notes }}</textarea>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary bg-black">
                    <button type="submit" class="btn btn-info fw-bold w-100 border-2 border-dark">GUARDAR CAMBIOS</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: #111; border: 2px solid var(--sbbl-gold); border-radius: 0;">
            <div class="modal-header border-bottom border-secondary bg-black">
                <h5 class="modal-title font-bangers fs-3 text-white"><i class="fas fa-plus me-2 text-warning"></i> NUEVO MATERIAL</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.inventory.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-white fw-bold mb-2">Nombre del Artículo *</label>
                            <input type="text" name="name" class="form-control bg-dark text-white border-secondary" required placeholder="Ej: Estadio Xtreme Stadium">
                        </div>
                        <div class="col-md-6">
                            <label class="text-white fw-bold mb-2">Marca / Modelo</label>
                            <input type="text" name="brand" class="form-control bg-dark text-white border-secondary" placeholder="Ej: Takara Tomy">
                        </div>
                        <div class="col-md-6">
                            <label class="text-white fw-bold mb-2">Categoría *</label>
                            <select name="category_id" class="form-select bg-dark text-white border-secondary" required>
                                <option value="" disabled selected>-- Seleccionar --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="text-white fw-bold mb-2">Provincia Base *</label>
                            <select name="province_id" class="form-select bg-dark text-white border-secondary select2-create" required>
                                <option value="" disabled selected>-- Seleccionar Provincia --</option>
                                @foreach($provinces as $prov)
                                    <option value="{{ $prov->id }}">{{ $prov->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="text-white fw-bold mb-2">Estado Inicial *</label>
                            <select name="status" class="form-select bg-dark text-white border-secondary" required>
                                <option value="impecable">IMPECABLE (S)</option>
                                <option value="operativo" selected>OPERATIVO (A)</option>
                                <option value="fatigado">FATIGADO (B)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="text-white fw-bold mb-2">Custodio Asignado</label>
                            <select name="user_id" class="form-select bg-dark text-white border-secondary select2-create">
                                <option value="">-- En almacén --</option>
                                @foreach($staffUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary bg-black">
                    <button type="submit" class="btn-shonen w-100 text-center" style="background: var(--sbbl-gold); color: #000; padding: 12px; font-size: 1.2rem;">
                        <span><i class="fas fa-save me-2"></i> REGISTRAR EN LA BASE DE DATOS</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Inicializar Select2 en el Modal de Creación
        $('.select2-create').select2({
            dropdownParent: $('#createModal'),
            width: '100%',
            dropdownCssClass: "bg-black border-warning text-white"
        });

        // Inicializar Select2 en TODOS los Modales de Edición
        $('.select2-edit').each(function() {
            var modalId = $(this).data('modal-id');
            $(this).select2({
                dropdownParent: $(modalId),
                width: '100%',
                dropdownCssClass: "bg-black border-info text-white"
            });
        });
    });
</script>
@endsection
