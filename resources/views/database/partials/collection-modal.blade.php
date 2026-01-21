{{-- resources/views/partials/collection-modal.blade.php --}}
<div class="modal fade" id="modalPart" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="partForm">
            @csrf
            {{-- Este input hidden controla si es POST (crear) o PUT (editar) --}}
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="hidden" name="type" id="partType">

            <div class="modal-content" style="background-color: #1e1e1e; color: #fff; border: 1px solid #444;">
                <div class="modal-header" style="border-bottom: 1px solid #444;">
                    <h5 class="modal-title" id="modalTitle">Añadir Pieza</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="partSelect" class="form-label text-secondary small text-uppercase fw-bold">Pieza</label>
                        {{-- Select2 se adjuntará aquí --}}
                        <select class="form-control select2-search" id="partSelect" name="part_id" style="width: 100%"></select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="weight" class="form-label text-secondary small text-uppercase fw-bold">Peso (g)</label>
                            <input type="number" class="form-control bg-dark text-white border-secondary" name="weight" id="weight" step="0.01" placeholder="Ej: 35.5">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label text-secondary small text-uppercase fw-bold">Cantidad</label>
                            <input type="number" class="form-control bg-dark text-white border-secondary" name="quantity" id="quantity" value="1">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="color" class="form-label text-secondary small text-uppercase fw-bold">Color</label>
                        <input type="text" class="form-control bg-dark text-white border-secondary" name="color" id="color" placeholder="Ej: Rojo, Stock, Metal Coat...">
                    </div>

                    <div class="mb-3">
                        <label for="comment" class="form-label text-secondary small text-uppercase fw-bold">Notas / Comentarios</label>
                        <textarea class="form-control bg-dark text-white border-secondary" name="comment" id="comment" rows="2" placeholder="Desgastado, nuevo, torneo..."></textarea>
                    </div>
                </div>

                <div class="modal-footer" style="border-top: 1px solid #444;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
