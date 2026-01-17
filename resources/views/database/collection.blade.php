@extends('layouts.app')

@section('title', 'Colección Beyblade X')

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Estilos optimizados y consistentes para dark mode */
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
        /* Estilos Select2 para dark mode */
        .select2-container {
            width: 100% !important;
        }
        .select2-container--default .select2-selection--single {
            background-color: #2c2c2c !important;
            border: 1px solid #555;
            color: #fff;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
             color: #fff;
        }
        .select2-dropdown {
             background-color: #1e1e1e !important;
             border: 1px solid #555;
        }
        .select2-results__option {
             color: #fff;
        }
        .select2-results__option--highlighted {
             background-color: #007bff !important;
             color: #fff;
        }
        .form-control.bg-dark { /* Ajuste de inputs en el modal */
            border: 1px solid #555;
        }
    </style>
@endsection

@section('content')
@php
    // Lógica de acceso
    $firstTrophyName = Auth::user()->profile->trophies->first()->name ?? '';
    $isLevel2or3 = in_array($firstTrophyName, ['SUSCRIPCIÓN NIVEL 3', 'SUSCRIPCIÓN NIVEL 2']);

    // Definición de las secciones de la colección
    $sections = [
        [
            'title' => 'Blades',
            'data' => $myBlades,
            'type' => 'Blade',
            'options' => $blades,
            'partKey' => 'partBlade',
            'nameField' => 'nombre_takara',
        ],
        [
            'title' => 'Ratchets',
            'data' => $myRatchets,
            'type' => 'Ratchet',
            'options' => $ratchets,
            'partKey' => 'partRatchet',
            'nameField' => 'nombre',
        ],
        [
            'title' => 'Bits',
            'data' => $myBits,
            'type' => 'Bit',
            'options' => $bits,
            'partKey' => 'partBit',
            'nameField' => 'nombre',
        ],
        [
            'title' => 'Assist Blades',
            'data' => $myAssistBlades,
            'type' => 'Assist Blade',
            'options' => $assist_blades,
            'partKey' => 'partAssistBlade',
            'nameField' => 'nombre',
        ],
    ];
@endphp
<div class="container-fluid" style="padding: 0px !important;">
    <h1 class="mb-5 text-center text-white">Gestión de piezas de Beyblade X</h1>

    <div class="row">
        @foreach (array_chunk($sections, 2) as $rowSections)
            @foreach ($rowSections as $section)
                {{-- Contenedor de la tabla, ahora dentro del bucle --}}
                <div class="col-md-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>{{ $section['title'] }}</span>
                            <button class="btn btn-outline-primary btn-sm open-modal"
                                    data-type="{{ $section['type'] }}"
                                    data-name-field="{{ $section['nameField'] }}"
                                    data-options='@json($section['options'])'>
                                <i class="fas fa-plus"></i> Añadir {{ $section['type'] }}
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
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($section['data'] as $item)
                                        <tr>
                                            {{-- Acceso al nombre de la pieza de forma dinámica --}}
                                            <td>{{ $item->{$section['partKey']}->{$section['nameField']} ?? $item->{$section['partKey']}->nombre ?? 'N/A' }}</td>
                                            <td>{{ $item->weight }}</td>
                                            <td>{{ $item->color }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ $item->comment }}</td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-outline-warning btn-sm open-edit-modal"
                                                    data-id="{{ $item->id }}"
                                                    data-type="{{ $section['type'] }}"
                                                    data-part-id="{{ $item->part_id }}"
                                                    data-weight="{{ $item->weight }}"
                                                    data-color="{{ $item->color }}"
                                                    data-quantity="{{ $item->quantity }}"
                                                    data-comment="{{ $item->comment }}"
                                                    data-name-field="{{ $section['nameField'] }}"> {{-- Añadido para edición --}}
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" action="{{ route('collection.destroy', $item->id) }}" class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No hay {{ Str::plural($section['type']) }} en tu colección.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        @endforeach
    </div>

    {{-- Modal reutilizable (Se mantiene sin cambios mayores, solo ajustes de estilo de input) --}}
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
                            <input type="number" class="form-control bg-dark text-white border-secondary" name="weight" id="weight" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label for="color" class="form-label">Color (opcional)</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary" name="color" id="color">
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Cantidad (opcional)</label>
                            <input type="number" class="form-control bg-dark text-white border-secondary" name="quantity" id="quantity">
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Comentarios (opcional)</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary" name="comment" id="comment">
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    jQuery(document).ready(function() {

        // Inicializa select2 solo una vez
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

        // Función para cargar opciones en Select2
        function loadSelectOptions(selectElement, options, nameField, selectedId = null) {
            selectElement.empty();
            selectElement.append(new Option('Selecciona una pieza', '', true, true)); // Placeholder
            for (const item of options) {
                const name = item[nameField] || item.nombre || item.nombre_takara || `Pieza #${item.id}`;
                const newOption = new Option(name, item.id, false, false);
                selectElement.append(newOption);
            }
            if (selectedId) {
                 // Si es edición, Select2 ya debe contener la opción, solo la seleccionamos
                selectElement.val(selectedId).trigger('change');
            } else {
                selectElement.val(null).trigger('change');
            }
        }

        // Lógica para abrir el modal de CREACIÓN (Añadir)
        jQuery('.open-modal').on('click', function() {
            const modal = jQuery('#modalPart');
            const type = jQuery(this).data('type');
            const options = jQuery(this).data('options');
            const nameField = jQuery(this).data('name-field');
            const select = jQuery('#partSelect');

            jQuery('#modalTitle').text('Añadir ' + type);
            jQuery('#partType').val(type);

            // Limpiar campos
            jQuery('#weight, #color, #quantity, #comment').val('');

            // Restaurar a modo de creación
            jQuery('#formMethod').val('POST');
            jQuery('#partForm').attr('action', '{{ route('collection.store') }}');

            // Cargar las opciones
            loadSelectOptions(select, options, nameField);

            modal.modal('show');
        });

        // Lógica para abrir el modal de EDICIÓN
        jQuery(document).on('click', '.open-edit-modal', function() {
            const modal = jQuery('#modalPart');
            const type = jQuery(this).data('type');
            const id = jQuery(this).data('id');
            const partId = jQuery(this).data('part-id');
            const nameField = jQuery(this).data('name-field');
            const select = jQuery('#partSelect');

            // En edición, necesitamos RECARGAR las opciones completas para que la pieza seleccionada exista en el select.
            // Para esto, debemos buscar la lista de opciones completa asociada al tipo (Blade, Ratchet, etc.)
            // En el caso de esta implementación, usaremos los datos que pasaste inicialmente.
            let optionsData;
            switch (type) {
                case 'Blade': optionsData = @json($blades); break;
                case 'Ratchet': optionsData = @json($ratchets); break;
                case 'Bit': optionsData = @json($bits); break;
                case 'Assist Blade': optionsData = @json($assist_blades); break;
                default: optionsData = [];
            }

            jQuery('#modalTitle').text('Editar ' + type);
            jQuery('#partType').val(type);

            // Establecer valores
            jQuery('#weight').val(jQuery(this).data('weight'));
            jQuery('#color').val(jQuery(this).data('color'));
            jQuery('#quantity').val(jQuery(this).data('quantity'));
            jQuery('#comment').val(jQuery(this).data('comment'));

            // Cambiar acción del formulario a UPDATE
            const form = jQuery('#partForm');
            form.attr('action', `/collection/${id}`);
            jQuery('#formMethod').val('PUT');

            // Cargar las opciones y seleccionar la actual
            loadSelectOptions(select, optionsData, nameField, partId);

            modal.modal('show');
        });

    });
</script>
@endsection
