@extends('layouts.app')

@section('title', 'Colección Beyblade X')

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .card {
            background-color: #1e1e1e !important;
            border: 1px solid #333;
        }
        .card-header {
            background-color: #2c2c2c;
            color: #fff !important;
        }
        .card-body {
            background-color: #2c2c2c !important;
            color: #fff !important;
        }
        .table {
            color: #e0e0e0 !important;
        }
        .table thead {
            background-color: #2a2a2a !important;
            color: #fff !important;
        }
        .select2 {
            width: 100% !important;
        }
    </style>
@endsection

@section('content')
@php
    // Determinar la clase CSS según el nivel de suscripción
    $firstTrophyName = Auth::user()->profile->trophies->first()->name ?? '';
    $subscriptionClass = '';
    $isLevel2or3 = false;

    switch ($firstTrophyName) {
        case 'SUSCRIPCIÓN NIVEL 3':
            $subscriptionClass = 'suscripcion-nivel-3';
            $isLevel2or3 = true;
            break;
        case 'SUSCRIPCIÓN NIVEL 2':
            $subscriptionClass = 'suscripcion';
            $isLevel2or3 = true;
            break;
        case 'SUSCRIPCIÓN NIVEL 1':
            $subscriptionClass = 'suscripcion';
            break;
        default:
            $subscriptionClass = '';
            break;
    }
@endphp

@if(Auth::user() && ($isLevel2or3 || Auth::user()->is_admin ))
<div class="container-fluid" style="padding: 0px !important;">
    <h1 class="mb-5 text-center text-white">Gestión de piezas de Beyblade X</h1>

    <div class="row">
        {{-- Tabla de Blades --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Blades</span>
                    <button class="btn btn-outline-primary btn-sm open-modal"
                            data-type="Blade"
                            data-options='@json($blades)'>
                        <i class="fas fa-plus"></i> Añadir Blade
                    </button>
                </div>
                <div class="card-body p-0" style="background-color: #2c2c2c !important; color: white !important;">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Peso (gr)</th>
                                <th>Color</th>
                                <th>Cantidad</th>
                                <th>Comentarios</th>
                                <th class="text-end">Acciones</th> <!-- Alineación de la columna Acciones -->
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($myBlades as $blade)
                                <tr>
                                    <td>{{ $blade->partBlade->nombre_takara }}</td>
                                    <td>{{ $blade->weight }}</td>
                                    <td>{{ $blade->color }}</td>
                                    <td>{{ $blade->quantity }}</td>
                                    <td>{{ $blade->comment }}</td>
                                    <td class="text-end"> <!-- Alineación de las acciones -->
                                        <button type="button" class="btn btn-outline-warning btn-sm open-edit-modal"
                                            data-id="{{ $blade->id }}"
                                            data-type="Blade"
                                            data-part-id="{{ $blade->part_id }}"
                                            data-weight="{{ $blade->weight }}"
                                            data-color="{{ $blade->color }}"
                                            data-quantity="{{ $blade->quantity }}"
                                            data-comment="{{ $blade->comment }}">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <form method="POST" action="{{ route('collection.destroy', $blade->id) }}" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Tabla de Ratchets --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Ratchets</span>
                    <button class="btn btn-outline-primary btn-sm open-modal"
                            data-type="Ratchet"
                            data-options='@json($ratchets)'>
                        <i class="fas fa-plus"></i> Añadir Ratchet
                    </button>
                </div>
                <div class="card-body p-0" style="background-color: #2c2c2c !important; color: white !important;">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Peso (gr)</th>
                                <th>Color</th>
                                <th>Cantidad</th>
                                <th>Comentarios</th>
                                <th class="text-end">Acciones</th> <!-- Alineación de la columna Acciones -->
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($myRatchets as $ratchet)
                                <tr>
                                    <td>{{ $ratchet->partRatchet->nombre }}</td>
                                    <td>{{ $ratchet->weight }}</td>
                                    <td>{{ $ratchet->color }}</td>
                                    <td>{{ $ratchet->quantity }}</td>
                                    <td>{{ $ratchet->comment }}</td>
                                    <td class="text-end"> <!-- Alineación de las acciones -->
                                        <button type="button" class="btn btn-outline-warning btn-sm open-edit-modal"
                                            data-id="{{ $ratchet->id }}"
                                            data-type="Ratchet"
                                            data-part-id="{{ $ratchet->part_id }}"
                                            data-weight="{{ $ratchet->weight }}"
                                            data-color="{{ $ratchet->color }}"
                                            data-quantity="{{ $ratchet->quantity }}"
                                            data-comment="{{ $ratchet->comment }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" action="{{ route('collection.destroy', $ratchet->id) }}" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Tabla de Bits --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Bits</span>
                    <button class="btn btn-outline-primary btn-sm open-modal"
                            data-type="Bit"
                            data-options='@json($bits)'>
                        <i class="fas fa-plus"></i> Añadir Bit
                    </button>
                </div>
                <div class="card-body p-0" style="background-color: #2c2c2c !important; color: white !important;">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Peso (gr)</th>
                                <th>Color</th>
                                <th>Cantidad</th>
                                <th>Comentarios</th>
                                <th class="text-end">Acciones</th> <!-- Alineación de la columna Acciones -->
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($myBits as $bit)
                                <tr>
                                    <td>{{ $bit->partBit->nombre }}</td>
                                    <td>{{ $bit->weight }}</td>
                                    <td>{{ $bit->color }}</td>
                                    <td>{{ $bit->quantity }}</td>
                                    <td>{{ $bit->comment }}</td>
                                    <td class="text-end"> <!-- Alineación de las acciones -->
                                        <button type="button" class="btn btn-outline-warning btn-sm open-edit-modal"
                                            data-id="{{ $bit->id }}"
                                            data-type="Bit"
                                            data-part-id="{{ $bit->part_id }}"
                                            data-weight="{{ $bit->weight }}"
                                            data-color="{{ $bit->color }}"
                                            data-quantity="{{ $bit->quantity }}"
                                            data-comment="{{ $bit->comment }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" action="{{ route('collection.destroy', $bit->id) }}" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Tabla de Assist Blades --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Assist Blades</span>
                    <button class="btn btn-outline-primary btn-sm open-modal"
                            data-type="Assist Blade"
                            data-options='@json($assist_blades)'>
                        <i class="fas fa-plus"></i> Añadir Assist Blade
                    </button>
                </div>
                <div class="card-body p-0" style="background-color: #2c2c2c !important; color: white !important;">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Peso (gr)</th>
                                <th>Color</th>
                                <th>Cantidad</th>
                                <th>Comentarios</th>
                                <th class="text-end">Acciones</th> <!-- Alineación de la columna Acciones -->
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($myAssistBlades as $assistBlade)
                                <tr>
                                    <td>{{ $assistBlade->partAssistBlade->nombre }}</td>
                                    <td>{{ $assistBlade->weight }}</td>
                                    <td>{{ $assistBlade->color }}</td>
                                    <td>{{ $assistBlade->quantity }}</td>
                                    <td>{{ $assistBlade->comment }}</td>
                                    <td class="text-end"> <!-- Alineación de las acciones -->
                                        <button type="button" class="btn btn-outline-warning btn-sm open-edit-modal"
                                            data-id="{{ $assistBlade->id }}"
                                            data-type="Assist Blade"
                                            data-part-id="{{ $assistBlade->part_id }}"
                                            data-weight="{{ $assistBlade->weight }}"
                                            data-color="{{ $assistBlade->color }}"
                                            data-quantity="{{ $assistBlade->quantity }}"
                                            data-comment="{{ $assistBlade->comment }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" action="{{ route('collection.destroy', $assistBlade->id) }}" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- Modal reutilizable --}}
    <div class="modal fade" id="modalPart" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="partForm">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="type" id="partType">
                <div class="modal-content bg-dark text-white">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title" id="modalTitle">Añadir Pieza</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="partSelect" class="form-label">Seleccionar pieza</label>
                            <select class="form-control select2-search" id="partSelect" name="part_id"></select>
                        </div>
                        <div class="mb-3">
                            <label for="weight" class="form-label">Peso en gr (opcional)</label>
                            <input type="number" class="form-control" name="weight" id="weight" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label for="color" class="form-label">Color (opcional)</label>
                            <input type="text" class="form-control" name="color" id="color">
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Cantidad (opcional)</label>
                            <input type="number" class="form-control" name="quantity" id="quantity">
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Comentarios (opcional)</label>
                            <input type="text" class="form-control" name="comment" id="comment">
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-outline-success">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<!-- Carga jQuery antes de cualquier otro script que lo use -->

<script>
jQuery(document).ready(function() {
    // Inicializa select2 para los campos con la clase 'select2-search'
    jQuery('.select2-search').select2({
        dropdownParent: jQuery('#modalPart')
    });

    // Confirmación de borrado
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (confirm('¿Estás seguro de que quieres eliminar esta pieza?')) {
                form.submit();
            }
        });
    });


    jQuery(document).on('click', '.open-edit-modal', function() {
    const modal = jQuery('#modalPart');
    const type = jQuery(this).data('type');
    const id = jQuery(this).data('id');

    // Establecer valores
    jQuery('#modalTitle').text('Editar ' + type);
    jQuery('#partType').val(type);
    jQuery('#weight').val(jQuery(this).data('weight'));
    jQuery('#color').val(jQuery(this).data('color'));
    jQuery('#quantity').val(jQuery(this).data('quantity'));
    jQuery('#comment').val(jQuery(this).data('comment'));

    // Seleccionar la opción correcta en el select
    const partId = jQuery(this).data('part-id');
    const select = jQuery('#partSelect');
    select.val(partId).trigger('change');

    // Cambiar acción del formulario a UPDATE
    const form = jQuery('#partForm');
    form.attr('action', `/collection/${id}`);
    jQuery('#formMethod').val('PUT');

    modal.modal('show');
});

// Restaurar el modal cuando es para crear
jQuery('.open-modal').on('click', function() {
    const modal = jQuery('#modalPart');
    const type = jQuery(this).data('type');
    const options = jQuery(this).data('options');

    jQuery('#modalTitle').text('Añadir ' + type);
    jQuery('#partType').val(type);

    // Limpiar campos
    jQuery('#weight, #color, #quantity, #comment').val('');
    jQuery('#formMethod').val('POST');
    jQuery('#partForm').attr('action', '{{ route('collection.store') }}');

    // Cargar las opciones en el select
    const select = jQuery('#partSelect');
    select.empty();
    for (const item of options) {
        select.append(new Option(item.nombre || item.nombre_takara, item.id));
    }

    select.val(null).trigger('change');
    modal.modal('show');
});

});

</script>
@else
<script type="text/javascript">
    window.location = "/subscriptions";
</script>
@endif
@endsection
