@extends('layouts.game')

@section('title', 'Reportes de Guerra')

@section('content')

    <div class="flex justify-between items-center mb-8 border-b border-cyan-500/30 pb-4">
        <div>
            <h2 class="text-3xl font-bold neon-text text-white">CANAL DE NOTICIAS</h2>
            <p class="text-cyan-400 text-xs tracking-widest">ACTUALIZACIONES DEL FRENTE</p>
        </div>
        <a href="{{ route('conquest.map') }}" class="btn-cyber px-4 py-2 text-xs font-bold border border-cyan-500 text-cyan-400 hover:bg-cyan-900/50">
            VOLVER AL MAPA
        </a>
    </div>

    <div class="max-w-3xl mx-auto space-y-6">

        @if($news->count() == 0)
            <div class="text-center text-gray-500 py-10 border border-dashed border-gray-800 rounded">
                <p>NO HAY REPORTES DE INTELIGENCIA DISPONIBLES.</p>
                <p class="text-xs mt-2">Esperando datos del satélite...</p>
                <a href="/generar-noticias" class="text-[10px] text-cyan-800 underline mt-4 block">Simular Datos</a>
            </div>
        @endif

        @foreach($news as $item)
            <div class="relative bg-black/80 border-l-4 {{ $item->border_class }} p-6 shadow-lg backdrop-blur-sm transform hover:scale-[1.01] transition-all duration-300">

                <span class="absolute top-4 right-4 text-[10px] text-gray-600 font-mono">
                    {{ $item->created_at->format('d/m/Y - H:i') }}
                </span>

                <div class="flex items-start gap-4">
                    <div class="text-2xl pt-1">{{ $item->icon }}</div>

                    <div>
                        <h3 class="text-lg font-bold uppercase tracking-wide mb-1 {{ $item->color_class }}">
                            {{ $item->title }}
                        </h3>
                        <p class="text-gray-300 text-sm leading-relaxed">
                            {{ $item->content }}
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

@endsection
