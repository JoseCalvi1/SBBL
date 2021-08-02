<?php

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

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/profiles/{profile}', [App\Http\Controllers\ProfileController::class, 'show'])->name('profiles.show');
Route::get('/profiles/{profile}/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profiles.edit');
Route::put('/profiles/{profile}', [App\Http\Controllers\ProfileController::class, 'update'])->name('profiles.update');

