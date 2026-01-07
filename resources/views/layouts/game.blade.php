<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SBBL CONQUISTA - @yield('title', 'Conquista')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="shortcut icon" type="image/png" href="{{ asset('/images/sbbl.png') }}">

    <style>
        body {
            font-family: 'Orbitron', sans-serif;
            background-color: #050505;
            /* CORRECCIÓN 1: Permitimos scroll vertical, ocultamos el horizontal */
            overflow-y: auto;
            overflow-x: hidden;
            color: white;
        }

        /* Tu Grid 3D */
        .cyber-grid {
            position: fixed; /* Cambiado a fixed para que no se mueva al hacer scroll */
            width: 200%;
            height: 200%;
            top: -50%;
            left: -50%;
            background-image:
                linear-gradient(rgba(0, 255, 255, 0.1) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            transform: perspective(500px) rotateX(60deg);
            animation: moveGrid 5s linear infinite;
            z-index: -1;
            opacity: 0.3;
            pointer-events: none; /* Importante para poder hacer clic a través de ella */
        }

        /*@keyframes moveGrid {
            0% { transform: perspective(500px) rotateX(60deg) translateY(0); }
            100% { transform: perspective(500px) rotateX(60deg) translateY(50px); }
        }*/

        .neon-text { text-shadow: 0 0 5px #fff, 0 0 10px #fff, 0 0 20px #0ff, 0 0 40px #0ff; }

        /* Estilos de Scrollbar Personalizados (Toque Cyberpunk) */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #050505;
        }
        ::-webkit-scrollbar-thumb {
            background: #333;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #0ff;
        }
    </style>
    @yield('styles')
</head>
{{-- CORRECCIÓN 2: Quitamos 'justify-center' y ponemos 'justify-start' para que empiece arriba --}}
{{-- CORRECCIÓN 3: Quitamos 'overflow-x-hidden' de aquí porque ya está en el CSS --}}
<body class="min-h-screen w-full flex flex-col items-center justify-start relative bg-[#050505] text-white font-['Orbitron']">

    <div class="cyber-grid"></div>

    {{-- Degradado fijo para que no moleste al scroll --}}
    <div class="fixed inset-0 bg-gradient-to-t from-black via-transparent to-black pointer-events-none z-0"></div>

    <main class="z-10 w-full max-w-[98%] px-4 py-8 text-center flex flex-col items-center flex-grow">
        @yield('content')
    </main>

    @yield('scripts')
</body>
</html>
