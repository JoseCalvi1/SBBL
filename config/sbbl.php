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
];
