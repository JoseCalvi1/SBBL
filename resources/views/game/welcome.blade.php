@extends('layouts.game')

@section('title', 'Inicio')

@section('content')

{{-- ============================================================== --}}
    {{-- 🎬 INTRODUCCIÓN CINEMÁTICA (CORREGIDO SCROLL MÓVIL) --}}
    {{-- ============================================================== --}}
    {{-- CAMBIO 1: Añadido 'overflow-y-auto' y quitado 'justify-center items-center' del contenedor padre --}}
    <div id="agent-intro-overlay" class="fixed inset-0 z-[9999] bg-black font-mono select-none hidden overflow-y-auto">

        {{-- CAMBIO 2: Wrapper flexible que permite scroll si el contenido es alto --}}
        <div class="min-h-screen w-full flex flex-col items-center justify-center py-12 relative">

            {{-- Fondo con Scanlines --}}
            <div class="absolute inset-0 pointer-events-none z-0 opacity-20" style="background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.06), rgba(0, 255, 0, 0.02), rgba(0, 0, 255, 0.06)); background-size: 100% 2px, 3px 100%;"></div>

            {{-- Círculo de Carga ISAC --}}
            {{-- CAMBIO 3: Reducido tamaño en móvil (w-24) vs escritorio (md:w-32) --}}
            <div class="relative w-24 h-24 md:w-32 md:h-32 mb-6 md:mb-8 z-10 shrink-0">
                <div class="absolute inset-0 border-4 border-orange-900/40 rounded-full"></div>
                <div class="absolute inset-0 border-t-4 border-orange-500 rounded-full animate-spin"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-orange-500 text-3xl md:text-4xl font-black animate-pulse">⚠️</span>
                </div>
            </div>

            {{-- Contenedor de Texto Narrativo --}}
            <div class="max-w-xl w-full px-6 md:px-8 z-10 relative">
                {{-- Caja de texto --}}
                {{-- CAMBIO 4: Altura mínima ajustada para móvil (180px) --}}
                <div id="intro-terminal-text" class="text-orange-500 text-sm md:text-lg leading-relaxed font-mono font-bold tracking-widest min-h-[180px] md:min-h-[200px] drop-shadow-[0_0_5px_rgba(249,115,22,0.8)] border-l-2 border-orange-500/50 pl-4 md:pl-6 flex flex-col justify-center">
                    </div>

                {{-- Barra de Estado --}}
                <div id="intro-status-bar" class="mt-4 border-t border-orange-900/50 pt-2 flex justify-between text-[10px] md:text-xs text-orange-700 uppercase tracking-widest hidden">
                    <span>RED SBBL: <span class="text-orange-500 animate-pulse">REINICIANDO...</span></span>
                    <span class="truncate max-w-[100px] md:max-w-none">AGENTE: {{ Auth::user()->name ?? 'UNKNOWN' }}</span>
                    <span>ESTADO: ACTIVADO</span>
                </div>
            </div>

            {{-- Botón Acceder --}}
            {{-- CAMBIO 5: Margen inferior extra para que no se pegue al borde en móviles --}}
            <button id="btn-enter-system" onclick="closeIntro()" class="z-10 mt-8 md:mt-12 mb-8 px-8 md:px-10 py-3 border border-orange-500 text-orange-500 hover:bg-orange-500 hover:text-black uppercase font-bold tracking-[0.2em] md:tracking-[0.3em] transition-all hidden animate-pulse shadow-[0_0_20px_rgba(249,115,22,0.4)] text-sm md:text-base">
                ACCEDER AL SISTEMA
            </button>
        </div>
    </div>
    {{-- ============================================================== --}}
    {{-- ============================================================== --}}


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

            {{-- BOTÓN PARA REPETIR LA HISTORIA --}}
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

    {{-- MODAL TUTORIAL --}}
    <div id="tutorial-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300">
        <div class="relative w-full max-w-3xl bg-gray-900 border border-cyan-500/50 shadow-[0_0_50px_rgba(34,211,238,0.2)] p-1 m-4">

            <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-cyan-500"></div>
            <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-cyan-500"></div>
            <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-cyan-500"></div>
            <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-cyan-500"></div>

            <div class="bg-black/80 p-6 md:p-10 text-left">

                <div class="flex justify-between items-start mb-8 border-b border-gray-800 pb-4">
                    <div>
                        <h3 class="text-2xl font-black text-white italic">PROTOCOLO DE GUERRA</h3>
                        <p class="text-xs text-cyan-500 tracking-widest uppercase">Instrucciones para nuevos reclutas</p>
                    </div>
                    <button onclick="toggleTutorial()" class="text-gray-500 hover:text-white text-2xl font-bold">&times;</button>
                </div>

                <div class="grid md:grid-cols-2 gap-8">
                    <div class="flex gap-4">
                        <div class="text-3xl">🗺️</div>
                        <div>
                            <h4 class="text-cyan-400 font-bold text-sm uppercase mb-1">1. Elige tu Objetivo</h4>
                            <p class="text-gray-400 text-xs leading-relaxed">
                                Selecciona una provincia en el mapa. Si es gris, es neutral. Si es roja, pertenece al enemigo.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="text-3xl">⚔️</div>
                        <div>
                            <h4 class="text-yellow-400 font-bold text-sm uppercase mb-1">2. Ordena el Ataque</h4>
                            <p class="text-gray-400 text-xs leading-relaxed">
                                Usa el botón "ORDENAR ATAQUE". Tu voto se suma al de tus compañeros de equipo en esa zona.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="text-3xl">💪</div>
                        <div>
                            <h4 class="text-red-400 font-bold text-sm uppercase mb-1">3. Tu Daño = Tus Puntos</h4>
                            <p class="text-gray-400 text-xs leading-relaxed">
                                Aquí no vale solo con votar. <strong>Cuantos más puntos ganes en torneos semanales y más puntos tenga tu equipo, más fuerte será tu golpe.</strong>
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="text-3xl">⏱️</div>
                        <div>
                            <h4 class="text-white font-bold text-sm uppercase mb-1">4. Ciclo de Guerra (Quincenal)</h4>
                            <p class="text-gray-400 text-xs leading-relaxed">
                                La guerra es de larga duración. Los turnos duran <strong class="text-cyan-400">2 SEMANAS</strong>.<br>
                                <strong class="text-green-400">DÍAS 1-13:</strong> Fase de Desgaste (Acumula daño).<br>
                                <strong class="text-red-400">DOMINGO FINAL:</strong> El mapa se resuelve cada <strong>2 Domingos</strong> (Semanas Pares) a las 23:59.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 text-center">
                    <button onclick="toggleTutorial()" class="bg-cyan-900/30 border border-cyan-500 text-cyan-300 px-8 py-2 text-xs font-bold uppercase hover:bg-cyan-500 hover:text-black transition-colors">
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
            // Comprobamos si el usuario ya ha visto la intro
            const hasSeenIntro = localStorage.getItem('intro_seen_v1');

            // Si NO la ha visto, la ejecutamos automáticamente
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

            // Limpiar estado
            textContainer.innerHTML = "";
            statusLine.classList.add('hidden');
            btn.classList.add('hidden');

            // --- EL GUIÓN DE LA HISTORIA ---
            const lines = [
                // Línea 1
                "<span class='text-gray-500'>[REPORTE: DÍA 180 TRAS EL COLAPSO]</span>",

                // Línea 2
                "LA RED CENTRAL HA SIDO <span class='text-red-500'>DESTRUIDA</span>.",

                // Línea 3
                "LA SOCIEDAD SE HA FRACTURADO EN FACCIONES.",
                "SOLO LA FUERZA BRUTA DICTA LA LEY AHORA.",

                // Línea 4
                "<span class='animate-pulse'>DETECTANDO SEÑAL DEL AGENTE...</span>",

                // Línea 5
                "IDENTIDAD CONFIRMADA. ACCESO AL <span class='text-yellow-400'>MERCADO NEGRO</span> AUTORIZADO.",

                // Línea 6
                "MISIÓN: RECUPERAR EL CONTROL DE LOS NODOS."
            ];

            let lineIndex = 0;
            let currentHTML = "";

            function printLine() {
                if (lineIndex < lines.length) {
                    const fullLine = lines[lineIndex];

                    // Añadimos la línea + cursor parpadeante
                    textContainer.innerHTML = currentHTML + fullLine + '<span class="animate-pulse">_</span>';

                    // Guardamos la línea en el histórico para no perderla en la siguiente vuelta
                    currentHTML += fullLine + "<br><br>"; // Doble salto para espacio

                    lineIndex++;

                    // Tiempo de lectura variable para hacerlo natural
                    let readingTime = 1500;
                    if(lineIndex === 1) readingTime = 800;
                    if(lineIndex === 4) readingTime = 2000; // Suspenso en "Detectando..."

                    setTimeout(printLine, readingTime);

                } else {
                    // FIN: Mostrar "Sistema en línea"
                    textContainer.innerHTML = currentHTML + '<span class="text-green-500 text-xl tracking-[0.5em] animate-pulse">SISTEMA EN LÍNEA</span>';

                    setTimeout(() => {
                        statusLine.classList.remove('hidden');
                        btn.classList.remove('hidden');
                    }, 500);
                }
            }

            // Iniciar secuencia con pequeña espera
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

            // Guardar que ya lo vio
            localStorage.setItem('intro_seen_v1', 'true');
        }

        // ==========================================
        // 🛠️ TUS SCRIPTS ORIGINALES
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
