@extends('layouts.game')

@section('title', 'Inicio')

@section('content')

{{-- ============================================================== --}}
{{-- 🎬 INTRODUCCIÓN CINEMÁTICA --}}
{{-- ============================================================== --}}
<div id="agent-intro-overlay" class="fixed inset-0 z-[9999] bg-black font-mono select-none hidden overflow-y-auto">
    <div class="min-h-screen w-full flex flex-col items-center justify-center py-12 relative">
        <div class="absolute inset-0 pointer-events-none z-0 opacity-20" style="background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.06), rgba(0, 255, 0, 0.02), rgba(0, 0, 255, 0.06)); background-size: 100% 2px, 3px 100%;"></div>

        <div class="relative w-24 h-24 md:w-32 md:h-32 mb-6 md:mb-8 z-10 shrink-0">
            <div class="absolute inset-0 border-4 border-orange-900/40 rounded-full"></div>
            <div class="absolute inset-0 border-t-4 border-orange-500 rounded-full animate-spin"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-orange-500 text-3xl md:text-4xl font-black animate-pulse">⚠️</span>
            </div>
        </div>

        <div class="max-w-xl w-full px-6 md:px-8 z-10 relative">
            <div id="intro-terminal-text" class="text-orange-500 text-sm md:text-lg leading-relaxed font-mono font-bold tracking-widest min-h-[180px] md:min-h-[200px] drop-shadow-[0_0_5px_rgba(249,115,22,0.8)] border-l-2 border-orange-500/50 pl-4 md:pl-6 flex flex-col justify-center"></div>
            <div id="intro-status-bar" class="mt-4 border-t border-orange-900/50 pt-2 flex justify-between text-[10px] md:text-xs text-orange-700 uppercase tracking-widest hidden">
                <span>RED SBBL: <span class="text-orange-500 animate-pulse">REINICIANDO...</span></span>
                <span class="truncate max-w-[100px] md:max-w-none">AGENTE: {{ Auth::user()->name ?? 'UNKNOWN' }}</span>
                <span>ESTADO: ACTIVADO</span>
            </div>
        </div>

        <button id="btn-enter-system" onclick="closeIntro()" class="z-10 mt-8 md:mt-12 mb-8 px-8 md:px-10 py-3 border border-orange-500 text-orange-500 hover:bg-orange-500 hover:text-black uppercase font-bold tracking-[0.2em] md:tracking-[0.3em] transition-all hidden animate-pulse shadow-[0_0_20px_rgba(249,115,22,0.4)] text-sm md:text-base">
            ACCEDER AL SISTEMA
        </button>
    </div>
</div>
{{-- ============================================================== --}}

@if(Auth::id() == 1) {{-- Solo visible para el Admin --}}
    <div class="fixed bottom-4 right-4 z-50">
        <form action="{{ route('admin.force_resolve') }}" method="POST" onsubmit="return confirm('⚠️ ¿ESTÁS SEGURO?\n\nEsto resolverá el turno, cambiará las zonas y gastará los buffs.\n\n¿Proceder?');">
            @csrf
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-full shadow-[0_0_20px_red] border-2 border-red-400 animate-pulse flex items-center gap-2">
                ☢️ FORZAR TURNO
            </button>
        </form>
    </div>
@endif

    {{-- UI PRINCIPAL DEL JUEGO --}}
    <div class="mb-8 mt-10 animate-pulse">
        @if(now()->isWeekend())
            <span class="bg-red-900/50 text-red-300 border border-red-500 px-6 py-2 rounded-full text-xs md:text-sm tracking-[0.2em] uppercase backdrop-blur-sm">
                ⛔ FASE DE CONQUISTA (VOTACIÓN CERRADA)
            </span>
        @else
            <span class="bg-green-900/50 text-green-300 border border-green-500 px-6 py-2 rounded-full text-xs md:text-sm tracking-[0.2em] uppercase backdrop-blur-sm">
                🟢 FASE DE VOTACIÓN: ACTIVA
            </span>
        @endif
    </div>

    {{-- Título y Badge --}}
    <div class="relative inline-block mb-4">
        <h1 class="text-5xl md:text-8xl font-black tracking-tighter neon-text relative z-10">
            CONQUEST
        </h1>
        <span class="absolute -top-5 right-50 md:-top-5 md:right-50 bg-yellow-500/10 border border-yellow-500 text-yellow-400 text-[10px] md:text-xs px-2 py-0.5 rounded font-mono font-bold tracking-widest transform -rotate-6 backdrop-blur-sm shadow-[0_0_10px_rgba(234,179,8,0.3)] animate-pulse whitespace-nowrap pointer-events-none">
            BETA v1.0
        </span>
    </div>

    <h2 class="text-lg md:text-2xl text-gray-400 tracking-[0.6em] mb-12 uppercase">
        SPANISH BEYBATTLE LEAGUE
    </h2>

    <div class="flex flex-col items-center gap-4">
        @auth
            <div class="text-cyan-300 mb-2 text-sm tracking-widest">
                BIENVENIDO, AGENTE <span class="font-bold text-white">{{ strtoupper(Auth::user()->name) }}</span>
            </div>

            <a href="{{ route('conquest.map') }}" class="btn-cyber px-12 py-5 text-xl font-bold uppercase tracking-widest border border-cyan-500 hover:bg-cyan-900/50 transition-all shadow-[0_0_15px_rgba(0,255,255,0.3)]">
                ENTRAR AL MAPA
            </a>
        @else
            <p class="text-gray-400 text-sm mb-2">Identifícate para conquistar territorios</p>
            <a href="{{ url('/login') }}" class="btn-cyber px-10 py-4 text-lg font-bold border border-yellow-500 text-yellow-400 shadow-[0_0_10px_rgba(234,179,8,0.3)] hover:bg-yellow-500/20 hover:text-white transition-all">
                INICIAR SESIÓN
            </a>
        @endauth

        <div class="flex gap-6 mt-4">
            <button onclick="toggleTutorial()" class="text-xs text-gray-500 hover:text-cyan-400 tracking-widest uppercase border-b border-transparent hover:border-cyan-400 transition-all">
                [ ? ] MANUAL DE COMBATE
            </button>

            <button onclick="playIntro(true)" class="text-xs text-orange-700 hover:text-orange-500 tracking-widest uppercase border-b border-transparent hover:border-orange-500 transition-all">
                [ ⚠️ ] REPRODUCIR PROTOCOLO
            </button>
        </div>
    </div>

    <div class="mt-20 grid grid-cols-3 gap-8 text-cyan-500/60 text-xs md:text-sm border-t border-cyan-500/20 pt-8 max-w-2xl mx-auto">
        <div class="group cursor-default">
            <p class="text-2xl font-bold text-white mb-1 group-hover:text-cyan-400 transition-colors">
                {{ $zonesCount }}
            </p>
            <p class="uppercase tracking-widest">Nodos Activos</p>
        </div>
        <div class="group cursor-default">
            <p class="text-2xl font-bold text-white mb-1 group-hover:text-cyan-400 transition-colors">
                {{ $bladersCount }}
            </p>
            <p class="uppercase tracking-widest">Agentes</p>
        </div>
        <div class="group cursor-default">
            <p class="text-2xl font-bold text-white mb-1 group-hover:text-red-400 transition-colors">
                DOM <span class="text-xs opacity-50">{{ $nextClose }}</span>
            </p>
            <p class="uppercase tracking-widest">Reinicio Red</p>
        </div>
    </div>

    {{-- ============================================================== --}}
    {{-- 📘 NUEVO MANUAL DE COMBATE --}}
    {{-- ============================================================== --}}
    <div id="tutorial-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300 overflow-y-auto py-8">

        <div class="relative w-full max-w-4xl bg-gray-900 border border-cyan-500/50 shadow-[0_0_50px_rgba(34,211,238,0.2)] p-1 m-4">

            {{-- Decoración Cyberpunk (Esquinas) --}}
            <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-cyan-500"></div>
            <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-cyan-500"></div>
            <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-cyan-500"></div>
            <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-cyan-500"></div>

            <div class="bg-black/90 p-6 md:p-8 text-left max-h-[85vh] overflow-y-auto custom-scrollbar">

                {{-- CABECERA MODAL --}}
                <div class="flex justify-between items-start mb-6 border-b border-gray-800 pb-4">
                    <div>
                        <h1 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-red-600 uppercase tracking-widest">
                            MANUAL DE GUERRA
                        </h1>
                        <p class="mt-1 text-xs text-cyan-500 tracking-[0.2em] uppercase">Protocolo de conquista v2.0</p>
                    </div>
                    <button onclick="toggleTutorial()" class="text-gray-500 hover:text-white text-3xl font-bold transition-colors">&times;</button>
                </div>

                {{-- CONTENIDO DEL MANUAL --}}
                <div class="space-y-6">

                    <section class="bg-gray-800/50 p-5 rounded border-l-4 border-blue-500">
                        <h2 class="text-lg font-bold text-white mb-2 flex items-center uppercase tracking-wide">
                            <span class="text-2xl mr-2">🗳️</span> ¿Cómo participo?
                        </h2>
                        <p class="text-gray-300 text-sm leading-relaxed">
                            Cada ciclo (2 semanas), tienes <strong>un voto militar</strong>. Debes entrar al mapa y elegir qué territorio enemigo quieres <strong>ATACAR</strong>.
                        </p>
                        <ul class="mt-2 list-disc list-inside text-gray-400 text-xs space-y-1">
                            <li>Solo puedes atacar una zona por turno.</li>
                            <li><strong>La resolución es automática:</strong> El domingo de cierre a las 23:59h.</li>
                        </ul>
                    </section>

                    <section class="bg-gray-800/50 p-5 rounded border-l-4 border-green-500">
                        <h2 class="text-lg font-bold text-white mb-2 flex items-center uppercase tracking-wide">
                            <span class="text-2xl mr-2">💪</span> Cálculo de Fuerza
                        </h2>
                        <p class="text-gray-300 text-sm mb-3">
                            Tu poder no es fijo. Depende de tu habilidad y de tu equipo.
                        </p>

                        <div class="bg-black p-3 rounded border border-gray-700 font-mono text-xs md:text-sm text-yellow-300 text-center mb-3">
                            PODER = (10 Base + Puntos Torneo) x (1 + Ranking/100)
                        </div>

                        <div class="grid md:grid-cols-2 gap-4 text-xs">
                            <div>
                                <strong class="text-white block mb-1">1. Puntos de Torneo:</strong>
                                <span class="text-gray-400">Puntos por tu posición en torneos de las últimas 2 semanas (+7 al 1º, +1 por participar).</span>
                            </div>
                            <div>
                                <strong class="text-white block mb-1">2. Bonus de Rango:</strong>
                                <span class="text-gray-400">El prestigio de tu equipo multiplica tu daño. <br><em>Ej: 50 Puntos de Ranking = +50% de Daño (x1.5).</em></span>
                            </div>
                        </div>
                    </section>

                    <section class="grid md:grid-cols-2 gap-4">
                        <div class="bg-red-900/20 p-4 rounded border border-red-900/50">
                            <h3 class="text-base font-bold text-red-400 mb-2 uppercase">⚔️ El Ataque</h3>
                            <p class="text-gray-400 text-xs leading-relaxed">
                                Tu poder se suma a la zona que votaste. Si tu equipo se divide atacando zonas distintas, vuestra fuerza se dividirá. ¡La coordinación es clave!
                            </p>
                        </div>

                        <div class="bg-blue-900/20 p-4 rounded border border-blue-900/50">
                            <h3 class="text-base font-bold text-blue-400 mb-2 uppercase">🛡️ Defensa Global</h3>
                            <p class="text-gray-400 text-xs leading-relaxed">
                                <strong>No necesitas votar para defender.</strong><br>
                                La "Vida" de vuestras zonas es la suma del poder de <strong>TODOS</strong> los miembros activos.<br>
                                <em class="text-blue-300 block mt-1">¡Si atacas, también estás defendiendo tu casa!</em>
                            </p>
                        </div>
                    </section>

                    <section class="bg-gradient-to-r from-purple-900/40 to-indigo-900/40 p-5 rounded border border-purple-500/30">
                        <h2 class="text-lg font-bold text-white mb-3 uppercase tracking-wide">✨ Factores Críticos</h2>

                        <div class="grid md:grid-cols-2 gap-4 text-xs">
                            <div class="flex items-start">
                                <span class="text-xl mr-2">🍀</span>
                                <div>
                                    <strong class="text-yellow-400 block mb-1">MVP Defensivo (x2)</strong>
                                    <p class="text-gray-400">
                                        Cada turno, un miembro aleatorio de cada equipo recibe un bonus <strong>x2 SOLO EN DEFENSA</strong>. ¡Un solo soldado puede salvar la base!
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <span class="text-xl mr-2">🛒</span>
                                <div>
                                    <strong class="text-green-400 block mb-1">Mercado Negro</strong>
                                    <p class="text-gray-400">
                                        Comprad <strong>Buffs de Ataque/Defensa</strong> con oro del equipo para multiplicar vuestras estadísticas este turno.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                {{-- PIE DE PÁGINA MODAL --}}
                <div class="mt-8 text-center pt-4 border-t border-gray-800">
                    <button onclick="toggleTutorial()" class="bg-cyan-900/30 border border-cyan-500 text-cyan-300 px-10 py-3 text-sm font-bold uppercase hover:bg-cyan-500 hover:text-black transition-all tracking-widest shadow-[0_0_15px_rgba(6,182,212,0.2)]">
                        ENTENDIDO, COMANDANTE
                    </button>
                </div>

            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        // ==========================================
        // 🎞️ SCRIPT DE LA HISTORIA (LORE)
        // ==========================================
        document.addEventListener("DOMContentLoaded", function() {
            const hasSeenIntro = localStorage.getItem('intro_seen_v1');
            if (!hasSeenIntro) {
                playIntro(false);
            }
        });

        function playIntro(force = false) {
            const overlay = document.getElementById('agent-intro-overlay');
            const textContainer = document.getElementById('intro-terminal-text');
            const statusLine = document.getElementById('intro-status-bar');
            const btn = document.getElementById('btn-enter-system');

            overlay.classList.remove('hidden');
            textContainer.innerHTML = "";
            statusLine.classList.add('hidden');
            btn.classList.add('hidden');

            const lines = [
                "<span class='text-gray-500'>[REPORTE: DÍA 180 TRAS EL COLAPSO]</span>",
                "LA RED CENTRAL HA SIDO <span class='text-red-500'>DESTRUIDA</span>.",
                "LA SOCIEDAD SE HA FRACTURADO EN FACCIONES.",
                "SOLO LA FUERZA BRUTA DICTA LA LEY AHORA.",
                "<span class='animate-pulse'>DETECTANDO SEÑAL DEL AGENTE...</span>",
                "IDENTIDAD CONFIRMADA. ACCESO AL <span class='text-yellow-400'>MERCADO NEGRO</span> AUTORIZADO.",
                "MISIÓN: RECUPERAR EL CONTROL DE LOS NODOS."
            ];

            let lineIndex = 0;
            let currentHTML = "";

            function printLine() {
                if (lineIndex < lines.length) {
                    const fullLine = lines[lineIndex];
                    textContainer.innerHTML = currentHTML + fullLine + '<span class="animate-pulse">_</span>';
                    currentHTML += fullLine + "<br><br>";
                    lineIndex++;
                    let readingTime = 1500;
                    if(lineIndex === 1) readingTime = 800;
                    if(lineIndex === 4) readingTime = 2000;
                    setTimeout(printLine, readingTime);
                } else {
                    textContainer.innerHTML = currentHTML + '<span class="text-green-500 text-xl tracking-[0.5em] animate-pulse">SISTEMA EN LÍNEA</span>';
                    setTimeout(() => {
                        statusLine.classList.remove('hidden');
                        btn.classList.remove('hidden');
                    }, 500);
                }
            }
            setTimeout(printLine, 1000);
        }

        function closeIntro() {
            const overlay = document.getElementById('agent-intro-overlay');
            overlay.style.transition = "opacity 0.8s ease-out";
            overlay.style.opacity = "0";
            setTimeout(() => {
                overlay.classList.add('hidden');
                overlay.style.opacity = "1";
            }, 800);
            localStorage.setItem('intro_seen_v1', 'true');
        }

        // ==========================================
        // 🛠️ SCRIPTS UI
        // ==========================================
        function toggleTutorial() {
            const modal = document.getElementById('tutorial-modal');
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
                setTimeout(() => modal.classList.remove('opacity-0'), 10);
            } else {
                modal.classList.add('opacity-0');
                setTimeout(() => modal.classList.add('hidden'), 300);
            }
        }

        document.addEventListener("DOMContentLoaded", () => {
            const body = document.querySelector('main') || document.body;
            const style = document.createElement('style');
            style.innerHTML = `
                .particle { position: absolute; background: white; border-radius: 50%; opacity: 0; pointer-events: none; animation: float 10s infinite; z-index: 0; }
                @keyframes float {
                    0% { transform: translateY(0) translateX(0); opacity: 0; }
                    20% { opacity: 0.3; }
                    100% { transform: translateY(-100vh) translateX(20px); opacity: 0; }
                }
                /* Scrollbar personalizada para el manual */
                .custom-scrollbar::-webkit-scrollbar { width: 8px; }
                .custom-scrollbar::-webkit-scrollbar-track { background: #111827; }
                .custom-scrollbar::-webkit-scrollbar-thumb { background: #0e7490; border-radius: 4px; }
                .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #22d3ee; }
            `;
            document.head.appendChild(style);

            for(let i=0; i<30; i++){
                let p = document.createElement("div");
                p.classList.add("particle");
                let size = Math.random() * 3 + 1 + "px";
                p.style.width = size;
                p.style.height = size;
                p.style.left = Math.random() * 100 + "vw";
                p.style.top = Math.random() * 100 + "vh";
                p.style.backgroundColor = Math.random() > 0.8 ? '#22d3ee' : '#ffffff';
                p.style.animationDuration = (Math.random() * 15 + 10) + "s";
                p.style.animationDelay = (Math.random() * 10) + "s";
                body.appendChild(p);
            }
        });
    </script>
@endsection
