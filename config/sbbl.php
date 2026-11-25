<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fecha de inicio de la temporada activa
    |--------------------------------------------------------------------------
    |
    | Fecha base (YYYY-MM-DD) utilizada para filtrar torneos, estadísticas
    | y rankings correspondientes a la temporada competitiva en curso.
    |
    */
    'season_start' => env('SBBL_SEASON_START', '2026-09-01'),

    /*
    |--------------------------------------------------------------------------
    | Fecha de corte del nuevo cálculo de Blader del Mes (Salón de la Fama)
    |--------------------------------------------------------------------------
    |
    | Los meses anteriores a esta fecha mantienen el histórico original basado
    | en tournament_results. A partir de este mes (inclusive), el ganador
    | mensual se calcula con los puntos ganados en torneos de ranking (points_log).
    |
    */
    'blader_month_v2_start' => env('SBBL_BLADER_MONTH_V2_START', '2026-11-01'),

    /*
    |--------------------------------------------------------------------------
    | Token de seguridad para tareas programadas (Cron) y mantenimiento
    |--------------------------------------------------------------------------
    */
    'cron_token' => env('SBBL_CRON_TOKEN'),
];
