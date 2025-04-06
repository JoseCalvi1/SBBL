    @extends('layouts.app')

    @section('styles')
    <style>
        body {
            background-color: #121212;
            color: white;
        }
        .hero {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 100px 20px;
            color: white;
            text-align: center;
            width: 100%;
            min-height: 400px;
            background-image: url('/../images/fondo_banner_nacional.webp') !important;
            background-size: cover;
            background-position: bottom;
            background-repeat: no-repeat;
            overflow: hidden; /* Para evitar que la capa negra sobresalga */
        }

        .hero::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); /* Ajusta la opacidad (0.5 = 50%) */
            z-index: 1;
        }

        .hero * {
            position: relative;
            z-index: 2; /* Asegura que el contenido esté sobre la capa oscura */
        }


        #countdown {
            font-size: 3rem;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            color: #0f0;
            border-radius: 5px;
            text-shadow: 0 0 10px #0f0;
            margin-top: 10px; /* Espacio superior */
        }


        .sponsors {
            text-align: center;
            padding: 50px 20px;
            color: white;
        }

        .sponsors .row {
            display: flex;
            justify-content: center; /* Centra horizontalmente */
            align-items: center; /* Centra verticalmente */
            height: 100%; /* Asegura que la fila ocupe toda la altura disponible */
        }
        .event-info {
            padding: 50px 20px;
            text-align: center;
            color: white;
        }
        .btn-register {
            background-color: #ffcc00;
            color: #121212;
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 5px;
        }
        .requirements-container {
            background-color: #1e1e1e;
            padding: 30px;
            border-radius: 10px;
            margin: 40px auto;
            max-width: 800px;
            color: white;
        }
        @media (max-width: 576px) {
        h2 {
            font-size: 1.5rem; /* Ajusta el tamaño en móviles */
            word-break: break-word; /* Permite cortar palabras largas si es necesario */
        }


    }
    </style>
    @endsection

    @section('content')
    <div class="hero">
        <h1>Gran Copa Nacional de Beyblade X</h1>
        <h2 class="countdown mt-4" id="countdown"></h2>
        <h4>¡Únete a la batalla definitiva el 7 de julio!</h4>
    </div>

    <div class="event-info" id="nacional">
        <div class="col-md-12 text-white text-center p-4" style="border: 1px solid #1e2a47; border-radius: 5px; background:#1e2a47; box-shadow: 0px 0px 10px rgba(255, 255, 255, 0.5)">
            <h2 class="text-lg font-bold mb-2">OBJETIVO NACIONAL SBBL</h2>
            <div class="relative w-full h-6 rounded-full" style="height: 20px; border: 2px solid">
                <div class=" bg-white h-full rounded-full" style="width: 54%; height: 18px"></div>
            </div>
            <p class="text-sm mt-2">1650€ / 3000€</p>

            <div class="mt-2">
                <p><strong>¿Aún no tienes una suscripción?</strong> Consíguela fácilmente haciendo clic en
                    <a href="https://sbbl.es/subscriptions" style="color: #007bff; font-weight: bold;">este enlace</a>.</p>

                <p>También puedes <strong>regalar una suscripción</strong> a un amigo, indicando su nombre de usuario y el nivel que deseas obsequiar.</p>

                <p>Si prefieres <strong>apoyar esta iniciativa</strong>, puedes contribuir directamente
                    <a href="https://www.paypal.com/paypalme/sbbloficial" style="color: #28a745; font-weight: bold;">aquí</a>.</p>
            </div>

            <div class="mt-4">
                <h3 class="text-md font-semibold">Aclaraciones</h3>
                <div class="w-full h-24 bg-gray-100 flex items-center justify-center text-gray-500">
                    <p>El objetivo es para crear el nacional en las instalaciones de Movistar KOI en Madrid con todo tipo de ayudas y herramientas, premios sorprendentes y mucho más con lo que se ha recaudado íntegramente de las suscripciones a la web, el merchandising y otras cosas que iremos anunciando.</p>
                    <p>En el caso de no llegar al objetivo se barajarían diferentes opciones para el desarrollo del torneo pudiendo incluso no ser en Madrid ya que tenemos otras ofertas más asequibles pero también muy buenas.</p>
                    <p>Con todo esto lo que queremos hacer es crear el mayor torneo que llevamos hasta la fecha y que sea una experiencia inolvidable para todos.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="requirements-container">
        <h2 class="mb-4 text-center">Requisitos y plazas para la Gran Copa Nacional 2025</h2>
        <p class="text-justify">
            El próximo 7 de julio se celebrará la Gran Copa Nacional 2025 de la SBBL en las instalaciones de Movistar KOI con motivo del cierre de temporada. Contaremos con una realización profesional y de alto nivel para todo el evento, junto a nuestro propio equipo de comentaristas. La financiación de este torneo nace de vuestras aportaciones a la liga, todo lo recaudado hasta ahora y hasta la realización del evento irá destinado al pago del mismo así como premios muy especiales para los participantes. Contamos con la ayuda de todos, cualquier aportación es bienvenida, por pequeña que sea.
        </p>

        <p class="text-justify">
            Como entenderéis, el número de plazas es limitado, así que se abrirá el día 5 de mayo el formulario de solicitud de participación para la Gran Copa Nacional. Todas las inscripciones se tendrán en cuenta, eligiéndose 80 clasificados bajo los criterios listados en los siguientes puntos. El periodo de inscripción terminará el día 2 de junio.
        </p>

        <h3 class="mt-4">Requisitos mínimos para participar en la Gran Copa Nacional 2025</h3>
        <p>Es necesario cumplir AL MENOS uno de ellos</p>
        <ul>
            <li>Participación mínima en 4 torneos ranking validados en esta temporada</li>
            <li>Realización mínima de 10 duelos validados en esta temporada</li>
            <li>Ser suscriptor de la SBBL</li>
        </ul>

        <h3>Orden de prioridad</h3>
        <p>Debido al límite de 80 plazas, una vez cumplido alguno de los requisitos mínimos se establece el siguiente orden de prioridad:</p>
        <ul>
            <li>Suscripción de la liga (cualquier nivel)</li>
            <li>Realización de algún pedido de merch de la liga o donación</li>
            <li>Número de participaciones en torneos ranking validados esta temporada</li>
            <li>Número de duelos realizados y validados esta temporada</li>
            <li>Antigüedad de la cuenta de SBBL</li>
        </ul>

        <h3>Plazas reservadas para menores de 18</h3>
        <p>Somos conscientes de que muchos participantes menores de edad no pueden colaborar económicamente con la liga. Por ello, se reservarán el 20% de las plazas (16) para aquellos menores de 18 años que hayan quedado fuera de la criba anterior, siguiendo el siguiente orden de prioridad:</p>
            <ul>
                <li>Número de participaciones en torneos ranking validados esta temporada</li>
                <li>Número de duelos realizados y validados esta temporada</li>
                <li>Antigüedad de la cuenta de SBBL</li>
            </ul>

        <p>A los participantes que obtengan su plaza por este medio se les podrá solicitar un documento identificatorio el día del evento para comprobar su edad.</p>

            <h3>Menores de 15 años</h3>
            <p>Los menores de 15 años clasificados tendrán que venir acompañados por un responsable mayor de edad.</p>

    </div>

    <div class="sponsors">
        <h2>Patrocinadores</h2>
        <p>Gracias a nuestros patrocinadores por hacer posible este evento.</p>
        <div class="row justify-content-center">
            <div class="col-md-3">
                <img src="/../images/Movistar_KOIlogo_square.webp" class="img-fluid" alt="Tierra Media">
            </div>
            <div class="col-md-3">
                <img src="/../images/MahouLogo.svg" class="img-fluid" alt="Tierra Media">
            </div>
            <div class="col-md-3">
                <img src="/../images/logotierramedia.png" class="img-fluid" alt="Tierra Media">
            </div>
        </div>
    </div>

    <!--<div class="hero" style="background: red !important">
        <h1>Esta página ha sido aprobada por EXTINTOC</h1>
    </div>-->
    @endsection

    @section('scripts')
    <script>
        function updateCountdown() {
            const eventDate = new Date('2025-07-07T00:00:00').getTime();
            const now = new Date().getTime();
            const diff = eventDate - now;

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            document.getElementById('countdown').innerText = `${days}d ${hours}h ${minutes}m ${seconds}s`;
        }
        setInterval(updateCountdown, 1000);
        updateCountdown();
    </script>
    @endsection
