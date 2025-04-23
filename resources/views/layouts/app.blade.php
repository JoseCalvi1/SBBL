<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @yield('head')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <link rel="shortcut icon" type="image/png" href="{{ asset('/images/sbbl.png') }}">

    @yield('styles')
    <style>
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
            z-index: 1000;
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
            z-index: 1001;
        }

        .subscription-button:hover .subscription-tooltip {
            display: block;
        }

        .hidden { display: none !important; }
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
            z-index: 1000;
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
            z-index: 1001;
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
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md shadow-sm" style="background-color: #1e2a47;">
            <div class="container">
                <a class="navbar-brand d-none d-sm-none d-md-block" href="{{ url('/') }}">
                    <img src="/images/logo_new.png" alt="Logo Spanish BeyBattle League" width="60" height="50">
                    <span style="color: white;">Spanish BeyBattle League</span>
                </a>
                <a class="navbar-brand d-block d-sm-block d-md-none" href="{{ url('/') }}">
                    <img src="/images/logo_new.png" alt="Logo SBBL" width="60" height="50">
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon" style="color:white"><i class="fas fa-bars" style="font-size:2em;"></i></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto"></ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto" style="color: white;">
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
                                <a class="nav-link" href="{{ route('equipos.index') }}" style="color: white;">
                                    {{ 'EQUIPOS' }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="https://discord.gg/JCtAHfJ8Ht" style="color: white;">
                                    {{ 'DISCORD' }}
                                </a>
                            </li>

                            @auth
                                @if (Auth::user()->is_referee || Auth::user()->is_admin)
                                    <li class="nav-item">
                                        <a id="navbarAdmin" style="color: white;" class="nav-link" href="{{ route('admin.dashboard') }}">
                                            {{ 'ADMIN' }}
                                        </a>
                                    </li>
                                @endif
                            @endauth

                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" style="color: white;" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ strtoupper(Auth::user()->name) }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" style="background-color: #283b63" aria-labelledby="navbarDropdown">
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
                @else
                    <!-- Vista con columnas laterales -->
                    <div class="row">
                        <div class="col-md-2" style="background-image: linear-gradient(to right, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.5)), url('/../images/webTile2.png'); background-size: auto; background-position: left;"></div>
                        <div class="col-md-8 col-sm-12" style="padding: 0px; background-color: #27295B;">
                            @yield('content')
                        </div>
                        <div class="col-md-2" style="background-image: linear-gradient(to left, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.5)), url('/../images/webTile2.png'); background-size: auto; background-position: right;"></div>
                    </div>
                @endif
            </div>

            <!-- Botón flotante para suscripciones -->
            <div class="subscription-button" id="subscriptionButton" onclick="window.location.href='{{ route('subscriptions') }}'">
                <i class="fas fa-credit-card"></i>
                <span class="d-none d-md-inline font-weight-bold" style="color: #27295B">SUSCRIPCIONES</span>
                <div class="subscription-tooltip">
                    <h4>¡Suscríbete y accede a contenido exclusivo!</h4>
                    <p>Obtén acceso a beneficios adicionales y contenido exclusivo con nuestras suscripciones de nivel 1, 2 y 3.</p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer style="background-color:rgb(119, 120, 120)">
            <div class="container">
                <div class="row text-white">
                    <div class="col-md-6 col-sm-12 p-5">
                        <h3>Sobre nosotros</h3>
                        <p>Bienvenidos a la Spanish BeyBattle League, o más sencillo, la SBBL.</p>
                        <p>La SBBL es una liga/organización creada con el objetivo de reunir a todos los bladers residentes en España dentro de una comunidad más grande y fuerte.</p>
                        <p>Con esto intentaríamos conseguir llevar a cabo mayor número de eventos, quedadas y torneos en todo nuestro país.</p>
                        <p>Si eres blader, resides en España y buscas una liga donde competir y disfrutar del beyblade con otr@s como tú, este es tu sitio.</p>
                        <p>¡Únete a la SBBL!</p>
                    </div>
                    <div class="col-md-3 col-sm-12 p-5">
                        <h3>Categorías</h3>
                        <ul class="navbar-nav ml-auto">
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('inicio.events') }}">
                                    {{ 'Eventos' }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('profiles.index') }}">
                                    {{ 'Bladers' }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('versus.all') }}">
                                    {{ 'Duelos' }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('equipos.index') }}">
                                    {{ 'Equipos' }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-3 col-sm-12 p-5">
                        <h3>Links útiles</h3>
                        <ul class="navbar-nav ml-auto">
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('inicio.rules') }}">
                                    {{ 'Reglamento' }}
                                </a>
                            </li>
                            @auth
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('inicio.contact') }}">
                                    {{ 'Contacta con nosotros' }}
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </div>
                    <div class="col-md-12 text-center pt-4 pb-2 border-top">
                        <div class="row">
                            <div class="col-md-6 col-sm-12 font-weight-bold">
                                Copyright 2022 - Spanish BeyBattle League
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="rrss text-center">
                                            <a style="display: inline-block; font-size:1.2em; font-weight: bold; text-decoration:none; color: white;" target="_blank" href="https://discord.gg/JCtAHfJ8Ht">Discord <i class="fab fa-discord" style="font-size:1em;"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="rrss text-center">
                                            <a style="display: inline-block; font-size:1.2em; font-weight: bold; text-decoration:none; color: white;" target="_blank" href="https://www.instagram.com/sbbl_oficial/">Instagram <i class="fab fa-instagram" style="font-size:1em;"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="rrss text-center">
                                            <a style="display: inline-block; font-size:1.2em; font-weight: bold; text-decoration:none; color: white;" target="_blank" href="https://www.youtube.com/@sbbl_oficial">Youtube <i class="fab fa-youtube" style="font-size:1em;"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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

    @yield('scripts')
</body>
</html>
