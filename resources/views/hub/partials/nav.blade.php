<nav class="bg-gray-900 border-t border-gray-800 fixed bottom-0 w-full z-20 pb-safe shadow-[0_-5px_15px_rgba(0,0,0,0.5)]">
    <div class="flex justify-around items-center h-16 px-1" id="nav-container">
        <button onclick="switchView('eventos', this)" data-target="eventos" class="nav-btn text-cyan-400 flex flex-col items-center justify-center w-full h-full transition-colors">
            <i class="fas fa-crosshairs text-lg mb-1"></i><span class="text-[9px] font-bold tracking-widest">ARENA</span>
        </button>
        <button onclick="switchView('ranking', this)" data-target="ranking" class="nav-btn text-gray-500 flex flex-col items-center justify-center w-full h-full transition-colors">
            <i class="fas fa-trophy text-lg mb-1"></i><span class="text-[9px] font-bold tracking-widest">RANKING</span>
        </button>
        <button onclick="switchView('duelos', this)" data-target="duelos" class="nav-btn text-gray-500 flex flex-col items-center justify-center w-full h-full transition-colors">
            <i class="fas fa-fist-raised text-lg mb-1"></i><span class="text-[9px] font-bold tracking-widest">DUELOS</span>
        </button>
        <button onclick="switchView('meta', this)" data-target="meta" class="nav-btn text-gray-500 flex flex-col items-center justify-center w-full h-full transition-colors">
            <i class="fas fa-chart-line text-lg mb-1"></i><span class="text-[9px] font-bold tracking-widest">META</span>
        </button>
        <button onclick="switchView('reglas', this)" data-target="reglas" class="nav-btn text-gray-500 flex flex-col items-center justify-center w-full h-full transition-colors">
            <i class="fas fa-book text-lg mb-1"></i><span class="text-[9px] font-bold tracking-widest">REGLAS</span>
        </button>
    </div>
</nav>