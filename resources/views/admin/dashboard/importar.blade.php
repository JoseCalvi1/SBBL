@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <a href="{{ route('nacional.ranking') }}" class="text-blue-600 hover:underline mb-4 inline-block">
        &larr; Volver a la lista general
    </a>

    <h1 class="text-2xl font-bold mb-6">Subir Solicitudes del Nacional (CSV)</h1>

    <form action="{{ route('nacional.procesar') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-md">
        @csrf
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Archivo CSV de inscripciones:</label>
            <input type="file" name="archivo_csv" accept=".csv" required class="border p-2 w-full">
            <p class="text-sm text-gray-500 mt-1">Asegúrate de que la primera columna contenga el ID del usuario.</p>
        </div>

        <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700">
            Cruzar Datos y Generar Lista
        </button>
    </form>
</div>
@endsection
