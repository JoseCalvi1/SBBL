<?php

use App\Http\Controllers\HubController;
use Illuminate\Support\Facades\Route;

// Dominio base dinámico (compatible con entorno local y producción)
$dominio = env('APP_DOMAIN', 'sbbl.es');

/*
|--------------------------------------------------------------------------
| SUBDOMINIO: SBBL HUB PWA (hub.sbbl.es)
|--------------------------------------------------------------------------
*/
Route::domain('hub.' . $dominio)->middleware('auth')->group(function () {
    Route::get('/', [HubController::class, 'index'])->name('hub.index');
    Route::get('/home', fn() => redirect('/'));
});
