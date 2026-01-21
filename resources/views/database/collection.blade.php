@extends('layouts.app')

@section('title', 'Colección Beyblade X')

@section('styles')
    {{-- CSS Esenciales --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    {{-- DataTables Dark Mode CSS --}}
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        /* --- 1. UI General Dark Mode --- */
        body { background-color: #121212; color: #e0e0e0; }
        .card { background-color: #1e1e1e; border: 1px solid #333; }

        /* --- 2. Pestañas personalizadas (Tabs) --- */
        .nav-tabs { border-bottom: 1px solid #333; }
        .nav-tabs .nav-link { color: #aaa; border: none; border-bottom: 2px solid transparent; transition: 0.3s; }
        .nav-tabs .nav-link:hover { color: #fff; }
        .nav-tabs .nav-link.active { background-color: transparent; color: #00d2ff; border-bottom: 2px solid #00d2ff; font-weight: bold; }
        .tab-content { padding-top: 20px; }

        /* --- 3. DataTables Dark adjustments --- */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_processing,
        .dataTables_wrapper .dataTables_paginate {
            color: #aaa !important;
        }
        .table { color: #e0e0e0 !important; border-color: #333; }
        .table thead th { background-color: #2a2a2a !important; color: #fff; border-bottom: 2px solid #444; }
        .table-hover tbody tr:hover { color: #fff; background-color: #2c3e50 !important; }

        /* --- 4. SOLUCIÓN Z-INDEX (Menú encima del modal) --- */
        .modal-backdrop { z-index: 1150 !important; }
        .modal { z-index: 1160 !important; }

        /* --- 5. SOLUCIÓN SELECT2 (Dark Mode & Fixes) --- */

        /* Contenedor principal (Caja cerrada) */
        .select2-container--default .select2-selection--single {
            background-color: #2c2c2c !important;
            border: 1px solid #555 !important;
            height: 38px;
            display: flex;
            align-items: center;
        }

        /* Texto de la selección actual */
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #fff !important;
            line-height: normal !important; /* Ajuste para centrado */
            padding-left: 12px;
        }

        /* Flecha del select */
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #fff transparent transparent transparent !important;
        }

        /* Desplegable (Dropdown) */
        .select2-dropdown {
            background-color: #1e1e1e !important;
            border: 1px solid #444 !important;
            z-index: 9999 !important; /* CRUCIAL: Para que salga encima del modal */
        }

        /* Input de búsqueda */
        .select2-search__field {
            background-color: #2c2c2c !important;
            color: #fff !important;
            border: 1px solid #555 !important;
        }

        /* Opciones de la lista */
        .select2-results__option {
            background-color: #1e1e1e !important;
            color: #e0e0e0 !important;
        }

        /* Opción Hover/Seleccionada */
        .select2-results__option--highlighted {
            background-color: #0d6efd !important;
            color: #fff !important;
        }
    </style>
@endsection

@section('content')
@php
    $sections = [
        ['id' => 'tab-blade', 'title' => 'Blades', 'data' => $myBlades, 'type' => 'Blade', 'options' => $blades, 'partKey' => 'partBlade', 'nameField' => 'nombre_takara'],
        ['id' => 'tab-ratchet', 'title' => 'Ratchets', 'data' => $myRatchets, 'type' => 'Ratchet', 'options' => $ratchets, 'partKey' => 'partRatchet', 'nameField' => 'nombre'],
        ['id' => 'tab-bit', 'title' => 'Bits', 'data' => $myBits, 'type' => 'Bit', 'options' => $bits, 'partKey' => 'partBit', 'nameField' => 'nombre'],
        ['id' => 'tab-assist', 'title' => 'Assist Blades', 'data' => $myAssistBlades, 'type' => 'Assist Blade', 'options' => $assist_blades, 'partKey' => 'partAssistBlade', 'nameField' => 'nombre'],
    ];
@endphp

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white"><i class="fas fa-boxes me-2"></i>Gestión de Inventario</h2>

        {{-- Botón de Acción Global --}}
        <button class="btn btn-danger d-none" id="global-delete-btn">
            <i class="fas fa-trash-alt"></i> Borrar Seleccionados (<span id="selected-count">0</span>)
        </button>
    </div>

    {{-- Navegación por Pestañas --}}
    <ul class="nav nav-tabs" id="collectionTabs" role="tablist">
        @foreach($sections as $index => $section)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $index === 0 ? 'active' : '' }}"
                        id="{{ $section['id'] }}-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#{{ $section['id'] }}"
                        type="button"
                        role="tab">
                    {{ $section['title'] }} <span class="badge bg-dark border border-secondary ms-2">{{ count($section['data']) }}</span>
                </button>
            </li>
        @endforeach
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="generator-tab" data-bs-toggle="tab" data-bs-target="#generator" type="button" role="tab">
                <i class="fas fa-random me-2"></i>Generador
            </button>
        </li>
    </ul>

    {{-- Contenido de las Pestañas --}}
    <div class="tab-content" id="collectionTabsContent">
        @foreach($sections as $index => $section)
            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="{{ $section['id'] }}" role="tabpanel">

                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="m-0 text-white">{{ $section['title'] }}</h5>
                        <button class="btn btn-primary btn-sm open-modal"
                                data-type="{{ $section['type'] }}"
                                data-name-field="{{ $section['nameField'] }}"
                                data-options='@json($section['options'])'>
                            <i class="fas fa-plus"></i> Nuevo {{ $section['type'] }}
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle datatable-init w-100" id="table-{{ $section['id'] }}">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 40px;">
                                            <input type="checkbox" class="form-check-input select-all-rows">
                                        </th>
                                        <th>Nombre</th>
                                        <th>Peso (g)</th>
                                        <th>Color</th>
                                        <th>Cant.</th>
                                        <th>Comentarios</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($section['data'] as $item)
                                        <tr id="row-{{ $item->id }}">
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $item->id }}">
                                            </td>
                                            <td class="fw-bold text-info">
                                                {{ $item->{$section['partKey']}->{$section['nameField']} ?? $item->{$section['partKey']}->nombre ?? 'N/A' }}
                                            </td>
                                            <td>{{ $item->weight ?? '-' }}</td>
                                            <td>
                                                @if($item->color)
                                                    <span class="badge bg-secondary">{{ $item->color }}</span>
                                                @else - @endif
                                            </td>
                                            <td>{{ $item->quantity }}</td>
                                            <td class="text-muted fst-italic small">{{ Str::limit($item->comment, 30) }}</td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-outline-warning open-edit-modal me-1"
                                                    title="Editar"
                                                    data-id="{{ $item->id }}"
                                                    data-type="{{ $section['type'] }}"
                                                    data-part-id="{{ $item->part_id }}"
                                                    data-weight="{{ $item->weight }}"
                                                    data-color="{{ $item->color }}"
                                                    data-quantity="{{ $item->quantity }}"
                                                    data-comment="{{ $item->comment }}"
                                                    data-name-field="{{ $section['nameField'] }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger delete-item"
                                                        title="Eliminar"
                                                        data-id="{{ $item->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        @endforeach
        <div class="tab-pane fade" id="generator" role="tabpanel">
            <div class="card shadow-lg text-center" style="background: linear-gradient(145deg, #1e1e1e, #2a2a2a);">
                <div class="card-body py-5">
                    <h3 class="text-white mb-4">🧪 Generador de Combos Aleatorio</h3>

                    {{-- La "Máquina Tragaperras" --}}
                    <div class="row justify-content-center mb-5">
                        {{-- Slot Blade --}}
                        <div class="col-md-3 col-10 mb-3">
                            <div class="p-4 border border-secondary rounded-3 bg-dark position-relative slot-box" style="height: 150px; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                                <span class="text-primary fw-bold fs-4 slot-text" id="slot-blade">?</span>
                                <div class="small text-muted position-absolute bottom-0 start-50 translate-middle-x mb-2">BLADE</div>
                            </div>
                        </div>
                        {{-- Slot Ratchet --}}
                        <div class="col-md-3 col-10 mb-3">
                            <div class="p-4 border border-secondary rounded-3 bg-dark position-relative slot-box" style="height: 150px; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                                <span class="text-success fw-bold fs-4 slot-text" id="slot-ratchet">?</span>
                                <div class="small text-muted position-absolute bottom-0 start-50 translate-middle-x mb-2">RATCHET</div>
                            </div>
                        </div>
                        {{-- Slot Bit --}}
                        <div class="col-md-3 col-10 mb-3">
                            <div class="p-4 border border-secondary rounded-3 bg-dark position-relative slot-box" style="height: 150px; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                                <span class="text-warning fw-bold fs-4 slot-text" id="slot-bit">?</span>
                                <div class="small text-muted position-absolute bottom-0 start-50 translate-middle-x mb-2">BIT</div>
                            </div>
                        </div>
                    </div>

                    {{-- Botones de Control --}}
                    <button class="btn btn-lg btn-primary px-5 fw-bold" id="btn-spin">
                        <i class="fas fa-sync-alt me-2"></i> GENERAR COMBO
                    </button>

                    <div id="combo-stats" class="mt-4 d-none">
                        <p class="text-white">Peso total estimado: <span id="total-weight" class="fw-bold text-info">0g</span></p>
                        <button class="btn btn-outline-light btn-sm" id="btn-copy-combo"><i class="far fa-copy"></i> Copiar texto</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Importamos el Modal desde el Partial --}}
    @include('database.partials.collection-modal')

</div>
@endsection

@section('scripts')
{{-- Scripts JS --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {

        // --- 1. CONFIGURACIÓN DATATABLES Y TABS ---
        const tables = $('.datatable-init').DataTable({
            language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
            responsive: true,
            pageLength: 10,
            columnDefs: [ { orderable: false, targets: [0, 6] } ],
            order: [[ 1, 'asc' ]]
        });

        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        });

        // --- 2. CONFIGURACIÓN MODAL Y SELECT2 ---
        $('.select2-search').select2({ dropdownParent: $('#modalPart'), width: '100%' });

        // --- 3. BORRADO MÚLTIPLE (CHECKBOXES) ---
        $(document).on('change', '.select-all-rows', function() {
            const tableId = $(this).closest('table').attr('id');
            const isChecked = $(this).is(':checked');
            $(`#${tableId} .row-checkbox`).prop('checked', isChecked);
            toggleGlobalActions();
        });

        $(document).on('change', '.row-checkbox', function() {
            toggleGlobalActions();
        });

        function toggleGlobalActions() {
            const totalSelected = $('.row-checkbox:checked').length;
            $('#selected-count').text(totalSelected);
            if(totalSelected > 0) {
                $('#global-delete-btn').removeClass('d-none').addClass('animate__animated animate__fadeIn');
            } else {
                $('#global-delete-btn').addClass('d-none');
            }
        }

        // --- 4. BORRADO INDIVIDUAL ---
        $(document).on('click', '.delete-item', function() {
            const id = $(this).data('id');
            const row = $(this).closest('tr');

            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás revertir esto",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, borrar',
                cancelButtonText: 'Cancelar',
                background: '#1e1e1e', color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/collection/' + id,
                        type: 'POST',
                        data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            const table = row.closest('table').DataTable();
                            table.row(row).remove().draw();

                            const Toast = Swal.mixin({
                                toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
                                background: '#1e1e1e', color: '#fff'
                            });
                            Toast.fire({ icon: 'success', title: 'Pieza eliminada' });
                        },
                        error: function() {
                            Swal.fire('Error', 'No se pudo eliminar.', 'error');
                        }
                    });
                }
            });
        });

        // --- 5. BORRADO MASIVO ---
        $('#global-delete-btn').click(function() {
            const selectedIds = $('.row-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            Swal.fire({
                title: `¿Borrar ${selectedIds.length} piezas?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Sí, borrar todo',
                background: '#1e1e1e', color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("collection.bulk_destroy") }}',
                        type: 'POST',
                        data: { ids: selectedIds, _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            location.reload();
                        },
                        error: function() {
                            Swal.fire('Error', 'Ocurrió un error al borrar.', 'error');
                        }
                    });
                }
            });
        });

        // --- 6. MODALES (ABRIR CREAR/EDITAR) ---
        $('.open-modal').on('click', function() {
            const type = $(this).data('type');
            const options = $(this).data('options');
            const nameField = $(this).data('name-field');

            $('#modalTitle').text('Añadir ' + type);
            $('#partType').val(type);
            $('#weight, #color, #quantity, #comment').val('');
            $('#quantity').val(1);
            $('#formMethod').val('POST');
            $('#partForm').attr('action', '{{ route('collection.store') }}');
            loadSelectOptions($('#partSelect'), options, nameField);
            $('#modalPart').modal('show');
        });

        $(document).on('click', '.open-edit-modal', function() {
            const type = $(this).data('type');
            const id = $(this).data('id');
            const partId = $(this).data('part-id');
            const nameField = $(this).data('name-field');
            const select = $('#partSelect');

            $('#modalTitle').text('Editar ' + type);
            $('#partType').val(type);
            $('#weight').val($(this).data('weight'));
            $('#color').val($(this).data('color'));
            $('#quantity').val($(this).data('quantity'));
            $('#comment').val($(this).data('comment'));
            $('#partForm').attr('action', `/collection/${id}`);
            $('#formMethod').val('PUT');

            let optionsData = [];
            if(type === 'Blade') optionsData = @json($blades);
            else if(type === 'Ratchet') optionsData = @json($ratchets);
            else if(type === 'Bit') optionsData = @json($bits);
            else if(type === 'Assist Blade') optionsData = @json($assist_blades);

            loadSelectOptions(select, optionsData, nameField, partId);
            $('#modalPart').modal('show');
        });

        function loadSelectOptions(selectElement, options, nameField, selectedId = null) {
            selectElement.empty();
            selectElement.append(new Option('Selecciona una pieza', '', true, true));
            options.forEach(item => {
                const name = item[nameField] || item.nombre || item.nombre_takara || `ID: ${item.id}`;
                selectElement.append(new Option(name, item.id, false, false));
            });
            if (selectedId) selectElement.val(selectedId).trigger('change');
            else selectElement.val(null).trigger('change');
        }

        // --- 7. LÓGICA DEL GENERADOR DE COMBOS ---
        // (Ahora está DENTRO del document.ready, por lo que funcionará bien)

        const myBladesData = @json($myBlades->values());
        const myRatchetsData = @json($myRatchets->values());
        const myBitsData = @json($myBits->values());

        $('#btn-spin').click(function() {
            if(myBladesData.length === 0 || myRatchetsData.length === 0 || myBitsData.length === 0) {
                Swal.fire('Ups', 'Necesitas tener al menos 1 Blade, 1 Ratchet y 1 Bit en tu colección.', 'warning');
                return;
            }

            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-cog fa-spin me-2"></i> Generando...');
            $('#combo-stats').addClass('d-none');

            let iterations = 0;
            const maxIterations = 20;
            const interval = setInterval(() => {
                $('#slot-blade').text(getRandomItem(myBladesData).part_blade?.nombre_takara || 'Unknown');
                $('#slot-ratchet').text(getRandomItem(myRatchetsData).part_ratchet?.nombre || 'Unknown');
                $('#slot-bit').text(getRandomItem(myBitsData).part_bit?.nombre || 'Unknown');

                iterations++;
                if (iterations >= maxIterations) {
                    clearInterval(interval);
                    finalizeCombo();
                    btn.prop('disabled', false).html('<i class="fas fa-sync-alt me-2"></i> GENERAR COMBO');
                }
            }, 100);
        });

        function getRandomItem(array) {
            return array[Math.floor(Math.random() * array.length)];
        }

        function finalizeCombo() {
            const finalBlade = getRandomItem(myBladesData);
            const finalRatchet = getRandomItem(myRatchetsData);
            const finalBit = getRandomItem(myBitsData);

            const bladeName = finalBlade.part_blade?.nombre_takara || 'N/A';
            const ratchetName = finalRatchet.part_ratchet?.nombre || 'N/A';
            const bitName = finalBit.part_bit?.nombre || 'N/A';

            $('#slot-blade').text(bladeName).addClass('animate__animated animate__pulse');
            $('#slot-ratchet').text(ratchetName).addClass('animate__animated animate__pulse');
            $('#slot-bit').text(bitName).addClass('animate__animated animate__pulse');

            const totalW = (parseFloat(finalBlade.weight) || 0) +
                           (parseFloat(finalRatchet.weight) || 0) +
                           (parseFloat(finalBit.weight) || 0);

            if(totalW > 0) $('#total-weight').text(totalW.toFixed(2) + 'g');
            else $('#total-weight').text('Datos incompletos');

            $('#combo-stats').removeClass('d-none').addClass('animate__animated animate__fadeInUp');

            // Configurar botón de copiar (VERSIÓN COMPATIBLE CON HTTP Y HTTPS)
            $('#btn-copy-combo').off('click').on('click', function() {
                const text = `Mi Combo Beyblade X: ${bladeName} ${ratchetName} ${bitName} (${totalW.toFixed(2)}g)`;

                // Función auxiliar para mostrar el mensaje de éxito
                const showToast = () => {
                    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, background: '#1e1e1e', color: '#fff'});
                    Toast.fire({ icon: 'success', title: 'Copiado al portapapeles' });
                };

                // INTENTO 1: API Moderna (Solo funciona en HTTPS o Localhost)
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text)
                        .then(showToast)
                        .catch(() => copyFallback(text)); // Si falla, usa el plan B
                } else {
                    // INTENTO 2: Si no hay HTTPS, usamos el método antiguo
                    copyFallback(text);
                }

                // Función de respaldo (Método antiguo compatible con todo)
                function copyFallback(textToCopy) {
                    const textArea = document.createElement("textarea");
                    textArea.value = textToCopy;

                    // Lo hacemos invisible pero parte del DOM para poder seleccionarlo
                    textArea.style.position = "fixed"; // Evita scroll al pegar
                    textArea.style.opacity = "0";
                    document.body.appendChild(textArea);

                    textArea.focus();
                    textArea.select();

                    try {
                        const successful = document.execCommand('copy');
                        if(successful) showToast();
                        else throw new Error('Fallo al copiar');
                    } catch (err) {
                        console.error('Error al copiar:', err);
                        Swal.fire('Ups', 'No se pudo copiar automáticamente. Inténtalo manualmente.', 'error');
                    }

                    document.body.removeChild(textArea);
                }
            });

            setTimeout(() => {
                $('.slot-text').removeClass('animate__animated animate__pulse');
            }, 1000);
        }

    }); // <-- FIN DEL DOCUMENT READY
</script>
@endsection
