const CACHE_NAME = 'sbbl-cyber-cache-v2';

// Recursos estáticos mínimos para instalar (tu CSS, JS principal)
const urlsToCache = [
    '/js/app.js',
    '/manifest.json'
];

// Fase de Instalación
self.addEventListener('install', event => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(urlsToCache))
    );
});

// Fase de Activación (Limpiar cachés viejos)
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Fase de Peticiones (Estrategia: Network First)
self.addEventListener('fetch', event => {
    // Excluimos las llamadas a la API de Laravel de la caché para no guardar datos fantasma
    if (event.request.url.includes('/api/') || event.request.method !== 'GET') {
        return;
    }

    event.respondWith(
        fetch(event.request).catch(() => {
            return caches.match(event.request);
        })
    );
});
