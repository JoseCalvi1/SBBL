<div id="listaModal" class="modal-overlay fixed inset-0 z-50 items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="bg-gray-900 w-full sm:w-96 rounded-t-2xl sm:rounded-2xl max-h-[85vh] flex flex-col border border-gray-700 shadow-[0_0_20px_rgba(0,0,0,0.8)] overflow-hidden">
        <div class="p-4 border-b border-gray-800 flex justify-between items-center bg-gray-950 rounded-t-2xl shrink-0">
            <h3 class="text-white font-bold text-lg font-mono"><i class="fas fa-users text-cyan-500 me-2"></i> INSCRITOS</h3>
            <button onclick="cerrarListaModal()" class="text-gray-400 hover:text-white text-2xl px-2">&times;</button>
        </div>
        <div class="p-4 overflow-y-auto flex-1 bg-black no-scrollbar" id="listadoJugadoresContent"></div>
    </div>
</div>