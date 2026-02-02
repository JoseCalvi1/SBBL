<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @yield('head')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <link rel="shortcut icon" type="image/png" href="{{ asset('/images/sbbl.png') }}">

    @yield('styles')
    <style>
        /* =====================================================
        VARIABLES DE Z-INDEX (ORDEN GLOBAL)
        ===================================================== */
        :root {
            --z-navbar: 1100;
            --z-dropdown: 1110;
            --z-sidebar: 1090;
            --z-floating: 1080;
            --z-cookie: 1070;
            --z-modal: 1200;
        }

        /* =====================================================
        RESET BÁSICO
        ===================================================== */
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: HelveticaNeue, sans-serif;
        }

        /* =====================================================
        NAVBAR SUPERIOR
        ===================================================== */
        nav.navbar {
            background-color: #1e2a47;
            height: 70px;
            position: relative;
            z-index: var(--z-navbar);
        }

        .navbar-nav .nav-link {
            color: white !important;
        }

        .navbar-nav .nav-link:hover {
            color: gold !important;
        }

        /* Dropdown */
        .navbar .dropdown-menu {
            background-color: #283b63;
            z-index: var(--z-dropdown);
        }

        .navbar .dropdown-menu a {
            color: white;
        }

        .navbar .dropdown-menu a:hover {
            color: #283b63 !important;
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
                background-color: #1e2a47;
                z-index: var(--z-navbar);
                padding: 10px 0;
            }

            .navbar .dropdown-menu {
                position: static;
                float: none;
                margin: 0.25rem 1rem;
                border-radius: 8px;
            }
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
            background-color: #283b63;
            padding: 20px;
            overflow-y: auto;
            flex-shrink: 0;
            z-index: var(--z-sidebar);
        }

        .sidebar-title {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            color: #a8c0ff;
            margin-bottom: 20px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 6px;
            cursor: pointer;
            background: #1f335f;
            color: #fff;
            text-decoration: none;
            transition: 0.2s;
        }

        .sidebar-link:hover {
            background: #3b4f8c;
            transform: translateX(4px);
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

            .sidebar.open {
                left: 0;
            }
        }

        /* =====================================================
        BOTÓN TOGGLE SIDEBAR (MÓVIL)
        ===================================================== */
        .sidebar-toggle {
            position: fixed;
            top: 85px; /* debajo de la navbar */
            left: 15px;
            width: 48px;
            height: 48px;
            background-color: #ffc107;
            color: #27295B;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            cursor: pointer;
            z-index: var(--z-navbar);
        }

        .sidebar-toggle:hover {
            background-color: #e0a800;
        }


        /* =====================================================
        CONTENIDO PRINCIPAL
        ===================================================== */
        .content-area {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background-color: #1e2a47;
        }

        /* =====================================================
        BOTÓN DE SUSCRIPCIÓN (FLOATING)
        ===================================================== */
        .subscription-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #ffc107;
            color: #27295B;
            padding: 10px 15px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: background 0.3s ease;
            z-index: var(--z-floating);
        }

        .subscription-button:hover {
            background: #e0a800;
        }

        .subscription-button i {
            font-size: 20px;
        }

        .subscription-tooltip {
            display: none;
            position: absolute;
            bottom: 60px;
            right: 0;
            background: white;
            color: #27295B;
            padding: 15px;
            border-radius: 8px;
            width: 250px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: calc(var(--z-floating) + 1);
        }

        .subscription-button:hover .subscription-tooltip {
            display: block;
        }

        /* =====================================================
        COOKIES
        ===================================================== */
        .hidden {
            display: none !important;
        }

        .cookie-banner {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #222;
            color: white;
            padding: 1em;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: var(--z-cookie);
        }

        .cookie-buttons button {
            margin-left: 0.5em;
            background: #0af;
            border: none;
            padding: 0.5em 1em;
            cursor: pointer;
            color: white;
        }

        .cookie-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: var(--z-modal);
        }

        .cookie-modal-content {
            background: white;
            padding: 2em;
            border-radius: 8px;
            text-align: left;
            max-width: 90%;
            max-height: 90%;
            overflow-y: auto;
        }

        .cookie-category {
            margin: 15px 0;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 5px;
        }

        .cookie-category small {
            display: block;
            color: #666;
            font-size: 0.8em;
            margin-top: 5px;
        }
        /* --- FOOTER DE MANDO --- */
.command-footer {
    background: #0b0c10; /* Fondo muy oscuro */
    border-top: 3px solid #1f2833; /* Línea separadora */
    color: #c5c6c7;
    padding-top: 3rem;
    padding-bottom: 1.5rem;
    font-family: 'Segoe UI', sans-serif;
    position: relative;
}

/* Efecto de "scanline" sutil en el borde superior */
.command-footer::before {
    content: '';
    position: absolute; top: -3px; left: 0; width: 100%; height: 3px;
    background: linear-gradient(90deg, #0b0c10 0%, #45a29e 50%, #0b0c10 100%);
    opacity: 0.7;
}

.footer-heading {
    color: #66fcf1; /* Cian neón suave */
    text-transform: uppercase;
    font-weight: 700;
    margin-bottom: 1.2rem;
    font-size: 1rem;
    letter-spacing: 1px;
}

.footer-desc {
    font-size: 0.9rem;
    line-height: 1.6;
    color: #9fa1a6;
    margin-bottom: 1.5rem;
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 0.6rem;
}

.footer-links a {
    color: #c5c6c7;
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 0.95rem;
}

.footer-links a:hover {
    color: #66fcf1;
    padding-left: 5px; /* Pequeño desplazamiento al hacer hover */
}

.social-icons-footer a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px; height: 40px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
    margin-right: 10px;
    color: #fff;
    transition: 0.3s;
    font-size: 1.1rem;
    text-decoration: none;
}

.social-icons-footer a:hover {
    background: #66fcf1;
    color: #0b0c10;
    transform: translateY(-3px);
}

.copyright-bar {
    border-top: 1px solid rgba(255,255,255,0.05);
    margin-top: 3rem;
    padding-top: 1.5rem;
    font-size: 0.85rem;
    color: #666;
    text-align: center;
}
    </style>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

      <!-- Configuración inicial de consentimiento (antes de cargar cualquier script) -->
      <script>
        // Configuración inicial del dataLayer
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}

        // Establecer consentimiento denegado por defecto
        gtag('consent', 'default', {
            'analytics_storage': 'denied',
            'ad_storage': 'denied',
            'ad_user_data': 'denied',
            'ad_personalization': 'denied',
            'wait_for_update': 500
        });

        // Función para actualizar el consentimiento
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
<body style="display: flex; flex-direction: column; min-height: 100vh; height: 100%; font-family: HelveticaNeue">
    <div id="app" style="flex: 1;">
<nav class="navbar navbar-expand-md shadow-sm" style="background-color: #1e2a47;">
    <div class="container">
        <a class="navbar-brand d-none d-md-none d-xl-block" href="{{ url('/') }}">
            <img src="/images/logo_new.png" alt="Logo Spanish BeyBattle League" width="60" height="50">
            <span style="color: white;">Spanish BeyBattle League</span>
        </a>
        <a class="navbar-brand d-block d-md-block d-xl-none" href="{{ url('/') }}">
            <img src="/images/logo_new.png" alt="Logo SBBL" width="60" height="50">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon" style="color:white"><i class="fas fa-bars" style="font-size:2em;"></i></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav me-auto"></ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ms-auto px-2" style="color: white;">
                <!-- Authentication Links -->
                @guest
                    @if (Route::has('login'))
                        <li class="nav-item">
                            <a class="nav-link" style="color: white;" href="{{ route('login') }}">{{ __('Login') }}</a>
                        </li>
                    @endif

                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" style="color: white;" href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                    @endif
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('equipos.index') }}" style="color: gold;">
                            {{ 'EQUIPOS' }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('inicio.events') }}" style="color: white;">
                            {{ 'EVENTOS' }}
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a id="navbarDropdownCommunity" style="color: white;" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" v-pre>
                            {{ 'COMUNIDAD' }}
                        </a>

                        <div class="dropdown-menu dropdown-menu-end" style="background-color: #283b63" aria-labelledby="navbarDropdownCommunity">
                            <a class="dropdown-item menu-item-2" style="color: white; font-size:1.2em;" href="{{ route('profiles.index') }}">
                                <i class="fas fa-user-ninja me-2"></i> {{ 'Bladers' }}
                            </a>
                            <a class="dropdown-item menu-item-2" style="color: white; font-size:1.2em;" href="{{ route('blog.index') }}">
                                <i class="fas fa-newspaper me-2"></i> {{ 'Blog' }}
                            </a>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" style="color: white;" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" v-pre>
                            {{ 'COMPETITIVO' }}
                        </a>

                        <div class="dropdown-menu dropdown-menu-end" style="background-color: #283b63" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item menu-item-2" style="color: white; font-size:1.2em;" href="{{ route('profiles.ranking') }}">
                                <i class="fas fa-trophy me-2"></i> {{ 'Rankings' }}
                            </a>
                            <a class="dropdown-item menu-item-2" style="color: white; font-size:1.2em;" href="{{ route('profiles.splits') }}">
                                <i class="fas fa-code-branch me-2"></i> {{ 'Splits' }}
                            </a>
                            <a class="dropdown-item menu-item-2" style="color: white; font-size:1.2em;" href="{{ route('inicio.rules') }}">
                                <i class="fas fa-gavel me-2"></i> {{ 'Reglamento' }}
                            </a>
                            <a class="dropdown-item menu-item-2" style="color: white; font-size:1.2em;" href="{{ route('inicio.stats') }}">
                                <i class="fas fa-chart-bar me-2"></i> {{ 'Estadísticas' }}
                            </a>
                            <a class="dropdown-item menu-item-2" style="color: white; font-size:1.2em;" href="{{ route('combates') }}">
                                <i class="fas fa-bullseye me-2"></i> {{ 'Combates' }}
                            </a>
                        </div>
                    </li>

                    @auth
                        @if (Auth::user()->is_referee || Auth::user()->is_admin)
                            <li class="nav-item">
                                <a id="navbarAdmin" style="color: white;" class="nav-link" href="{{ route('admin.dashboard') }}" target="_blank">
                                    {{ 'ADMIN' }}
                                </a>
                            </li>
                        @endif
                    @endauth

                    <li class="nav-item dropdown">
                        <a id="navbarDropdownProfile" style="color: white;" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" v-pre>
                            {{ strtoupper(Auth::user()->name) }}
                        </a>

                        <div class="dropdown-menu dropdown-menu-end" style="background-color: #283b63" aria-labelledby="navbarDropdownProfile">
                            <a class="dropdown-item" style="color: white;" href="{{ route('profiles.show', ['profile' => Auth::user()->id]) }}">
                                {{ 'Ver perfil' }}
                            </a>

                            <a class="dropdown-item" style="color: white;" href="{{ route('logout') }}"
                               onclick="event.preventDefault();
                                             document.getElementById('logout-form').submit();">
                                {{ __('Logout') }}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>

        <main>
            <div class="container-fluid">
                @if (Request::is('beyblade-database*'))
                    <!-- Vista a ancho completo -->
                    <div class="row">
                        @php
                            $fondo = '/../images/webTile2.png'; // Imagen por defecto

                            if (isset($blade)) {
                                switch ($blade->sistema) {
                                    case 'UX':
                                        $fondo = '/../images/FONDO_UX.webp';
                                        break;
                                    case 'CX':
                                        $fondo = '/../images/FONDO_CX.webp';
                                        break;
                                    case 'BX':
                                        $fondo = '/../images/FONDO_BX.webp';
                                        break;
                                }
                            }

                            if (isset($beyblade->sistema)) {
                                switch ($beyblade->sistema) {
                                    case 'UX':
                                        $fondo = '/../images/FONDO_UX.webp';
                                        break;
                                    case 'CX':
                                        $fondo = '/../images/FONDO_CX.webp';
                                        break;
                                    case 'BX':
                                        $fondo = '/../images/FONDO_BX.webp';
                                        break;
                                }
                            }
                        @endphp

                        <div class="col-12 fondo-database"
                            style="background-image: linear-gradient(to right, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('{{ $fondo }}');
                                background-size: auto;
                                background-position: left;">
                            @yield('content')
                        </div>
                    </div>
                @elseif (Request::is('dashboard*'))

                <button id="sidebarToggle" class="sidebar-toggle d-lg-none" aria-label="Abrir panel">
                    <i class="fas fa-cog"></i>
                </button>

                <div class="row">
                    <div class="col-12 p-0">
                            {{-- DASHBOARD: SIDEBAR + CONTENIDO (ACTIVADO SOLO EN RUTAS 'dashboard*') --}}
                        <div class="dashboard-wrapper">
                            {{-- SIDEBAR LATERAL --}}
                            <nav class="sidebar text-white" id="sidebar">
                                <h2 class="sidebar-title">Panel</h2>
                                <h4>ÁRBITROS</h4>
                                <a href="{{ route('events.indexAdmin') }}" class="sidebar-link"><i class="fas fa-calendar-alt"></i> Eventos</a>
                                @if(Auth::user()->is_jury || Auth::user()->is_admin)
                                <h4>JUECES</h4>
                                    <a href="{{ route('teams_versus.index') }}" class="sidebar-link"><i class="fas fa-users-cog"></i> Equipos Duelos</a>
                                    <a href="{{ route('equipos.indexAdmin') }}" class="sidebar-link"><i class="fas fa-users"></i> Equipos</a>
                                    <a href="{{ route('database.indexPartes') }}" class="sidebar-link"><i class="fas fa-cogs"></i> Partes Beyblades</a>
                                    <a href="{{ route('database.indexBeys') }}" class="sidebar-link"><i class="fas fa-cube"></i> Crear Beyblades</a>
                                @endif
                                @if(Auth::user()->is_admin)
                                <h4>ADMIN</h4>
                                    <a href="{{ route('admin.dashboard.reviews') }}" class="sidebar-link"><i class="fas fa-chart-bar"></i>Revisiones</a>
                                    <a href="{{ route('productos.index') }}" class="sidebar-link"><i class="fas fa-shopping-bag"></i> Productos</a>
                                    <a href="{{ route('profiles.indexAdmin') }}" class="sidebar-link"><i class="fas fa-user-shield"></i> Gestión Usuarios</a>
                                    <a href="{{ route('profiles.indexAdminX') }}" class="sidebar-link"><i class="fas fa-user-cog"></i> Usuarios X</a>
                                    <a href="{{ route('trophies.index') }}" class="sidebar-link"><i class="fas fa-award"></i> Asignaciones</a>
                                    <a href="{{ route('index.anuncios') }}" class="sidebar-link"><i class="fas fa-bullhorn"></i> Anuncios</a>
                                @endif
                            </nav>

                            {{-- CONTENIDO PRINCIPAL A LA DERECHA --}}
                            <main class="content-area">
                                @yield('content')
                            </main>
                        </div>
                        </div>
                </div>

                @else
                    <!-- Vista con columnas laterales -->
                    <div class="row">
                        <!-- LATERAL IZQUIERDO -->
                        <div class="col-md-2 p-0" style="
                            position: relative;
                            background-color: transparent;
                        ">
                            <div style="
                                position: absolute;
                                inset: 0;
                                background: linear-gradient(to right, rgba(51, 51, 153, 1), rgba(0, 255, 204, 0.5));
                                z-index: 1;
                            "></div>
                            <div style="
                                position: absolute;
                                inset: 0;
                                background-image: url('/../images/s2tile_3.png');
                                background-repeat: repeat;
                                background-size: auto;
                                background-position: left;
                                z-index: 2;
                            "></div>
                        </div>

                        <!-- CONTENIDO CENTRAL -->
                        <div class="col-md-8 col-sm-12" style="padding: 0px; background-color: #27295B; min-height:50vh; position: relative; z-index: 3;">
                            @yield('content')
                        </div>


                        <!-- LATERAL DERECHO -->
                        <div class="col-md-2 p-0" style="
                            position: relative;
                            background-color: transparent;
                        ">
                            <div style="
                                position: absolute;
                                inset: 0;
                                background: linear-gradient(to left, rgba(51, 51, 153, 1), rgba(0, 255, 204, 0.5));
                                z-index: 1;
                            "></div>
                            <div style="
                                position: absolute;
                                inset: 0;
                                background-image: url('/../images/s2tile_3.png');
                                background-repeat: repeat;
                                background-size: auto;
                                background-position: right;
                                z-index: 2;
                            "></div>
                        </div>
                    </div>


                @endif
            </div>

            <!-- Botón flotante para suscripciones -->
            <div class="subscription-button" id="subscriptionButton" onclick="window.location.href='{{ route('planes.index') }}'">
                <i class="fas fa-credit-card"></i>
                <span class="d-none d-md-inline font-weight-bold" style="color: #27295B">SUSCRIPCIONES</span>
                <div class="subscription-tooltip">
                    <h4>¡Suscríbete y accede a contenido exclusivo!</h4>
                    <p>Obtén acceso a beneficios adicionales y contenido exclusivo con nuestras suscripciones de nivel 1, 2 y 3.</p>
                </div>
            </div>
            <!-- Carrito flotante -->
            @if ($cantidadCarrito != 0)
                <a href="{{ route('carrito.show') }}" style="position: fixed;bottom: 100px;right: 20px;background: #28a745;color: white;padding: 12px 16px;border-radius: 50%;text-align: center;box-shadow: 0 4px 6px rgba(0,0,0,0.2);z-index: 1000;font-size: 18px;text-decoration: none;">
                🛒
                <span style="position: absolute;top: -5px;right: -5px;background: red;color: white;border-radius: 50%;width: 20px;height: 20px;font-size: 12px;line-height: 20px;">
                    {{ $cantidadCarrito ?? 0 }}
                </span>
            </a>
            @endif

        </main>

        <!-- Footer -->
        <footer class="command-footer">
    <div class="container">
        <div class="row gy-4">

            {{-- COLUMNA 1: SOBRE NOSOTROS (MISIÓN) --}}
            <div class="col-lg-4 col-md-6 pe-lg-5">
                <h5 class="footer-heading">Misión SBBL</h5>
                <p class="footer-desc">
                    La <strong>Spanish BeyBattle League</strong> es la organización central para todos los bladers en España.
                    Nuestro objetivo es unificar la comunidad, organizar torneos oficiales y crear un entorno competitivo y divertido para todos.
                </p>
                <div class="social-icons-footer mt-3">
                    <a href="https://discord.gg/JCtAHfJ8Ht" target="_blank" title="Discord"><i class="fab fa-discord"></i></a>
                    <a href="https://www.instagram.com/sbbl_oficial/" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.youtube.com/@sbbl_oficial" target="_blank" title="Youtube"><i class="fab fa-youtube"></i></a>
                    <a href="https://x.com/SBBLOficial" target="_blank" title="X (Twitter)"><i class="fab fa-twitter"></i></a>
                </div>
            </div>

            {{-- COLUMNA 2: NAVEGACIÓN PRINCIPAL --}}
            <div class="col-lg-2 col-md-3 col-6">
                <h5 class="footer-heading">Navegación</h5>
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
                <h5 class="footer-heading">Competitivo</h5>
                <ul class="footer-links">
                    <li><a href="{{ route('inicio.events') }}">Calendario de Eventos</a></li>
                    <li><a href="{{ route('profiles.ranking') }}">Rankings Oficiales</a></li>
                    <li><a href="{{ route('profiles.splits') }}">Splits & Temporadas</a></li>
                    <li><a href="{{ route('versus.all') }}">Historial de Duelos</a></li>
                    <li><a href="{{ route('inicio.rules') }}">Reglamento Oficial</a></li>
                </ul>
            </div>

            {{-- COLUMNA 4: ENLACES RÁPIDOS (Lo que me pasaste extra) --}}
            <div class="col-lg-3 col-md-12">
                <h5 class="footer-heading">Accesos Directos</h5>
                <ul class="footer-links">
                    <li><a href="{{ route('inicio.halloffame') }}" class="text-warning"><i class="fas fa-trophy me-1"></i> Salón de la Fama</a></li>
                    <li><a href="{{ route('inicio.resumen_semanal') }}"><i class="fas fa-bullseye me-1"></i> Resumen Semanal</a></li>
                    <li><a href="{{ route('inicio.stats') }}"><i class="fas fa-chart-bar me-1"></i> Estadísticas Globales</a></li>
                    @guest
                        <li class="mt-2"><a href="{{ route('register') }}" class="btn btn-outline-light btn-sm rounded-pill px-3">Únete a la Batalla</a></li>
                    @endguest
                </ul>
            </div>

        </div>

        {{-- BARRA DE COPYRIGHT --}}
        <div class="copyright-bar row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                &copy; {{ date('Y') }} <strong>Spanish BeyBattle League</strong>. Todos los derechos reservados.
            </div>
            <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                <small>Diseñado por la comunidad para la comunidad.</small>
            </div>
        </div>
    </div>
</footer>
    </div>

    <!-- Banner de Cookies Actualizado -->
    <div id="cookie-banner" class="cookie-banner hidden">
        <p>Utilizamos cookies propias y de terceros para analizar el tráfico, personalizar contenidos y anuncios.
           <a href="{{ route('politica.cookies') }}" target="_blank">Más información</a></p>
        <div class="cookie-buttons">
            <button onclick="acceptOnlyNecessary()">Rechazar</button>
            <button onclick="showSettings()">Personalizar</button>
            <button onclick="acceptAllCookies()">Aceptar todas</button>
        </div>
    </div>

    <!-- Modal de configuración Actualizado -->
    <div id="cookie-settings-modal" class="cookie-modal hidden">
        <div class="cookie-modal-content">
            <h3>Preferencias de cookies</h3>
            <p>Selecciona las cookies que deseas aceptar:</p>

            <div class="cookie-category">
                <label>
                    <input type="checkbox" id="necessaryCookies" checked disabled>
                    Cookies necesarias
                    <small>(Siempre activas, son esenciales para el funcionamiento del sitio)</small>
                </label>
            </div>

            <div class="cookie-category">
                <label>
                    <input type="checkbox" id="analyticsCookies">
                    Cookies analíticas
                    <small>(Nos ayudan a entender cómo usas el sitio mediante Google Analytics)</small>
                </label>
            </div>

            <div class="cookie-category">
                <label>
                    <input type="checkbox" id="marketingCookies">
                    Cookies de marketing
                    <small>(Para mostrar anuncios relevantes mediante Google AdSense y medir su eficacia)</small>
                </label>
            </div>

            <div class="cookie-buttons">
                <button onclick="saveSettings()">Guardar preferencias</button>
                <button onclick="cookieModal.classList.add('hidden')">Cancelar</button>
            </div>
        </div>
    </div>
<!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Font Awesome 6 (recomendado) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<!-- jQuery primero -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Bundle con Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Select2 -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<!-- Scripts Actualizados -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
<script>
    // Función para descargar imágenes
    function downloadImage() {
        const element = document.getElementById('wrapped');
        html2canvas(element, {
            useCORS: true,
            onrendered: function(canvas) {
                const link = document.createElement('a');
                link.download = 'SBBL_Wrapped.png';
                link.href = canvas.toDataURL();
                link.click();
            }
        });
    }

    // Cookies - Nuevas funciones
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
