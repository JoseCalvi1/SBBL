@extends('layouts.app')

@section('title', 'Administración de Equipos')

@section('styles')
<style>
    /* Estilos del Panel de Admin */
    .admin-panel { background-color: var(--sbbl-blue-2); padding: 25px; border: 3px solid #000; box-shadow: 6px 6px 0 rgba(0,0,0,0.8); }
    .table-dark-shonen { border-collapse: separate; border-spacing: 0 8px; }
    .table-dark-shonen tbody tr { background: #1a1a1a; box-shadow: 3px 3px 0 #000; transition: 0.2s; }
    .table-dark-shonen tbody tr:hover { transform: translate(-2px, -2px); box-shadow: 5px 5px 0 var(--sbbl-gold); }
    .table-dark-shonen td, .table-dark-shonen th { border: none; padding: 15px; vertical-align: middle; color: #fff; }
    .table-dark-shonen thead th { font-family: 'Oswald', cursive; font-size: 1.1rem; color: var(--sbbl-gold); text-transform: uppercase; background: #000; letter-spacing: 1px;}

    /* Filtros */
    .filtros-admin select { background: #111; color: #fff; border: 2px solid #333; font-weight: bold; border-radius: 0; }
    .filtros-admin select:focus { border-color: var(--shonen-cyan); box-shadow: none; }
</style>
@endsection

@section('content')
<div class="container py-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h2 class="font-Oswald text-white m-0" style="font-size: 2.5rem; text-shadow: 2px 2px 0 #000;">
            <i class="fas fa-shield-alt text-warning me-2"></i> ADMINISTRACIÓN DE EQUIPOS
        </h2>
        <a href="{{ route('equipos.create') }}" class="btn-shonen btn-shonen-warning px-4 py-2">
            <span><i class="fas fa-plus me-2"></i> NUEVO EQUIPO</span>
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm fw-bold"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
    @endif

    <div class="admin-panel mb-4">
        {{-- BARRA DE FILTROS --}}
        <form method="GET" action="{{ route('equipos.indexAdmin') }}" class="filtros-admin row g-3 mb-4 border-bottom border-dark pb-4">
            <div class="col-md-5">
                <label class="text-white font-Oswald mb-1"><i class="fas fa-filter me-1 text-info"></i> FILTRAR POR ESTADO:</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="all" {{ $statusFilter == 'all' ? 'selected' : '' }}>Todos los estados</option>
                    <option value="pending" {{ $statusFilter == 'pending' ? 'selected' : '' }}>⚠️ Pendientes de Validación</option>
                    <option value="updated" {{ $statusFilter == 'updated' ? 'selected' : '' }}>🔄 Cambios Actualizados</option>
                    <option value="accepted" {{ $statusFilter == 'accepted' ? 'selected' : '' }}>✅ Aceptados</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="text-white font-Oswald mb-1"><i class="fas fa-map-marker-alt me-1 text-danger"></i> FILTRAR POR REGIÓN:</label>
                <select name="region" class="form-select" onchange="this.form.submit()">
                    <option value="all" {{ $regionFilter == 'all' ? 'selected' : '' }}>Todas las Regiones</option>
                    @foreach ($regiones as $region)
                        <option value="{{ $region->id }}" {{ $regionFilter == $region->id ? 'selected' : '' }}>{{ mb_strtoupper($region->name) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <a href="{{ route('equipos.indexAdmin') }}" class="btn btn-outline-secondary w-100 fw-bold border-2 rounded-0">LIMPIAR</a>
            </div>
        </form>

        {{-- TABLA DE EQUIPOS --}}
        <div class="table-responsive">
            <table class="table table-dark-shonen mb-0">
                <thead>
                    <tr>
                        <th width="20%">Nombre</th>
                        <th width="30%">Descripción</th>
                        <th width="15%">Estado</th>
                        <th width="15%">Región</th>
                        <th width="20%" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($equipos as $equipo)
                        <tr>
                            <td class="fw-bold fs-5 font-Oswald">{{ $equipo->name }}</td>
                            <td class="small text-muted">{{ Str::limit($equipo->description, 60) }}</td>
                            <td>
                                @switch($equipo->status)
                                    @case('pending')
                                        <span class="badge bg-warning text-dark border border-dark"><i class="fas fa-clock me-1"></i> PENDIENTE</span>
                                        @break
                                    @case('updated')
                                        <span class="badge bg-info text-dark border border-dark"><i class="fas fa-sync me-1"></i> REVISAR CAMBIO</span>
                                        @break
                                    @case('accepted')
                                        <span class="badge bg-success border border-dark"><i class="fas fa-check me-1"></i> ACEPTADO</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ mb_strtoupper($equipo->status) }}</span>
                                @endswitch
                            </td>
                            <td class="small text-uppercase fw-bold text-info">
                                {{ optional(optional(optional($equipo->captain)->profile)->region)->name ?? 'GLOBAL' }}
                            </td>
                            <td class="text-end">
                                <div class="d-flex flex-wrap gap-1 justify-content-end">
                                    <a href="{{ route('equipos.show', $equipo) }}" class="btn btn-dark btn-sm border-secondary" title="Ver Equipo"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('equipos.edit', $equipo) }}" class="btn btn-dark btn-sm border-secondary" title="Editar"><i class="fas fa-edit"></i></a>

                                    <form action="{{ route('equipos.destroy', $equipo) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas ELIMINAR este equipo permanentemente?')" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                                    </form>

                                    @if ($equipo->status == "pending")
                                        <form action="{{ route('equipos.acceptInvitation', $equipo) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm fw-bold">ACEPTAR</button>
                                        </form>
                                    @endif
                                    @if ($equipo->status == "updated")
                                        <form action="{{ route('equipos.acceptInvitation', $equipo) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-info btn-sm fw-bold text-dark">VALIDAR</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted fw-bold">
                                No se encontraron equipos con los filtros actuales.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="d-flex justify-content-center mt-4">
            {{ $equipos->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection
