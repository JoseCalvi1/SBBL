# Contexto y Arquitectura del Proyecto: SBBL (Spanish BeyBattle League)

## Stack Tecnológico e Infraestructura
- **Framework:** Laravel + plantillas Blade + JavaScript vanilla / Alpine.
- **Base de Datos:** MySQL con integridad referencial estricta (claves foráneas activas).
- **Despliegue y Subdominios (OVH):**
  - Web principal: `sbbl.es`
  - Centro operativo / software hub: `hub.sbbl.es`
  - Ecosistema: `fantasy.sbbl.es`, `world.sbbl.es`
  - Al tocar `routes/web.php`, respetar estrictamente los grupos de dominio (`Route::domain(...)`) para no romper el enrutamiento entre `sbbl.es` y `hub.sbbl.es`.

## Dominio y Reglas de Negocio Críticas
1. **Motores de Torneo y Arena en Tiempo Real:**
   - Soportar formatos **Suizo (Swiss)** (con lógica de desempate y emparejamientos sin repetición) y **Doble Eliminación (Double Elimination)**.
   - Controles de arena en vivo: priorizar consultas ligeras y transacciones atómicas en BD para evitar condiciones de carrera durante eventos presenciales.
2. **Sistema de Puntuación Oficial:**
   - Tipos de finalización y puntuación: `spin` (1 pt), `over` (2 pts), `burst` (2 pts), `xtreme` (3 pts).
   - Respetar los límites configurables de escalado de puntos (1 a 3).
3. **Módulo BeyLab (Analítica y Estadísticas):**
   - Matrices de comparación de combos, desglose por tipo de finalización, tier lists y métricas de rendimiento por jugador.
   - **Filtro de temporada:** Las estadísticas y rankings activos filtran por la temporada iniciada el `2026-09-01` salvo que se solicite histórico global.
4. **Roles e Integración con Discord:**
   - Jerarquía de permisos: `admin`, `judge` (árbitro), `staff`, `reviewer`.
   - Webhooks de Discord: las notificaciones automáticas mapean menciones por comunidad/región usando la configuración `DISCORD_ROLES`.

## Convenciones de Desarrollo
- **Integridad de BD:** Antes de sugerir borrados o modificaciones de esquema en torneos, partidas o participantes, verificar restricciones `FOREIGN KEY` y cascadas.
- **Consultas de Ranking:** Al contar participantes únicos por región o provincia, asegurar `COUNT(DISTINCT user_id)` filtrado por torneos válidos de ranking y fecha de temporada.
- **Estilos:** Mantener coherencia con el sistema visual oscuro/competitivo existente en Blade sin introducir frameworks frontend pesados innecesarios.
