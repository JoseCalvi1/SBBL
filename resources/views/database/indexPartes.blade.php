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
        <!-- Blade Section -->
        <div class="col-md-6">
            <h3>Blades</h3>
            <form action="{{ route('blades.store') }}" method="POST" class="mb-3">
                @csrf
                <input type="text" name="nombre" class="form-control" placeholder="Nombre del Blade" required>
                <input type="hidden" name="type" class="form-control" value="Blade">
                <button type="submit" class="btn btn-primary mt-2 w-100">Crear Blade</button>
            </form>
            <div id="blades-list">
                @foreach ($blades as $blade)
                    <div class="part-item">
                        <span>{{ $blade->nombre_takara }}<br>({{ ($blade->recolor == false ? 'Base' : 'Recolor') }})</span>
                        <div>
                            @if (!empty($blade->nombre_takara) && empty($blade->tipo) && empty($blade->imagen)) <!-- Asegúrate de agregar las condiciones correctas para los otros campos -->
                                <a href="{{ route('blades.edit', $blade->id) }}" class="btn btn-info btn-sm">Completar</a>
                            @else
                                <a href="{{ route('blades.edit', $blade->id) }}" class="btn btn-warning btn-sm">Editar</a>
                            @endif
                            <button class="btn btn-danger btn-sm delete-btn" data-toggle="modal" data-target="#deleteConfirmationModal" data-id="{{ $blade->id }}" data-type="blades" data-name="{{ $blade->nombre_takara }}">Eliminar</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>


        <!-- Ratchet Section -->
        <div class="col-md-6">
            <h3>Ratchets</h3>
            <form action="{{ route('ratchets.store') }}" method="POST" class="mb-3">
                @csrf
                <input type="text" name="nombre" class="form-control" placeholder="Nombre del Ratchet" required>
                <input type="hidden" name="type" class="form-control" value="Ratchet">
                <button type="submit" class="btn btn-primary mt-2 w-100">Crear Ratchet</button>
            </form>
            <div id="ratchets-list">
                @foreach ($ratchets as $ratchet)
                    <div class="part-item">
                        <span>{{ $ratchet->nombre }}<br>({{ ($ratchet->recolor == false ? 'Base' : 'Recolor') }})</span>
                        <div>
                            @if (!empty($ratchet->nombre) && empty($ratchet->imagen)) <!-- Asegúrate de agregar las condiciones correctas para los otros campos -->
                                <a href="{{ route('ratchets.edit', $ratchet->id) }}" class="btn btn-info btn-sm">Completar</a>
                            @else
                                <a href="{{ route('ratchets.edit', $ratchet->id) }}" class="btn btn-warning btn-sm">Editar</a>
                            @endif
                            <button class="btn btn-danger btn-sm delete-btn" data-toggle="modal" data-target="#deleteConfirmationModal" data-id="{{ $ratchet->id }}" data-type="ratchets" data-name="{{ $ratchet->nombre }}">Eliminar</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Bit Section -->
        <div class="col-md-6">
            <h3>Bits</h3>
            <form action="{{ route('bits.store') }}" method="POST" class="mb-3">
                @csrf
                <input type="text" name="nombre" class="form-control" placeholder="Nombre del Bit" required>
                <input type="hidden" name="type" class="form-control" value="Bit">
                <button type="submit" class="btn btn-primary mt-2 w-100">Crear Bit</button>
            </form>
            <div id="bits-list">
                @foreach ($bits as $bit)
                    <div class="part-item">
                        <span>{{ $bit->nombre }}<br>({{ ($bit->recolor == false ? 'Base' : 'Recolor') }})</span>
                        <div>
                            @if (!empty($bit->nombre) && empty($bit->imagen) && empty($bit->tarjeta)) <!-- Asegúrate de agregar las condiciones correctas para los otros campos -->
                                <a href="{{ route('bits.edit', $bit->id) }}" class="btn btn-info btn-sm">Completar</a>
                            @else
                                <a href="{{ route('bits.edit', $bit->id) }}" class="btn btn-warning btn-sm">Editar</a>
                            @endif
                            <button class="btn btn-danger btn-sm delete-btn" data-toggle="modal" data-target="#deleteConfirmationModal" data-id="{{ $bit->id }}" data-type="bits" data-name="{{ $bit->nombre }}">Eliminar</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Assist Blade Section -->
        <div class="col-md-6">
            <h3>Assist Blades</h3>
            <form action="{{ route('assistBlade.store') }}" method="POST" class="mb-3">
                @csrf
                <input type="text" name="nombre" class="form-control" placeholder="Nombre del Assist Blade" required>
                <input type="hidden" name="type" class="form-control" value="AssistBlade">
                <button type="submit" class="btn btn-primary mt-2 w-100">Crear Assist Blade</button>
            </form>
            <div id="ratchets-list">
                @foreach ($assistBlades as $assistBlade)
                    <div class="part-item">
                        <span>{{ $assistBlade->nombre }}<br>({{ ($assistBlade->recolor == false ? 'Base' : 'Recolor') }})</span>
                        <div>
                            @if (!empty($assistBlade->nombre) && empty($assistBlade->imagen)) <!-- Asegúrate de agregar las condiciones correctas para los otros campos -->
                                <a href="{{ route('assistBlade.edit', $assistBlade->id) }}" class="btn btn-info btn-sm">Completar</a>
                            @else
                                <a href="{{ route('assistBlade.edit', $assistBlade->id) }}" class="btn btn-warning btn-sm">Editar</a>
                            @endif
                            <button class="btn btn-danger btn-sm delete-btn" data-toggle="modal" data-target="#deleteConfirmationModal" data-id="{{ $assistBlade->id }}" data-type="assistBlade" data-name="{{ $assistBlade->nombre }}">Eliminar</button>
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
