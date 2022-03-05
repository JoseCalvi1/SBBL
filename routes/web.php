<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [App\Http\Controllers\InicioController::class, 'index'])->name('inicio.index');

Auth::routes();

Route::get('/home', [App\Http\Controllers\InicioController::class, 'index'])->name('inicio.index');
Route::get('/all-events', [App\Http\Controllers\InicioController::class, 'events'])->name('inicio.events');
Route::get('/random-combo-generator', [App\Http\Controllers\InicioController::class, 'combo'])->name('inicio.combo');
Route::get('/rules', [App\Http\Controllers\InicioController::class, 'rules'])->name('inicio.rules');

Route::get('/events', [App\Http\Controllers\EventController::class, 'index'])->name('events.index');
Route::get('/events/create', [App\Http\Controllers\EventController::class, 'create'])->name('events.create');
Route::get('/events/{event}', [App\Http\Controllers\EventController::class, 'show'])->name('events.show');
Route::post('/events', [App\Http\Controllers\EventController::class, 'store'])->name('events.store');
Route::get('/events/{event}/edit', [App\Http\Controllers\EventController::class, 'edit'])->name('events.edit');
Route::put('/events/{event}', [App\Http\Controllers\EventController::class, 'update'])->name('events.update');
Route::delete('/events/{event}', [App\Http\Controllers\EventController::class, 'destroy'])->name('events.destroy');

Route::get('/versus', [App\Http\Controllers\VersusController::class, 'index'])->name('versus.index');
Route::get('/versus/create', [App\Http\Controllers\VersusController::class, 'create'])->name('versus.create');
Route::get('/versus/{duel}', [App\Http\Controllers\VersusController::class, 'show'])->name('versus.show');
Route::post('/versus', [App\Http\Controllers\VersusController::class, 'store'])->name('versus.store');
Route::get('/versus/{duel}/edit', [App\Http\Controllers\VersusController::class, 'edit'])->name('versus.edit');
Route::put('/versus/{duel}', [App\Http\Controllers\VersusController::class, 'update'])->name('versus.update');

Route::get('/profiles', [App\Http\Controllers\ProfileController::class, 'index'])->name('profiles.index');
Route::get('/profiles/{profile}', [App\Http\Controllers\ProfileController::class, 'show'])->name('profiles.show');
Route::get('/profiles/{profile}/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profiles.edit');
Route::put('/profiles/{profile}', [App\Http\Controllers\ProfileController::class, 'update'])->name('profiles.update');


Route::post('/videos', [App\Http\Controllers\VideoController::class, 'store'])->name('videos.store');
