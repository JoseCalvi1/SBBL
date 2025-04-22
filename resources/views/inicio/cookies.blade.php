@extends('layouts.app')

@section('content')
<div class="container mt-5 text-white">
    <h1>Política de Cookies</h1>

    <p>En <strong>Spanish BeyBattle League</strong> utilizamos cookies para garantizar el correcto funcionamiento del sitio web y mejorar la experiencia del usuario.</p>

    <h3>¿Qué son las cookies?</h3>
    <p>Las cookies son pequeños archivos de texto que se almacenan en tu dispositivo cuando visitas un sitio web. Se utilizan para recordar tus preferencias, ofrecerte contenido personalizado y obtener estadísticas sobre el uso del sitio.</p>

    <h3>¿Qué tipos de cookies utilizamos?</h3>
    <ul>
        <li><strong>Cookies necesarias:</strong> Son esenciales para el funcionamiento del sitio web. Sin ellas, no podrías navegar por la web ni utilizar algunas de sus funciones.</li>
        <li><strong>Cookies de análisis:</strong> Nos permiten conocer cómo interactúan los usuarios con nuestro sitio web y así mejorar nuestros servicios.</li>
        <li><strong>Cookies de terceros:</strong> Algunas páginas pueden mostrar contenido de terceros (como vídeos de YouTube o botones de redes sociales), lo cual puede instalar cookies de esos servicios.</li>
    </ul>

    <h3>¿Cómo puedes gestionar las cookies?</h3>
    <p>Puedes permitir, bloquear o eliminar las cookies instaladas en tu dispositivo desde la configuración de tu navegador. Ten en cuenta que al desactivar las cookies, algunas funcionalidades de esta web podrían no funcionar correctamente.</p>

    <h3>Consentimiento</h3>
    <p>Al continuar navegando en este sitio web, aceptas el uso de cookies conforme a esta política. Puedes cambiar tu configuración de cookies en cualquier momento.</p>

    <h3>Más información</h3>
    <p>Si tienes dudas sobre nuestra política de cookies, puedes contactarnos en <a href="mailto:admin@sbbl.com" class="text-info">admin@sbbl.com</a>.</p>

    <a href="{{ url('/') }}" class="btn btn-warning mt-3">Volver al inicio</a>
</div>
@endsection
