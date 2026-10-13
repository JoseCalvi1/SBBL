<div id="perfilModal" class="modal-overlay fixed inset-0 z-[60] items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="bg-gray-900 w-full sm:w-[400px] rounded-t-2xl sm:rounded-2xl max-h-[90vh] flex flex-col border border-gray-700 shadow-[0_0_20px_rgba(0,0,0,0.8)] overflow-hidden">
        <div class="p-4 border-b border-gray-800 flex justify-between items-center bg-gray-950 rounded-t-2xl shrink-0">
            <h3 class="text-white font-bold text-lg font-mono"><i class="fas fa-id-badge text-cyan-500 me-2"></i> INTEL BLADER</h3>
            <button onclick="cerrarPerfil()" class="text-gray-400 hover:text-white text-2xl px-2">&times;</button>
        </div>
        <div class="p-4 overflow-y-auto flex-1 bg-black no-scrollbar" id="perfilContent">
            <div class="text-center mt-10">
                <i class="fas fa-circle-notch fa-spin text-cyan-500 text-3xl mb-3"></i>
                <p class="text-gray-500 text-sm font-bold tracking-widest">DESCIFRANDO DATOS...</p>
            </div>
        </div>
    </div>
</div>