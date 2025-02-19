@extends('layouts.app')

@section('styles')
<style>
/* Estilos para el título */
.title {
    text-align: center;
    color: #ffffff;
    margin-bottom: 40px;
}

.title h1 {
    font-size: 4rem;
    margin: 0;
    font-weight: bold;
    letter-spacing: 2px;
}

.title h2 {
    font-size: 1.8rem;
    margin-top: 10px;
    text-shadow: 1px 1px 5px #000;
    font-weight: 300;
}

/* Estilos para el menú */
.menu-container {
    display: inline-block;
    margin-top: 20px;
}

.menu-row {
    display: flex;
    justify-content: center;
    gap: 25px;
    margin-bottom: 20px;
}

.menu-button {
    display: inline-block;
    width: 160px;
    height: 60px;
    background: linear-gradient(135deg, #222, #444); /* Degradado oscuro */
    clip-path: polygon(10% 0%, 100% 0%, 90% 100%, 0% 100%); /* Lados torcidos hacia el mismo lado */
    text-align: center;
    line-height: 60px;
    color: #ffffff;
    font-weight: bold;
    font-size: 1rem;
    text-decoration: none;
    transition: transform 0.3s, background 0.3s, box-shadow 0.3s;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
}

.menu-button:hover {
    transform: scale(1.05);
    background: linear-gradient(135deg, #444, #424242);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.7);
    text: white;
    text-decoration: none;
}

/* Botón de Colección en naranja */
.menu-button.coleccion {
    background: linear-gradient(135deg, #FFA500, #FF8C00); /* Naranja */
}

.menu-button.coleccion:hover {
    background: linear-gradient(135deg, #FF8C00, #FFA500); /* Efecto al hacer hover */
}

/* Créditos */
.credits {
    margin-top: 30px;
    text-align: center;
    color: #ffffff;
    font-size: 1rem;
    opacity: 0.7;
}

/* Redes sociales */
.social-icons {
    display: flex;
    justify-content: center;
    margin-top: 20px;
    gap: 15px;
}

.social-icons a {
    color: white;
    font-size: 1.5rem;
    transition: color 0.3s;
}

.social-icons a:hover {
    color: #1E90FF; /* Azul claro al pasar el ratón */
}
</style>
@endsection

@section('content')
<div class="container-fluid text-center mt-4">
    <!-- Título -->
    <div class="title">
        <h1>SBBL</h1>
        <h2>BEYBLADE DATABASE</h2>
    </div>

    <!-- Menú -->
    <div class="menu-container">
        <div class="menu-row">
            <a href="{{ route('database.beyblades') }}" class="menu-button">Beyblades</a>
            <a href="#" class="menu-button">Sistema</a>
            <a href="{{ route('database.parts') }}" class="menu-button">Partes</a>
            <a href="#" class="menu-button coleccion">Colección</a> <!-- Botón Colección en naranja -->
        </div>
        <div class="menu-row">
            <a href="#" class="menu-button">Estadios</a>
            <a href="#" class="menu-button">Accesorios</a>
            <a href="#" class="menu-button">Listado</a>
        </div>
    </div>

    <!-- Créditos -->
    <div class="credits">
        <p>Desarrollado por la comunidad SBBL | Todos los derechos reservados © {{ date('Y') }}</p>
    </div>

    <!-- Redes Sociales -->
    <div class="social-icons mb-2">
        <a href="https://discord.gg/JCtAHfJ8Ht" target="_blank"><i class="fab fa-discord"></i></a>
        <a href="https://www.youtube.com/@sbbl_oficial" target="_blank"><i class="fab fa-youtube"></i></a>
        <a href="https://www.instagram.com/sbbl_oficial/" target="_blank"><i class="fab fa-instagram"></i></a>
        <a href="https://x.com/SBBLOficial" target="_blank"><i class="fab fa-twitter"></i></a>
        <a href="https://www.twitch.tv/sbbl_oficial" target="_blank"><i class="fab fa-twitch"></i></a>
    </div>
</div>
@endsection
