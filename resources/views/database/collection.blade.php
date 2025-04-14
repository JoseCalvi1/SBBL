@extends('layouts.app')

@section('styles')
    @include('database.partials.mainmenu-styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .card {
            background-color: #1e1e1e !important;
            border: 1px solid #333;
        }
        .card-header {
            background-color: #2c2c2c;
            color: #fff;
        }
        .table {
            color: #e0e0e0 !important;
        }
        .table thead {
            background-color: #2a2a2a;
            color: #fff;
        }
        .select2 {
            width: 100% !important;
        }
    </style>
@endsection

@section('content')
@if(Auth::user())
<div class="container-fluid py-4">
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
                <div class="card-body p-0">
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
                                        <!-- <button class="btn btn-outline-warning btn-sm"><i class="fas fa-edit"></i></button>-->
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
                <div class="card-body p-0">
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
                                        <!-- <button class="btn btn-outline-warning btn-sm"><i class="fas fa-edit"></i></button>-->
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
                <div class="card-body p-0">
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
                                        <!-- <button class="btn btn-outline-warning btn-sm"><i class="fas fa-edit"></i></button>-->
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
                <div class="card-body p-0">
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
                                        <!-- <button class="btn btn-outline-warning btn-sm"><i class="fas fa-edit"></i></button>-->
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
            <form method="POST" action="{{ route('collection.store') }}">
                @csrf
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
                            <input type="text" class="form-control" name="weight" id="weight">
                        </div>
                        <div class="mb-3">
                            <label for="color" class="form-label">Color (opcional)</label>
                            <input type="text" class="form-control" name="color" id="color">
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Cantidad (opcional)</label>
                            <input type="text" class="form-control" name="quantity" id="quantity">
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

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


    // Asegúrate de que el evento solo se agregue cuando el modal esté cargado
    jQuery('.open-modal').on('click', function () {
        const type = jQuery(this).data('type');
        const options = jQuery(this).data('options');

        // Convertimos 'options' a un array real si no lo es
        const optionsArray = Array.isArray(options) ? options : Object.values(options);

        // Ahora procesamos las opciones
        const select = jQuery('#partSelect');
        jQuery('#partType').val(type);
        select.empty();  // Limpiamos las opciones existentes

        // Llenamos las opciones dependiendo del tipo de pieza
        if (type === 'Blade') {
            optionsArray.forEach(opt => {
                select.append(new Option(opt.nombre_takara, opt.id));
            });
        } else if (type === 'Ratchet') {
            optionsArray.forEach(opt => {
                select.append(new Option(opt.nombre, opt.id));
            });
        } else if (type === 'Bit') {
            optionsArray.forEach(opt => {
                select.append(new Option(opt.nombre, opt.id));
            });
        } else if (type === 'Assist Blade') {
            optionsArray.forEach(opt => {
                select.append(new Option(opt.nombre, opt.id));
            });
        }

        // Mostrar el modal utilizando Bootstrap 5 sin jQuery
        const modal = new bootstrap.Modal(document.getElementById('modalPart'));
        modal.show();
    });
});

</script>
@else
<script type="text/javascript">
    window.location = "/";
</script>
@endif
@endsection
