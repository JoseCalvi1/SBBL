@extends('layouts.app')

@section('content')
<div class="container mt-4 text-white my-5">
    <h2>📢 Enviar anuncio a Discord</h2>

    @if(session('success'))
        <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mt-3">{{ session('error') }}</div>
    @endif

    <form action="{{ route('index.announcements.send') }}" method="POST" class="mt-4">
        @csrf

        <div class="mb-3">
            <label for="mention" class="form-label">Mención</label>
            <select name="mention" id="mention" class="form-select">
                <option value="none">Sin mención</option>
                <option value="875324662010228746">@everyone (todos los usuarios)</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="message" class="form-label">Mensaje</label>
            <textarea name="message" id="message" rows="6" class="form-control"
                      placeholder="Escribe tu anuncio... puedes incluir enlaces, emojis, etc."></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Enviar a Discord</button>
    </form>
</div>
@endsection
