# Contexto y Arquitectura del Proyecto: SBBL (Spanish BeyBattle League)

## Stack Tecnológico e Infraestructura
- **Framework:** Laravel 8 (`^8.40`) + plantillas Blade + JavaScript vanilla / Alpine (`public/js/hub.js`, `public/js/stats.js` con fuentes en `resources/js/`).
- **Base de Datos:** MySQL con integridad referencial estricta (claves foráneas activas).
- **Configuración Centralizada (`config/sbbl.php`):** Centraliza `season_start` (`SBBL_SEASON_START`, por defecto `2026-09-01`) y `cron_token` (`SBBL_CRON_TOKEN`) para tareas programadas y mantenimiento.
- **Despliegue, Subdominios (OVH) y Enrutamiento (`routes/`):**
  - Web principal: `sbbl.es` (`routes/public.php`) + `nacional.sbbl.es` (`routes/public.php`).
  - Centro operativo / software hub: `hub.sbbl.es` (`routes/hub.php`).
  - Ecosistema: `conquista.sbbl.es` (`routes/conquest.php`), `fantasy.sbbl.es` (`routes/fantasy.php`), `world.sbbl.es`.
  - Rutas compartidas Web/Hub (`/api/*` y `/cron/*`): `routes/web.php`.
  - Todos los archivos de rutas (`web.php`, `public.php`, `hub.php`, `conquest.php`, `fantasy.php`) se cargan en `app/Providers/RouteServiceProvider.php` bajo el grupo de middleware `web` (sesiones, cookies y protección CSRF). Al modificar rutas, respetar estrictamente los grupos `Route::domain(...)` para no romper el aislamiento entre subdominios.
- **Capa de Servicios (`app/Services/`):**
  - `app/Services/Tournament/`: `SwissEngine.php`, `DoubleEliminationEngine.php` y `StandingsService.php` (cálculo unificado de clasificación y desempates).
  - `app/Services/Analytics/BeyLabService.php`: lógica unificada de BeyLab (Hub + Web), queries parametrizadas, caché de 10 minutos y matriz H2H consolidada.
  - `app/Services/Discord/DiscordNotifier.php`: envío centralizado de webhooks de Discord (con guarda de entorno `production` y mapa `DISCORD_ROLES`).

## Dominio y Reglas de Negocio Críticas
1. **Motores de Torneo y Arena en Tiempo Real:**
   - Soportar formatos **Suizo (Swiss)** (con lógica de desempate y emparejamientos sin repetición) y **Doble Eliminación (Double Elimination)**.
   - Controles de arena en vivo: priorizar consultas ligeras y transacciones atómicas en BD (`DB::transaction` + `lockForUpdate()`) para evitar condiciones de carrera durante eventos presenciales.
   - En `tournament_matches`, usar `winner_id` (FK a `users`) para partidas individuales y `winner_team_id` (FK a `teams`) para partidas por equipos.
2. **Sistema de Puntuación Oficial:**
   - Tipos de finalización y puntuación: `spin` (1 pt), `over` (2 pts), `burst` (2 pts), `xtreme` (3 pts).
   - Respetar los límites configurables de escalado de puntos (1 a 3).
3. **Módulo BeyLab (Analítica y Estadísticas):**
   - Matrices de comparación de combos, desglose por tipo de finalización, tier lists y métricas de rendimiento por jugador.
   - **Filtro de temporada:** Las estadísticas y rankings activos filtran por la temporada configurada en `config('sbbl.season_start')` (`2026-09-01`) salvo que se solicite histórico global.
4. **Roles e Integración con Discord:**
   - Jerarquía de permisos: `admin`, `judge` (árbitro), `staff`, `reviewer` (en BD/código histórico también coexisten `juez`, `arbitro`, `editor`, `revisor`).
   - Webhooks de Discord: las notificaciones automáticas mapean menciones por comunidad/región usando `DiscordNotifier::DISCORD_ROLES` y solo se envían en entorno `production`.

## Convenciones de Desarrollo
- **Integridad de BD:** Antes de sugerir borrados o modificaciones de esquema en torneos, partidas o participantes, verificar restricciones `FOREIGN KEY` y cascadas.
- **Consultas de Ranking:** Al contar participantes únicos por región o provincia, asegurar `COUNT(DISTINCT user_id)` filtrado por torneos válidos de ranking y fecha de temporada.
- **Estilos:** Mantener coherencia con el sistema visual oscuro/competitivo existente en Blade sin introducir frameworks frontend pesados innecesarios.
