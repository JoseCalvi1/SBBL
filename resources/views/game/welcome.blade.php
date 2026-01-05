@extends('layouts.game')

@section('title', 'Inicio')

@section('content')

    <div class="mb-8 animate-pulse">
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

    {{-- MODIFICACIÓN AQUÍ: Título y Badge alineado --}}
    <div class="relative inline-block mb-4">
        <h1 class="text-5xl md:text-8xl font-black tracking-tighter neon-text relative z-10">
            CONQUEST
        </h1>

        <span class="absolute -top-5 right-50 md:-top-5 md:right-50 bg-yellow-500/10 border border-yellow-500 text-yellow-400 text-[10px] md:text-xs px-2 py-0.5 rounded font-mono font-bold tracking-widest transform -rotate-6 backdrop-blur-sm shadow-[0_0_10px_rgba(234,179,8,0.3)] animate-pulse whitespace-nowrap pointer-events-none">
            BETA v1.0
        </span>
    </div>
    {{-- FIN MODIFICACIÓN --}}

    <h2 class="text-lg md:text-2xl text-gray-400 tracking-[0.6em] mb-12 uppercase">
        SPANISH BEYBATTLE LEAGUE
    </h2>

    <div class="flex flex-col items-center gap-4">
        @auth
            <div class="text-cyan-300 mb-2 text-sm tracking-widest">
                BIENVENIDO, BLADER <span class="font-bold text-white">{{ strtoupper(Auth::user()->name) }}</span>
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

        <button onclick="toggleTutorial()" class="mt-4 text-xs text-gray-500 hover:text-cyan-400 tracking-widest uppercase border-b border-transparent hover:border-cyan-400 transition-all">
            [ ? ] LEER MANUAL DE COMBATE
        </button>
    </div>

    <div class="mt-20 grid grid-cols-3 gap-8 text-cyan-500/60 text-xs md:text-sm border-t border-cyan-500/20 pt-8 max-w-2xl mx-auto">
        <div class="group cursor-default">
            <p class="text-2xl font-bold text-white mb-1 group-hover:text-cyan-400 transition-colors">
                {{ $zonesCount }}
            </p>
            <p class="uppercase tracking-widest">Zonas Activas</p>
        </div>
        <div class="group cursor-default">
            <p class="text-2xl font-bold text-white mb-1 group-hover:text-cyan-400 transition-colors">
                {{ $bladersCount }}
            </p>
            <p class="uppercase tracking-widest">Bladers</p>
        </div>
        <div class="group cursor-default">
            <p class="text-2xl font-bold text-white mb-1 group-hover:text-red-400 transition-colors">
                DOM <span class="text-xs opacity-50">{{ $nextClose }}</span>
            </p>
            <p class="uppercase tracking-widest">Próx. Cierre</p>
        </div>
    </div>

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
                            <h4 class="text-white font-bold text-sm uppercase mb-1">4. Horarios de Guerra</h4>
                            <p class="text-gray-400 text-xs leading-relaxed">
                                <strong class="text-green-400">LUNES - VIERNES:</strong> Fase de Votación (Ataca).<br>
                                <strong class="text-red-400">SÁBADO - DOMINGO:</strong> Fase de Conquista (Resolución).<br>
                                El mapa se resuelve automáticamente el Domingo a las 23:59.
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
        // Lógica del Tutorial Popup
        function toggleTutorial() {
            const modal = document.getElementById('tutorial-modal');

            if (modal.classList.contains('hidden')) {
                // Abrir
                modal.classList.remove('hidden');
                // Pequeño delay para permitir que la transición CSS funcione
                setTimeout(() => modal.classList.remove('opacity-0'), 10);
            } else {
                // Cerrar
                modal.classList.add('opacity-0');
                setTimeout(() => modal.classList.add('hidden'), 300);
            }
        }

        // Script de partículas (Ambiente War Zone)
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
