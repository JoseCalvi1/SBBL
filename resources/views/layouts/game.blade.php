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
            overflow: hidden; /* Evita scroll si no es necesario */
            color: white;
        }

        /* Tu Grid 3D (Se mantiene igual) */
        .cyber-grid {
            position: absolute;
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
        }

        @keyframes moveGrid {
            0% { transform: perspective(500px) rotateX(60deg) translateY(0); }
            100% { transform: perspective(500px) rotateX(60deg) translateY(50px); }
        }

        .neon-text { text-shadow: 0 0 5px #fff, 0 0 10px #fff, 0 0 20px #0ff, 0 0 40px #0ff; }

        .btn-cyber {
            background: rgba(0, 255, 255, 0.05);
            color: #0ff;
            border: 1px solid #0ff;
            box-shadow: 0 0 10px rgba(0, 255, 255, 0.3);
            transition: all 0.3s ease;
        }
        .btn-cyber:hover {
            background: #0ff;
            color: #000;
            box-shadow: 0 0 20px #0ff;
        }
    </style>
    @yield('styles')
</head>
<body class="min-h-screen w-full flex flex-col items-center justify-center relative bg-[#050505] text-white font-['Orbitron'] overflow-x-hidden">

    <div class="cyber-grid fixed"></div> <div class="fixed inset-0 bg-gradient-to-t from-black via-transparent to-black pointer-events-none"></div>

    <main class="z-10 w-full max-w-[98%] px-4 py-8 text-center flex flex-col items-center justify-center flex-grow">
        @yield('content')
    </main>

    @yield('scripts')
</body>
</html>
