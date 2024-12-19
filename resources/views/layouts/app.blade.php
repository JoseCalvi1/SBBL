<!doctype html>
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

     <!-- AdSense -->
     <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7050675485532592"
     crossorigin="anonymous"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <link rel="shortcut icon" type="image/png" href="{{ asset('/images/sbbl.png') }}">

    <!-- Global site tag (gtag.js) - Google Analytics -->
   <script async src="https://www.googletagmanager.com/gtag/js?id=G-KSK91ZXWE3"></script>
   <script>
     window.dataLayer = window.dataLayer || [];
     function gtag(){dataLayer.push(arguments);}
     gtag('js', new Date());

     gtag('config', 'G-KSK91ZXWE3');
   </script>

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

    </style>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md shadow-sm"  style=" background-color: #1e2a47;">
            <div class="container">
                <a class="navbar-brand d-none d-sm-none d-md-block" href="{{ url('/') }}">
                    <img src="/images/logo_new.png" alt="Logo" width="60" height="50">
                    <span style="font-size: 0.8em; color: white;">Spanish BeyBattle League</span>
                </a>
                <a class="navbar-brand d-block d-sm-block d-md-none" href="{{ url('/') }}">
                    <img src="/images/logo_new.png" alt="Logo" width="60" height="50">
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon" style="color:white"><i class="fas fa-bars" style="font-size:2em;"></i></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

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
                        <!--<li class="nav-item">
                            <a class="nav-link" href="{{ route('inicio.index') }}">
                                {{ 'INICIO' }}
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('inicio.events') }}">
                                {{ 'EVENTOS' }}
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('versus.all') }}">
                                {{ 'DUELOS' }}
                            </a>
                        </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('profiles.index') }}">
                                    {{ 'BLADERS' }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('profiles.ranking') }}">
                                    {{ 'RANKINGS' }}
                                </a>
                            </li>-->



                            <!--<li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ 'GENERATIONS' }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="nav-link" href="{{ route('generations.index') }}">
                                        {{ 'RANKING' }}
                                    </a>

                                    <a class="nav-link" href="{{ route('generations.versus') }}">
                                        {{ 'DUELOS' }}
                                    </a>

                                </div>
                            </li>-->

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('equipos.index') }}" style="color: white;">
                                {{ 'EQUIPOS' }}
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('mercado.index') }}" style="color: white;">
                                {{ 'MERCADO' }}
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="https://discord.gg/JCtAHfJ8Ht" style="color: white;">
                                {{ 'DISCORD' }}
                            </a>
                        </li>

                    @if (Auth::user()->is_referee)
                        <li class="nav-item">
                            <a id="navbarAdmin" style="color: white;" class="nav-link" href="{{ route('admin.dashboard') }}">
                                {{ 'ADMIN' }}
                            </a>
                        </li>
                    @endif
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
                <div class="row">
                    <div class="col-md-2" style="background-image: linear-gradient(to right, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.5)), url('/../images/webTile2.png'); background-size: auto; background-position: left;"></div>
                    <div class="col-md-8 col-sm-12" style="padding: 0px; background-color: #27295B;">
                        @yield('content')
                    </div>
                    <div class="col-md-2" style="background-image: linear-gradient(to left, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.5)), url('/../images/webTile2.png'); background-size: auto; background-position: right;"></div>
                </div>
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

    </div>
    <!-- En tu archivo layouts.app, antes de </body> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
<script>
    function downloadImage() {
        const element = document.getElementById('wrapped');
        html2canvas(element, {
            useCORS: true,  // Habilita el soporte para imágenes cruzadas
            onrendered: function(canvas) {
                const link = document.createElement('a');
                link.download = 'SBBL_Wrapped.png';
                link.href = canvas.toDataURL();
                link.click();
            }
        });
    }
</script>

</body>
<footer style="background-color:rgb(119, 120, 120)">
    <div class="container">
        <div class="row text-white">
            <div class="col-md-6 col-sm-12 p-5">
                <h3>Sobre nosotros</h3>
                <br>Bienvenidos a la Spanish BeyBattle League, o más sencillo, la SBBL.

                <br><br>La SBBL es una liga/organización creada con el objetivo de reunir a todos los bladers residentes en España dentro de una comunidad más grande y fuerte.

                Con esto intentaríamos conseguir llevar a cabo mayor número de eventos, quedadas y torneos en todo nuestro país.

                <br><br>Si eres blader, resides en España y buscas una liga donde competir y disfrutar del beyblade con otr@s como tú, este es tu sitio.

                <br><br>!Únete a la SBBL!</p>
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
                    @if (isset(Auth::user()->id))
                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('inicio.contact') }}">
                            {{ 'Contacta con nosotros' }}
                        </a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('inicio.privacy') }}">
                            {{ 'Política de privacidad' }}
                        </a>
                    </li>
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
@yield('scripts')
</html>
