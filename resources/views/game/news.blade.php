@extends('layouts.game')

@section('title', 'Reportes de Guerra')

@section('content')

    {{-- CABECERA CIBERNÉTICA --}}
    <div class="flex flex-col md:flex-row justify-between items-end mb-10 border-b-2 border-cyan-500/50 pb-6 relative">
        <div class="absolute bottom-0 left-0 w-1/3 h-1 bg-gradient-to-r from-cyan-500 to-transparent"></div>

        <div>
            <h2 class="text-4xl md:text-5xl font-black italic tracking-tighter text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-400">
                WAR<span class="text-cyan-500">FEED</span>
            </h2>
            <div class="flex items-center gap-2 mt-2">
                <span class="relative flex h-3 w-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                </span>
                <p class="text-cyan-400 text-xs font-mono tracking-[0.3em] uppercase">TRANSMISIÓN EN VIVO</p>
            </div>
        </div>

        <div class="mt-4 md:mt-0 flex flex-col items-end">
            <a href="{{ route('conquest.map') }}" class="group relative px-6 py-3 font-bold text-white transition-all duration-200 bg-cyan-900/20 border border-cyan-500/50 hover:bg-cyan-500 hover:text-black hover:shadow-[0_0_15px_rgba(6,182,212,0.6)]">
                <span class="absolute top-0 left-0 w-2 h-2 border-t-2 border-l-2 border-cyan-400 group-hover:border-black"></span>
                <span class="absolute bottom-0 right-0 w-2 h-2 border-b-2 border-r-2 border-cyan-400 group-hover:border-black"></span>
                VOLVER AL MAPA
            </a>
        </div>
    </div>

    <div class="max-w-4xl mx-auto space-y-8 relative">

        <div class="absolute left-4 md:left-8 top-0 bottom-0 w-px bg-gradient-to-b from-cyan-900 via-cyan-500/30 to-transparent z-0"></div>

        @if($news->count() == 0)
            <div class="ml-12 text-center text-gray-500 py-16 border border-dashed border-gray-800 rounded bg-gray-900/50 backdrop-blur">
                <div class="text-4xl mb-4 grayscale opacity-30">📡</div>
                <p class="font-mono text-sm tracking-widest">SIN DATOS DE INTELIGENCIA</p>
                <p class="text-xs mt-2 text-gray-600">Escaneando frecuencias...</p>
                {{-- Botón de simulación solo para ti --}}
                @if(Auth::id() == 1)
                     <a href="/generar-noticias" class="text-[10px] text-cyan-800 underline mt-4 block">CMD: FORCE_GEN</a>
                @endif
            </div>
        @endif

        @foreach($news as $item)
            @php
                // --- CONFIGURACIÓN POR ARRAY (COMPATIBLE PHP 7.x) ---
                $defaultConfig = [
                    'border' => 'border-gray-500',
                    'bg'     => 'bg-gray-800',
                    'icon'   => '📡',
                    'icon_bg'=> 'bg-gray-500',
                    'text'   => 'text-gray-300',
                    'label'  => 'INFO'
                ];

                $styles = [
                    'conquest' => [
                        'border' => 'border-yellow-500',
                        'bg'     => 'bg-yellow-900/10',
                        'icon'   => '👑',
                        'icon_bg'=> 'bg-yellow-500',
                        'text'   => 'text-yellow-400',
                        'label'  => 'CAMBIO DE MANDO'
                    ],
                    'defense' => [
                        'border' => 'border-blue-500',
                        'bg'     => 'bg-blue-900/10',
                        'icon'   => '🛡️',
                        'icon_bg'=> 'bg-blue-500',
                        'text'   => 'text-blue-400',
                        'label'  => 'DEFENSA EXITOSA'
                    ],
                    'attack' => [
                        'border' => 'border-red-500',
                        'bg'     => 'bg-red-900/10',
                        'icon'   => '⚔️',
                        'icon_bg'=> 'bg-red-500',
                        'text'   => 'text-red-400',
                        'label'  => 'COMBATE EN CURSO'
                    ]
                ];

                // Si el tipo existe en el array, lo usa. Si no, usa el default.
                $config = isset($styles[$item->type]) ? $styles[$item->type] : $defaultConfig;
            @endphp

            <div class="relative pl-12 md:pl-20 group">

                <div class="absolute left-0 md:left-4 top-0 z-10">
                    <div class="w-10 h-10 rounded-full {{ $config['icon_bg'] }} flex items-center justify-center text-lg shadow-[0_0_15px_rgba(0,0,0,0.5)] border-2 border-black group-hover:scale-110 transition-transform duration-300">
                        {{ $config['icon'] }}
                    </div>
                </div>

                <div class="relative bg-black/60 border-r-4 {{ $config['border'] }} p-6 backdrop-blur-md hover:bg-white/5 transition-all duration-300 shadow-lg group-hover:shadow-[0_0_20px_rgba(0,0,0,0.5)]">

                    <div class="absolute top-0 left-0 w-2 h-2 border-t border-l border-white/20"></div>
                    <div class="absolute bottom-0 left-0 w-2 h-2 border-b border-l border-white/20"></div>

                    <div class="flex justify-between items-start mb-2 border-b border-white/5 pb-2">
                        <div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-white/10 text-white tracking-wider mb-2 inline-block">
                                {{ $config['label'] }}
                            </span>
                            <span class="text-[10px] text-gray-500 font-mono ml-2 uppercase">
                                // SAT-ID: {{ $item->id * 4023 }}
                            </span>
                        </div>
                        <span class="text-[10px] font-mono text-cyan-500/70">
                            {{ $item->created_at->diffForHumans() }}
                        </span>
                    </div>

                    <h3 class="text-lg md:text-xl font-black uppercase italic tracking-wide mb-2 {{ $config['text'] }}">
                        {{ $item->title }}
                    </h3>

                    <div class="text-gray-300 text-sm leading-relaxed font-sans prose prose-invert max-w-none">
                        {{-- Usamos nl2br para respetar saltos de línea --}}
                        {!! nl2br(e($item->content)) !!}
                    </div>

                    <div class="mt-4 pt-3 border-t border-dashed border-white/10 flex items-center gap-2">
                        <div class="w-6 h-6 rounded bg-cyan-900/50 flex items-center justify-center border border-cyan-500/30">
                            <span class="text-xs">🤖</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[9px] text-gray-400 uppercase tracking-widest leading-none">Reportero</span>
                            <span class="text-[10px] font-bold text-cyan-400 leading-none">WAR-NET AI v2.0</span>
                        </div>
                    </div>

                </div>
            </div>
        @endforeach
    </div>

@endsection
