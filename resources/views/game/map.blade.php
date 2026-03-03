@extends('layouts.game')

@section('title', 'Mapa Táctico')

@section('content')

    @php
        $activeVote = \Illuminate\Support\Facades\DB::table('conquest_votes')
            ->join('zones', 'conquest_votes.zone_id', '=', 'zones.id')
            ->where('conquest_votes.user_id', Auth::id())
            ->orderByDesc('conquest_votes.created_at') // Cogemos el último voto por si acaso
            ->select('zones.slug')
            ->first();

        // Si tiene voto, guardamos el slug (ej: 'madrid'), si no, null
        $currentVoteSlug = $activeVote ? $activeVote->slug : null;
    @endphp

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Header: Ajustado padding y flex para móviles --}}
  <div class="mb-6 mt-5 flex flex-col md:flex-row justify-between items-end border-b border-cyan-500/30 pb-4 w-full">
        <div class="text-left w-full md:w-auto">
            <h2 class="text-3xl font-bold neon-text text-white">MAPA TÁCTICO</h2>
            <div class="flex gap-4 items-center flex-wrap">

                <p class="text-cyan-400 text-sm tracking-widest mr-2">SISTEMA ACTIVO</p>

                {{-- BOTÓN INICIO --}}
                <a href="{{ route('conquest.index') }}"
                   class="flex items-center gap-1 text-[10px] bg-gray-800/40 text-gray-400 px-2 py-1 border border-gray-600/30 hover:bg-gray-700 hover:text-white transition-colors">
                    <span>↩️</span> INICIO
                </a>

                {{-- BOTÓN REPORTES --}}
                <a href="{{ route('conquest.news') }}" class="flex items-center gap-1 text-[10px] bg-red-900/30 text-red-400 px-2 py-1 border border-red-500/30 hover:bg-red-900/60 transition-colors animate-pulse">
                    <span>📰</span> REPORTES
                </a>

                {{-- BOTÓN MERCADO --}}
                <a href="{{ route('market.index') }}"
                class="flex items-center gap-2 text-[10px] bg-purple-900/30 text-purple-300 px-3 py-1 border border-purple-500/50 hover:bg-purple-800/60 hover:text-white transition-all shadow-[0_0_10px_rgba(168,85,247,0.3)] group">
                    <span class="text-lg group-hover:rotate-12 transition-transform">🛒</span>
                    <div class="flex flex-col leading-none text-left">
                        <span class="font-bold tracking-widest">MERCADO</span>
                        <span class="text-[8px] text-purple-400 group-hover:text-purple-200">
                            {{ Auth::user()->coins }} COINS
                        </span>
                    </div>
                </a>

                {{-- ✨ NUEVO BOTÓN: ESTADÍSTICAS DE FACCIÓN ✨ --}}
                <button onclick="toggleFactionStats()"
                class="flex items-center gap-2 text-[10px] bg-blue-900/30 text-blue-300 px-3 py-1 border border-blue-500/50 hover:bg-blue-800/60 hover:text-white transition-all shadow-[0_0_10px_rgba(59,130,246,0.3)] group">
                    <span class="text-lg group-hover:scale-110 transition-transform">📊</span>
                    <div class="flex flex-col leading-none text-left">
                        <span class="font-bold tracking-widest uppercase text-yellow-500">Eras</span>
                        <span class="text-[8px] text-blue-400">ANIVERSARIO</span>
                    </div>
                </button>

            </div>
        </div>

        <div class="text-right hidden md:block">
            <span class="block text-xs text-gray-400 uppercase">Frecuencia</span>
            @if(Auth::user()->activeTeam)
                <span class="text-xl font-bold uppercase drop-shadow-md flex items-center justify-end gap-2"
                      style="color: {{ Auth::user()->activeTeam->color }}">
                    <span class="w-3 h-3 rounded-full animate-pulse bg-current"></span>
                    {{ Auth::user()->activeTeam->name }}
                </span>
            @else
                <span class="text-xl font-bold text-gray-500">MERCENARIO</span>
            @endif
        </div>
    </div>

    {{-- CONTENEDOR PRINCIPAL:
         - Quitamos 'h-full' fijo para evitar cortes en móvil.
         - Añadimos 'pb-20' para dar espacio al scroll final.
    --}}
    <div class="flex flex-col xl:flex-row gap-6 items-start relative justify-center w-full pb-20">
@if(Auth::check() && !Auth::user()->faction)
<div class="fixed inset-0 z-[9999] bg-black/95 flex items-center justify-center p-4">
    <div class="max-w-4xl w-full text-center">

        <h2 class="text-3xl font-black text-yellow-500 mb-4 italic uppercase drop-shadow-[0_0_10px_rgba(234,179,8,0.5)]">
             ELIGE TU ERA - ANIVERSARIO BEYBLADE
        </h2>

        {{-- 📜 TEXTO EXPLICATIVO DEL EVENTO --}}
        <div class="bg-gray-900/50 border border-cyan-500/30 p-4 md:p-6 mb-8 rounded-lg text-sm md:text-base text-gray-300 leading-relaxed shadow-lg">
            <p class="mb-3">
                Para celebrar el <strong class="text-white">mes aniversario de Beyblade</strong>, hemos activado este evento temporal durante marzo. Únete a una de las cuatro eras históricas para obtener <strong class="text-cyan-400">bonificaciones tácticas exclusivas</strong> en el Mapa Táctico.
            </p>
            <p class="mb-4">
                🏆 Al finalizar el mes, la facción con más agentes reclutados se alzará con la victoria y <strong class="text-yellow-400">desbloqueará un fondo especial para la web</strong> tematizado con su generación.
            </p>
            <div class="inline-block bg-red-900/30 border border-red-500/50 px-4 py-2 rounded text-red-300 text-xs md:text-sm uppercase tracking-widest font-bold animate-pulse">
                ⚠️ Lee bien los bonus: Solo tienes una oportunidad y la decisión es irreversible.
            </div>
        </div>

        {{-- BOTONES DE FACCIÓN CON CONFIRMACIÓN --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <form action="{{ route('faction.choose') }}" method="POST" onsubmit="return confirm('¿Estás 100% seguro de unirte a la era BAKUTEN? Esta decisión no se puede cambiar en todo el mes.');">
                @csrf
                <input type="hidden" name="faction" value="bakuten">
                <button type="submit" class="w-full p-4 border border-blue-500 bg-blue-900/20 hover:bg-blue-600 transition-all group">
                    <span class="text-4xl block mb-2">🐉</span>
                    <span class="font-bold block text-blue-400 group-hover:text-white">BAKUTEN</span>
                    <span class="text-[10px] text-gray-400">+2 ATK Fijo</span>
                </button>
            </form>

            <form action="{{ route('faction.choose') }}" method="POST" onsubmit="return confirm('¿Estás 100% seguro de unirte a la era METAL FIGHT? Esta decisión no se puede cambiar en todo el mes.');">
                @csrf
                <input type="hidden" name="faction" value="metal">
                <button type="submit" class="w-full p-4 border border-red-500 bg-red-900/20 hover:bg-red-600 transition-all group">
                    <span class="text-4xl block mb-2">⚙️</span>
                    <span class="font-bold block text-red-400 group-hover:text-white">METAL FIGHT</span>
                    <span class="text-[10px] text-gray-400">33% DEF x1.1</span>
                </button>
            </form>

            <form action="{{ route('faction.choose') }}" method="POST" onsubmit="return confirm('¿Estás 100% seguro de unirte a la era BURST? Esta decisión no se puede cambiar en todo el mes.');">
                @csrf
                <input type="hidden" name="faction" value="burst">
                <button type="submit" class="w-full p-4 border border-yellow-500 bg-yellow-900/20 hover:bg-yellow-600 transition-all group">
                    <span class="text-4xl block mb-2">💥</span>
                    <span class="font-bold block text-yellow-400 group-hover:text-white">BURST</span>
                    <span class="text-[10px] text-gray-400">20% ATK x1.2</span>
                </button>
            </form>

            <form action="{{ route('faction.choose') }}" method="POST" onsubmit="return confirm('¿Estás 100% seguro de unirte a la era BEYBLADE X? Esta decisión no se puede cambiar en todo el mes.');">
                @csrf
                <input type="hidden" name="faction" value="x">
                <button type="submit" class="w-full p-4 border border-green-500 bg-green-900/20 hover:bg-green-600 transition-all group">
                    <span class="text-4xl block mb-2">❌</span>
                    <span class="font-bold block text-green-400 group-hover:text-white">BEYBLADE X</span>
                    <span class="text-[10px] text-gray-400">Ruleta VIP</span>
                </button>
            </form>

        </div>

    </div>
</div>
@endif
        {{-- COLUMNA IZQUIERDA: Logs --}}
        @if(Auth::user()->active_team)
        {{-- He cambiado 'hidden xl:block' por 'w-full xl:w-64'.
             Ahora se ve en móvil arriba del mapa. Si prefieres que no se vea en móvil, déjalo como estaba. --}}
        <div class="w-full xl:w-64 flex-shrink-0 z-20 order-2 xl:order-1">
            <div class="bg-black/90 border-l-2 border-cyan-500 p-2 mb-1 backdrop-blur-sm pointer-events-auto">
                <h4 class="text-cyan-400 font-bold text-xs uppercase tracking-widest">CANAL DE MANDO</h4>
                <p class="text-[10px] text-gray-500">{{ Auth::user()->active_team->name }} Log</p>
            </div>
            {{-- Limitamos altura en móvil para que no ocupe toda la pantalla --}}
            <div class="space-y-1 pointer-events-auto max-h-[30vh] xl:max-h-[60vh] overflow-y-auto no-scrollbar mask-gradient-bottom">
                @forelse($teamActivity as $activity)
                    <div class="bg-gray-900/80 p-2 border-l border-gray-700 text-xs shadow-lg animate-fade-in-left">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-bold text-white">{{ $activity->user_name }}</span>
                            <span class="text-[9px] text-gray-500 font-mono">{{ \Carbon\Carbon::parse($activity->created_at)->format('H:i') }}</span>
                        </div>
                        <div class="text-gray-400 text-[10px] uppercase">
                            <span class="text-red-400">⚔️ ATACANDO</span> {{ $activity->zone_name }}
                        </div>
                    </div>
                @empty
                    <div class="p-2 text-center text-gray-600 text-[10px] border border-gray-800 bg-black/50">Sin actividad reciente.</div>
                @endforelse
            </div>
        </div>
        @endif

        {{-- COLUMNA CENTRAL: MAPA --}}
        <div class="flex-1 relative flex justify-center w-full order-1 xl:order-2">

            <div class="absolute top-0 left-0 right-0 flex flex-wrap justify-center gap-4 mb-4 text-[10px] md:text-xs tracking-widest uppercase text-gray-400 font-mono z-10 pointer-events-none">
                <div class="flex items-center gap-2 pointer-events-auto bg-black/50 px-2 rounded"><span class="w-2 h-2 border border-cyan-400 bg-transparent"></span> Neutral</div>
                <div class="flex items-center gap-2 pointer-events-auto bg-black/50 px-2 rounded"><span class="w-2 h-2 bg-white shadow-[0_0_5px_white]"></span> Aliado</div>
                <div class="flex items-center gap-2 pointer-events-auto bg-black/50 px-2 rounded"><span class="w-2 h-2 border border-yellow-500 animate-pulse"></span> Conflicto</div>
            </div>

            <div class="relative w-full aspect-video drop-shadow-[0_0_15px_rgba(0,255,255,0.3)]">
                <svg viewBox="0 0 569 392" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto select-none" id="spain-map">
                     @include('game.partials.map-svg')
                </svg>

                {{-- PANEL DE ATAQUE (Tooltip grande) --}}
                <div id="info-panel" class="absolute bottom-4 left-0 right-0 m-auto w-11/12 bg-black/95 border border-cyan-500 p-6 rounded-md hidden backdrop-blur-md transition-all z-40 shadow-2xl">
                    <button onclick="cerrarPanel()" class="absolute top-2 right-4 text-gray-500 hover:text-white text-xl">&times;</button>
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="text-left">
                            <h3 id="panel-title" class="text-2xl font-black italic text-white mb-1 neon-text">ZONA</h3>
                            <p class="text-xs text-gray-400 tracking-widest">PROPIEDAD ACTUAL:</p>
                            <p id="panel-owner" class="text-lg font-bold text-white tracking-widest">NEUTRAL</p>
                        </div>
                        <div class="flex flex-col items-end w-full md:w-auto">
                            <button id="btn-attack" class="btn-cyber w-full md:w-auto px-6 py-3 font-bold text-sm md:text-lg flex justify-center items-center gap-2 group border border-cyan-500 hover:bg-cyan-900/30 transition-colors text-cyan-300">
                                <span class="group-hover:animate-pulse">⚔️</span>
                                <span id="btn-text">ORDENAR ATAQUE</span>
                            </button>
                            <p id="attack-message" class="mt-2 text-xs font-bold text-center hidden"></p>
                        </div>
                    </div>
                    <div id="battle-stats-container" class="mt-4 pt-4 border-t border-gray-800 hidden">
                        <p class="text-[10px] text-gray-500 uppercase tracking-widest mb-2">SITUACIÓN DE COMBATE</p>
                        <div id="battle-bars" class="space-y-3"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA: ESTADÍSTICAS --}}
        <div class="w-full xl:w-1/4 space-y-4 flex-shrink-0 order-3">

            <div class="bg-black/80 border border-cyan-500/30 p-4 rounded backdrop-blur-sm shadow-lg">
                <p class="text-[10px] font-mono tracking-widest mb-2 text-gray-500 border-b border-gray-800 pb-1">ESTADO DEL SISTEMA</p>
                <div class="text-xs font-mono space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">FASE:</span>
                        <span class="{{ $phaseColor }} font-bold uppercase animate-pulse bg-black/50 px-2 py-1 rounded border border-current text-[10px]">{{ $phaseName }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">ENLACE:</span>
                        @if($votingEnabled)
                            <span class="text-green-400 font-bold flex items-center gap-1">ONLINE ●</span>
                        @else
                            <span class="text-red-500 font-bold flex items-center gap-1">OFFLINE ●</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-black/80 border border-cyan-500/30 p-4 rounded backdrop-blur-sm shadow-lg relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 text-6xl opacity-5 group-hover:opacity-10 transition-opacity">⏳</div>
                <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-2">PRÓXIMA RESOLUCIÓN</p>
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-2xl animate-pulse text-red-500">⚠️</span>
                    <div class="text-right">
                        <div id="countdown" class="text-2xl font-mono font-bold text-white tracking-widest leading-none">--:--:--</div>
                        <span class="text-[9px] text-gray-600">TIEMPO T-MINUS</span>
                    </div>
                </div>
                <div class="border-t border-gray-800 pt-2 flex justify-between items-center">
                    <span class="text-[9px] text-gray-500 uppercase">SECUENCIA</span>
                    <div class="flex items-baseline gap-1 text-xs">
                        <span class="font-bold text-cyan-500">R{{ $currentRound }}</span>
                        <span class="text-gray-600">➜</span>
                        <span class="font-bold text-gray-500">R{{ $nextRound }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-black/80 border border-cyan-500/30 p-3 rounded backdrop-blur-sm shadow-lg max-h-[400px] overflow-hidden flex flex-col">
                <div class="flex justify-between items-center mb-3 border-b border-gray-800 pb-2">
                    <p class="text-[10px] text-gray-400 uppercase tracking-widest">DOMINIO GLOBAL</p>
                    <span class="text-[9px] text-cyan-700 bg-cyan-900/20 px-1 rounded">TOP TEAMS</span>
                </div>
                <div class="overflow-y-auto no-scrollbar space-y-1 pr-1 flex-1">
                    @foreach($globalLeaderboard as $rank)
                        <div class="flex justify-between items-center p-2 rounded bg-gradient-to-r from-gray-900/80 to-transparent border-l-2 hover:bg-gray-800 transition-colors"
                             style="border-left-color: {{ $rank->team->color }}">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full shadow-[0_0_5px_currentColor]" style="background-color: {{ $rank->team->color }}; color: {{ $rank->team->color }}"></div>
                                <span class="text-xs font-bold text-gray-300 truncate w-24">{{ $rank->team->name }}</span>
                            </div>
                            <div class="flex items-baseline gap-1">
                                <span class="text-sm font-mono font-bold text-white">{{ $rank->total }}</span>
                                <span class="text-[8px] text-gray-600">ZONAS</span>
                            </div>
                        </div>
                    @endforeach
                    @if($globalLeaderboard->isEmpty())
                         <div class="text-center py-4 text-gray-600 text-xs italic">Esperando datos...</div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    @if(Auth::user()->activeTeam)
        @include('game.partials.chat')
    @endif
<a href="{{ route('market.index') }}"
   class="fixed bottom-4 left-4 z-40 bg-gray-900/90 border border-purple-500 text-purple-300 p-3 rounded-full shadow-[0_0_15px_rgba(168,85,247,0.5)] hover:scale-110 transition-transform group flex items-center gap-2 pr-5">
    <span class="text-2xl group-hover:animate-bounce">🎒</span>
    <div class="flex flex-col">
        <span class="text-[10px] font-bold text-white">TIENDA</span>
        <span class="text-[9px] text-yellow-400 font-mono">{{ Auth::user()->coins }} $</span>
    </div>
</a>
@endsection

{{-- TOOLTIP (Fuera del flujo) --}}
<div id="map-tooltip" class="fixed pointer-events-none opacity-0 bg-black/90 border border-white/20 text-white text-xs font-bold px-3 py-1.5 rounded z-50 transition-opacity duration-150 shadow-[0_0_10px_black] uppercase tracking-widest backdrop-blur-sm">
    Cargando...
</div>

@section('scripts')
{{-- ================================================= --}}
    {{-- 📊 MODAL DE ESTADÍSTICAS DE FACCIONES (ANIVERSARIO) --}}
    {{-- ================================================= --}}
    <div id="faction-stats-modal" class="fixed inset-0 z-[9999] bg-black/90 backdrop-blur-sm flex items-center justify-center p-4 hidden opacity-0 transition-opacity duration-300">
        <div class="bg-gray-900 w-full max-w-2xl rounded border border-yellow-500 shadow-[0_0_30px_rgba(234,179,8,0.2)] p-1 relative">

            {{-- Decoración Cyberpunk Esquinas --}}
            <div class="absolute top-0 left-0 w-3 h-3 border-t-2 border-l-2 border-yellow-500"></div>
            <div class="absolute top-0 right-0 w-3 h-3 border-t-2 border-r-2 border-yellow-500"></div>
            <div class="absolute bottom-0 left-0 w-3 h-3 border-b-2 border-l-2 border-yellow-500"></div>
            <div class="absolute bottom-0 right-0 w-3 h-3 border-b-2 border-r-2 border-yellow-500"></div>

            <div class="bg-black p-6 md:p-8">

                {{-- Header Modal --}}
                <div class="flex justify-between items-start mb-8 border-b border-gray-800 pb-4">
                    <div>
                        <h3 class="text-2xl font-black text-white italic uppercase tracking-widest">
                            <span class="text-yellow-500">CENSO GLOBAL:</span> GUERRA DE ERAS
                        </h3>
                        <p class="text-xs text-gray-400 uppercase mt-1">Distribución de Agentes por Facción</p>
                    </div>
                    <button onclick="toggleFactionStats()" class="text-gray-500 hover:text-white text-3xl font-bold leading-none">&times;</button>
                </div>

                {{-- Contenido Gráficas --}}
                <div class="space-y-6">
                    @php
                        // Configuramos colores e iconos para cada facción
                        $factionConfig = [
                            'bakuten' => ['color' => 'bg-blue-500', 'shadow' => 'shadow-[0_0_10px_rgba(59,130,246,0.8)]', 'icon' => '🐉', 'label' => 'BAKUTEN'],
                            'metal'   => ['color' => 'bg-red-500', 'shadow' => 'shadow-[0_0_10px_rgba(239,68,68,0.8)]', 'icon' => '⚙️', 'label' => 'METAL FIGHT'],
                            'burst'   => ['color' => 'bg-yellow-500', 'shadow' => 'shadow-[0_0_10px_rgba(234,179,8,0.8)]', 'icon' => '💥', 'label' => 'BURST'],
                            'x'       => ['color' => 'bg-green-500', 'shadow' => 'shadow-[0_0_10px_rgba(34,197,94,0.8)]', 'icon' => '❌', 'label' => 'BEYBLADE X']
                        ];
                    @endphp

                    @if($factionStats->count() == 0)
                        <div class="text-center text-gray-500 py-10 font-mono text-sm">ESPERANDO REGISTROS EN LA BASE DE DATOS...</div>
                    @else
                        @foreach(['bakuten', 'metal', 'burst', 'x'] as $fac)
                            @php
                                // Buscamos si hay gente en esta facción
                                $stat = $factionStats->firstWhere('faction', $fac);
                                $count = $stat ? $stat->total : 0;
                                $percent = ($count / $totalFactions) * 100;
                                $config = $factionConfig[$fac];
                            @endphp

                            <div>
                                <div class="flex justify-between items-end mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xl">{{ $config['icon'] }}</span>
                                        <span class="text-white font-bold uppercase tracking-widest text-sm">{{ $config['label'] }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-mono text-gray-400">{{ $count }} Agentes</span>
                                        <span class="text-xs font-bold text-white ml-2">{{ number_format($percent, 1) }}%</span>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-900 h-3 rounded-sm overflow-hidden border border-gray-800">
                                    <div class="h-full {{ $config['color'] }} {{ $config['shadow'] }} transition-all duration-1000 ease-out"
                                         style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- Footer Modal --}}
                <div class="mt-8 text-center pt-4 border-t border-gray-800">
                    <p class="text-[10px] text-gray-500 font-mono">Los porcentajes reflejan el número total de participantes registrados ({{ $totalFactions }}).</p>
                </div>

            </div>
        </div>
    </div>

    {{-- Script de apertura/cierre --}}
    <script>
        function toggleFactionStats() {
            const modal = document.getElementById('faction-stats-modal');
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
                // Pequeño delay para que la transición de opacidad funcione
                setTimeout(() => modal.classList.remove('opacity-0'), 10);
            } else {
                modal.classList.add('opacity-0');
                setTimeout(() => modal.classList.add('hidden'), 300);
            }
        }
    </script>
<script>
    // --- VARIABLES GLOBALES ---
    const zonesData = @json($zones);
    const teamStats = @json($teamAttackStats ?? []);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ID de mi equipo actual
    const myTeamId = {{ Auth::user()->activeTeam ? Auth::user()->activeTeam->id : 'null' }};
    const isVotingEnabled = @json($votingEnabled);

    // AQUÍ ESTÁ EL CAMBIO: Recogemos el voto actual del usuario desde PHP
    const myCurrentVoteSlug = @json($currentVoteSlug);

    let currentSelectedSlug = null;
    let chatInterval = null;
    const tooltip = document.getElementById('map-tooltip');

    const closingTime = new Date("{{ $nextClose->toIso8601String() }}").getTime();

    // --- FUNCIÓN DEL CONTADOR ---
    function updateCountdown() {
        const now = new Date().getTime();
        const distance = closingTime - now;

        if (distance < 0) {
            const countdownEl = document.getElementById("countdown");
            if(countdownEl) {
                countdownEl.innerHTML = "RESOLVIENDO...";
                countdownEl.classList.add("text-red-500");
            }
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        let text = "";
        if(days > 0) text += days + "d ";
        text += (hours < 10 ? "0"+hours : hours) + "h ";
        text += (minutes < 10 ? "0"+minutes : minutes) + "m ";
        text += (seconds < 10 ? "0"+seconds : seconds) + "s ";

        const el = document.getElementById("countdown");
        if(el) el.innerHTML = text;
    }

    setInterval(updateCountdown, 1000);
    updateCountdown();

    document.addEventListener("DOMContentLoaded", () => {
        // --- CONFIGURACIÓN DEL MAPA ---
        zonesData.forEach(zone => {
            const mapElement = document.getElementById(zone.slug);
            if (mapElement) {
                mapElement.classList.add('zone-path');
                mapElement.style.cursor = 'pointer';
                mapElement.style.transition = 'all 0.3s';

                let fillColor = '#2d3748';
                let strokeColor = '#4a5568';
                let strokeWidth = "0.5px";
                let fillOpacity = "1";
                let isEnemy = false;
                let isMine = false;

                if (zone.team) {
                    if (myTeamId && zone.team.id === myTeamId) {
                        isMine = true;
                        fillColor = zone.team.color;
                        fillOpacity = "0.5";
                        strokeColor = '#ffffff';
                        strokeWidth = "1.5px";
                    } else {
                        isEnemy = true;
                        fillColor = '#7f1d1d';
                        fillOpacity = "0.9";
                        strokeColor = zone.team.color;
                        strokeWidth = "1.0px";
                    }
                }

                mapElement.style.setProperty('fill', fillColor, 'important');
                mapElement.style.setProperty('fill-opacity', fillOpacity, 'important');
                mapElement.style.setProperty('stroke', strokeColor, 'important');
                mapElement.style.setProperty('stroke-width', strokeWidth, 'important');

                const children = mapElement.querySelectorAll('path, polygon, rect');
                if(children.length > 0) {
                    children.forEach(child => {
                        child.style.setProperty('fill', fillColor, 'important');
                        child.style.setProperty('fill-opacity', fillOpacity, 'important');
                        child.style.setProperty('stroke', strokeColor, 'important');
                        child.style.setProperty('stroke-width', strokeWidth, 'important');
                    });
                }

                // Radar Effect (Si hay actividad)
                if(teamStats[zone.id] && teamStats[zone.id].votes > 0) {
                    // Solo animamos si NO hay niebla de guerra o si tengo radar (lógica futura)
                    // Por ahora lo dejamos sutil
                }

                // Eventos Mouse
                mapElement.addEventListener('mouseenter', function() {
                    tooltip.style.opacity = '1';
                    tooltip.innerText = zone.name.toUpperCase();
                    if (isEnemy) {
                        tooltip.className = "fixed pointer-events-none bg-red-900/95 border border-red-500 text-white text-xs font-bold px-3 py-1.5 rounded z-50 shadow-lg uppercase tracking-widest backdrop-blur-sm";
                        tooltip.innerHTML += ` <span class="text-[9px] block text-red-300 mt-1">OCUPADO POR ${zone.team.name}</span>`;
                    } else if (isMine) {
                        tooltip.className = "fixed pointer-events-none bg-cyan-900/95 border border-cyan-500 text-white text-xs font-bold px-3 py-1.5 rounded z-50 shadow-lg uppercase tracking-widest backdrop-blur-sm";
                        tooltip.innerHTML += ` <span class="text-[9px] block text-cyan-300 mt-1">TU TERRITORIO</span>`;
                    } else {
                        tooltip.className = "fixed pointer-events-none bg-black/90 border border-gray-600 text-gray-300 text-xs font-bold px-3 py-1.5 rounded z-50 shadow-lg uppercase tracking-widest backdrop-blur-sm";
                        tooltip.innerHTML += ` <span class="text-[9px] block text-gray-500 mt-1">NEUTRAL</span>`;
                    }
                });

                mapElement.addEventListener('mousemove', function(e) {
                    tooltip.style.top = (e.clientY - 50) + 'px';
                    tooltip.style.left = (e.clientX + 15) + 'px';
                });

                mapElement.addEventListener('mouseleave', function() {
                    tooltip.style.opacity = '0';
                });

                mapElement.addEventListener('click', function(e) {
                    e.stopPropagation();
                    abrirPanel(zone.slug);
                });
            }
        });

        // Listeners Globales
        const btnAttack = document.getElementById('btn-attack');
        if(btnAttack) {
            btnAttack.addEventListener('click', function() {
                if(!currentSelectedSlug) return;
                enviarVoto(this);
            });
        }

        const chatInput = document.getElementById('chat-input');
        if(chatInput){
            chatInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') enviarMensaje(); });
            cargarChat();
            chatInterval = setInterval(cargarChat, 4000);
        }
    });

    // --- FUNCIONES DE INTERFAZ (MODIFICADAS) ---
    function abrirPanel(slug) {
        currentSelectedSlug = slug;
        const zoneInfo = zonesData.find(z => z.slug === slug);
        if(!zoneInfo) return;

        document.getElementById('info-panel').classList.remove('hidden');
        const statsContainer = document.getElementById('battle-stats-container');
        const barsContainer = document.getElementById('battle-bars');

        barsContainer.innerHTML = '';

        const titleEl = document.getElementById('panel-title');
        const ownerEl = document.getElementById('panel-owner');
        const btn = document.getElementById('btn-attack');
        const btnText = document.getElementById('btn-text');

        // 1. Títulos y Colores
        titleEl.innerText = zoneInfo.name.toUpperCase();
        let ownerName = "TERRITORIO NEUTRAL";
        let ownerColor = "#ffffff";
        let titleClass = "text-white";

        if(zoneInfo.team) {
            ownerName = zoneInfo.team.name;
            if(myTeamId && zoneInfo.team.id === myTeamId) {
                ownerColor = zoneInfo.team.color;
                titleClass = "text-cyan-400";
            } else {
                ownerColor = "#ff5555";
                titleClass = "text-red-500 neon-text-red";
            }
        }

        titleEl.className = `text-2xl font-black italic mb-1 ${titleClass}`;
        ownerEl.innerText = ownerName;
        ownerEl.style.color = ownerColor;

        resetButton();

        // 2. Lógica de Estadísticas (Niebla vs Resolución)
        const zoneBattleData = teamStats[zoneInfo.id];
        statsContainer.classList.remove('hidden');

        // PRIORIDAD 1: ¿Es la zona que YA estoy atacando? -> BLOQUEAR 🎯
        // Comparamos el slug de esta zona con el que sacamos de la base de datos
        if (myCurrentVoteSlug === slug) {
            btn.disabled = true;
            btnText.innerText = "🎯 OBJETIVO FIJADO ";
            // Estilo azul táctico para indicar que es tu objetivo actual
            btn.className = "w-full py-3 pr-2 font-bold text-sm md:text-lg flex justify-center items-center gap-2 border border-blue-500 text-blue-300 cursor-not-allowed bg-blue-900/40 rounded shadow-[0_0_15px_rgba(59,130,246,0.4)] animate-pulse";
            return; // IMPORTANTE: Return para que no siga ejecutando código
        }

        // CASO A: Votación ACTIVA -> Niebla de Guerra (Oculto total)
        if (isVotingEnabled) {
            barsContainer.innerHTML = `
                <div class="text-center py-3 border border-dashed border-gray-700 rounded bg-gray-900/50">
                    <p class="text-gray-500 text-[10px] font-mono mb-1 tracking-widest animate-pulse">📡 SEÑAL ENCRIPTADA</p>
                    <p class="text-[9px] text-gray-600">Información clasificada hasta el cierre de votaciones.</p>
                </div>
            `;
        }
        // CASO B: Votación CERRADA -> Mostrar Barras SIN números
        else if (zoneBattleData && zoneBattleData.teams && zoneBattleData.teams.length > 0) {
            zoneBattleData.teams.sort((a, b) => b.votes - a.votes);

            zoneBattleData.teams.forEach(t => {
                let percent = (t.votes / zoneBattleData.total_votes) * 100;

                // NOTA: Solo mostramos nombre y barra. Nada de números.
                const barHTML = `
                    <div class="group">
                        <div class="flex justify-between text-[10px] uppercase font-bold mb-1">
                            <span style="color:${t.color}">${t.name}</span>
                            {{-- Aquí hemos quitado el texto de daño/porcentaje --}}
                        </div>
                        <div class="w-full h-1.5 bg-gray-900 rounded-full overflow-hidden border border-white/5">
                            <div class="h-full shadow-[0_0_10px_currentColor]" style="width: ${percent}%; background-color: ${t.color}; box-shadow: 0 0 5px ${t.color}; transition: width 1s ease-out;"></div>
                        </div>
                    </div>`;
                barsContainer.innerHTML += barHTML;
            });
        }
        else {
             barsContainer.innerHTML = '<p class="text-gray-600 text-[10px] italic text-center">Zona en calma.</p>';
        }

        // 3. ESTADO DEL BOTÓN DE ATAQUE

        // PRIORIDAD 1: Si es el territorio que YA he votado -> BLOQUEAR
        if (myCurrentVoteSlug === slug) {
            btn.disabled = true;
            btnText.innerText = "🎯 OBJETIVO FIJADO";
            btn.className = "w-full py-3 font-bold text-sm md:text-lg flex justify-center items-center gap-2 border border-blue-500/50 text-blue-300 cursor-not-allowed bg-blue-900/20 rounded shadow-[0_0_10px_rgba(59,130,246,0.2)]";
            return; // Salimos, no se puede hacer nada más
        }

        // PRIORIDAD 2: Si la votación está cerrada (fase resolución) -> BLOQUEAR
        if (!isVotingEnabled) {
            btn.disabled = true;
            btnText.innerText = "⛔ FASE CONQUISTA (CERRADO)";
            btn.className = "w-full py-3 font-bold text-sm md:text-lg flex justify-center items-center gap-2 border border-red-500/30 text-red-500/50 cursor-not-allowed bg-red-900/10 rounded";
            return;
        }

        // PRIORIDAD 3: Si es mi propio territorio -> BLOQUEAR (Ya asegurado)
        if (myTeamId && zoneInfo.team_id === myTeamId) {
            btn.disabled = true;
            btnText.innerText = "🛡️ TERRITORIO ASEGURADO";
            btn.className = "w-full py-3 font-bold text-sm md:text-lg flex justify-center items-center gap-2 border border-green-500/30 text-green-500/50 cursor-not-allowed bg-green-900/10 rounded";
        }
        // PRIORIDAD 4: Botón Normal (Atacar/Cambiar Voto)
        else {
            const myAttack = zoneBattleData?.teams?.find(t => t.id === myTeamId);
            btn.className = "btn-cyber w-full md:w-auto px-6 py-3 font-bold text-sm md:text-lg flex justify-center items-center gap-2 group border border-cyan-500 hover:bg-cyan-900/30 transition-colors text-cyan-300";
            btnText.innerText = "ORDENAR ATAQUE";

            // Si mi equipo ataca aquí, lo ponemos amarillo
            if(myAttack) {
                 btn.classList.add('border-yellow-500', 'text-yellow-400');
                 btn.classList.remove('border-cyan-500', 'text-cyan-300');
            }
        }
    }

    function cerrarPanel() { document.getElementById('info-panel').classList.add('hidden'); }

    function resetButton() {
        const btn = document.getElementById('btn-attack');
        const msg = document.getElementById('attack-message');
        if(btn) {
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed', 'bg-green-900/10', 'border-green-500/30', 'text-green-500/50');
        }
        if(msg) msg.classList.add('hidden');
    }

    function enviarVoto(btn) {
        const msg = document.getElementById('attack-message');
        const btnText = document.getElementById('btn-text');
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
        btnText.innerText = "ENVIANDO...";

        fetch("{{ route('conquest.vote') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ zone_slug: currentSelectedSlug })
        })
        .then(response => response.json())
        .then(data => {
            msg.classList.remove('hidden');
            if(data.success) {
                msg.className = "mt-2 text-xs font-bold text-center text-green-400 animate-pulse";
                msg.innerText = "ORDEN RECIBIDA. ACTUALIZANDO SATÉLITE...";
                btnText.innerText = "✅ RECARGANDO...";
                setTimeout(() => { window.location.reload(); }, 1000);
            } else {
                msg.className = "mt-2 text-xs font-bold text-center text-red-400";
                msg.innerText = data.error || "Error al procesar";
                resetButton();
                btnText.innerText = "REINTENTAR";
            }
        })
        .catch(error => {
            console.error(error);
            resetButton();
            btnText.innerText = "ERROR DE RED";
        });
    }

    // --- FUNCIONES DEL CHAT ---
    function toggleChat() {
        const chat = document.getElementById('team-chat');
        if(chat) chat.classList.toggle('translate-y-[calc(100%-40px)]');
        const arrow = document.getElementById('chat-arrow');
        if(arrow) arrow.classList.toggle('rotate-180');
    }

    function cargarChat() {
        fetch("{{ route('chat.fetch') }}")
        .then(r => r.json())
        .then(data => {
             const chatBox = document.getElementById('chat-messages');
             if(!chatBox) return;
             chatBox.innerHTML = '';
             if(data.length === 0) {
                 chatBox.innerHTML = '<p class="text-gray-600 text-center italic mt-4">Canal silencioso...</p>';
                 return;
             }
             data.forEach(msg => {
                 const isMe = msg.user.name === "{{ Auth::user()->name }}";
                 const color = isMe ? 'text-cyan-400' : 'text-yellow-500';
                 const align = isMe ? 'text-right' : 'text-left';
                 const bg = isMe ? 'bg-cyan-900/20' : 'bg-transparent';
                 chatBox.innerHTML += `
                    <div class="mb-1 p-1 rounded ${align} ${bg}">
                        <span class="font-bold ${color} text-[10px] uppercase block">${msg.user.name}</span>
                        <span class="text-gray-300 break-words">${msg.message}</span>
                    </div>`;
             });
             chatBox.scrollTop = chatBox.scrollHeight;
        })
        .catch(console.error);
    }

    function enviarMensaje() {
        const input = document.getElementById('chat-input');
        const txt = input.value.trim();
        if(!txt) return;
        fetch("{{ route('chat.send') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ message: txt })
        }).then(() => {
            input.value = '';
            cargarChat();
        });
    }

    // --- FUNCIONES MOTD ---
    window.toggleMotdEdit = function() {
        const display = document.getElementById('pinned-display');
        const edit = document.getElementById('pinned-edit');
        if(display && edit) {
            display.classList.toggle('hidden');
            edit.classList.toggle('hidden');
        }
    };

    window.saveMotd = function() {
        const input = document.getElementById('motd-input');
        const msg = input.value;
        fetch("{{ route('chat.update_motd') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ message: msg })
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                document.getElementById('motd-text').innerText = msg;
                toggleMotdEdit();
            } else {
                alert(data.error || 'Error');
            }
        });
    };
</script>
@endsection

@section('styles')
<style>
    /* Asegura que la transición funcione incluso forzando propiedades */
    #spain-map path, #spain-map polygon, #spain-map g {
        transition: fill 0.3s ease, fill-opacity 0.3s ease, stroke 0.3s ease !important;
    }
    #spain-map g:hover path, #spain-map path:hover {
        filter: brightness(1.2); /* Efecto de brillo al pasar el ratón */
    }
    /* Ocultar scrollbar pero permitir scroll */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }

    /* Animación suave de entrada para los logs */
    @keyframes fadeInLeft {
        from { opacity: 0; transform: translateX(-10px); }
        to { opacity: 1; transform: translateX(0); }
    }
    .animate-fade-in-left {
        animation: fadeInLeft 0.3s ease-out forwards;
    }

    /* Máscara para que la lista se desvanezca abajo suavemente */
    .mask-gradient-bottom {
        mask-image: linear-gradient(to bottom, black 80%, transparent 100%);
        -webkit-mask-image: linear-gradient(to bottom, black 80%, transparent 100%);
    }
</style>
@endsection
