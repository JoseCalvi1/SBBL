<div id="arbitrajeModal" class="modal-overlay fixed inset-0 z-[70] items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="bg-gray-900 w-full sm:w-[500px] h-[95vh] sm:h-[85vh] rounded-t-2xl sm:rounded-2xl flex flex-col border border-gray-700 shadow-[0_0_20px_rgba(0,0,0,0.9)] overflow-hidden">
        
        <!-- Cabecera del Panel -->
        <div class="p-4 border-b border-gray-800 flex justify-between items-center bg-gray-950 rounded-t-2xl shrink-0">
            <div>
                <h3 class="text-white font-bold text-lg font-mono leading-none"><i class="fas fa-tablet-alt text-yellow-500 me-2"></i> PANEL DE ARENA</h3>
                <p id="arbitraje_event_name" class="text-[10px] text-gray-500 font-bold tracking-widest uppercase mt-1">Cargando...</p>
            </div>
            <button onclick="cerrarPanelArbitraje()" class="text-gray-400 hover:text-white text-2xl px-2 h-10 w-10 flex items-center justify-center rounded-full bg-gray-800">&times;</button>
        </div>

        <!-- Área de Emparejamientos -->
        <div class="p-4 overflow-y-auto flex-1 bg-black no-scrollbar" id="arbitrajeContent">
            <!-- Se llena con JS -->
            <div class="text-center mt-10">
                <i class="fas fa-circle-notch fa-spin text-yellow-500 text-3xl mb-3"></i>
                <p class="text-gray-500 text-sm font-bold tracking-widest">SINTONIZANDO ARENA...</p>
            </div>
        </div>

        <!-- Botonera Inferior del Juez -->
        <div class="p-4 bg-gray-950 border-t border-gray-800 shrink-0">
            <button onclick="generarSiguienteRonda()" id="btnGenerarRonda" class="w-full bg-green-700 hover:bg-green-600 active:bg-green-800 text-white font-bold py-3.5 rounded-lg text-sm tracking-wide shadow-lg border border-green-500 transition-colors hidden">
                <i class="fas fa-cogs mr-2"></i> GENERAR RONDA 1
            </button>
        </div>
    </div>
</div>