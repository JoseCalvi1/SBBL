@extends('layouts.app')

@section('styles')
    <style>
        /* Tema oscuro global */
        body, .container, .modal-content {
            color: #e0e0e0;
        }

        .btn-primary {
            background-color: #6200ea;
            border-color: #6200ea;
        }

        .btn-primary:hover {
            background-color: #3700b3;
            border-color: #3700b3;
        }

        .btn-warning {
            background-color: #ff9800;
            border-color: #ff9800;
        }

        .btn-danger {
            background-color: #f44336;
            border-color: #f44336;
        }

        .btn-danger:hover {
            background-color: #d32f2f;
            border-color: #d32f2f;
        }

        .part-item {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            background-color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .part-item button {
            margin-left: 5px;
        }

        .modal-content {
            background-color: #333;
        }

        .modal-header {
            border-bottom: 1px solid #444;
        }

        .modal-body {
            padding: 20px;
        }

        .form-control {
            background-color: #555;
            color: #fff;
            border: 1px solid #444;
        }

        .form-control:focus {
            background-color: #666;
            border-color: #6200ea;
            color: #fff;
        }

        .part-item a {
            color: white;
        }
    </style>
@endsection

@section('content')
<div id="app" class="container py-5">
    <div class="row">
        <div class="col-md-12 mb-3">
            <a href="{{ route('beyblades.create') }}" class="btn btn-success">Crear Beyblade</a>
        </div>
        <!-- Blade Section -->
        <div class="col-md-12">
            <h3>Beyblades</h3>
            <div id="blades-list">
                @foreach ($beyblades as $beyblade)
                    <div class="part-item">
                        <span>{{ $beyblade->blade_nombre }} {{ $beyblade->ratchet_nombre }} {{ $beyblade->bit_nombre }}</span>
                        <div>
                            @if (!empty($beyblade->tipo) && empty($beyblade->tarjeta)) <!-- Asegúrate de agregar las condiciones correctas para los otros campos -->
                                <a href="{{ route('beyblades.edit', $beyblade->id) }}" class="btn btn-info btn-sm">Completar</a>
                            @else
                                <a href="{{ route('beyblades.edit', $beyblade->id) }}" class="btn btn-warning btn-sm">Editar</a>
                            @endif
                            <button class="btn btn-danger btn-sm delete-btn" data-toggle="modal" data-target="#deleteConfirmationModal" data-id="{{ $beyblade->id }}" data-type="ratchets" data-name="{{ $beyblade->blade_nombre }} {{ $beyblade->ratchet_nombre }} {{ $beyblade->bit_nombre }}">Eliminar</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="background-color: #333;">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirmar eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas eliminar <strong id="partName"></strong>?
            </div>
            <div class="modal-footer">
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="type" id="partType">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Cuando se hace clic en el botón de eliminar, se actualiza el modal
        $('.delete-btn').click(function() {
            var id = $(this).data('id');
            var type = $(this).data('type');
            var name = $(this).data('name');

            // Establecer el nombre en el modal
            $('#partName').text(name);

            // Establecer el action del formulario de eliminación con la URL correcta
            $('#deleteForm').attr('action', '/' + type.toLowerCase() + '/' + id);
            // Establecer el tipo en el campo oculto
            $('#partType').val(type);
        });
    });
</script>
@endsection
