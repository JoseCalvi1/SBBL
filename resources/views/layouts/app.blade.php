<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @yield('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <link rel="shortcut icon" type="image/png" href="{{ asset('/images/sbbl.png') }}">

    @yield('styles')

    <style>
        /* =====================================================
           VARIABLES GLOBALES: Z-INDEX & COLORES LIGA + SHONEN
           ¡ESTO APLICA A TODA LA WEB!
        ===================================================== */
        /* 1. IMPORTAMOS OSWALD PARA BUENA LECTURA DE NÚMEROS */
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,700;0,900;1,900&family=Oswald:wght@500;700&display=swap');

        :root {
            /* Z-INDEX */
            --z-navbar: 1100;
            --z-dropdown: 1110;
            --z-sidebar: 1090;
            --z-floating: 1080;
            --z-cookie: 1070;
            --z-modal: 1200;

            /* PALETA OFICIAL LIGA (Azules) + ACENTOS SHONEN */
            --sbbl-bg-dark: #111827;     /* Fondo general más oscuro */
            --sbbl-blue-1: #1e2a47;      /* Azul principal (Navbar) */
            --sbbl-blue-2: #27295B;      /* Azul medio (Contenido/Paneles) */
            --sbbl-blue-3: #283b63;      /* Azul claro (Hover/Sidebar) */

            --sbbl-gold: #ffc107;        /* Dorado Liga */
            --shonen-red: #ff2a2a;       /* Rojo Acción */
            --shonen-cyan: #00ffcc;      /* Cian Energía */
            --text-main: #ffffff;
        }

        /* =====================================================
        RESET BÁSICO Y FONDO GLOBAL (MANGA DOTS)
        ===================================================== */
        html, body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: 'Montserrat', sans-serif;
            background-color: var(--sbbl-bg-dark);
            color: var(--text-main);
            background-image: radial-gradient(rgba(255, 255, 255, 0.1) 2px, transparent 2px);
            background-size: 20px 20px;
        }

        /* 2. APLICAMOS OSWALD A LOS TÍTULOS Y A LA CLASE */
        .font-Oswald {
            font-family: 'Oswald', sans-serif;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Oswald', sans-serif;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* =====================================================
           COMPONENTES REUTILIZABLES
        ===================================================== */
        .command-panel {
            background-color: var(--sbbl-blue-2);
            border: 3px solid #000;
            border-radius: 0 15px 0 15px;
            box-shadow: 6px 6px 0px #000;
            overflow: hidden;
            position: relative;
            transition: 0.2s;
        }
        .command-panel:hover {
            transform: translate(-2px, -2px);
            box-shadow: 8px 8px 0px var(--sbbl-gold);
            border-color: var(--sbbl-gold);
        }
        .panel-header {
            background: #000;
            padding: 12px 20px;
            border-bottom: 3px solid var(--sbbl-gold);
            color: #fff;
            font-family: 'Oswald', sans-serif;
            font-weight: 700;
            font-size: 1.6rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Botones Universales Shonen */
        .btn-shonen {
            font-family: 'Oswald', sans-serif;
            font-weight: 700;
            font-size: 1.3rem;
            letter-spacing: 1px;
            border-radius: 0;
            border: 3px solid #000;
            transform: skewX(-5deg);
            transition: 0.2s;
            display: inline-block;
            text-transform: uppercase;
            text-decoration: none;
            padding: 5px 20px;
        }
        .btn-shonen > * { transform: skewX(5deg); display: block; }
        .btn-shonen-info { background: var(--sbbl-blue-3); color: #fff; box-shadow: 4px 4px 0 #000; }
        .btn-shonen-info:hover { background: #fff; color: #000; box-shadow: 5px 5px 0 var(--sbbl-gold); transform: translate(-2px, -2px) skewX(-5deg); }
        .btn-shonen-warning { background: var(--sbbl-gold); color: #000; box-shadow: 4px 4px 0 #000; }
        .btn-shonen-warning:hover { background: var(--shonen-red); color: #fff; box-shadow: 5px 5px 0 #000; transform: translate(-2px, -2px) skewX(-5deg); }

        /* Estilos de Suscripción (Clases Globales) */
        .suscripcion-nivel-3 { color: var(--sbbl-gold) !important; text-shadow: 2px 2px 0 #000; }
        .suscripcion-nivel-2 { color: #e2e8f0 !important; text-shadow: 2px 2px 0 #000; }
        .suscripcion-nivel-1 { color: #b45309 !important; text-shadow: 2px 2px 0 #000; }

        /* =====================================================
           NAVBAR SUPERIOR
        ===================================================== */
        nav.navbar {
            background-color: var(--sbbl-blue-1) !important;
            border-bottom: 4px solid var(--sbbl-gold);
            height: 70px;
            position: relative;
            z-index: var(--z-navbar);
            box-shadow: 0 4px 0 #000;
        }

        .navbar-brand span { font-family: 'Oswald', sans-serif; font-weight: 700; font-size: 1.8rem; letter-spacing: 1px; text-shadow: 2px 2px 0 #000; }

        .navbar-nav .nav-link {
            color: white !important;
            font-family: 'Oswald', sans-serif;
            font-weight: 700;
            font-size: 1.3rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: 0.2s;
            transform: skewX(-10deg);
            margin: 0 5px;
        }

        .navbar-nav .nav-link:hover, .navbar-nav .nav-link:focus {
            color: var(--sbbl-gold) !important;
            background: rgba(0,0,0,0.3);
            transform: translate(-2px, -2px) skewX(-10deg);
            box-shadow: 3px 3px 0 #000;
        }

        /* Dropdown */
        .navbar .dropdown-menu {
            background-color: var(--sbbl-blue-3);
            border: 3px solid var(--sbbl-gold);
            border-radius: 0;
            box-shadow: 6px 6px 0 #000;
            z-index: var(--z-dropdown);
            margin-top: 15px;
        }

        .navbar .dropdown-item {
            color: white;
            font-family: 'Oswald', sans-serif;
            font-weight: 500;
            font-size: 1.2rem !important;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: 0.2s;
            border-bottom: 1px dashed rgba(255,255,255,0.2);
        }

        .navbar .dropdown-item:hover {
            background-color: var(--sbbl-gold);
            color: #000 !important;
            padding-left: 25px;
        }

        /* =====================================================
           COLLAPSE NAVBAR (MÓVIL)
        ===================================================== */
        @media (max-width: 767.98px) {
            .navbar-collapse {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background-color: var(--sbbl-blue-1);
                border-bottom: 4px solid var(--sbbl-gold);
                box-shadow: 0 4px 0 #000;
                z-index: var(--z-navbar);
                padding: 10px 0;
            }
            .navbar-nav .nav-link { transform: none; text-align: center; border-bottom: 1px solid #000; }
            .navbar-nav .nav-link:hover { transform: none; box-shadow: none; }
            .navbar .dropdown-menu { border: none; box-shadow: none; margin: 0; }
        }

        /* =====================================================
           SIDEBAR (DASHBOARD)
        ===================================================== */
        .dashboard-wrapper {
            display: flex;
            height: calc(100vh - 70px);
            overflow: hidden;
        }

        .sidebar {
            width: 260px;
            background-color: var(--sbbl-blue-3);
            border-right: 4px solid var(--sbbl-gold);
            padding: 20px;
            overflow-y: auto;
            flex-shrink: 0;
            z-index: var(--z-sidebar);
            box-shadow: 4px 0 0 #000;
        }

        .sidebar-title {
            font-family: 'Oswald', sans-serif;
            font-weight: 700;
            font-size: 2.5rem;
            text-align: center;
            color: var(--sbbl-gold);
            text-shadow: 2px 2px 0 #000;
            margin-bottom: 20px;
            letter-spacing: 2px;
        }

        .sidebar h4 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            color: #a8c0ff;
            font-size: 0.8rem;
            margin-top: 20px;
            border-bottom: 2px dashed #000;
            padding-bottom: 5px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            border-radius: 0;
            border: 2px solid #000;
            margin-bottom: 8px;
            cursor: pointer;
            background: var(--sbbl-blue-1);
            color: #fff;
            text-decoration: none;
            transition: 0.2s;
            font-family: 'Oswald', sans-serif;
            font-weight: 500;
            font-size: 1.2rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            transform: skewX(-5deg);
        }
        .sidebar-link > * { transform: skewX(5deg); }

        .sidebar-link:hover {
            background: var(--sbbl-gold);
            border-color: #000;
            color: #000;
            transform: translateX(10px) skewX(-5deg);
            box-shadow: 4px 4px 0 #000;
        }

        /* Sidebar móvil */
        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                left: -260px;
                top: 70px;
                height: calc(100vh - 70px);
                transition: left 0.3s ease;
            }
            .sidebar.open { left: 0; }
        }

        .sidebar-toggle {
            position: fixed; top: 85px; left: 15px;
            width: 50px; height: 50px;
            background-color: var(--sbbl-gold); color: #000;
            border: 3px solid #000; border-radius: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; box-shadow: 4px 4px 0 #000;
            cursor: pointer; z-index: var(--z-navbar);
            transform: skewX(-10deg); transition: 0.2s;
        }
        .sidebar-toggle > * { transform: skewX(10deg); }
        .sidebar-toggle:hover { background-color: var(--shonen-cyan); transform: translate(-2px,-2px) skewX(-10deg); box-shadow: 6px 6px 0 #000; }

        /* =====================================================
           CONTENIDO PRINCIPAL
        ===================================================== */
        .content-area {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background-color: var(--sbbl-blue-1);
            background-image: radial-gradient(rgba(255, 255, 255, 0.05) 2px, transparent 2px);
            background-size: 20px 20px;
        }

        /* =====================================================
           BOTONES FLOTANTES
        ===================================================== */
        .subscription-button {
            position: fixed; bottom: 20px; right: 20px;
            background: var(--sbbl-gold); color: #000;
            padding: 10px 20px; border-radius: 0;
            border: 3px solid #000; box-shadow: 5px 5px 0 var(--shonen-red);
            display: flex; align-items: center; gap: 10px;
            font-family: 'Oswald', sans-serif; font-weight: 700; font-size: 1.4rem; letter-spacing: 1px;
            cursor: pointer; transition: 0.2s;
            z-index: var(--z-floating); transform: skewX(-10deg);
            text-transform: uppercase;
        }
        .subscription-button > * { transform: skewX(10deg); }
        .subscription-button:hover { background: #fff; transform: translate(-3px,-3px) skewX(-10deg); box-shadow: 8px 8px 0 #000; }

        .subscription-tooltip {
            display: none; position: absolute; bottom: 70px; right: 0;
            background: #000; color: #fff; padding: 15px;
            border: 3px solid var(--sbbl-gold); border-radius: 0;
            width: 280px; box-shadow: 6px 6px 0 var(--sbbl-blue-3);
            z-index: calc(var(--z-floating) + 1);
            font-family: 'Montserrat', sans-serif; font-size: 0.9rem;
        }
        .subscription-tooltip h4 { font-family: 'Oswald', sans-serif; font-weight: 700; color: var(--sbbl-gold); font-size: 1.8rem; letter-spacing: 1px; text-transform: uppercase; margin-top:0;}
        .subscription-button:hover .subscription-tooltip { display: block; }

        .cart-floating-btn {
            position: fixed; bottom: 100px; right: 20px;
            background: var(--shonen-cyan); color: #000;
            padding: 15px; border-radius: 0; border: 3px solid #000;
            text-align: center; box-shadow: 5px 5px 0 var(--sbbl-blue-3);
            z-index: 1000; font-size: 22px; text-decoration: none;
            transform: skewX(-10deg); transition: 0.2s;
        }
        .cart-floating-btn > * { transform: skewX(10deg); }
        .cart-floating-btn:hover { background: #fff; transform: translate(-3px,-3px) skewX(-10deg); box-shadow: 8px 8px 0 var(--shonen-red); }
        .cart-badge {
            position: absolute; top: -15px; right: -15px;
            background: var(--shonen-red); color: white;
            border: 2px solid #000; font-family: 'Oswald', sans-serif; font-weight: 700;
            width: 30px; height: 30px; font-size: 1.2rem;
            display: flex; align-items: center; justify-content: center;
        }

        /* =====================================================
           COOKIES
        ===================================================== */
        .hidden { display: none !important; }

        .cookie-banner {
            position: fixed; bottom: 0; left: 0; right: 0;
            background: var(--sbbl-blue-1); border-top: 4px solid var(--sbbl-gold);
            color: white; padding: 1.5em;
            display: flex; justify-content: space-between; align-items: center;
            z-index: var(--z-cookie); box-shadow: 0 -5px 20px #000;
            font-weight: 700;
        }
        .cookie-banner a { color: var(--shonen-cyan); font-weight: 900; }

        .cookie-buttons button {
            margin-left: 0.5em; background: #000;
            border: 2px solid #fff; padding: 0.3em 1.5em;
            cursor: pointer; color: white; font-family: 'Oswald', sans-serif; font-weight: 700;
            font-size: 1.2rem; letter-spacing: 1px; transition: 0.2s; text-transform: uppercase;
            transform: skewX(-10deg);
        }
        .cookie-buttons button:hover { background: var(--sbbl-gold); color: #000; border-color: #000; box-shadow: 4px 4px 0 var(--shonen-red); transform: translate(-2px,-2px) skewX(-10deg); }

        .cookie-modal {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.85); display: flex; justify-content: center; align-items: center;
            z-index: var(--z-modal);
        }
        .cookie-modal-content {
            background: var(--sbbl-blue-2); padding: 2em; border: 4px solid #000;
            box-shadow: 10px 10px 0 var(--sbbl-gold); text-align: left;
            max-width: 600px; max-height: 90%; overflow-y: auto; color: #fff;
        }
        .cookie-modal-content h3 { font-family: 'Oswald', sans-serif; font-weight: 700; color: var(--sbbl-gold); font-size: 2.5rem; letter-spacing: 1px; text-shadow: 2px 2px 0 #000; margin-top:0; }
        .cookie-category { margin: 15px 0; padding: 15px; background: var(--sbbl-blue-1); border: 2px solid #000; border-left: 5px solid var(--sbbl-gold); }
        .cookie-category label { font-weight: 900; font-size: 1.1rem; cursor: pointer; }
        .cookie-category small { display: block; color: #aaa; font-size: 0.85em; margin-top: 5px; font-weight: normal; }

        /* =====================================================
           FOOTER DE MANDO
        ===================================================== */
        .command-footer {
            background: var(--sbbl-blue-1);
            border-top: 4px solid var(--shonen-red);
            box-shadow: 0 -4px 0 #000;
            color: #ddd;
            padding-top: 3rem;
            padding-bottom: 1.5rem;
            position: relative;
        }
        .footer-heading {
            font-family: 'Oswald', sans-serif; font-weight: 700;
            color: var(--sbbl-gold);
            text-transform: uppercase;
            font-size: 1.8rem;
            letter-spacing: 1px;
            margin-bottom: 1.2rem;
            text-shadow: 2px 2px 0 #000;
        }
        .footer-desc { font-size: 0.95rem; line-height: 1.6; font-weight: 600; }
        .footer-links { list-style: none; padding: 0; margin: 0; }
        .footer-links li { margin-bottom: 0.8rem; }
        .footer-links a {
            color: #fff; text-decoration: none; transition: all 0.2s ease;
            font-family: 'Oswald', sans-serif; font-weight: 500; font-size: 1.3rem; letter-spacing: 1px; text-transform: uppercase;
        }
        .footer-links a:hover { color: var(--shonen-cyan); padding-left: 10px; text-shadow: 2px 2px 0 #000; }

        .social-icons-footer a {
            display: inline-flex; align-items: center; justify-content: center;
            width: 45px; height: 45px; background: var(--sbbl-blue-3);
            border: 2px solid #000; border-radius: 0; margin-right: 10px; color: #fff;
            transition: 0.2s; font-size: 1.2rem; text-decoration: none; transform: skewX(-10deg);
        }
        .social-icons-footer a > * { transform: skewX(10deg); }
        .social-icons-footer a:hover { background: var(--sbbl-gold); color: #000; box-shadow: 4px 4px 0 var(--shonen-red); transform: translate(-2px,-2px) skewX(-10deg); }

        .copyright-bar {
            border-top: 2px dashed #000; margin-top: 3rem; padding-top: 1.5rem;
            font-size: 0.85rem; color: #aaa; text-align: center; font-weight: 700;
        }
    </style>

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}

        gtag('consent', 'default', {
            'analytics_storage': 'denied',
            'ad_storage': 'denied',
            'ad_user_data': 'denied',
            'ad_personalization': 'denied',
            'wait_for_update': 500
        });

        function updateConsent(analytics, marketing) {
            gtag('consent', 'update', {
                'analytics_storage': analytics ? 'granted' : 'denied',
                'ad_storage': marketing ? 'granted' : 'denied',
                'ad_user_data': marketing ? 'granted' : 'denied',
                'ad_personalization': marketing ? 'granted' : 'denied'
            });
        }
    </script>
</head>
<body style="display: flex; flex-direction: column; min-height: 100vh;">
    <div id="app" style="flex: 1; display: flex; flex-direction: column;">

        <nav class="navbar navbar-expand-md shadow-sm">
            <div class="container">
                <a class="navbar-brand d-none d-md-none d-xl-flex align-items-center" href="{{ url('/') }}">
                    <img src="/images/logo_new.png" alt="Logo Spanish BeyBattle League" width="60" height="50" class="me-2">
                    <span class="text-white">SPANISH BEYBATTLE LEAGUE</span>
                </a>
                <a class="navbar-brand d-block d-md-block d-xl-none" href="{{ url('/') }}">
                    <img src="/images/logo_new.png" alt="Logo SBBL" width="60" height="50">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}" style="border: 2px solid var(--sbbl-gold); border-radius: 0;">
                    <span class="navbar-toggler-icon" style="color:var(--sbbl-gold)"><i class="fas fa-bars" style="font-size:1.5em; line-height: 1.5;"></i></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto"></ul>

                    <ul class="navbar-nav ms-auto px-2">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}" style="color: var(--sbbl-gold) !important;">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item"><a class="nav-link" href="{{ route('equipos.index') }}" style="color: var(--sbbl-gold) !important;">EQUIPOS</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('inicio.events') }}">EVENTOS</a></li>

                            <li class="nav-item dropdown">
                                <a id="navbarDropdownCommunity" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" v-pre>COMUNIDAD</a>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownCommunity">
                                    <a class="dropdown-item" href="{{ route('profiles.index') }}"><i class="fas fa-user-ninja me-2"></i> Bladers</a>
                                    <a class="dropdown-item" href="{{ route('blog.index') }}"><i class="fas fa-newspaper me-2"></i> Blog</a>
                                </div>
                            </li>

                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" v-pre>COMPETITIVO</a>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('profiles.ranking') }}"><i class="fas fa-trophy me-2"></i> Rankings</a>
                                    <a class="dropdown-item" href="{{ route('profiles.splits') }}"><i class="fas fa-code-branch me-2"></i> Splits</a>
                                    <a class="dropdown-item" href="{{ route('inicio.rules') }}"><i class="fas fa-gavel me-2"></i> Reglamento</a>
                                    <a class="dropdown-item" href="{{ route('inicio.stats') }}"><i class="fas fa-chart-bar me-2"></i> Estadísticas</a>
                                    <a class="dropdown-item" href="{{ route('combates') }}"><i class="fas fa-bullseye me-2"></i> Combates</a>
                                </div>
                            </li>

                            @auth
                                @if (Auth::user()->hasAnyRole(['juez', 'admin', 'arbitro', 'revisor']))
                                    <li class="nav-item">
                                        <a id="navbarAdmin" class="nav-link" style="color: var(--shonen-red) !important;" href="{{ route('admin.dashboard') }}" target="_blank">ADMIN</a>
                                    </li>
                                @endif
                            @endauth

                            <li class="nav-item dropdown">
                                <a id="navbarDropdownProfile" class="nav-link dropdown-toggle" style="color: var(--shonen-cyan) !important;" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" v-pre>
                                    {{ strtoupper(Auth::user()->name) }}
                                </a>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownProfile">
                                    <a class="dropdown-item" href="{{ route('profiles.show', ['profile' => Auth::user()->id]) }}">Ver perfil</a>
                                    <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ __('Logout') }}</a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main style="flex: 1;">
            <div class="container-fluid p-0">
                @if (Request::is('beyblade-database*'))
                    <div class="row m-0">
                        @php
                            $fondo = '/../images/webTile2.png';
                            if (isset($blade)) {
                                switch ($blade->sistema) {
                                    case 'UX': $fondo = '/../images/FONDO_UX.webp'; break;
                                    case 'CX': $fondo = '/../images/FONDO_CX.webp'; break;
                                    case 'BX': $fondo = '/../images/FONDO_BX.webp'; break;
                                }
                            }
                            if (isset($beyblade->sistema)) {
                                switch ($beyblade->sistema) {
                                    case 'UX': $fondo = '/../images/FONDO_UX.webp'; break;
                                    case 'CX': $fondo = '/../images/FONDO_CX.webp'; break;
                                    case 'BX': $fondo = '/../images/FONDO_BX.webp'; break;
                                }
                            }
                        @endphp
                        <div class="col-12 fondo-database p-0" style="background-image: linear-gradient(to right, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('{{ $fondo }}'); background-size: cover; background-position: center; min-height: 80vh;">
                            @yield('content')
                        </div>
                    </div>
                @elseif (Request::is('dashboard*'))

                    <button id="sidebarToggle" class="sidebar-toggle d-lg-none" aria-label="Abrir panel">
                        <div><i class="fas fa-cog"></i></div>
                    </button>

                    <div class="row m-0">
                        <div class="col-12 p-0">
                            <div class="dashboard-wrapper">
                                <nav class="sidebar" id="sidebar">
                                    <h2 class="sidebar-title">PANEL ADMIN</h2>
                                    <h4>ÁRBITROS</h4>
                                    <a href="{{ route('events.indexAdmin') }}" class="sidebar-link"><div><i class="fas fa-calendar-alt"></i> Eventos</div></a>

                                    @if (Auth::user()->hasAnyRole(['juez', 'admin', 'arbitro']))
                                    <h4>JUECES</h4>
                                        <a href="{{ route('teams_versus.index') }}" class="sidebar-link"><div><i class="fas fa-users-cog"></i> Equipos Duelos</div></a>
                                        <a href="{{ route('equipos.indexAdmin') }}" class="sidebar-link"><div><i class="fas fa-users"></i> Equipos</div></a>
                                        <a href="{{ route('admin.inventory.index') }}" class="sidebar-link"><div><i class="fas fa-boxes"></i> Inventario</div></a>
                                        <a href="{{ route('database.indexPartes') }}" class="sidebar-link"><div><i class="fas fa-cogs"></i> Partes</div></a>
                                        <a href="{{ route('database.indexBeys') }}" class="sidebar-link"><div><i class="fas fa-cube"></i> Crear Beyblades</div></a>
                                    @endif

                                    @if(Auth::user()->hasRole('admin'))
                                    <h4>ADMIN CORE</h4>
                                        <a href="{{ route('admin.dashboard.reviews') }}" class="sidebar-link"><div><i class="fas fa-chart-bar"></i> Revisiones</div></a>
                                        <a href="{{ route('productos.index') }}" class="sidebar-link"><div><i class="fas fa-shopping-bag"></i> Productos</div></a>
                                        <a href="{{ route('profiles.indexAdmin') }}" class="sidebar-link"><div><i class="fas fa-user-shield"></i> Gestión</div></a>
                                        <a href="{{ route('admin.treasury.index') }}" class="sidebar-link"><div><i class="fas fa-file-invoice-dollar"></i> Tesorería</div></a>
                                        <a href="{{ route('profiles.indexAdminX') }}" class="sidebar-link"><div><i class="fas fa-user-cog"></i> Usuarios X</div></a>
                                        <a href="{{ route('trophies.index') }}" class="sidebar-link"><div><i class="fas fa-award"></i> Asignaciones</div></a>
                                        <a href="{{ route('index.anuncios') }}" class="sidebar-link"><div><i class="fas fa-bullhorn"></i> Anuncios</div></a>
                                        <a href="{{ route('nacional.ranking') }}" class="sidebar-link"><div><i class="fas fa-flag"></i> List Nacional</div></a>
                                    @endif
                                </nav>

                                <main class="content-area">
                                    @yield('content')
                                </main>
                            </div>
                        </div>
                    </div>

                @else
                    <div class="row m-0 flex-grow-1">

                        <div class="col-md-2 p-0 d-none d-md-block"
                            style="background: linear-gradient(to right, rgba(30, 42, 71, 1), rgba(39, 41, 91, 0.5)), url('/images/s2tile_3.png') repeat left;">
                        </div>

                        <div class="col-md-8 col-sm-12 p-0"
                            style="background-color: var(--sbbl-blue-2); min-height: 70vh; position: relative; z-index: 3; border-left: 4px solid #000; border-right: 4px solid #000; box-shadow: 0 0 20px rgba(0,0,0,0.8);">

                            {{-- Efecto sutil de rejilla --}}
                            <div style="position: absolute; inset: 0; background-image: radial-gradient(rgba(255, 255, 255, 0.1) 2px, transparent 2px); background-size: 20px 20px; pointer-events: none; z-index: 0;"></div>

                            {{-- Contenido inyectado --}}
                            <div style="position: relative; z-index: 1;">
                                @yield('content')
                            </div>
                        </div>

                        <div class="col-md-2 p-0 d-none d-md-block"
                            style="background: linear-gradient(to left, rgba(30, 42, 71, 1), rgba(39, 41, 91, 0.5)), url('/images/s2tile_3.png') repeat right;">
                        </div>

                    </div>
                @endif
            </div>

            <div class="subscription-button" id="subscriptionButton" onclick="window.location.href='{{ route('planes.index') }}'">
                <div>
                    <i class="fas fa-credit-card me-2"></i>
                    <span class="d-none d-md-inline font-weight-bold">AURA SBBL</span>
                </div>
                <div class="subscription-tooltip">
                    <h4>¡DESBLOQUEA TU AURA!</h4>
                    <p>Obtén acceso a estadísticas avanzadas y contenido exclusivo con nuestras suscripciones.</p>
                </div>
            </div>

            @if (isset($cantidadCarrito) && $cantidadCarrito != 0)
                <a href="{{ route('carrito.show') }}" class="cart-floating-btn">
                    <div>
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-badge">{{ $cantidadCarrito ?? 0 }}</span>
                    </div>
                </a>
            @endif

        </main>

        <footer class="command-footer">
            <div class="container">
                <div class="row gy-4">

                    {{-- COLUMNA 1: SOBRE NOSOTROS (MISIÓN) --}}
                    <div class="col-lg-4 col-md-6 pe-lg-5">
                        <h5 class="footer-heading">EL EQUIPO SBBL</h5>
                        <p class="footer-desc">
                            La <strong>Spanish BeyBattle League</strong> es la organización central para todos los bladers en España.
                            Nuestro objetivo es unificar la comunidad, organizar torneos oficiales y crear un entorno competitivo y divertido para todos.
                        </p>
                        <div class="social-icons-footer mt-3">
                            <a href="https://discord.gg/JCtAHfJ8Ht" target="_blank" title="Discord"><div><i class="fab fa-discord"></i></div></a>
                            <a href="https://www.instagram.com/sbbl_oficial/" target="_blank" title="Instagram"><div><i class="fab fa-instagram"></i></div></a>
                            <a href="https://www.youtube.com/@sbbl_oficial" target="_blank" title="Youtube"><div><i class="fab fa-youtube"></i></div></a>
                            <a href="https://x.com/SBBLOficial" target="_blank" title="X (Twitter)"><div><i class="fab fa-twitter"></i></div></a>
                        </div>
                    </div>

                    {{-- COLUMNA 2: NAVEGACIÓN PRINCIPAL --}}
                    <div class="col-lg-2 col-md-3 col-6">
                        <h5 class="footer-heading">MAPA</h5>
                        <ul class="footer-links">
                            <li><a href="{{ url('/') }}">Inicio</a></li>
                            <li><a href="{{ route('profiles.index') }}">Bladers</a></li>
                            <li><a href="{{ route('equipos.index') }}">Equipos</a></li>
                            <li><a href="{{ route('blog.index') }}">Noticias</a></li>
                            <li><a href="{{ route('inicio.contact') }}">Contacto</a></li>
                        </ul>
                    </div>

                    {{-- COLUMNA 3: COMPETITIVO --}}
                    <div class="col-lg-3 col-md-3 col-6">
                        <h5 class="footer-heading">ARENA</h5>
                        <ul class="footer-links">
                            <li><a href="{{ route('inicio.events') }}">Calendario de Misiones</a></li>
                            <li><a href="{{ route('profiles.ranking') }}">Rankings Oficiales</a></li>
                            <li><a href="{{ route('profiles.splits') }}">Temporadas</a></li>
                            <li><a href="{{ route('inicio.rules') }}">Reglamento</a></li>
                        </ul>
                    </div>

                    {{-- COLUMNA 4: ENLACES RÁPIDOS --}}
                    <div class="col-lg-3 col-md-12">
                        <h5 class="footer-heading">SISTEMAS</h5>
                        <ul class="footer-links">
                            <li><a href="{{ route('inicio.halloffame') }}" style="color: var(--sbbl-gold);"><i class="fas fa-trophy me-1"></i> Salón de la Fama</a></li>
                            <li><a href="{{ route('inicio.resumen_semanal') }}"><i class="fas fa-bullseye me-1"></i> Data_Log Semanal</a></li>
                            <li><a href="{{ route('inicio.stats') }}"><i class="fas fa-chart-bar me-1"></i> Radar Meta</a></li>
                            @guest
                                <li class="mt-3"><a href="{{ route('register') }}" class="btn-shonen btn-shonen-info" style="font-size: 1.1rem; padding: 5px 15px;"><div>Únete a la Batalla</div></a></li>
                            @endguest
                        </ul>
                    </div>

                </div>

                {{-- BARRA DE COPYRIGHT --}}
                <div class="copyright-bar row align-items-center">
                    <div class="col-md-6 text-center text-md-start">
                        &copy; {{ date('Y') }} <strong>Spanish BeyBattle League</strong>. TODOS LOS DERECHOS RESERVADOS.
                    </div>
                    <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                        FORJADO POR LA COMUNIDAD PARA LA COMUNIDAD.
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <div id="cookie-banner" class="cookie-banner hidden">
        <p class="m-0 mb-2 mb-md-0" style="max-width: 70%;">El sistema requiere recolectar datos de combate (cookies) para funcionar a máxima potencia. <a href="{{ route('politica.cookies') }}" target="_blank">Leer manifiesto</a></p>
        <div class="cookie-buttons d-flex flex-wrap gap-2">
            <button onclick="acceptOnlyNecessary()">Rechazar</button>
            <button onclick="showSettings()" style="background: #333;">Ajustes</button>
            <button onclick="acceptAllCookies()" style="background: var(--sbbl-gold); color: #000;">ACEPTAR TODAS</button>
        </div>
    </div>

    <div id="cookie-settings-modal" class="cookie-modal hidden">
        <div class="cookie-modal-content">
            <h3>PROTOCOLOS DE DATOS</h3>
            <p class="fw-bold mb-4">Selecciona los permisos de acceso de tu terminal:</p>

            <div class="cookie-category">
                <label><input type="checkbox" id="necessaryCookies" checked disabled class="me-2"> Núcleo del Sistema (Necesarias)</label>
                <small>(Siempre activas, son esenciales para el funcionamiento del sitio)</small>
            </div>

            <div class="cookie-category">
                <label><input type="checkbox" id="analyticsCookies" class="me-2"> Escáner Analítico</label>
                <small>(Nos ayudan a entender cómo usas el sitio mediante Google Analytics)</small>
            </div>

            <div class="cookie-category">
                <label><input type="checkbox" id="marketingCookies" class="me-2"> Transmisiones Comerciales</label>
                <small>(Para mostrar anuncios relevantes mediante Google AdSense y mantener los servidores)</small>
            </div>

            <div class="cookie-buttons mt-4 text-end">
                <button onclick="cookieModal.classList.add('hidden')" style="background: #333;">Cancelar</button>
                <button onclick="saveSettings()" style="background: var(--sbbl-blue-3);">Confirmar Protocolo</button>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/app.js') }}" defer></script>

    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        // Función para descargar imágenes generales (SBBL Wrapped)
        function downloadImage() {
            const element = document.getElementById('wrapped');
            if(element) {
                html2canvas(element, {
                    useCORS: true,
                    scale: 2
                }).then(canvas => {
                    const link = document.createElement('a');
                    link.download = 'SBBL_Wrapped.png';
                    link.href = canvas.toDataURL("image/png");
                    link.click();
                });
            }
        }

        // Cookies - Funciones
        const cookieBanner = document.getElementById('cookie-banner');
        const cookieModal = document.getElementById('cookie-settings-modal');

        // Inicializar Consent Mode por defecto
        window.dataLayer = window.dataLayer || [];
        function gtag(){ dataLayer.push(arguments); }

        gtag('consent', 'default', {
            'ad_storage': 'denied',
            'analytics_storage': 'denied'
        });

        // Cargar preferencias al iniciar
        document.addEventListener('DOMContentLoaded', () => {
            const consent = localStorage.getItem("cookieConsent");
            if (!consent) {
                cookieBanner.classList.remove("hidden");
            } else {
                const prefs = JSON.parse(consent);
                setCheckboxStates(prefs);
                updateConsent(prefs.analytics, prefs.marketing);
                if (prefs.analytics) loadAnalytics();
                if (prefs.marketing) loadMarketing();
            }
        });

        function setCheckboxStates(prefs) {
            document.getElementById('analyticsCookies').checked = prefs.analytics || false;
            document.getElementById('marketingCookies').checked = prefs.marketing || false;
        }

        function acceptOnlyNecessary() {
            saveCookiePreferences(false, false);
            cookieBanner.classList.add("hidden");
            trackCookieEvent('reject', 'all');
        }

        function acceptAllCookies() {
            saveCookiePreferences(true, true);
            cookieBanner.classList.add("hidden");
            trackCookieEvent('accept', 'all');
        }

        function showSettings() {
            const consent = localStorage.getItem("cookieConsent");
            if (consent) {
                const prefs = JSON.parse(consent);
                setCheckboxStates(prefs);
            }
            cookieModal.classList.remove("hidden");
        }

        function saveSettings() {
            const analytics = document.getElementById('analyticsCookies').checked;
            const marketing = document.getElementById('marketingCookies').checked;
            saveCookiePreferences(analytics, marketing);
            cookieModal.classList.add("hidden");
        }

        function saveCookiePreferences(analytics, marketing) {
            const consent = {
                analytics,
                marketing,
                date: new Date().toISOString()
            };
            localStorage.setItem("cookieConsent", JSON.stringify(consent));

            updateConsent(analytics, marketing);

            if (analytics) loadAnalytics();
            if (marketing) loadMarketing();

            trackCookieEvent('update', `analytics:${analytics},marketing:${marketing}`);
        }

        function updateConsent(analytics, marketing) {
            gtag('consent', 'update', {
                'ad_storage': marketing ? 'granted' : 'denied',
                'analytics_storage': analytics ? 'granted' : 'denied'
            });
        }

        // Cargar Google Analytics
        function loadAnalytics() {
            if (!window.gaLoaded) {
                window.gaLoaded = true;

                const script = document.createElement('script');
                script.src = "https://www.googletagmanager.com/gtag/js?id=G-KSK91ZXWE3";
                script.async = true;
                document.head.appendChild(script);

                script.onload = () => {
                    gtag('js', new Date());
                    gtag('config', 'G-KSK91ZXWE3', {
                        'anonymize_ip': true,
                        'allow_ad_personalization_signals': localStorage.getItem("cookieConsent")
                            ? JSON.parse(localStorage.getItem("cookieConsent")).marketing
                            : false
                    });
                };
            }
        }

        // Cargar Google AdSense
        function loadMarketing() {
            if (!window.adsenseLoaded) {
                window.adsenseLoaded = true;

                const adsenseScript = document.createElement('script');
                adsenseScript.src = "https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7050675485532592";
                adsenseScript.async = true;
                adsenseScript.crossOrigin = "anonymous";
                document.head.appendChild(adsenseScript);

                adsenseScript.onload = () => {
                    (window.adsbygoogle = window.adsbygoogle || []).push({
                        google_ad_client: "ca-pub-7050675485532592",
                        enable_page_level_ads: true
                    });
                };
            }
        }

        // Registrar evento de consentimiento
        function trackCookieEvent(action, type) {
            if (typeof gtag === 'function') {
                gtag('event', 'cookie_consent', {
                    'event_category': 'cookies',
                    'event_label': `${action}_${type}`
                });
            }
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');

            if (toggle && sidebar) {
                toggle.addEventListener('click', function () {
                    sidebar.classList.toggle('open');
                });
            }
        });
    </script>

    @yield('scripts')
</body>
</html>
