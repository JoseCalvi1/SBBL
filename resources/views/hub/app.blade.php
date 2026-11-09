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
    <meta name="user-roles" content="{{ Auth::check() ? implode(',', Auth::user()->roles->pluck('name')->toArray()) : '' }}">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- LIBRERÍA TOM SELECT PARA BUSCADOR EN SELECTS -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<style>
    /* Adaptación de TomSelect al Modo Oscuro de la SBBL */
    .ts-wrapper { width: 100%; }
    .ts-control { background-color: #111827 !important; border: 1px solid #374151 !important; color: white !important; border-radius: 0.5rem !important; padding: 0.75rem 1rem !important; font-size: 0.875rem !important; box-shadow: none !important; }
    .ts-control > input { color: white !important; font-size: 0.875rem !important; }
    .ts-dropdown { background-color: #1f2937 !important; border: 1px solid #374151 !important; color: white !important; border-radius: 0.5rem !important; margin-top: 4px !important; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5) !important; }
    .ts-dropdown .option { padding: 0.5rem 1rem !important; cursor: pointer !important; }
    .ts-dropdown .option:hover, .ts-dropdown .active { background-color: #374151 !important; color: #22d3ee !important; }
    .ts-control.disabled, .ts-control.disabled * { background-color: #1f2937 !important; cursor: not-allowed !important; opacity: 0.7 !important; }
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

    <!-- GOOGLE ANALYTICS 4 (SBBL HUB + DIFERENCIACIÓN PWA/WEB) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-KSK91ZXWE3"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        const isStandalonePWA = window.navigator.standalone || window.matchMedia('(display-mode: standalone)').matches;

        gtag('config', 'G-KSK91ZXWE3', {
            'anonymize_ip': true,
            'page_title': 'SBBL HUB - Arena',
            'page_path': '/hub/eventos',
            'app_platform': isStandalonePWA ? 'pwa_instalada' : 'navegador_movil'
        });

        // Función global para registrar cambios de pestaña y modales en el Hub
        window.trackHubView = function(virtualPath, title) {
            if (typeof gtag === 'function') {
                gtag('event', 'page_view', {
                    'page_title': title,
                    'page_path': virtualPath,
                    'app_platform': isStandalonePWA ? 'pwa_instalada' : 'navegador_movil'
                });
            }
        };
    </script>
</head>
<body class="flex flex-col h-screen overflow-hidden font-sans">

    @include('hub.partials.header')
    @include('hub.partials.banners')

    <!-- BARRA DE PREVENCIÓN DE DESASTRES (COLA OFFLINE DE ARBITRAJE) -->
    <div id="offlineSyncBanner" class="hidden bg-amber-950 border-b border-amber-500 px-4 py-2 flex items-center justify-between z-50">
        <div class="flex items-center gap-2">
            <i class="fas fa-wifi-slash text-amber-400 text-xs animate-pulse"></i>
            <span id="offlineSyncText" class="text-[10px] font-mono font-bold text-amber-200 uppercase">MODO OFFLINE: 0 RESULTADOS EN COLA</span>
        </div>
        <button type="button" onclick="sincronizarColaOffline()" id="btnSyncOffline" class="bg-amber-400 hover:bg-amber-300 text-gray-950 font-black text-[10px] px-2.5 py-1 rounded uppercase tracking-wider">
            SINCRONIZAR AHORA
        </button>
    </div>

    <!-- INDICADOR PULL-TO-REFRESH NATIVO -->
    <div id="ptr-indicator" class="fixed top-14 left-1/2 -translate-x-1/2 z-40 pointer-events-none transition-all duration-200 opacity-0 -translate-y-6">
        <div class="bg-gray-900 border border-cyan-500/60 text-cyan-400 rounded-full px-3.5 py-1.5 shadow-xl flex items-center gap-2 text-[10px] font-mono font-bold uppercase">
            <i id="ptr-icon" class="fas fa-arrow-down transition-transform duration-200"></i>
            <span id="ptr-text">Desliza para actualizar</span>
        </div>
    </div>
    
    
    <!-- ÁREA DE CONTENIDO PRINCIPAL -->
    <main class="flex-1 overflow-y-auto no-scrollbar p-4 relative bg-black pb-24" id="main-content">
        
        <!-- PESTAÑA 1: ARENA -->
        <section id="view-eventos" class="view-section active" data-index="0">
            @if(Auth::check() && Auth::user()->hasAnyRole(['admin', 'juez']))
            <div class="mb-4">
                <button onclick="abrirModalEventManager()" class="w-full bg-cyan-900/40 hover:bg-cyan-800 border border-cyan-500 text-cyan-400 hover:text-white font-bold py-3 rounded-lg text-sm tracking-widest transition-all shadow-[0_0_15px_rgba(6,182,212,0.2)]">
                    <i class="fas fa-plus-circle mr-2"></i> ORGANIZAR EVENTO
                </button>
            </div>
            @endif

            <div class="flex justify-between items-center mb-3 border-b border-cyan-500/50 pb-2">
                <h2 class="text-2xl font-bold text-white pl-2 font-mono"><i class="fas fa-crosshairs text-cyan-400 mr-2"></i>ARENA</h2>
                <button onclick="refrescarPestanaActual('eventos', this)" class="bg-gray-900 hover:bg-gray-800 active:scale-90 text-cyan-400 border border-cyan-500/40 px-3 py-1.5 rounded-lg text-[10px] font-black tracking-widest uppercase flex items-center gap-1.5 transition-all shadow-sm">
                    <i class="fas fa-sync-alt"></i> ACTUALIZAR
                </button>
            </div>

            <!-- Selectores de Tipo y Región -->
            <div class="flex gap-2 mb-2.5">
                <div class="relative flex-1">
                    <select id="filtro-tipo" onchange="cargarEventos()" class="w-full bg-gray-900 border border-gray-700 text-cyan-400 text-[10px] font-bold tracking-widest p-2.5 rounded-lg appearance-none outline-none focus:border-cyan-400 shadow-inner transition-colors">
                        <option value="">TODOS LOS TIPOS</option>
                        <option value="ranking">RANKING</option>
                        <option value="quedada">QUEDADAS</option>
                        <option value="copaespecial1">COPA SOLO CX</option>
                        <option value="crew_battle">TORNEOS DE EQUIPOS</option>
                        @if(Auth::check() && Auth::user()->hasAnyRole(['admin', 'juez', 'arbitro', 'revisor']))
                            <option value="express">DUELOS EXPRESS (STAFF)</option>
                        @endif
                        <option value="historial">FINALIZADOS</option>
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

            <!-- NUEVO: FILTROS PERSONALES RÁPIDOS (APUNTADO / JUEZ) -->
            <input type="hidden" id="filtro-personal" value="">
            <div class="flex gap-1.5 mb-4 overflow-x-auto no-scrollbar pb-1">
                <button type="button" id="btn-pers-all" onclick="setFiltroPersonal('')" class="flex-1 py-2 px-2.5 rounded-lg border text-[10px] font-black tracking-wider uppercase transition-all bg-cyan-900/50 border-cyan-400 text-cyan-300 shadow-[0_0_10px_rgba(6,182,212,0.2)] whitespace-nowrap">
                    <i class="fas fa-globe-europe mr-1"></i> TODOS
                </button>
                <button type="button" id="btn-pers-inscrito" onclick="setFiltroPersonal('inscrito')" class="flex-1 py-2 px-2.5 rounded-lg border text-[10px] font-bold tracking-wider uppercase transition-all bg-gray-900 border-gray-800 text-gray-400 hover:text-white whitespace-nowrap">
                    <i class="fas fa-user-check text-cyan-400 mr-1"></i> APUNTADO
                </button>
                @if(Auth::check() && Auth::user()->hasAnyRole(['admin', 'juez', 'arbitro', 'revisor']))
                <button type="button" id="btn-pers-juez" onclick="setFiltroPersonal('juez')" class="flex-1 py-2 px-2.5 rounded-lg border text-[10px] font-bold tracking-wider uppercase transition-all bg-gray-900 border-gray-800 text-gray-400 hover:text-white whitespace-nowrap">
                    <i class="fas fa-gavel text-yellow-500 mr-1"></i> COMO JUEZ
                </button>
                @endif
            </div>

            <div id="lista-eventos" class="space-y-4 pb-4"></div>
        </section>

        <!-- PESTAÑA 2: RANKING -->
        <section id="view-ranking" class="view-section" data-index="1">
            <div class="flex justify-between items-center mb-2 border-b border-yellow-500/50 pb-2">
                <div class="flex items-center gap-2">
                    <h2 class="text-2xl font-bold text-white pl-2 font-mono"><i class="fas fa-trophy text-yellow-500 mr-2"></i>RANKING</h2>
                    <span class="text-[9px] bg-yellow-900/30 text-yellow-300 px-2 py-0.5 rounded border border-yellow-500/30 font-bold">LIGA S3</span>
                </div>
                <button onclick="refrescarPestanaActual('ranking', this)" class="bg-gray-900 hover:bg-gray-800 active:scale-90 text-yellow-400 border border-yellow-500/40 px-3 py-1.5 rounded-lg text-[10px] font-black tracking-widest uppercase flex items-center gap-1.5 transition-all shadow-sm">
                    <i class="fas fa-sync-alt"></i> ACTUALIZAR
                </button>
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
                <div class="flex items-center gap-2">
                    <h2 class="text-2xl font-bold text-white pl-2 font-mono"><i class="fas fa-fist-raised text-red-500 mr-2"></i>DUELOS</h2>
                    <span class="text-[9px] bg-red-900/30 text-red-300 px-2 py-0.5 rounded border border-red-500/30 font-bold">3V3</span>
                </div>
                <button onclick="refrescarPestanaActual('duelos', this)" class="bg-gray-900 hover:bg-gray-800 active:scale-90 text-red-400 border border-red-500/40 px-3 py-1.5 rounded-lg text-[10px] font-black tracking-widest uppercase flex items-center gap-1.5 transition-all shadow-sm">
                    <i class="fas fa-sync-alt"></i> ACTUALIZAR
                </button>
            </div>
            <button onclick="abrirModalDuelo()" class="w-full bg-red-900/40 hover:bg-red-800 border border-red-500 text-white font-bold py-3 rounded-lg text-sm tracking-widest transition-all shadow-[0_0_15px_rgba(239,68,68,0.2)] mb-4">
                <i class="fas fa-bolt text-yellow-400 mr-2"></i> DECLARAR DUELO
            </button>
            <div id="lista-duelos" class="space-y-3 pb-4"></div>
        </section>

        <!-- PESTAÑA 4: EL META -->
        <!-- PESTAÑA 4: EL META + BEY-LAB -->
        <section id="view-meta" class="view-section" data-index="3">
            <div class="flex justify-between items-center mb-3 border-b border-purple-500/50 pb-2">
                <h2 class="text-2xl font-bold text-white pl-2 font-mono"><i class="fas fa-flask text-purple-400 mr-2"></i>META & LAB</h2>
                <button onclick="refrescarPestanaActual('meta', this)" class="bg-gray-900 hover:bg-gray-800 active:scale-90 text-purple-400 border border-purple-500/40 px-3 py-1.5 rounded-lg text-[10px] font-black tracking-widest uppercase flex items-center gap-1.5 transition-all shadow-sm">
                    <i class="fas fa-sync-alt"></i> ACTUALIZAR
                </button>
            </div>

            <!-- Conmutador Ranking Meta vs Bey-Lab -->
            <div class="flex mb-4 bg-gray-900 border border-gray-800 rounded-xl p-1">
                <button type="button" id="tab-meta-list" onclick="switchMetaSubTab('list')" class="flex-1 text-center text-xs font-black tracking-widest py-2 rounded-lg bg-purple-900/60 text-purple-300 border border-purple-500/50 transition-all">
                    <i class="fas fa-chart-line mr-1"></i> TIER LIST
                </button>
                <button type="button" id="tab-meta-lab" onclick="switchMetaSubTab('lab')" class="flex-1 text-center text-xs font-bold tracking-widest py-2 rounded-lg text-gray-400 hover:text-white transition-all">
                    <i class="fas fa-vial mr-1 text-cyan-400"></i> BEY-LAB (SIMULADOR)
                </button>
            </div>

            <!-- SUB-VISTA A: TIER LIST ACTUAL -->
            <div id="subview-meta-list" class="block">
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
                <div id="lista-meta" class="space-y-3 pb-4"></div>
            </div>

            <!-- SUB-VISTA B: SBBL BEY-LAB (SIMULADOR DE DECKS Y MATCHUPS) -->
            <!-- SUB-VISTA B: SBBL BEY-LAB (DISEÑO LIMPIO Y ESTRUCTURADO) -->
            <div id="subview-meta-lab" class="hidden space-y-4 pb-8">
                
                <!-- Selector de Mesa de Trabajo (Tu Deck vs Deck Rival) -->
                <div class="bg-gray-900/90 border border-gray-800 rounded-2xl overflow-hidden shadow-xl">
                    <div class="grid grid-cols-2 border-b border-gray-800 bg-gray-950">
                        <button type="button" id="btn-workbench-my" onclick="switchLabWorkbench('my')" class="py-3 px-3 text-left border-b-2 border-cyan-400 bg-cyan-950/20 transition-all">
                            <span class="block text-[9px] font-mono uppercase tracking-widest text-cyan-400 font-bold">BANCO 01</span>
                            <span class="text-xs font-black text-white uppercase">TU ALINEACIÓN</span>
                        </button>
                        <button type="button" id="btn-workbench-rival" onclick="switchLabWorkbench('rival')" class="py-3 px-3 text-left border-b-2 border-transparent text-gray-500 hover:text-gray-300 transition-all">
                            <span class="block text-[9px] font-mono uppercase tracking-widest text-red-400/80 font-bold">BANCO 02</span>
                            <span class="text-xs font-black uppercase">DECK RIVAL / META</span>
                        </button>
                    </div>

                    <!-- Panel Banco 01: Tu Deck -->
                    <div id="workbench-my" class="p-3.5 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] text-gray-400 font-medium">Configura hasta 3 combos para evaluar su rendimiento</span>
                            <button type="button" onclick="importarUltimoDeckAlLab()" class="bg-gray-800 hover:bg-gray-700 text-cyan-300 border border-gray-700 px-2.5 py-1 rounded-lg text-[10px] font-bold transition-colors shrink-0">
                                Importar mi Deck
                            </button>
                        </div>
                        <div id="lab-my-deck-inputs" class="space-y-2.5"></div>
                        <div id="lab-legality-alert" class="hidden p-2.5 rounded-lg bg-red-950/50 border border-red-500/50 text-red-300 text-[10px] font-bold text-center"></div>
                    </div>

                    <!-- Panel Banco 02: Deck Rival -->
                    <div id="workbench-rival" class="hidden p-3.5 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] text-gray-400 font-medium">Elige un rival a medida o carga el Top 3 más usado</span>
                            <button type="button" onclick="cargarMetaBossEnLab()" class="bg-gray-800 hover:bg-gray-700 text-amber-300 border border-gray-700 px-2.5 py-1 rounded-lg text-[10px] font-bold transition-colors shrink-0">
                                Cargar Top 3 Meta
                            </button>
                        </div>
                        <div id="lab-rival-deck-inputs" class="space-y-2.5"></div>
                    </div>

                    <!-- Barra Inferior de Acción -->
                    <div class="p-3 bg-gray-950 border-t border-gray-800/80">
                        <button type="button" onclick="ejecutarSimulacionLab()" id="btnEjecutarLab" class="w-full bg-cyan-500 hover:bg-cyan-400 text-gray-950 font-black py-3 rounded-xl text-xs tracking-widest uppercase transition-all active:scale-[0.99] shadow-md">
                            ANALIZAR ENFRENTAMIENTO
                        </button>
                    </div>
                </div>

                <!-- Contenedor de Resultados Estructurados -->
                <div id="lab-results-container" class="space-y-4"></div>
            </div>
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
    @include('hub.modals.event_manager')

    @include('hub.scripts')

</body>
</html>