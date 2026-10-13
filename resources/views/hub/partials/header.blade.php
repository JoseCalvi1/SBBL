<header class="bg-gray-900 border-b border-gray-800 p-4 flex justify-between items-center z-10 shadow-md">
    <div class="font-bold text-xl neon-text tracking-widest font-mono">SBBL HUB</div>
    @auth
        <button onclick="abrirPerfil('{{ Auth::user()->id }}')" class="text-xs text-gray-400 font-bold border border-gray-700 px-3 py-1 rounded-full bg-gray-800 shadow-inner hover:text-cyan-400 hover:border-cyan-500 transition-colors focus:outline-none active:scale-95">
            <i class="fas fa-user text-cyan-400 mr-1"></i> {{ strtoupper(Auth::user()->name) }}
        </button>
    @endauth
</header>