<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SBBL HUB</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0b0c10">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { overscroll-behavior-y: none; background-color: #0b0c10; color: #c5c6c7; user-select: none; -webkit-user-select: none; }
        .neon-text { color: #66fcf1; text-shadow: 0 0 5px rgba(102, 252, 241, 0.5); }
        
        .view-section { display: none; opacity: 0; transition: opacity 0.2s ease-out, transform 0.2s ease-out; }
        .view-section.active { display: block; opacity: 1; transform: translateX(0); }
        .view-section.slide-left { animation: slideLeft 0.2s forwards; }
        .view-section.slide-right { animation: slideRight 0.2s forwards; }
        
        @keyframes slideLeft { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes slideRight { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
        
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .modal-overlay { display: none; background: rgba(0,0,0,0.9); }
        .modal-overlay.active { display: flex; animation: fadeIn 0.2s; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        
        .rank-tab { border-bottom: 2px solid transparent; color: #6b7280; transition: all 0.2s; }
        .rank-tab.active { border-bottom-color: #eab308; color: #eab308; font-weight: bold; }
    </style>
</head>
<body class="flex flex-col h-screen overflow-hidden font-sans">

    @include('hub.partials.header')
    @include('hub.partials.banners')

    <!-- ÁREA DE CONTENIDO PRINCIPAL -->
    <main class="flex-1 overflow-y-auto no-scrollbar p-4 relative bg-black pb-24" id="main-content">
        
        <!-- PESTAÑA 1: ARENA -->
        <section id="view-eventos" class="view-section active" data-index="0">
            <div class="flex justify-between items-center mb-4 border-b border-cyan-500/50 pb-2">
                <h2 class="text-2xl font-bold text-white pl-2 font-mono"><i class="fas fa-crosshairs text-cyan-400 mr-2"></i>ARENA</h2>
            </div>
            <div class="flex gap-2 mb-4">
                <div class="relative flex-1">
                    <select id="filtro-tipo" onchange="cargarEventos()" class="w-full bg-gray-900 border border-gray-700 text-cyan-400 text-[10px] font-bold tracking-widest p-2.5 rounded-lg appearance-none outline-none focus:border-cyan-400 shadow-inner transition-colors">
                        <option value="">TODOS LOS TIPOS</option>
                        <option value="ranking">RANKING OFICIAL</option>
                        <option value="rankingplus">RANKING PLUS</option>
                        <option value="quedada">QUEDADAS</option>
                        <option value="grancopa">GRAN COPA</option>
                        <option value="hasbro">FORMATO HASBRO</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-3 text-gray-500 text-xs pointer-events-none"></i>
                </div>
                <div class="relative flex-1">
                    <select id="filtro-region" onchange="cargarEventos()" class="w-full bg-gray-900 border border-gray-700 text-cyan-400 text-[10px] font-bold tracking-widest p-2.5 rounded-lg appearance-none outline-none focus:border-cyan-400 shadow-inner transition-colors">
                        <option value="">TODA ESPAÑA</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-3 text-gray-500 text-xs pointer-events-none"></i>
                </div>
            </div>
            <div id="lista-eventos" class="space-y-4 pb-4"></div>
        </section>

        <!-- PESTAÑA 2: RANKING -->
        <section id="view-ranking" class="view-section" data-index="1">
            <div class="flex justify-between items-center mb-2 border-b border-yellow-500/50 pb-2">
                <h2 class="text-2xl font-bold text-white pl-2 font-mono"><i class="fas fa-trophy text-yellow-500 mr-2"></i>RANKING</h2>
                <div class="text-[10px] bg-yellow-900/30 text-yellow-300 px-2 py-1 rounded border border-yellow-500/30 font-bold">LIGA S3</div>
            </div>
            <div class="flex mb-4 bg-gray-900 rounded-lg p-1">
                <button onclick="switchRankTab('individual')" id="tab-individual" class="rank-tab active flex-1 text-center text-xs tracking-widest py-2 rounded">BLADERS</button>
                <button onclick="switchRankTab('equipos')" id="tab-equipos" class="rank-tab flex-1 text-center text-xs tracking-widest py-2 rounded">EQUIPOS</button>
            </div>
            <div id="lista-ranking-individual" class="space-y-2 pb-4 block"></div>
            <div id="lista-ranking-equipos" class="space-y-2 pb-4 hidden"></div>
        </section>

        <!-- PESTAÑA 3: DUELOS -->
        <section id="view-duelos" class="view-section" data-index="2">
            <div class="flex justify-between items-center mb-4 border-b border-red-500/50 pb-2">
                <h2 class="text-2xl font-bold text-white pl-2 font-mono"><i class="fas fa-fist-raised text-red-500 mr-2"></i>DUELOS</h2>
                <div class="text-[10px] bg-red-900/30 text-red-300 px-2 py-1 rounded border border-red-500/30 font-bold">EQUIPOS 3V3</div>
            </div>
            <button onclick="abrirModalDuelo()" class="w-full bg-red-900/40 hover:bg-red-800 border border-red-500 text-white font-bold py-3 rounded-lg text-sm tracking-widest transition-all shadow-[0_0_15px_rgba(239,68,68,0.2)] mb-4">
                <i class="fas fa-bolt text-yellow-400 mr-2"></i> DECLARAR DUELO
            </button>
            <div id="lista-duelos" class="space-y-3 pb-4"></div>
        </section>

        <!-- PESTAÑA 4: EL META -->
        <section id="view-meta" class="view-section" data-index="3">
            <div class="flex justify-between items-center mb-4 border-b border-purple-500/50 pb-2">
                <h2 class="text-2xl font-bold text-white pl-2 font-mono"><i class="fas fa-chart-line text-purple-500 mr-2"></i>EL META</h2>
            </div>
            
            <!-- NUEVOS FILTROS DE META -->
            <div class="flex gap-2 mb-4">
                <div class="relative flex-1">
                    <select id="meta-filtro-tiempo" onchange="cargarMeta()" class="w-full bg-gray-900 border border-gray-700 text-purple-400 text-[10px] font-bold tracking-widest p-2.5 rounded-lg appearance-none outline-none focus:border-purple-500 shadow-inner">
                        <option value="30">ÚLTIMOS 30 DÍAS</option>
                        <option value="60">ÚLTIMOS 60 DÍAS</option>
                        <option value="90">ÚLTIMOS 90 DÍAS</option>
                        <option value="all">HISTÓRICO COMPLETO</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-3 text-gray-500 text-xs pointer-events-none"></i>
                </div>
                <div class="relative w-1/3">
                    <select id="meta-filtro-rango" onchange="cargarMeta()" class="w-full bg-gray-900 border border-gray-700 text-purple-400 text-[10px] font-bold tracking-widest p-2.5 rounded-lg appearance-none outline-none focus:border-purple-500 shadow-inner">
                        <option value="5">TOP 50</option>
                        <option value="1">VER TODOS</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-3 text-gray-500 text-xs pointer-events-none"></i>
                </div>
            </div>
            <!-- FIN FILTROS -->
            
            <div id="lista-meta" class="space-y-3 pb-4"></div>
        </section>

        <!-- PESTAÑA 5: REGLAMENTO -->
        <section id="view-reglas" class="view-section" data-index="4">
            <div class="flex justify-between items-center mb-4 border-b border-green-500/50 pb-2">
                <h2 class="text-2xl font-bold text-white pl-2 font-mono"><i class="fas fa-book text-green-500 mr-2"></i>REGLAMENTO</h2>
            </div>
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-lg text-center relative overflow-hidden mt-6">
                <i class="fas fa-gavel text-5xl text-gray-700 mb-4"></i>
                <h3 class="text-white font-bold text-xl font-mono mb-2 uppercase tracking-wide">Normativa Oficial</h3>
                <p class="text-gray-400 text-sm mb-8 leading-relaxed">Consulta el reglamento oficial para la Temporada 3.</p>
                <a href="https://sbbl.es/blog/reglamento-s3-v1" target="_blank" class="block w-full bg-green-700 hover:bg-green-600 active:bg-green-800 border border-green-500 text-white font-bold py-4 rounded-lg text-sm tracking-widest transition-all">
                    <i class="fas fa-external-link-alt mr-2"></i> ABRIR REGLAMENTO
                </a>
            </div>
        </section>

    </main>

    @include('hub.partials.nav')

    <!-- Modales -->
    @include('hub.modals.deck')
    @include('hub.modals.lista')
    @include('hub.modals.duelo')
    @include('hub.modals.perfil')
    @include('hub.modals.arbitraje')
    @include('hub.modals.batalla')
    @include('hub.modals.topcut')
    @include('hub.modals.meta_stats')

    @include('hub.scripts')

</body>
</html>