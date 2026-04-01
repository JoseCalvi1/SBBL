@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">Ranking de Prioridad - Nacional</h1>
        <a href="{{ route('nacional.importar') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
            Subir nuevo CSV
        </a>
    </div>

    @if($esBusquedaCsv)
        <h2 class="text-xl font-bold text-blue-400 mb-4">🏆 Torneo INDIVIDUAL</h2>
    @endif

    <div class="overflow-x-auto shadow-md rounded-lg mb-10">
        <table class="min-w-full table-auto border-collapse">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="px-4 py-2 text-left">Pos.</th>
                    <th class="px-4 py-2 text-left">ID</th>
                    <th class="px-4 py-2 text-left">Jugador</th>
                    <th class="px-4 py-2 text-center">Grupo</th>
                    <th class="px-4 py-2 text-center">Copas</th>
                    <th class="px-4 py-2 text-center">Pts. Desempate</th>
                    <th class="px-4 py-2 text-center text-sm font-light">(Camp | Fid | Rank)</th>
                </tr>
            </thead>
            <tbody class="text-white">
                @forelse ($listaIndividual as $index => $jugador)
                    <tr class="border-b hover:bg-gray-700 {{ $index % 2 == 0 ? 'bg-gray-800/50' : '' }}">
                        <td class="px-4 py-3 font-bold">{{ $jugador->posicion }}</td>
                        <td class="px-4 py-3 text-gray-400">{{ $jugador->id_formateado }}</td>
                        <td class="px-4 py-3 font-semibold">{{ $jugador->nombre_usuario }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded text-sm font-bold
                                {{ $jugador->grupo_prioridad == 'Grupo A' ? 'bg-green-200 text-green-800' : '' }}
                                {{ $jugador->grupo_prioridad == 'Grupo B' ? 'bg-blue-200 text-blue-800' : '' }}
                                {{ $jugador->grupo_prioridad == 'Grupo C' ? 'bg-yellow-200 text-yellow-800' : '' }}
                                {{ $jugador->grupo_prioridad == 'Grupo D' ? 'bg-red-200 text-red-800' : '' }}">
                                {{ $jugador->grupo_prioridad }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center font-bold">{{ $jugador->copas_inscritas }}</td>
                        <td class="px-4 py-3 text-center font-bold text-lg text-indigo-400">{{ $jugador->total_desempate }}</td>
                        <td class="px-4 py-3 text-center text-gray-400 text-sm">
                            {{ $jugador->pt_campeon }}|{{ $jugador->pt_fidelidad }}|{{ $jugador->pt_ranking }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No hay registros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($esBusquedaCsv && count($listaEquipos) > 0)
        <h2 class="text-xl font-bold text-green-400 mb-4">🛡️ Torneo por EQUIPOS</h2>
        <div class="overflow-x-auto shadow-md rounded-lg">
            <table class="min-w-full table-auto border-collapse">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-4 py-2 text-left">Pos.</th>
                        <th class="px-4 py-2 text-left">Jugador</th>
                        <th class="px-4 py-2 text-center">Grupo</th>
                        <th class="px-4 py-2 text-center">Pts. Desempate</th>
                    </tr>
                </thead>
                <tbody class="text-white">
                    @foreach($listaEquipos as $jugador)
                        <tr class="border-b">
                            <td class="px-4 py-3 font-bold">{{ $jugador->posicion }}</td>
                            <td class="px-4 py-3">{{ $jugador->nombre_usuario }}</td>
                            <td class="px-4 py-3 text-center">{{ $jugador->grupo_prioridad }}</td>
                            <td class="px-4 py-3 text-center">{{ $jugador->total_desempate }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
