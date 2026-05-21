<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SBBL Fantasy</title>

    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0b0c10">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('// PWA Terminal Activa. Scope:', registration.scope);
                    })
                    .catch(error => {
                        console.error('// Error en la matriz de la PWA:', error);
                    });
            });
        }
    </script>

    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
</head>
<body style="background-color: #1a202c; overscroll-behavior-y: none; user-select: none; -webkit-user-select: none; touch-action: pan-y;">

    <div id="app">
        <fantasy-roster></fantasy-roster>
    </div>

    <div id="cookie-banner" class="cookie-banner hidden" style="position: fixed; bottom: 0; left: 0; width: 100%; background: rgba(11, 12, 16, 0.95); border-top: 2px solid #f2a900; padding: 15px; z-index: 9999; display: none; flex-direction: column; align-items: center; text-align: center; font-family: 'Share Tech Mono', monospace; backdrop-filter: blur(5px);">
        <p class="m-0 mb-3" style="color: #c5c6c7; font-size: 0.9rem; max-width: 800px;">
            ⚠️ <strong>ALERTA DE SISTEMA:</strong> El terminal requiere recolectar datos de combate (cookies) para funcionar a máxima potencia y financiar los servidores de la SBBL. <a href="{{ route('politica.cookies') }}" target="_blank" style="color: #66fcf1; text-decoration: underline;">Leer manifiesto de datos</a>.
        </p>
        <div class="cookie-buttons" style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: center;">
            <button onclick="acceptOnlyNecessary()" style="background: transparent; color: #ff0055; border: 1px solid #ff0055; padding: 8px 15px; border-radius: 4px; font-weight: bold; text-transform: uppercase;">Rechazar</button>
            <button onclick="showSettings()" style="background: #1f2833; color: #c5c6c7; border: 1px solid #45a29e; padding: 8px 15px; border-radius: 4px; text-transform: uppercase;">Ajustes</button>
            <button onclick="acceptAllCookies()" style="background: #f2a900; color: #000; border: none; padding: 8px 20px; border-radius: 4px; font-weight: bold; text-transform: uppercase; box-shadow: 0 0 10px rgba(242, 169, 0, 0.4);">ACEPTAR PROTOCOLO</button>
        </div>
    </div>

    <div id="cookie-settings-modal" class="cookie-modal hidden" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.85); z-index: 10000; display: none; align-items: center; justify-content: center;">
        <div class="cookie-modal-content" style="background: #12141a; border: 1px solid #45a29e; border-top: 4px solid #66fcf1; padding: 25px; border-radius: 8px; width: 90%; max-width: 500px; color: #c5c6c7; font-family: 'Share Tech Mono', monospace;">
            <h3 style="color: #66fcf1; margin-top: 0;">// PROTOCOLOS DE DATOS</h3>
            <p style="margin-bottom: 20px; font-size: 0.9rem;">Selecciona los permisos de acceso de tu terminal:</p>

            <div class="cookie-category" style="background: #0b0c10; padding: 10px; border-left: 3px solid #f2a900; margin-bottom: 10px;">
                <label style="color: #fff; font-weight: bold;"><input type="checkbox" id="necessaryCookies" checked disabled style="accent-color: #f2a900;"> Núcleo del Sistema (Obligatorio)</label>
                <div style="font-size: 0.75rem; color: #45a29e; margin-top: 5px;">Esenciales para la encriptación y el funcionamiento del Fantasy.</div>
            </div>

            <div class="cookie-category" style="background: #0b0c10; padding: 10px; border-left: 3px solid #66fcf1; margin-bottom: 10px;">
                <label style="color: #fff; font-weight: bold;"><input type="checkbox" id="analyticsCookies" style="accent-color: #66fcf1;"> Escáner Analítico (Google Analytics)</label>
                <div style="font-size: 0.75rem; color: #45a29e; margin-top: 5px;">Nos ayudan a rastrear el rendimiento del servidor y uso del sitio.</div>
            </div>

            <div class="cookie-category" style="background: #0b0c10; padding: 10px; border-left: 3px solid #e0129a; margin-bottom: 20px;">
                <label style="color: #fff; font-weight: bold;"><input type="checkbox" id="marketingCookies" style="accent-color: #e0129a;"> Transmisiones Comerciales (AdSense)</label>
                <div style="font-size: 0.75rem; color: #45a29e; margin-top: 5px;">Permite proyectar hologramas comerciales para financiar la liga.</div>
            </div>

            <div class="cookie-buttons" style="display: flex; justify-content: flex-end; gap: 10px;">
                <button onclick="document.getElementById('cookie-settings-modal').classList.add('hidden')" style="background: transparent; color: #c5c6c7; border: 1px solid #c5c6c7; padding: 8px 15px; border-radius: 4px;">CANCELAR</button>
                <button onclick="saveSettings()" style="background: #66fcf1; color: #000; border: none; padding: 8px 15px; border-radius: 4px; font-weight: bold;">CONFIRMAR PROTOCOLO</button>
            </div>
        </div>
    </div>

    <script src="{{ mix('js/app.js') }}"></script>

    <script>
        const cookieBanner = document.getElementById('cookie-banner');
        const cookieModal = document.getElementById('cookie-settings-modal');
        window.dataLayer = window.dataLayer || [];
        function gtag(){ dataLayer.push(arguments); }
        gtag('consent', 'default', { 'ad_storage': 'denied', 'analytics_storage': 'denied' });

        document.addEventListener('DOMContentLoaded', () => {
            const consent = localStorage.getItem("cookieConsent");
            if (!consent) {
                cookieBanner.classList.remove("hidden");
                // Pequeño script para evitar que Vue sobreescriba display: none;
                cookieBanner.style.display = 'flex';
            } else {
                cookieBanner.style.display = 'none';
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
        function acceptOnlyNecessary() { saveCookiePreferences(false, false); hideBanners(); trackCookieEvent('reject', 'all'); }
        function acceptAllCookies() { saveCookiePreferences(true, true); hideBanners(); trackCookieEvent('accept', 'all'); }

        function hideBanners() {
            cookieBanner.classList.add("hidden");
            cookieBanner.style.display = 'none';
            cookieModal.classList.add("hidden");
            cookieModal.style.display = 'none';
        }

        function showSettings() {
            const consent = localStorage.getItem("cookieConsent");
            if (consent) { setCheckboxStates(JSON.parse(consent)); }
            cookieModal.classList.remove("hidden");
            cookieModal.style.display = 'flex';
        }

        function saveSettings() {
            const analytics = document.getElementById('analyticsCookies').checked;
            const marketing = document.getElementById('marketingCookies').checked;
            saveCookiePreferences(analytics, marketing);
            hideBanners();
        }

        function saveCookiePreferences(analytics, marketing) {
            const consent = { analytics, marketing, date: new Date().toISOString() };
            localStorage.setItem("cookieConsent", JSON.stringify(consent));
            updateConsent(analytics, marketing);
            if (analytics) loadAnalytics();
            if (marketing) loadMarketing();
            trackCookieEvent('update', `analytics:${analytics},marketing:${marketing}`);
        }

        function updateConsent(analytics, marketing) {
            gtag('consent', 'update', { 'ad_storage': marketing ? 'granted' : 'denied', 'analytics_storage': analytics ? 'granted' : 'denied' });
        }

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
                        'allow_ad_personalization_signals': localStorage.getItem("cookieConsent") ? JSON.parse(localStorage.getItem("cookieConsent")).marketing : false
                    });
                };
            }
        }

        function loadMarketing() {
            if (!window.adsenseLoaded) {
                window.adsenseLoaded = true;
                const adsenseScript = document.createElement('script');
                adsenseScript.src = "https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7050675485532592";
                adsenseScript.async = true;
                adsenseScript.crossOrigin = "anonymous";
                document.head.appendChild(adsenseScript);
                adsenseScript.onload = () => {
                    (window.adsbygoogle = window.adsbygoogle || []).push({ google_ad_client: "ca-pub-7050675485532592", enable_page_level_ads: true });
                };
            }
        }

        function trackCookieEvent(action, type) {
            if (typeof gtag === 'function') {
                gtag('event', 'cookie_consent', { 'event_category': 'cookies', 'event_label': `${action}_${type}` });
            }
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Empujamos un estado fantasma al historial inicial
            window.history.pushState({ noExit: true }, "");

            // Escuchamos cuando el usuario intenta usar el botón 'Atrás' o el gesto de volver
            window.addEventListener('popstate', function(event) {
                // Bloqueamos la salida volviendo a inyectar el estado fantasma
                window.history.pushState({ noExit: true }, "");
                console.log("// Sistema: Gesto de retroceso bloqueado.");

                // (Opcional) Si quieres ser muy pro, aquí podrías decirle a tu Vue
                // que cambie a la pestaña 'home' en lugar de simplemente no hacer nada.
            });
        });
    </script>
</body>
</html>
