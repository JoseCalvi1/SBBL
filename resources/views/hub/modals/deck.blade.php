<div id="deckModal" class="modal-overlay fixed inset-0 z-50 items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="bg-gray-900 w-full sm:w-96 rounded-t-2xl sm:rounded-2xl max-h-[90vh] sm:max-h-[85vh] flex flex-col border border-gray-700 shadow-[0_0_20px_rgba(0,0,0,0.8)] overflow-hidden">
        <div class="p-4 border-b border-gray-800 flex justify-between items-center bg-gray-950 rounded-t-2xl sm:rounded-t-2xl shrink-0">
            <h3 class="text-white font-bold text-lg font-mono"><i class="fas fa-layer-group text-yellow-500 me-2"></i> REGISTRO DE DECK</h3>
            <button onclick="cerrarModal()" class="text-gray-400 hover:text-white text-2xl px-2">&times;</button>
        </div>
        <div class="p-4 overflow-y-auto flex-1 bg-black no-scrollbar" id="deckFormContainer">
            <form id="deckForm" onsubmit="enviarDeck(event)">
                <input type="hidden" id="modal_event_id" value="">
                <div class="bg-gray-900 border border-gray-700 p-3 rounded-lg mb-4 text-center">
                    <label class="flex items-center justify-center gap-3 cursor-pointer py-1">
                        <input type="checkbox" id="tentativeCheck" onchange="toggleTentative()" class="w-5 h-5 accent-cyan-500">
                        <span class="text-white font-bold text-sm">INSCRIPCIÓN TENTATIVA</span>
                    </label>
                    <p id="tentativeWarning" class="text-yellow-500 text-[10px] mt-2 hidden uppercase tracking-wide">Aviso: Deberás registrar tu Deck definitivo antes del torneo.</p>
                    <button type="button" onclick="autorrellenarDeck()" class="mt-3 w-full bg-gray-800 hover:bg-gray-700 border border-gray-600 text-cyan-400 font-bold py-2 rounded text-xs tracking-widest transition-colors flex justify-center items-center gap-2">
                        <i class="fas fa-history"></i> CARGAR ÚLTIMO DECK
                    </button>
                </div>
                <div id="beysContainer" class="space-y-4"></div>
                <button type="submit" id="btnSubmitDeck" class="w-full bg-cyan-600 active:bg-cyan-500 text-white font-bold py-3 mt-6 rounded-lg text-sm tracking-wide shadow-md transition-colors">CONFIRMAR DECK</button>
            </form>
        </div>
    </div>
</div>