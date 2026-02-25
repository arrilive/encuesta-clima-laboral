<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EncuestaController;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

// Sprint 2 - Rutas públicas de encuesta
Route::get('/encuesta', [EncuestaController::class, 'bienvenida'])->name('encuesta.bienvenida');
Route::post('/encuesta/acceso', [EncuestaController::class, 'acceso'])->name('encuesta.acceso');
Route::get('/encuesta/{token}', [EncuestaController::class, 'demograficos'])->name('encuesta.demograficos');
Route::post('/encuesta/{token}/demograficos', [EncuestaController::class, 'guardarDemograficos'])->name('encuesta.demograficos.guardar');
